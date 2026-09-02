<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $query = Employee::with(['department', 'user']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('employee_code', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('designation', 'like', "%{$search}%");
            });
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('status')) {
            $query->where('employment_status', $request->status);
        }

        $employees = $query->orderBy('id', 'asc')->paginate(12)->withQueryString();
        $departments = Department::orderBy('name')->get();

        $stats = [
            'total' => Employee::count(),
            'active' => Employee::where('employment_status', 'active')->count(),
            'inactive' => Employee::where('employment_status', 'inactive')->count(),
            'resigned' => Employee::where('employment_status', 'resigned')->count(),
        ];

        return view('employees.index', compact('employees', 'departments', 'stats'));
    }

    public function create()
    {
        $departments = Department::orderBy('name')->get();
        $nextCode = Employee::generateUniqueCode();

        return view('employees.create', compact('departments', 'nextCode'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_code' => ['required', 'string', 'max:50', 'unique:employees,employee_code'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:employees,email', 'unique:users,email'],
            'mobile_number' => ['nullable', 'string', 'max:30'],
            'designation' => ['required', 'string', 'max:255'],
            'department_id' => ['required', 'exists:departments,id'],
            'joining_date' => ['nullable', 'date'],
            'employment_status' => ['required', 'in:active,inactive,resigned'],
            'salary' => ['nullable', 'numeric', 'min:0'],
            'leave_quota' => ['required', 'integer', 'min:0'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:30'],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'bank_account_no' => ['nullable', 'string', 'max:50'],
            'bank_ifsc' => ['nullable', 'string', 'max:30'],
            'upi_id' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
            'photo' => ['nullable', 'image', 'max:2048'],
            'create_user_account' => ['nullable', 'boolean'],
            'password' => ['nullable', 'required_if:create_user_account,1', 'string', 'min:6'],
        ]);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('employees', 'public');
        }

        $userId = null;
        if ($request->boolean('create_user_account')) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($request->password ?? 'password'),
                'role' => 'employee',
                'is_active' => $validated['employment_status'] === 'active',
            ]);
            $userId = $user->id;
        }

        $employee = Employee::create([
            'employee_code' => $validated['employee_code'],
            'user_id' => $userId,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'mobile_number' => $validated['mobile_number'] ?? null,
            'emergency_contact_name' => $validated['emergency_contact_name'] ?? null,
            'emergency_contact_phone' => $validated['emergency_contact_phone'] ?? null,
            'bank_name' => $validated['bank_name'] ?? null,
            'bank_account_no' => $validated['bank_account_no'] ?? null,
            'bank_ifsc' => $validated['bank_ifsc'] ?? null,
            'upi_id' => $validated['upi_id'] ?? null,
            'designation' => $validated['designation'],
            'department_id' => $validated['department_id'],
            'joining_date' => $validated['joining_date'] ?? null,
            'employment_status' => $validated['employment_status'],
            'salary' => $validated['salary'] ?? null,
            'leave_quota' => $validated['leave_quota'],
            'notes' => $validated['notes'] ?? null,
            'photo' => $photoPath,
        ]);

        if ($userId) {
            User::where('id', $userId)->update(['employee_id' => $employee->id]);
        }

        AuditService::log('create', 'Employee', "Created employee {$employee->name} ({$employee->employee_code})", null, $employee->toArray());

        return redirect()->route('employees.index')->with('success', "Employee {$employee->name} created successfully.");
    }

    public function show(Employee $employee)
    {
        $employee->load(['department', 'user']);

        $attendances = $employee->attendances()
            ->orderBy('date', 'desc')
            ->take(30)
            ->get();

        $workEntries = $employee->workEntries()
            ->with('category')
            ->orderBy('date', 'desc')
            ->take(30)
            ->get();

        $leaveRequests = $employee->leaveRequests()
            ->with('leaveType')
            ->orderBy('start_date', 'desc')
            ->get();

        // Performance calculation
        $currentMonth = now()->month;
        $currentYear = now()->year;
        $monthlyWorks = (int) $employee->workEntries()
            ->whereYear('date', $currentYear)
            ->whereMonth('date', $currentMonth)
            ->sum('quantity');

        $totalWorks = (int) $employee->workEntries()->sum('quantity');

        $monthAtt = $employee->attendances()
            ->whereYear('date', $currentYear)
            ->whereMonth('date', $currentMonth)
            ->get();
        $totalAttCount = $monthAtt->count();
        $presentScore = $monthAtt->whereIn('status', ['present', 'wfh'])->count() + ($monthAtt->where('status', 'half_day')->count() * 0.5);
        $attendanceRate = $totalAttCount > 0 ? round(($presentScore / $totalAttCount) * 100, 1) : 100;

        return view('employees.show', compact(
            'employee',
            'attendances',
            'workEntries',
            'leaveRequests',
            'monthlyWorks',
            'totalWorks',
            'attendanceRate'
        ));
    }

    public function edit(Employee $employee)
    {
        $departments = Department::orderBy('name')->get();
        return view('employees.edit', compact('employee', 'departments'));
    }

    public function update(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'employee_code' => ['required', 'string', 'max:50', 'unique:employees,employee_code,' . $employee->id],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:employees,email,' . $employee->id],
            'mobile_number' => ['nullable', 'string', 'max:30'],
            'designation' => ['required', 'string', 'max:255'],
            'department_id' => ['required', 'exists:departments,id'],
            'joining_date' => ['nullable', 'date'],
            'employment_status' => ['required', 'in:active,inactive,resigned'],
            'salary' => ['nullable', 'numeric', 'min:0'],
            'leave_quota' => ['required', 'integer', 'min:0'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:30'],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'bank_account_no' => ['nullable', 'string', 'max:50'],
            'bank_ifsc' => ['nullable', 'string', 'max:30'],
            'upi_id' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
            'photo' => ['nullable', 'image', 'max:2048'],
        ]);

        $oldValues = $employee->toArray();

        if ($request->hasFile('photo')) {
            if ($employee->photo) {
                Storage::disk('public')->delete($employee->photo);
            }
            $validated['photo'] = $request->file('photo')->store('employees', 'public');
        }

        $employee->update($validated);

        // Update associated user if active status changed
        if ($employee->user) {
            $employee->user->update([
                'name' => $employee->name,
                'email' => $employee->email,
                'is_active' => $employee->employment_status === 'active',
            ]);
        }

        AuditService::log('update', 'Employee', "Updated employee {$employee->name} ({$employee->employee_code})", $oldValues, $employee->toArray());

        return redirect()->route('employees.show', $employee)->with('success', 'Employee updated successfully.');
    }

    public function destroy(Employee $employee)
    {
        $old = $employee->toArray();
        $name = $employee->name;

        if ($employee->user) {
            $employee->user->delete();
        }
        $employee->delete();

        AuditService::log('delete', 'Employee', "Deleted employee {$name}", $old);

        return redirect()->route('employees.index')->with('success', "Employee {$name} deleted successfully.");
    }
}
