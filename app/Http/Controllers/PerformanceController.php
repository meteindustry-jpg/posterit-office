<?php

namespace App\Http\Controllers;

use App\Models\DailyAttendance;
use App\Models\DailyWorkEntry;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Todo;
use App\Models\User;
use App\Models\WorkCategory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PerformanceController extends Controller
{
    public function index(Request $request)
    {
        $tz = CompanySetting::get('timezone', config('app.timezone', 'Asia/Kolkata')) ?: 'Asia/Kolkata';
        $now = now()->setTimezone($tz);
        $month = (int) $request->get('month', $now->month);
        $year = (int) $request->get('year', $now->year);
        $departmentId = $request->get('department_id');

        $monthStartCarbon = Carbon::create($year, $month, 1, 0, 0, 0, $tz)->startOfMonth();
        $monthEndCarbon = Carbon::create($year, $month, 1, 23, 59, 59, $tz)->endOfMonth();
        $monthStartDate = $monthStartCarbon->format('Y-m-d');
        $monthEndDate = $monthEndCarbon->format('Y-m-d');
        $monthStart = $monthStartDate.' 00:00:00';
        $monthEnd = $monthEndDate.' 23:59:59';

        // Calculate company-wide working days recorded in this month
        $companyAttendanceDays = DailyAttendance::whereBetween('date', [$monthStartDate, $monthEndDate])
            ->distinct('date')
            ->count('date');

        // Preload categories mapped by name for color lookups
        $categoryColors = WorkCategory::pluck('color', 'name')->toArray();

        $empQuery = Employee::where('employment_status', 'active')->with('department');
        if ($departmentId) {
            $empQuery->where('department_id', $departmentId);
        }
        $employees = $empQuery->get();

        $performanceData = [];

        foreach ($employees as $employee) {
            $userId = $employee->user_id ?: User::where('employee_id', $employee->id)->orWhere('email', $employee->email)->value('id');

            // 1. Completed Tasks from Todos (Assigned to employee or created by employee)
            $completedTodos = $userId ? Todo::where(function ($q) use ($userId) {
                $q->where('assigned_to_user_id', $userId)
                    ->orWhere('user_id', $userId);
            })
                ->where(function ($q) {
                    $q->where('is_completed', true)
                        ->orWhere('status', 'completed');
                })
                ->whereBetween(DB::raw('COALESCE(completed_at, updated_at)'), [$monthStart, $monthEnd])
                ->get() : collect();

            // Track any work entry ids already attached to completed todos to prevent double counting
            $linkedWorkEntryIds = $completedTodos->pluck('work_entry_id')->filter()->toArray();

            // 2. Daily Work Entries (manual deliverables logged)
            $workEntriesQuery = DailyWorkEntry::where('employee_id', $employee->id)
                ->whereBetween('date', [$monthStartDate, $monthEndDate])
                ->with('category');

            if (! empty($linkedWorkEntryIds)) {
                $workEntriesQuery->whereNotIn('id', $linkedWorkEntryIds);
            }

            $workEntries = $workEntriesQuery->get();
            $workEntriesQty = (int) $workEntries->sum('quantity');

            // Total Completed Tasks / Output Units
            $totalTasks = $completedTodos->count() + $workEntriesQty;

            // 3. Attendance & Days Active
            $monthAtt = DailyAttendance::where('employee_id', $employee->id)
                ->whereBetween('date', [$monthStartDate, $monthEndDate])
                ->get();

            $presentCount = $monthAtt->whereIn('status', ['present', 'wfh'])->count();
            $halfCount = $monthAtt->where('status', 'half_day')->count();
            $leaveCount = $monthAtt->where('status', 'leave')->count();
            $effectivePresent = $presentCount + ($halfCount * 0.5);

            // Attendance Rate % based on company working days recorded so far
            $totalBaseDays = $companyAttendanceDays > 0 ? $companyAttendanceDays : $monthAtt->count();
            $attendanceRate = $totalBaseDays > 0 ? round(($effectivePresent / $totalBaseDays) * 100, 1) : 0;

            // Days Active (distinct dates with attendance presence OR completed task OR work entry)
            $activeDates = collect();
            foreach ($monthAtt->whereIn('status', ['present', 'wfh', 'half_day']) as $att) {
                $activeDates->push(substr($att->date, 0, 10));
            }
            foreach ($completedTodos as $todo) {
                $d = $todo->completed_at ? $todo->completed_at->format('Y-m-d') : ($todo->updated_at ? $todo->updated_at->format('Y-m-d') : null);
                if ($d) {
                    $activeDates->push($d);
                }
            }
            foreach ($workEntries as $entry) {
                $activeDates->push(substr($entry->date, 0, 10));
            }
            $daysWorked = $activeDates->unique()->count();

            $avgDailyWork = $daysWorked > 0 ? round($totalTasks / $daysWorked, 1) : 0;

            // 4. Category breakdown to determine Top Category
            $categoryCounts = [];
            foreach ($completedTodos as $todo) {
                if ($todo->category) {
                    $categoryCounts[$todo->category] = ($categoryCounts[$todo->category] ?? 0) + 1;
                }
            }
            foreach ($workEntries as $entry) {
                $catName = $entry->category?->name;
                if ($catName) {
                    $categoryCounts[$catName] = ($categoryCounts[$catName] ?? 0) + ($entry->quantity ?: 1);
                }
            }
            arsort($categoryCounts);

            $topCategoryName = ! empty($categoryCounts) ? array_key_first($categoryCounts) : 'N/A';
            $topCategoryColor = $categoryColors[$topCategoryName] ?? match (strtolower($topCategoryName)) {
                'social media', 'digital marketing' => '#0071e3',
                'design', 'graphics' => '#af52de',
                'video', 'video editor' => '#ff2d55',
                'illustration' => '#ff9500',
                default => '#6366f1',
            };

            // 5. Rating logic
            // Excellent: High output + good attendance
            // Good: Consistent work + active attendance
            // Average: Present or some work
            // Needs Improvement: Inactive or poor attendance
            if ($totalTasks >= 5 && $attendanceRate >= 80) {
                $rating = 'Excellent';
                $ratingBadge = 'badge-success';
                $ratingColor = 'emerald';
            } elseif ($totalTasks >= 2 && $attendanceRate >= 70) {
                $rating = 'Good';
                $ratingBadge = 'badge-info';
                $ratingColor = 'indigo';
            } elseif ($totalTasks >= 1 || $attendanceRate >= 60 || $daysWorked >= 1) {
                $rating = 'Average';
                $ratingBadge = 'badge-warning';
                $ratingColor = 'amber';
            } else {
                $rating = 'Needs Improvement';
                $ratingBadge = 'badge-danger';
                $ratingColor = 'rose';
            }

            $performanceData[] = [
                'employee' => $employee,
                'total_works' => $totalTasks,
                'days_worked' => $daysWorked,
                'avg_daily_work' => $avgDailyWork,
                'attendance_rate' => $attendanceRate,
                'leave_count' => $leaveCount,
                'top_category' => $topCategoryName,
                'top_category_color' => $topCategoryColor,
                'rating' => $rating,
                'rating_color' => $ratingColor,
            ];
        }

        // Sort: primary total_works DESC, secondary attendance_rate DESC, tertiary days_worked DESC
        usort($performanceData, function ($a, $b) {
            if ($b['total_works'] !== $a['total_works']) {
                return $b['total_works'] <=> $a['total_works'];
            }
            if ($b['attendance_rate'] !== $a['attendance_rate']) {
                return $b['attendance_rate'] <=> $a['attendance_rate'];
            }

            return $b['days_worked'] <=> $a['days_worked'];
        });

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
