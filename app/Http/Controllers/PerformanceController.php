<?php

namespace App\Http\Controllers;

use App\Models\DailyAttendance;
use App\Models\DailyWorkEntry;
use App\Models\Department;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PerformanceController extends Controller
{
    public function index(Request $request)
    {
        $month = (int) $request->get('month', now()->month);
        $year = (int) $request->get('year', now()->year);
        $departmentId = $request->get('department_id');

        $empQuery = Employee::where('employment_status', 'active')->with('department');
        if ($departmentId) {
            $empQuery->where('department_id', $departmentId);
        }
        $employees = $empQuery->get();

        $performanceData = [];

        foreach ($employees as $employee) {
            // Total works in month
            $totalWorks = (int) DailyWorkEntry::where('employee_id', $employee->id)
                ->whereYear('date', $year)
                ->whereMonth('date', $month)
                ->sum('quantity');

            // Unique days worked
            $daysWorked = DailyWorkEntry::where('employee_id', $employee->id)
                ->whereYear('date', $year)
                ->whereMonth('date', $month)
                ->distinct('date')
                ->count('date');

            $avgDailyWork = $daysWorked > 0 ? round($totalWorks / $daysWorked, 1) : 0;

            // Attendance in month
            $monthAtt = DailyAttendance::where('employee_id', $employee->id)
                ->whereYear('date', $year)
                ->whereMonth('date', $month)
                ->get();
            
            $totalAttRecords = $monthAtt->count();
            $presentUnits = $monthAtt->whereIn('status', ['present', 'wfh'])->count() + ($monthAtt->where('status', 'half_day')->count() * 0.5);
            $attendanceRate = $totalAttRecords > 0 ? round(($presentUnits / $totalAttRecords) * 100, 1) : 100;
            $leaveCount = $monthAtt->where('status', 'leave')->count();

            // Top category for this employee
            $topCategory = DailyWorkEntry::select('work_category_id', DB::raw('SUM(quantity) as qty'))
                ->where('employee_id', $employee->id)
                ->whereYear('date', $year)
                ->whereMonth('date', $month)
                ->groupBy('work_category_id')
                ->orderByDesc('qty')
                ->with('category')
                ->first();

            // Rating logic
            // Score = TotalWorks (weight 60%) + AttendanceRate (weight 40%)
            $rating = 'Needs Improvement';
            $ratingBadge = 'badge-danger';
            $ratingColor = 'rose';

            if ($totalWorks >= 80 && $attendanceRate >= 90) {
                $rating = 'Excellent';
                $ratingBadge = 'badge-success';
                $ratingColor = 'emerald';
            } elseif ($totalWorks >= 50 && $attendanceRate >= 80) {
                $rating = 'Good';
                $ratingBadge = 'badge-info';
                $ratingColor = 'indigo';
            } elseif ($totalWorks >= 20 || $attendanceRate >= 70) {
                $rating = 'Average';
                $ratingBadge = 'badge-warning';
                $ratingColor = 'amber';
            }

            $performanceData[] = [
                'employee' => $employee,
                'total_works' => $totalWorks,
                'days_worked' => $daysWorked,
                'avg_daily_work' => $avgDailyWork,
                'attendance_rate' => $attendanceRate,
                'leave_count' => $leaveCount,
                'top_category' => $topCategory ? $topCategory->category->name : 'N/A',
                'top_category_color' => $topCategory ? $topCategory->category->color : '#6366f1',
                'rating' => $rating,
                'rating_color' => $ratingColor,
            ];
        }

        // Sort descending by total works to calculate rank
        usort($performanceData, fn ($a, $b) => $b['total_works'] <=> $a['total_works']);

        foreach ($performanceData as $idx => &$item) {
            $item['rank'] = $idx + 1;
        }

        $departments = Department::orderBy('name')->get();

        return view('performance.index', compact(
            'performanceData',
            'month',
            'year',
            'departmentId',
            'departments'
        ));
    }
}
