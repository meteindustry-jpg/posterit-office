<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function showRegister()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        $departments = Department::orderBy('name')->get();

        return view('auth.register', compact('departments'));
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email', 'unique:employees,email'],
            'mobile_number' => ['nullable', 'string', 'max:30'],
            'department_id' => ['required', 'exists:departments,id'],
            'designation' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $employeeCode = Employee::generateUniqueCode();

        $employee = Employee::create([
            'employee_code' => $employeeCode,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'mobile_number' => $validated['mobile_number'] ?? null,
            'designation' => $validated['designation'],
            'department_id' => $validated['department_id'],
            'joining_date' => now()->format('Y-m-d'),
            'employment_status' => 'inactive', // Inactive until Super Admin approves
            'leave_quota' => 18,
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'employee',
            'employee_id' => $employee->id,
            'is_active' => false, // Inactive until Super Admin approves
        ]);

        $employee->update(['user_id' => $user->id]);

        AuditService::log('register', 'Authentication', "New employee registration pending approval: {$employee->name} ({$employeeCode})");

        return redirect()->route('login')->with('success', "Registration submitted successfully! Your account ({$employee->name}) is pending approval from the Super Admin. You will be able to sign in once approved.");
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $loginInput = trim($request->input('email'));
        $password = $request->input('password');

        // Look up by exact email or name (case-insensitive)
        $user = User::where('email', $loginInput)
            ->orWhereRaw('LOWER(email) = ?', [strtolower($loginInput)])
            ->orWhereRaw('LOWER(name) = ?', [strtolower($loginInput)])
            ->orWhereRaw('LOWER(name) LIKE ?', ['%'.strtolower($loginInput).'%'])
            ->first();

        if ($user && Hash::check($password, $user->password)) {
            if (! $user->is_active) {
                throw ValidationException::withMessages([
                    'email' => 'Your account has been registered and is pending approval from the Super Admin. Please contact management to activate your access.',
                ]);
            }

            Auth::login($user, $request->boolean('remember'));
            $request->session()->regenerate();

            AuditService::log('login', 'Authentication', "User {$user->name} logged into system.");

            return redirect()->intended(route('dashboard'));
        }

        throw ValidationException::withMessages([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }

    public function logout(Request $request)
    {
        if (Auth::check()) {
            AuditService::log('logout', 'Authentication', 'User logged out.');
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function profile()
    {
        $user = Auth::user();
        $user->load('employee.department');

        return view('profile.show', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'avatar' => ['nullable', 'image', 'max:2048'],
            'mobile_number' => ['nullable', 'string', 'max:30'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:30'],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'bank_account_no' => ['nullable', 'string', 'max:50'],
            'bank_ifsc' => ['nullable', 'string', 'max:30'],
            'upi_id' => ['nullable', 'string', 'max:100'],
        ]);

        $old = ['name' => $user->name, 'email' => $user->email];
        $path = null;

        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = $path;
        }

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->save();

        if ($user->employee) {
            $empData = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'mobile_number' => $validated['mobile_number'] ?? $user->employee->mobile_number,
                'emergency_contact_name' => $validated['emergency_contact_name'] ?? null,
                'emergency_contact_phone' => $validated['emergency_contact_phone'] ?? null,
                'bank_name' => $validated['bank_name'] ?? null,
                'bank_account_no' => $validated['bank_account_no'] ?? null,
                'bank_ifsc' => $validated['bank_ifsc'] ?? null,
                'upi_id' => $validated['upi_id'] ?? null,
            ];
            if ($path) {
                $empData['photo'] = $path;
            }
            $user->employee->update($empData);
        }

        AuditService::log('update', 'Profile', 'User updated personal profile details.', $old, ['name' => $user->name, 'email' => $user->email]);

        return back()->with('success', 'Profile and account details updated successfully.');
    }

    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $user = Auth::user();
        $user->password = Hash::make($validated['password']);
        $user->save();

        AuditService::log('update', 'Security', 'User changed account password.');

        return back()->with('success', 'Password updated successfully.');
    }
}
