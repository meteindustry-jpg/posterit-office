<?php

namespace App\Http\Controllers;

use App\Models\CompanySetting;
use App\Models\DailyAttendance;
use App\Models\DailyWorkEntry;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Holiday;
use App\Models\LeaveRequest;
use App\Models\Todo;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $tz = CompanySetting::get('timezone', config('app.timezone', 'Asia/Kolkata')) ?: 'Asia/Kolkata';
        $now = now()->setTimezone($tz);
        $today = $now->format('Y-m-d');
        $currentMonth = $now->month;
        $currentYear = $now->year;

        // If Employee role, load Employee-specific view data
        if ($user->isEmployee() && $user->employee) {
            return $this->employeeDashboard($user);
        }

        // Admin & Manager Attendance / Self Clock-In (Super Admin does not clock in)
        $adminEmployee = null;
        $adminTodayAttendance = null;
        $adminCheckInFormatted = null;
        $adminCheckOutFormatted = null;
        $adminWorkedHours = 0;
        $adminWorkedMinutes = 0;
        $adminCheckInTimestamp = null;
        $officeTimingStart = CompanySetting::get('office_timing_start', '09:30');
        $officeTimingEnd = CompanySetting::get('office_timing_end', '18:30');

        if (! $user->isSuperAdmin()) {
            $adminEmployee = $user->employee ?: Employee::where('user_id', $user->id)->orWhere('email', $user->email)->first();
            if (! $adminEmployee && $user->isAdmin()) {
                $dept = Department::first();
                $adminEmployee = Employee::create([
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'employee_code' => Employee::generateUniqueCode(),
                    'department_id' => $dept?->id,
                    'designation' => 'Office Administrator',
                    'employment_status' => 'active',
                    'joining_date' => $today,
                    'leave_quota' => 18,
                ]);
                $user->update(['employee_id' => $adminEmployee->id]);
            }

            if ($adminEmployee) {
                $adminTodayAttendance = DailyAttendance::where('employee_id', $adminEmployee->id)
                    ->whereDate('date', $today)
                    ->first();

                if ($adminTodayAttendance && $adminTodayAttendance->check_in) {
                    $attDateStr = $adminTodayAttendance->date ? $adminTodayAttendance->date->format('Y-m-d') : $today;
                    $inTime = Carbon::parse($attDateStr.' '.$adminTodayAttendance->check_in, $tz);
                    $adminCheckInFormatted = $inTime->format('h:i A');

                    if ($adminTodayAttendance->check_out) {
                        $adminCheckOutFormatted = Carbon::parse($attDateStr.' '.$adminTodayAttendance->check_out, $tz)->format('h:i A');
                    }

                    $outTime = $adminTodayAttendance->check_out ? Carbon::parse($attDateStr.' '.$adminTodayAttendance->check_out, $tz) : $now;
                    $diffMins = $outTime->greaterThanOrEqualTo($inTime) ? $inTime->diffInMinutes($outTime) : 0;

                    $adminWorkedHours = floor($diffMins / 60);
                    $adminWorkedMinutes = $diffMins % 60;
                    $adminCheckInTimestamp = $inTime->timestamp * 1000;
                }
            }
        }

        // Admin & Manager Dashboard Data
        $totalEmployees = Employee::where('employment_status', 'active')->count();

        // Attendance Today
        $todayAttendances = DailyAttendance::whereDate('date', $today)->get();
        $presentToday = $todayAttendances->where('status', 'present')->count();
        $wfhToday = $todayAttendances->where('status', 'wfh')->count();
        $halfDayToday = $todayAttendances->where('status', 'half_day')->count();
        $leaveToday = $todayAttendances->where('status', 'leave')->count();
        $absentToday = $todayAttendances->where('status', 'absent')->count();

        $recordedCount = $todayAttendances->count();
        $pendingAttendanceCount = max(0, $totalEmployees - $recordedCount);

        // Works
        $totalWorksToday = (int) DailyWorkEntry::whereDate('date', $today)->sum('quantity');
        $monthlyWorkCount = (int) DailyWorkEntry::whereYear('date', $currentYear)
            ->whereMonth('date', $currentMonth)
            ->sum('quantity');

        // Employees with work logged today
        $employeesWithWorkToday = DailyWorkEntry::whereDate('date', $today)
            ->distinct('employee_id')
            ->count('employee_id');
        $pendingWorkEntries = max(0, ($presentToday + $wfhToday + $halfDayToday) - $employeesWithWorkToday);

        // Overall Attendance % this month
        $monthAttendances = DailyAttendance::whereYear('date', $currentYear)
            ->whereMonth('date', $currentMonth)
            ->get();

        $totalMonthRecords = $monthAttendances->count();
        $presentLikeCount = $monthAttendances->whereIn('status', ['present', 'wfh'])->count() + ($monthAttendances->where('status', 'half_day')->count() * 0.5);
        $attendancePercentage = $totalMonthRecords > 0 ? round(($presentLikeCount / $totalMonthRecords) * 100, 1) : 100;

        // Top Performer of the Month
        $topPerformerData = DailyWorkEntry::select('employee_id', DB::raw('SUM(quantity) as total_qty'))
            ->whereYear('date', $currentYear)
            ->whereMonth('date', $currentMonth)
            ->groupBy('employee_id')
            ->orderByDesc('total_qty')
            ->with('employee.department')
            ->first();

        // Top 5 Performers
        $topPerformers = DailyWorkEntry::select('employee_id', DB::raw('SUM(quantity) as total_qty'))
            ->whereYear('date', $currentYear)
            ->whereMonth('date', $currentMonth)
            ->groupBy('employee_id')
            ->orderByDesc('total_qty')
            ->with('employee.department')
            ->take(5)
            ->get();

        // Work Categories breakdown (this month)
        $categoryBreakdown = DailyWorkEntry::select('work_category_id', DB::raw('SUM(quantity) as total_qty'))
            ->whereYear('date', $currentYear)
            ->whereMonth('date', $currentMonth)
            ->groupBy('work_category_id')
            ->orderByDesc('total_qty')
            ->with('category')
            ->get();

        // Last 14 Days Work Trend
        $dates = [];
        $workTrend = [];
        for ($i = 13; $i >= 0; $i--) {
            $d = now()->subDays($i)->format('Y-m-d');
            $dates[] = now()->subDays($i)->format('d M');
            $workTrend[] = (int) DailyWorkEntry::whereDate('date', $d)->sum('quantity');
        }

        // Upcoming Holidays in next 30 days
        $upcomingHolidays = Holiday::where('date', '>=', $today)
            ->orderBy('date', 'asc')
            ->take(4)
            ->get();

        // Pending Leave Requests
        $pendingLeaves = LeaveRequest::with(['employee.department', 'leaveType'])
            ->where('status', 'pending')
            ->orderBy('start_date', 'asc')
            ->take(5)
            ->get();

        // Recent Work Entries
        $recentWorkEntries = DailyWorkEntry::with(['employee.department', 'category'])
            ->orderBy('id', 'desc')
            ->take(7)
            ->get();

        // My Pending Todo Tasks
        $myPendingTodos = Todo::where(function ($q) use ($user) {
            $q->where('user_id', $user->id)
                ->orWhere('assigned_to_user_id', $user->id);
        })
            ->where('is_completed', false)
            ->orderByRaw("CASE WHEN priority = 'high' THEN 1 WHEN priority = 'medium' THEN 2 ELSE 3 END")
            ->orderBy('due_date', 'asc')
            ->take(4)
            ->get();

        $pendingRegistrationCount = $user->isSuperAdmin()
            ? User::where('is_active', false)->whereNotNull('employee_id')->count()
            : 0;

        return view('dashboard.index', compact(
            'totalEmployees',
            'presentToday',
            'wfhToday',
            'halfDayToday',
            'leaveToday',
            'absentToday',
            'pendingAttendanceCount',
            'totalWorksToday',
            'monthlyWorkCount',
            'pendingWorkEntries',
            'attendancePercentage',
            'topPerformerData',
            'topPerformers',
            'categoryBreakdown',
            'dates',
            'workTrend',
            'upcomingHolidays',
            'pendingLeaves',
            'recentWorkEntries',
            'myPendingTodos',
            'pendingRegistrationCount',
            'adminEmployee',
            'adminTodayAttendance',
            'adminCheckInFormatted',
            'adminCheckOutFormatted',
            'adminWorkedHours',
            'adminWorkedMinutes',
            'adminCheckInTimestamp',
            'officeTimingStart',
            'officeTimingEnd'
        ));
    }

    protected function employeeDashboard($user)
    {
        $employee = $user->employee;
        $tz = CompanySetting::get('timezone', config('app.timezone', 'Asia/Kolkata')) ?: 'Asia/Kolkata';
        $now = now()->setTimezone($tz);
        $today = $now->format('Y-m-d');
        $currentMonth = $now->month;
        $currentYear = $now->year;

        $myTodayAttendance = DailyAttendance::where('employee_id', $employee->id)
            ->whereDate('date', $today)
            ->first();

        $myMonthlyWorks = (int) DailyWorkEntry::where('employee_id', $employee->id)
            ->whereYear('date', $currentYear)
            ->whereMonth('date', $currentMonth)
            ->sum('quantity');

        $myTodayWorks = (int) DailyWorkEntry::where('employee_id', $employee->id)
            ->whereDate('date', $today)
            ->sum('quantity');

        // Week stats
        $weekStart = $now->copy()->startOfWeek();
        $weekEnd = $now->copy()->endOfWeek();
        $myWeeklyWorks = (int) DailyWorkEntry::where('employee_id', $employee->id)
            ->whereBetween('date', [$weekStart->format('Y-m-d'), $weekEnd->format('Y-m-d')])
            ->sum('quantity');

        // Attendance stats
        $myMonthAttendances = DailyAttendance::where('employee_id', $employee->id)
            ->whereYear('date', $currentYear)
            ->whereMonth('date', $currentMonth)
            ->get();
        $totalDays = $myMonthAttendances->count();
        $monthPresentDays = $myMonthAttendances->whereIn('status', ['present', 'wfh'])->count() + ($myMonthAttendances->where('status', 'half_day')->count() * 0.5);
        $myAttendanceRate = $totalDays > 0 ? round(($monthPresentDays / $totalDays) * 100, 1) : 100;

        $officeTimingStart = CompanySetting::get('office_timing_start', '09:30');
        $officeTimingEnd = CompanySetting::get('office_timing_end', '18:30');
        $shiftStart = Carbon::parse($today.' '.$officeTimingStart, $tz);
        $shiftEnd = Carbon::parse($today.' '.$officeTimingEnd, $tz);
        $standardShiftHours = max(1, round($shiftStart->diffInMinutes($shiftEnd) / 60, 1));

        $totalMonthHoursEst = round($monthPresentDays * $standardShiftHours, 1);

        // Today's worked hours calculation
        $todayWorkedHours = 0;
        $todayWorkedMinutes = 0;
        $todayProgressPct = 0;
        $todayCheckInFormatted = null;
        $todayCheckOutFormatted = null;

        if ($myTodayAttendance && $myTodayAttendance->check_in) {
            $attDateStr = $myTodayAttendance->date ? $myTodayAttendance->date->format('Y-m-d') : $today;
            $inTime = Carbon::parse($attDateStr.' '.$myTodayAttendance->check_in, $tz);
            $todayCheckInFormatted = $inTime->format('h:i A');

            if ($myTodayAttendance->check_out) {
                $todayCheckOutFormatted = Carbon::parse($attDateStr.' '.$myTodayAttendance->check_out, $tz)->format('h:i A');
            }

            $outTime = $myTodayAttendance->check_out ? Carbon::parse($attDateStr.' '.$myTodayAttendance->check_out, $tz) : $now;

            if ($outTime->greaterThanOrEqualTo($inTime)) {
                $diffMins = $inTime->diffInMinutes($outTime);
            } else {
                $diffMins = 0;
            }

            $todayWorkedHours = floor($diffMins / 60);
            $todayWorkedMinutes = $diffMins % 60;
            $todayProgressPct = min(100, round(($diffMins / ($standardShiftHours * 60)) * 100));
        }

        $todayCheckInTimestamp = ($myTodayAttendance && $myTodayAttendance->check_in)
            ? Carbon::parse(($myTodayAttendance->date ? $myTodayAttendance->date->format('Y-m-d') : $today).' '.$myTodayAttendance->check_in, $tz)->timestamp * 1000
            : null;

        // Leave stats
        $usedLeaves = $employee->used_leaves;
        $remainingLeaves = $employee->remaining_leaves;

        // Recent work
        $recentWorks = DailyWorkEntry::where('employee_id', $employee->id)
            ->with('category')
            ->orderBy('date', 'desc')
            ->take(10)
            ->get();

        // Upcoming holidays
        $upcomingHolidays = Holiday::where('date', '>=', $today)
            ->orderBy('date', 'asc')
            ->take(4)
            ->get();

        // My Leave requests
        $myLeaves = LeaveRequest::where('employee_id', $employee->id)
            ->with('leaveType')
            ->orderBy('id', 'desc')
            ->take(5)
            ->get();

        // My Active Tasks / Todo items
        $myPendingTodos = Todo::where(function ($q) use ($user) {
            $q->where('assigned_to_user_id', $user->id)
                ->orWhere('user_id', $user->id);
        })
            ->where('is_completed', false)
            ->orderByRaw("CASE WHEN priority = 'high' THEN 1 WHEN priority = 'medium' THEN 2 ELSE 3 END")
            ->orderBy('due_date', 'asc')
            ->take(6)
            ->get();

        return view('dashboard.employee', compact(
            'employee',
            'myTodayAttendance',
            'todayCheckInFormatted',
            'todayCheckOutFormatted',
            'myMonthlyWorks',
            'myTodayWorks',
            'myWeeklyWorks',
            'myAttendanceRate',
            'monthPresentDays',
            'totalMonthHoursEst',
            'todayWorkedHours',
            'todayWorkedMinutes',
            'todayProgressPct',
            'todayCheckInTimestamp',
            'usedLeaves',
            'remainingLeaves',
            'recentWorks',
            'upcomingHolidays',
            'myLeaves',
            'myPendingTodos',
            'officeTimingStart',
            'officeTimingEnd',
            'standardShiftHours'
        ));
    }
}
