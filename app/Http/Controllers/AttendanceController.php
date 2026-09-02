<?php

namespace App\Http\Controllers;

use App\Models\CompanySetting;
use App\Models\DailyAttendance;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Holiday;
use App\Services\AuditService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->get('date', now()->format('Y-m-d'));
        $departmentId = $request->get('department_id');
        $user = Auth::user();

        if ($user->isEmployee()) {
            $employee = $this->resolveEmployeeForUser($user);
            $employees = collect([$employee]);

            $existingAttendances = DailyAttendance::where('employee_id', $employee->id)
                ->whereDate('date', $date)
                ->get()
                ->keyBy('employee_id');

            $allDateAttendances = $existingAttendances;
            $stats = [
                'total_active' => 1,
                'present' => $allDateAttendances->where('status', 'present')->count(),
                'absent' => $allDateAttendances->where('status', 'absent')->count(),
                'half_day' => $allDateAttendances->where('status', 'half_day')->count(),
                'leave' => $allDateAttendances->where('status', 'leave')->count(),
                'wfh' => $allDateAttendances->where('status', 'wfh')->count(),
            ];
            $stats['pending'] = max(0, 1 - ($stats['present'] + $stats['absent'] + $stats['half_day'] + $stats['leave'] + $stats['wfh']));
        } else {
            $empQuery = Employee::where('employment_status', 'active')->with('department');
            if ($departmentId) {
                $empQuery->where('department_id', $departmentId);
            }
            $employees = $empQuery->orderBy('name')->get();

            $existingAttendances = DailyAttendance::whereDate('date', $date)
                ->get()
                ->keyBy('employee_id');

            // Counters for this date
            $allDateAttendances = DailyAttendance::whereDate('date', $date)->get();
            $stats = [
                'total_active' => Employee::where('employment_status', 'active')->count(),
                'present' => $allDateAttendances->where('status', 'present')->count(),
                'absent' => $allDateAttendances->where('status', 'absent')->count(),
                'half_day' => $allDateAttendances->where('status', 'half_day')->count(),
                'leave' => $allDateAttendances->where('status', 'leave')->count(),
                'wfh' => $allDateAttendances->where('status', 'wfh')->count(),
            ];
            $stats['pending'] = max(0, $stats['total_active'] - $allDateAttendances->count());
        }

        $departments = Department::orderBy('name')->get();

        return view('attendance.index', compact(
            'employees',
            'existingAttendances',
            'date',
            'stats',
            'departments',
            'departmentId'
        ));
    }

    public function storeBatch(Request $request)
    {
        $request->validate([
            'date' => ['required', 'date'],
            'attendances' => ['required', 'array'],
            'attendances.*.employee_id' => ['required', 'exists:employees,id'],
            'attendances.*.status' => ['required', 'in:present,absent,half_day,leave,wfh'],
            'attendances.*.check_in' => ['nullable', 'string'],
            'attendances.*.check_out' => ['nullable', 'string'],
            'attendances.*.remarks' => ['nullable', 'string', 'max:255'],
        ]);

        $date = $request->date;
        $savedCount = 0;
        $user = Auth::user();
        $employee = $user->isEmployee() ? $this->resolveEmployeeForUser($user) : null;
        $officeStart = CompanySetting::get('office_timing_start', '09:30');
        $officeEnd = CompanySetting::get('office_timing_end', '18:30');

        foreach ($request->attendances as $item) {
            if ($employee && $item['employee_id'] != $employee->id) {
                continue;
            }

            $attendance = DailyAttendance::where('employee_id', $item['employee_id'])
                ->whereDate('date', $date)
                ->first();

            $data = [
                'status' => $item['status'],
                'check_in' => $item['check_in'] ?? ($item['status'] === 'present' || $item['status'] === 'wfh' ? $officeStart : null),
                'check_out' => $item['check_out'] ?? ($item['status'] === 'present' || $item['status'] === 'wfh' ? $officeEnd : null),
                'remarks' => $item['remarks'] ?? null,
                'recorded_by_user_id' => $user->id,
            ];

            if ($attendance) {
                $attendance->update($data);
            } else {
                $data['employee_id'] = $item['employee_id'];
                $data['date'] = Carbon::parse($date)->format('Y-m-d 00:00:00');
                DailyAttendance::create($data);
            }
            $savedCount++;
        }

        AuditService::log('bulk_update', 'Attendance', "Marked daily attendance for {$savedCount} employees on {$date}");

        return redirect()->route('attendance.index', ['date' => $date])
            ->with('success', "Daily attendance for {$date} saved successfully ({$savedCount} employees).");
    }

    public function monthlyGrid(Request $request)
    {
        $month = (int) $request->get('month', now()->month);
        $year = (int) $request->get('year', now()->year);
        $departmentId = $request->get('department_id');
        $user = Auth::user();

        if ($user->isEmployee()) {
            $employee = $this->resolveEmployeeForUser($user);
            $employees = collect([$employee]);
        } else {
            $query = Employee::where('employment_status', 'active')->with('department');
            if ($departmentId) {
                $query->where('department_id', $departmentId);
            }
            $employees = $query->orderBy('name')->get();
        }

        $departments = Department::orderBy('name')->get();

        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();
        $daysInMonth = $startDate->daysInMonth;

        $attendances = DailyAttendance::whereYear('date', $year)
            ->whereMonth('date', $month)
            ->get()
            ->groupBy('employee_id');

        $holidays = Holiday::whereYear('date', $year)
            ->whereMonth('date', $month)
            ->get()
            ->keyBy(function ($h) {
                return (int) date('j', strtotime($h->date));
            });

        // Navigation dates
        $prevDate = $startDate->copy()->subMonth();
        $nextDate = $startDate->copy()->addMonth();

        return view('attendance.monthly-grid', compact(
            'employees',
            'departments',
            'departmentId',
            'month',
            'year',
            'daysInMonth',
            'attendances',
            'holidays',
            'startDate',
            'prevDate',
            'nextDate'
        ));
    }

    protected function resolveEmployeeForUser($user)
    {
        $employee = $user->employee;
        if ($employee) {
            return $employee;
        }

        $employee = Employee::where('email', $user->email)->first();
        if ($employee) {
            $employee->update(['user_id' => $user->id]);
            return $employee;
        }

        $dept = Department::first();
        return Employee::create([
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'employee_code' => Employee::generateUniqueCode(),
            'department_id' => $dept ? $dept->id : null,
            'designation' => ucfirst(str_replace('_', ' ', $user->role)),
            'employment_status' => 'active',
            'joining_date' => now()->subYear()->format('Y-m-d'),
            'leave_quota' => 18,
            'used_leaves' => 0,
        ]);
    }

    public function clockIn(Request $request)
    {
        $user = Auth::user();
        $employee = $this->resolveEmployeeForUser($user);

        $todayStr = now()->format('Y-m-d');
        $nowTime = now()->format('H:i');
        $officeStart = CompanySetting::get('office_timing_start', '09:30');
        $graceMinutes = (int) CompanySetting::get('late_grace_minutes', 15);
        $lateThreshold = Carbon::parse($todayStr . ' ' . $officeStart)->addMinutes($graceMinutes)->format('H:i');

        // Late detection: after official office start time + grace period
        $isLate = ($nowTime > $lateThreshold);
        $remarks = $isLate ? 'Self Clock-In (Late Arrival)' : 'Self Clock-In';

        $attendance = DailyAttendance::where('employee_id', $employee->id)
            ->whereDate('date', $todayStr)
            ->first();

        if ($attendance) {
            $attendance->update([
                'status' => 'present',
                'check_in' => $nowTime,
                'check_out' => null,
                'recorded_by_user_id' => $user->id,
                'remarks' => $remarks,
            ]);
        } else {
            $attendance = DailyAttendance::create([
                'employee_id' => $employee->id,
                'date' => Carbon::parse($todayStr)->format('Y-m-d 00:00:00'),
                'status' => 'present',
                'check_in' => $nowTime,
                'check_out' => null,
                'recorded_by_user_id' => $user->id,
                'remarks' => $remarks,
            ]);
        }

        AuditService::log('clock_in', 'Attendance', "Employee {$employee->name} clocked in at {$nowTime} on {$todayStr}");

        $msg = $isLate 
            ? "Attendance marked at {$nowTime} (Late Arrival). Have a productive shift!"
            : "Attendance marked successfully at {$nowTime}. Have a great shift!";

        return back()->with('success', $msg);
    }

    public function clockOut(Request $request)
    {
        $user = Auth::user();
        $employee = $this->resolveEmployeeForUser($user);

        $todayStr = now()->format('Y-m-d');
        $nowTime = now()->format('H:i');
        $officeStart = CompanySetting::get('office_timing_start', '09:30');

        $attendance = DailyAttendance::where('employee_id', $employee->id)
            ->whereDate('date', $todayStr)
            ->first();

        if (!$attendance) {
            $attendance = DailyAttendance::create([
                'employee_id' => $employee->id,
                'date' => Carbon::parse($todayStr)->format('Y-m-d 00:00:00'),
                'status' => 'present',
                'check_in' => $officeStart,
                'check_out' => $nowTime,
                'recorded_by_user_id' => $user->id,
                'remarks' => 'Self Clock-Out',
            ]);
        } else {
            $attendance->update([
                'check_out' => $nowTime,
            ]);
        }

        AuditService::log('clock_out', 'Attendance', "Employee {$employee->name} clocked out at {$nowTime} on {$todayStr}");

        return back()->with('success', "Clocked out successfully at {$nowTime}. Great work today!");
    }

    public function exportMonthly(Request $request)
    {
        $month = (int) $request->get('month', now()->month);
        $year = (int) $request->get('year', now()->year);
        $departmentId = $request->get('department_id');
        $user = Auth::user();

        if ($user->isEmployee()) {
            $employee = $this->resolveEmployeeForUser($user);
            $employees = collect([$employee]);
        } else {
            $query = Employee::where('employment_status', 'active')->with('department');
            if ($departmentId) {
                $query->where('department_id', $departmentId);
            }
            $employees = $query->orderBy('name')->get();
        }

        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $daysInMonth = $startDate->daysInMonth;

        $attendances = DailyAttendance::whereYear('date', $year)
            ->whereMonth('date', $month)
            ->get()
            ->groupBy('employee_id');

        $fileName = "attendance_matrix_{$year}_" . str_pad($month, 2, '0', STR_PAD_LEFT) . ".csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ];

        $callback = function () use ($employees, $attendances, $daysInMonth, $year, $month) {
            $file = fopen('php://output', 'w');

            // Header row
            $headerRow = ['Employee Code', 'Employee Name', 'Department'];
            for ($d = 1; $d <= $daysInMonth; $d++) {
                $headerRow[] = "Day {$d}";
            }
            $headerRow[] = 'Present Days';
            $headerRow[] = 'Attendance Rate %';

            fputcsv($file, $headerRow);

            foreach ($employees as $emp) {
                $empAtts = ($attendances->get($emp->id) ?? collect())->keyBy(function ($item) {
                    return (int) date('j', strtotime($item->date));
                });

                $presentCount = 0;
                $halfCount = 0;
                $totalRecorded = $empAtts->count();

                $row = [
                    $emp->employee_code,
                    $emp->name,
                    $emp->department->name ?? 'N/A',
                ];

                for ($d = 1; $d <= $daysInMonth; $d++) {
                    $rec = $empAtts->get($d);
                    $st = $rec ? $rec->status : null;
                    if ($st === 'present' || $st === 'wfh') $presentCount++;
                    if ($st === 'half_day') $halfCount++;

                    $statusLetter = match ($st) {
                        'present' => 'P',
                        'wfh' => 'WFH',
                        'half_day' => 'HD',
                        'leave' => 'L',
                        'absent' => 'A',
                        default => '-',
                    };
                    $row[] = $statusLetter;
                }

                $effectivePresent = $presentCount + ($halfCount * 0.5);
                $rate = $totalRecorded > 0 ? round(($effectivePresent / $totalRecorded) * 100, 1) : 0;

                $row[] = "{$effectivePresent} days";
                $row[] = "{$rate}%";

                fputcsv($file, $row);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
