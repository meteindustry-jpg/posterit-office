<?php

namespace App\Http\Controllers;

use App\Models\DailyWorkEntry;
use App\Models\Department;
use App\Models\Employee;
use App\Models\WorkCategory;
use App\Services\AuditService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DailyWorkEntryController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->get('date', now()->format('Y-m-d'));
        $employeeId = $request->get('employee_id');
        $categoryId = $request->get('category_id');
        $departmentId = $request->get('department_id');

        $query = DailyWorkEntry::with(['employee.department', 'category', 'creator']);

        if (Auth::user()->isEmployee() && Auth::user()->employee) {
            $query->where('employee_id', Auth::user()->employee->id);
            $employeeId = Auth::user()->employee->id;
        } elseif ($employeeId) {
            $query->where('employee_id', $employeeId);
        }

        if ($date) {
            $query->where('date', $date);
        }
        if ($categoryId) {
            $query->where('work_category_id', $categoryId);
        }
        if ($departmentId) {
            $query->whereHas('employee', function ($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });
        }

        $entries = $query->orderBy('id', 'desc')->paginate(20)->withQueryString();

        $employees = Employee::where('employment_status', 'active')->orderBy('name')->get();
        $categories = WorkCategory::where('is_active', true)->orderBy('name')->get();
        $departments = Department::orderBy('name')->get();

        $totalWorksOnDate = DailyWorkEntry::where('date', $date)->sum('quantity');
        $activeEmployeesCount = Employee::where('employment_status', 'active')->count();
        $workedEmployeesCount = DailyWorkEntry::where('date', $date)->distinct('employee_id')->count('employee_id');

        return view('work-entries.index', compact(
            'entries',
            'date',
            'employees',
            'categories',
            'departments',
            'employeeId',
            'categoryId',
            'departmentId',
            'totalWorksOnDate',
            'activeEmployeesCount',
            'workedEmployeesCount'
        ));
    }

    public function batchCreate(Request $request)
    {
        $date = $request->get('date', now()->format('Y-m-d'));
        $employees = Employee::where('employment_status', 'active')
            ->with('department')
            ->orderBy('name')
            ->get();
        $categories = WorkCategory::where('is_active', true)->orderBy('name')->get();

        // Existing entries for this date
        $existingEntries = DailyWorkEntry::where('date', $date)
            ->with(['employee', 'category'])
            ->get();

        return view('work-entries.batch', compact('date', 'employees', 'categories', 'existingEntries'));
    }

    public function batchStore(Request $request)
    {
        $request->validate([
            'date' => ['required', 'date'],
            'entries' => ['required', 'array', 'min:1'],
            'entries.*.employee_id' => ['required', 'exists:employees,id'],
            'entries.*.work_category_id' => ['required', 'exists:work_categories,id'],
            'entries.*.quantity' => ['required', 'integer', 'min:1'],
            'entries.*.remarks' => ['nullable', 'string', 'max:255'],
        ]);

        $date = $request->date;
        $count = 0;

        DB::transaction(function () use ($request, $date, &$count) {
            foreach ($request->entries as $item) {
                DailyWorkEntry::create([
                    'date' => $date,
                    'employee_id' => $item['employee_id'],
                    'work_category_id' => $item['work_category_id'],
                    'quantity' => $item['quantity'],
                    'remarks' => $item['remarks'] ?? null,
                    'created_by_user_id' => Auth::id(),
                ]);
                $count++;
            }
        });

        AuditService::log('bulk_create', 'Work Entry', "Recorded {$count} daily work entries for date {$date}");

        return redirect()->route('work-entries.index', ['date' => $date])
            ->with('success', "{$count} work entries successfully logged for {$date}.");
    }

    public function edit(DailyWorkEntry $workEntry)
    {
        $employees = Employee::where('employment_status', 'active')->orderBy('name')->get();
        $categories = WorkCategory::where('is_active', true)->orderBy('name')->get();
        return view('work-entries.edit', compact('workEntry', 'employees', 'categories'));
    }

    public function update(Request $request, DailyWorkEntry $workEntry)
    {
        $validated = $request->validate([
            'date' => ['required', 'date'],
            'employee_id' => ['required', 'exists:employees,id'],
            'work_category_id' => ['required', 'exists:work_categories,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'remarks' => ['nullable', 'string', 'max:255'],
        ]);

        $old = $workEntry->toArray();
        $workEntry->update($validated);

        AuditService::log('update', 'Work Entry', "Updated work entry #{$workEntry->id}", $old, $workEntry->toArray());

        return redirect()->route('work-entries.index', ['date' => $workEntry->date->format('Y-m-d')])
            ->with('success', 'Work entry updated successfully.');
    }

    public function destroy(DailyWorkEntry $workEntry)
    {
        $old = $workEntry->toArray();
        $date = $workEntry->date->format('Y-m-d');
        $workEntry->delete();

        AuditService::log('delete', 'Work Entry', "Deleted work entry #{$workEntry->id}", $old);

        return back()->with('success', 'Work entry deleted.');
    }
}
