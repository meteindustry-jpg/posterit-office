<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $pendingUsers = User::where('is_active', false)
            ->whereNotNull('employee_id')
            ->with('employee.department')
            ->orderByDesc('created_at')
            ->get();

        $users = User::with('employee.department')->orderBy('id')->paginate(15);
        $employees = Employee::where('employment_status', 'active')->orderBy('name')->get();

        return view('users.index', compact('users', 'employees', 'pendingUsers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'role' => ['required', 'in:super_admin,admin,manager,employee'],
            'employee_id' => ['nullable', 'exists:employees,id'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['is_active'] = $request->boolean('is_active', true);

        $user = User::create($validated);

        if ($user->employee_id) {
            Employee::where('id', $user->employee_id)->update(['user_id' => $user->id]);
        }

        AuditService::log('create', 'User Management', "Created user {$user->name} with role {$user->role}");

        return back()->with('success', "User '{$user->name}' created successfully.");
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'password' => ['nullable', 'string', 'min:6'],
            'role' => ['required', 'in:super_admin,admin,manager,employee'],
            'employee_id' => ['nullable', 'exists:employees,id'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if (! empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $validated['is_active'] = $request->boolean('is_active');
        $old = $user->toArray();
        $user->update($validated);

        AuditService::log('update', 'User Management', "Updated user {$user->name}", $old, $user->toArray());

        return back()->with('success', "User '{$user->name}' updated.");
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->withErrors(['error' => 'You cannot delete your own account.']);
        }

        if ($user->isSuperAdmin() && User::where('role', 'super_admin')->count() <= 1) {
            return back()->withErrors(['error' => 'Cannot delete the only Super Admin account.']);
        }

        $name = $user->name;
        $old = $user->toArray();
        $user->delete();

        AuditService::log('delete', 'User Management', "Deleted user {$name}", $old);

        return back()->with('success', "User '{$name}' deleted.");
    }

    public function approve(User $user)
    {
        $user->update(['is_active' => true]);

        if ($user->employee) {
            $user->employee->update(['employment_status' => 'active']);
        }

        AuditService::log('approve', 'User Management', "Super Admin approved registration for {$user->name} ({$user->email})");

        return back()->with('success', "Account for '{$user->name}' has been approved and activated.");
    }

    public function reject(User $user)
    {
        $name = $user->name;
        $employee = $user->employee;

        if ($employee) {
            $employee->delete();
        }

        $user->delete();

        AuditService::log('reject', 'User Management', "Super Admin rejected registration for {$name}");

        return back()->with('success', "Registration request for '{$name}' has been rejected and removed.");
    }
}
