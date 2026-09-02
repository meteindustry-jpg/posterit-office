<?php

namespace App\Http\Controllers;

use App\Models\DailyAttendance;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Services\AuditService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeaveController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = LeaveRequest::with(['employee.department', 'leaveType', 'actionBy']);

        // If employee role, only show their own leaves
        if ($user->isEmployee() && $user->employee) {
            $query->where('employee_id', $user->employee->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }
        if ($request->filled('leave_type_id')) {
            $query->where('leave_type_id', $request->leave_type_id);
        }

        $leaves = $query->orderBy('id', 'desc')->paginate(15)->withQueryString();

        $employees = Employee::where('employment_status', 'active')->orderBy('name')->get();
        $leaveTypes = LeaveType::all();

        $baseStatQuery = LeaveRequest::query();
        if ($user->isEmployee() && $user->employee) {
            $baseStatQuery->where('employee_id', $user->employee->id);
        }

        $stats = [
            'all' => (clone $baseStatQuery)->count(),
            'pending' => (clone $baseStatQuery)->where('status', 'pending')->count(),
            'approved' => (clone $baseStatQuery)->where('status', 'approved')->whereYear('start_date', now()->year)->count(),
            'rejected' => (clone $baseStatQuery)->where('status', 'rejected')->whereYear('start_date', now()->year)->count(),
        ];

        return view('leaves.index', compact('leaves', 'employees', 'leaveTypes', 'stats'));
    }

    public function create()
    {
        $user = Auth::user();
        $employees = Employee::where('employment_status', 'active')->orderBy('name')->get();
        $leaveTypes = LeaveType::all();
        $currentEmployee = $user->employee;

        return view('leaves.create', compact('employees', 'leaveTypes', 'currentEmployee'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $rules = [
            'leave_type_id' => ['required', 'exists:leave_types,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'reason' => ['required', 'string', 'max:500'],
        ];

        if ($user->isAdmin() || $user->isManager()) {
            $rules['employee_id'] = ['required', 'exists:employees,id'];
        }

        $validated = $request->validate($rules);

        $employeeId = $user->isEmployee() ? $user->employee->id : $validated['employee_id'];

        $start = Carbon::parse($validated['start_date']);
        $end = Carbon::parse($validated['end_date']);
        $totalDays = $start->diffInDays($end) + 1;

        $leave = LeaveRequest::create([
            'employee_id' => $employeeId,
            'leave_type_id' => $validated['leave_type_id'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'total_days' => $totalDays,
            'reason' => $validated['reason'],
            'status' => 'pending',
        ]);

        AuditService::log('create', 'Leave Request', "Submitted leave request #{$leave->id} for {$totalDays} days", null, $leave->toArray());

        return redirect()->route('leaves.index')->with('success', 'Leave request submitted successfully.');
    }

    public function updateStatus(Request $request, LeaveRequest $leave)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:approved,rejected'],
            'action_remarks' => ['nullable', 'string', 'max:255'],
        ]);

        $old = $leave->toArray();

        $leave->update([
            'status' => $validated['status'],
            'action_remarks' => $validated['action_remarks'] ?? $request->input('action_remarks'),
            'action_by_user_id' => Auth::id(),
        ]);

        // Auto-sync approved leave days to daily_attendances
        if ($validated['status'] === 'approved') {
            $curr = Carbon::parse($leave->start_date);
            $end = Carbon::parse($leave->end_date);
            $typeName = $leave->leaveType ? $leave->leaveType->name : 'Approved Leave';

            while ($curr->lte($end)) {
                $dateStr = $curr->format('Y-m-d');
                DailyAttendance::updateOrCreate(
                    [
                        'employee_id' => $leave->employee_id,
                        'date' => Carbon::parse($dateStr)->format('Y-m-d 00:00:00'),
                    ],
                    [
                        'status' => 'leave',
                        'check_in' => null,
                        'check_out' => null,
                        'recorded_by_user_id' => Auth::id(),
                        'remarks' => "Approved Leave: {$typeName}",
                    ]
                );
                $curr->addDay();
            }
        } elseif ($old['status'] === 'approved' && $validated['status'] === 'rejected') {
            $curr = Carbon::parse($leave->start_date);
            $end = Carbon::parse($leave->end_date);
            while ($curr->lte($end)) {
                $dateStr = $curr->format('Y-m-d');
                DailyAttendance::where('employee_id', $leave->employee_id)
                    ->whereDate('date', $dateStr)
                    ->where('status', 'leave')
                    ->where('remarks', 'like', 'Approved Leave%')
                    ->delete();
                $curr->addDay();
            }
        }

        AuditService::log('update', 'Leave Request', "Leave request #{$leave->id} marked as {$validated['status']}", $old, $leave->toArray());

        return back()->with('success', "Leave request {$validated['status']} successfully.");
    }
}
