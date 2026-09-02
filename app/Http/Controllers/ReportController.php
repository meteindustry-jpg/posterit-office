<?php

namespace App\Http\Controllers;

use App\Models\DailyAttendance;
use App\Models\DailyWorkEntry;
use App\Models\Department;
use App\Models\Employee;
use App\Models\WorkCategory;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $reportType = $request->get('type', 'daily_work'); // daily_work, attendance, employee_summary, category_summary
        $period = $request->get('period', 'monthly'); // daily, weekly, monthly, yearly, custom
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $employeeId = $request->get('employee_id');
        $departmentId = $request->get('department_id');
        $categoryId = $request->get('category_id');
        $attendanceStatus = $request->get('attendance_status');

        // Resolve dates based on period
        if (! $startDate || ! $endDate) {
            $dates = $this->resolvePeriodDates($period);
            $startDate = $dates['start'];
            $endDate = $dates['end'];
        }

        $employees = Employee::where('employment_status', 'active')->orderBy('name')->get();
        $departments = Department::orderBy('name')->get();
        $categories = WorkCategory::orderBy('name')->get();

        // Query data according to report type
        $data = match ($reportType) {
            'attendance' => $this->getAttendanceReport($startDate, $endDate, $employeeId, $departmentId, $attendanceStatus),
            'employee_summary' => $this->getEmployeeSummaryReport($startDate, $endDate, $departmentId),
            'category_summary' => $this->getCategorySummaryReport($startDate, $endDate, $departmentId),
            default => $this->getWorkEntryReport($startDate, $endDate, $employeeId, $departmentId, $categoryId),
        };

        return view('reports.index', compact(
            'reportType',
            'period',
            'startDate',
            'endDate',
            'employeeId',
            'departmentId',
            'categoryId',
            'attendanceStatus',
            'employees',
            'departments',
            'categories',
            'data'
        ));
    }

    public function export(Request $request, ?string $format = null)
    {
        $format = $format ?: $request->get('format', 'csv');
        $reportType = $request->get('type', 'daily_work');
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->endOfMonth()->format('Y-m-d'));
        $employeeId = $request->get('employee_id');
        $departmentId = $request->get('department_id');
        $categoryId = $request->get('category_id');
        $attendanceStatus = $request->get('attendance_status');

        $data = match ($reportType) {
            'attendance' => $this->getAttendanceReport($startDate, $endDate, $employeeId, $departmentId, $attendanceStatus),
            'employee_summary' => $this->getEmployeeSummaryReport($startDate, $endDate, $departmentId),
            'category_summary' => $this->getCategorySummaryReport($startDate, $endDate, $departmentId),
            default => $this->getWorkEntryReport($startDate, $endDate, $employeeId, $departmentId, $categoryId),
        };

        if ($format === 'csv' || $format === 'excel') {
            return $this->exportCsv($reportType, $data, $startDate, $endDate);
        }

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('reports.pdf', compact('reportType', 'startDate', 'endDate', 'data'));
            return $pdf->download("posterit-report-{$reportType}-{$startDate}-to-{$endDate}.pdf");
        }

        if ($format === 'print') {
            return view('reports.print', compact('reportType', 'startDate', 'endDate', 'data'));
        }

        return redirect()->back();
    }

    protected function resolvePeriodDates(string $period): array
    {
        $now = now();
        return match ($period) {
            'daily' => ['start' => $now->format('Y-m-d'), 'end' => $now->format('Y-m-d')],
            'weekly' => ['start' => $now->copy()->startOfWeek()->format('Y-m-d'), 'end' => $now->copy()->endOfWeek()->format('Y-m-d')],
            'yearly' => ['start' => $now->copy()->startOfYear()->format('Y-m-d'), 'end' => $now->copy()->endOfYear()->format('Y-m-d')],
            default => ['start' => $now->copy()->startOfMonth()->format('Y-m-d'), 'end' => $now->copy()->endOfMonth()->format('Y-m-d')],
        };
    }

    protected function getWorkEntryReport($startDate, $endDate, $employeeId, $departmentId, $categoryId)
    {
        $query = DailyWorkEntry::with(['employee.department', 'category'])
            ->whereBetween('date', [$startDate, $endDate]);

        if ($employeeId) {
            $query->where('employee_id', $employeeId);
        }
        if ($categoryId) {
            $query->where('work_category_id', $categoryId);
        }
        if ($departmentId) {
            $query->whereHas('employee', fn ($q) => $q->where('department_id', $departmentId));
        }

        $records = $query->orderBy('date', 'desc')->get();
        $totalQuantity = $records->sum('quantity');

        return [
            'type' => 'daily_work',
            'records' => $records,
            'total_quantity' => $totalQuantity,
            'total_entries' => $records->count(),
        ];
    }

    protected function getAttendanceReport($startDate, $endDate, $employeeId, $departmentId, $attendanceStatus)
    {
        $query = DailyAttendance::with(['employee.department'])
            ->whereBetween('date', [$startDate, $endDate]);

        if ($employeeId) {
            $query->where('employee_id', $employeeId);
        }
        if ($attendanceStatus) {
            $query->where('status', $attendanceStatus);
        }
        if ($departmentId) {
            $query->whereHas('employee', fn ($q) => $q->where('department_id', $departmentId));
        }

        $records = $query->orderBy('date', 'desc')->get();

        return [
            'type' => 'attendance',
            'records' => $records,
            'present_count' => $records->where('status', 'present')->count(),
            'wfh_count' => $records->where('status', 'wfh')->count(),
            'half_day_count' => $records->where('status', 'half_day')->count(),
            'leave_count' => $records->where('status', 'leave')->count(),
            'absent_count' => $records->where('status', 'absent')->count(),
        ];
    }

    protected function getEmployeeSummaryReport($startDate, $endDate, $departmentId)
    {
        $empQuery = Employee::with('department')->where('employment_status', 'active');
        if ($departmentId) {
            $empQuery->where('department_id', $departmentId);
        }
        $employees = $empQuery->get();

        $summaries = [];
        foreach ($employees as $emp) {
            $workCount = (int) DailyWorkEntry::where('employee_id', $emp->id)
                ->whereBetween('date', [$startDate, $endDate])
                ->sum('quantity');

            $attendances = DailyAttendance::where('employee_id', $emp->id)
                ->whereBetween('date', [$startDate, $endDate])
                ->get();

            $totalAtt = $attendances->count();
            $presentUnits = $attendances->whereIn('status', ['present', 'wfh'])->count() + ($attendances->where('status', 'half_day')->count() * 0.5);
            $rate = $totalAtt > 0 ? round(($presentUnits / $totalAtt) * 100, 1) : 100;

            $summaries[] = [
                'employee' => $emp,
                'total_works' => $workCount,
                'present_days' => $attendances->where('status', 'present')->count(),
                'wfh_days' => $attendances->where('status', 'wfh')->count(),
                'leave_days' => $attendances->where('status', 'leave')->count(),
                'absent_days' => $attendances->where('status', 'absent')->count(),
                'attendance_rate' => $rate,
            ];
        }

        return [
            'type' => 'employee_summary',
            'summaries' => $summaries,
        ];
    }

    protected function getCategorySummaryReport($startDate, $endDate, $departmentId)
    {
        $query = DailyWorkEntry::select('work_category_id', DB::raw('SUM(quantity) as total_qty'), DB::raw('COUNT(id) as total_tasks'))
            ->whereBetween('date', [$startDate, $endDate]);

        if ($departmentId) {
            $query->whereHas('employee', fn ($q) => $q->where('department_id', $departmentId));
        }

        $records = $query->groupBy('work_category_id')
            ->orderByDesc('total_qty')
            ->with('category')
            ->get();

        return [
            'type' => 'category_summary',
            'records' => $records,
            'total_volume' => $records->sum('total_qty'),
        ];
    }

    protected function exportCsv(string $reportType, array $data, string $startDate, string $endDate): StreamedResponse
    {
        $filename = "posterit-{$reportType}-{$startDate}-{$endDate}.csv";

        return response()->streamDownload(function () use ($reportType, $data) {
            $handle = fopen('php://output', 'w');
            
            // Output UTF-8 BOM for Excel support
            fputs($handle, "\xEF\xBB\xBF");

            if ($reportType === 'daily_work') {
                fputcsv($handle, ['ID', 'Date', 'Employee Code', 'Employee Name', 'Department', 'Category', 'Quantity', 'Remarks']);
                foreach ($data['records'] as $r) {
                    fputcsv($handle, [
                        $r->id,
                        $r->date->format('Y-m-d'),
                        $r->employee->employee_code,
                        $r->employee->name,
                        $r->employee->department->name ?? 'N/A',
                        $r->category->name ?? 'N/A',
                        $r->quantity,
                        $r->remarks ?? '',
                    ]);
                }
            } elseif ($reportType === 'attendance') {
                fputcsv($handle, ['ID', 'Date', 'Employee Code', 'Employee Name', 'Department', 'Status', 'Check In', 'Check Out', 'Remarks']);
                foreach ($data['records'] as $r) {
                    fputcsv($handle, [
                        $r->id,
                        $r->date->format('Y-m-d'),
                        $r->employee->employee_code,
                        $r->employee->name,
                        $r->employee->department->name ?? 'N/A',
                        strtoupper($r->status),
                        $r->check_in ?? '',
                        $r->check_out ?? '',
                        $r->remarks ?? '',
                    ]);
                }
            } elseif ($reportType === 'employee_summary') {
                fputcsv($handle, ['Employee Code', 'Name', 'Department', 'Total Works', 'Present Days', 'WFH Days', 'Leaves', 'Absents', 'Attendance %']);
                foreach ($data['summaries'] as $s) {
                    fputcsv($handle, [
                        $s['employee']->employee_code,
                        $s['employee']->name,
                        $s['employee']->department->name ?? 'N/A',
                        $s['total_works'],
                        $s['present_days'],
                        $s['wfh_days'],
                        $s['leave_days'],
                        $s['absent_days'],
                        $s['attendance_rate'] . '%',
                    ]);
                }
            } elseif ($reportType === 'category_summary') {
                fputcsv($handle, ['Work Category', 'Total Tasks Logged', 'Total Quantity Completed']);
                foreach ($data['records'] as $r) {
                    fputcsv($handle, [
                        $r->category->name ?? 'N/A',
                        $r->total_tasks,
                        $r->total_qty,
                    ]);
                }
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
