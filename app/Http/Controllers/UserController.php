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
        $users = User::with('employee.department')->orderBy('id')->paginate(15);
        $employees = Employee::where('employment_status', 'active')->orderBy('name')->get();
        return view('users.index', compact('users', 'employees'));
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
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
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

        $name = $user->name;
        $old = $user->toArray();
        $user->delete();

        AuditService::log('delete', 'User Management', "Deleted user {$name}", $old);

        return back()->with('success', "User '{$name}' deleted.");
    }
}
