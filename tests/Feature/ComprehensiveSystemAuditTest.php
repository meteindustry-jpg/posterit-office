<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Employee;
use App\Models\Holiday;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\PayrollRun;
use App\Models\Payslip;
use App\Models\Todo;
use App\Models\User;
use App\Models\WorkCategory;
use App\Models\DailyWorkEntry;
use App\Models\DailyAttendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ComprehensiveSystemAuditTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_dashboards_render_for_all_roles(): void
    {
        foreach (['admin', 'manager', 'employee'] as $role) {
            $user = User::where('role', $role)->first();
            $this->assertNotNull($user, "User for role {$role} must exist.");

            $response = $this->actingAs($user)->get('/dashboard');
            $response->assertStatus(200);
        }
    }

    public function test_attendance_endpoints_render(): void
    {
        $admin = User::where('role', 'admin')->first();
        $employee = User::where('role', 'employee')->first();

        // Admin checks
        $this->actingAs($admin)->get('/attendance')->assertStatus(200);
        $this->actingAs($admin)->get('/attendance/monthly-grid')->assertStatus(200);
        $this->actingAs($admin)->get('/attendance/export-monthly?month=' . now()->month . '&year=' . now()->year)->assertStatus(200);

        // Employee clock in / out
        $this->actingAs($employee)->post('/attendance/clock-in')->assertRedirect();
        $this->actingAs($employee)->post('/attendance/clock-out')->assertRedirect();
    }

    public function test_employees_crud_endpoints(): void
    {
        $admin = User::where('role', 'admin')->first();
        $emp = Employee::first();

        $this->actingAs($admin)->get('/employees')->assertStatus(200);
        $this->actingAs($admin)->get('/employees/create')->assertStatus(200);
        if ($emp) {
            $this->actingAs($admin)->get("/employees/{$emp->id}")->assertStatus(200);
            $this->actingAs($admin)->get("/employees/{$emp->id}/edit")->assertStatus(200);
        }
    }

    public function test_work_entries_endpoints(): void
    {
        $manager = User::where('role', 'manager')->first();
        $employee = User::where('role', 'employee')->first();

        $this->actingAs($manager)->get('/work-entries')->assertStatus(200);
        $this->actingAs($manager)->get('/work-entries/batch')->assertStatus(200);
        $this->actingAs($employee)->get('/work-entries')->assertStatus(200);
    }

    public function test_leaves_endpoints(): void
    {
        $employee = User::where('role', 'employee')->first();
        $manager = User::where('role', 'manager')->first();

        $this->actingAs($employee)->get('/leaves')->assertStatus(200);
        $this->actingAs($employee)->get('/leaves/create')->assertStatus(200);
        $this->actingAs($manager)->get('/leaves')->assertStatus(200);
    }

    public function test_payroll_endpoints(): void
    {
        $admin = User::where('role', 'admin')->first();
        $employee = User::where('role', 'employee')->first();

        $this->actingAs($admin)->get('/payroll')->assertStatus(200);
        $this->actingAs($employee)->get('/my-payslips')->assertStatus(200);
    }

    public function test_holidays_endpoints(): void
    {
        $admin = User::where('role', 'admin')->first();
        $this->actingAs($admin)->get('/holidays')->assertStatus(200);
    }

    public function test_departments_and_categories_endpoints(): void
    {
        $admin = User::where('role', 'admin')->first();
        $this->actingAs($admin)->get('/departments')->assertStatus(200);
        $this->actingAs($admin)->get('/categories')->assertStatus(200);
    }

    public function test_todos_endpoints(): void
    {
        $employee = User::where('role', 'employee')->first();
        $this->actingAs($employee)->get('/todos')->assertStatus(200);
    }

    public function test_reports_and_performance_endpoints(): void
    {
        $manager = User::where('role', 'manager')->first();
        $this->actingAs($manager)->get('/reports')->assertStatus(200);
        $this->actingAs($manager)->get('/performance')->assertStatus(200);
    }

    public function test_settings_and_audit_logs_endpoints(): void
    {
        $superAdmin = User::where('role', 'super_admin')->first();
        $this->actingAs($superAdmin)->get('/settings')->assertStatus(200);
        $this->actingAs($superAdmin)->get('/audit-logs')->assertStatus(200);
        $this->actingAs($superAdmin)->get('/users')->assertStatus(200);

        // Test updating expanded settings
        $res = $this->actingAs($superAdmin)->post('/settings', [
            'company_name' => 'Posterit Creative Studio Ltd',
            'company_tagline' => 'Next-Gen Media & Creative Agency',
            'company_email' => 'admin@posterit.com',
            'company_phone' => '+91 99999 88888',
            'company_website' => 'https://posterit.com',
            'company_tax_id' => 'GSTIN27AABCP1234F1Z5',
            'company_address' => 'Mumbai Media Center, Bandra',
            'working_days' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'],
            'office_timing_start' => '10:00',
            'office_timing_end' => '19:00',
            'late_grace_minutes' => 20,
            'half_day_hours' => 4.5,
            'default_leave_count' => 20,
            'currency_symbol' => '₹',
            'theme_mode' => 'light',
            'payslip_footer_note' => 'Confidential & Proprietary',
            'attendance_reminder_enabled' => 1,
        ]);
        $res->assertRedirect();
        $this->assertEquals('Posterit Creative Studio Ltd', \App\Models\CompanySetting::get('company_name'));
        $this->assertEquals(20, (int) \App\Models\CompanySetting::get('late_grace_minutes'));
    }

    public function test_profile_and_global_search_endpoints(): void
    {
        $employee = User::where('role', 'employee')->first();
        $this->actingAs($employee)->get('/profile')->assertStatus(200);
        $res = $this->actingAs($employee)->get('/api/search?q=test');
        $res->assertStatus(200);
    }

    public function test_global_search_returns_accessible_url_for_employees(): void
    {
        $employeeUser = User::where('role', 'employee')->first();
        $otherEmp = Employee::where('id', '!=', $employeeUser->employee?->id)->first();

        $res = $this->actingAs($employeeUser)->get('/api/search?q=' . urlencode(substr($otherEmp->name, 0, 4)));
        $res->assertStatus(200);

        $json = $res->json();
        if (!empty($json['employees'])) {
            $firstResultUrl = $json['employees'][0]['url'];
            // Non-admin search result URL should NOT be employees.show (which gives 403)
            $this->assertStringNotContainsString('/employees/', $firstResultUrl);
        }
    }

    public function test_leave_approval_syncs_to_daily_attendance(): void
    {
        $admin = User::where('role', 'admin')->first();
        $employee = Employee::first();
        $leaveType = LeaveType::first();

        $leave = LeaveRequest::create([
            'employee_id' => $employee->id,
            'leave_type_id' => $leaveType->id,
            'start_date' => now()->addDays(5)->format('Y-m-d'),
            'end_date' => now()->addDays(6)->format('Y-m-d'),
            'total_days' => 2,
            'reason' => 'Family occasion',
            'status' => 'pending',
        ]);

        // Approve leave
        $this->actingAs($admin)->patch("/leaves/{$leave->id}/status", [
            'status' => 'approved',
            'action_remarks' => 'Approved by manager',
        ])->assertRedirect();

        // Verify DailyAttendance was created for the leave dates
        $att1 = DailyAttendance::where('employee_id', $employee->id)
            ->whereDate('date', $leave->start_date)
            ->first();
        $this->assertNotNull($att1);
        $this->assertEquals('leave', $att1->status);
        $this->assertStringContainsString('Approved Leave', $att1->remarks);

        // Reject previously approved leave and ensure attendance is cleaned up
        $this->actingAs($admin)->patch("/leaves/{$leave->id}/status", [
            'status' => 'rejected',
            'action_remarks' => 'Revoked',
        ])->assertRedirect();

        $attAfterReject = DailyAttendance::where('employee_id', $employee->id)
            ->whereDate('date', $leave->start_date)
            ->first();
        $this->assertNull($attAfterReject);
    }

    public function test_employee_profile_settings_and_bank_details(): void
    {
        $employeeUser = User::where('role', 'employee')->first();
        $this->assertNotNull($employeeUser);

        // Update profile with contact, emergency contact, and bank details
        $res = $this->actingAs($employeeUser)->put('/profile', [
            'name' => $employeeUser->name,
            'email' => $employeeUser->email,
            'mobile_number' => '+91 98765 11111',
            'emergency_contact_name' => 'Jane Doe (Spouse)',
            'emergency_contact_phone' => '+91 98765 22222',
            'bank_name' => 'HDFC Bank Ltd',
            'bank_account_no' => '50100123456789',
            'bank_ifsc' => 'HDFC0000123',
            'upi_id' => 'janedoe@okhdfcbank',
        ]);
        $res->assertRedirect();

        $employee = $employeeUser->fresh()->employee;
        if ($employee) {
            $this->assertEquals('+91 98765 11111', $employee->mobile_number);
            $this->assertEquals('Jane Doe (Spouse)', $employee->emergency_contact_name);
            $this->assertEquals('HDFC Bank Ltd', $employee->bank_name);
            $this->assertEquals('50100123456789', $employee->bank_account_no);
            $this->assertEquals('HDFC0000123', $employee->bank_ifsc);
        }
    }

    public function test_dynamic_company_logo_and_brand_in_sidebar_and_login(): void
    {
        $oldName = \App\Models\CompanySetting::get('company_name');
        $oldTagline = \App\Models\CompanySetting::get('company_tagline');

        \App\Models\CompanySetting::set('company_name', 'Acme Studios');
        \App\Models\CompanySetting::set('company_tagline', 'Creative Agency');

        $admin = User::where('role', 'admin')->first();
        $response = $this->actingAs($admin)->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Acme Studios');
        $response->assertSee('Creative Agency');

        auth()->logout();
        $loginResponse = $this->get('/login');
        $loginResponse->assertStatus(200);
        $loginResponse->assertSee('Acme Studios');

        \App\Models\CompanySetting::set('company_name', $oldName);
        \App\Models\CompanySetting::set('company_tagline', $oldTagline);
    }

    public function test_employee_self_registration_flow(): void
    {
        auth()->logout();

        // 1. Visit register page
        $res = $this->get('/register');
        $res->assertStatus(200);
        $res->assertSee('Employee Registration');

        // 2. Submit valid registration
        $dept = \App\Models\Department::first();
        $regData = [
            'name' => 'Sara Ali',
            'email' => 'sara.ali@example.com',
            'mobile_number' => '+91 99999 88888',
            'department_id' => $dept->id,
            'designation' => 'Motion Designer',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ];

        $postRes = $this->post('/register', $regData);
        $postRes->assertRedirect(route('dashboard'));

        // 3. Verify user & employee in DB
        $user = User::where('email', 'sara.ali@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('employee', $user->role);
        $this->assertTrue((bool)$user->is_active);

        $employee = Employee::where('email', 'sara.ali@example.com')->first();
        $this->assertNotNull($employee);
        $this->assertEquals('Motion Designer', $employee->designation);
        $this->assertEquals($user->id, $employee->user_id);
        $this->assertEquals($employee->id, $user->employee_id);
        $this->assertStringStartsWith('EMP-', $employee->employee_code);

        // 4. Authenticated session check
        $this->assertAuthenticatedAs($user);
    }

    public function test_todo_reference_image_detection_and_preview_methods(): void
    {
        $user = User::first();
        $this->assertNotNull($user);

        // 1. Direct Image URL
        $todoImage = Todo::create([
            'user_id' => $user->id,
            'title' => 'Design Banner with Image Reference',
            'priority' => 'medium',
            'status' => 'todo',
            'category' => 'Graphics',
            'reference_url' => 'https://images.unsplash.com/photo-1579783902614-a3fb3927b675.jpg',
        ]);
        $this->assertTrue($todoImage->isReferenceImage());
        $this->assertEquals('https://images.unsplash.com/photo-1579783902614-a3fb3927b675.jpg', $todoImage->referenceImagePreviewUrl());
        $this->assertTrue($todoImage->is_reference_image);
        $this->assertNotNull($todoImage->reference_preview);

        // 2. Google Drive File Link
        $todoDrive = Todo::create([
            'user_id' => $user->id,
            'title' => 'Design with Google Drive Image',
            'priority' => 'high',
            'status' => 'todo',
            'category' => 'Branding',
            'reference_url' => 'https://drive.google.com/file/d/1a2b3c4d5e6f7g8h9/view?usp=sharing',
        ]);
        $this->assertTrue($todoDrive->isReferenceImage());
        $this->assertEquals('https://drive.google.com/thumbnail?id=1a2b3c4d5e6f7g8h9&sz=w1000', $todoDrive->referenceImagePreviewUrl());

        // 3. Local Stored Image File
        $todoLocal = Todo::create([
            'user_id' => $user->id,
            'title' => 'Design with Uploaded Screenshot',
            'priority' => 'low',
            'status' => 'todo',
            'category' => 'Social Media',
            'reference_url' => 'todos/sample_design.png',
        ]);
        $this->assertTrue($todoLocal->isReferenceImage());
        $this->assertStringContainsString('todos/sample_design.png', $todoLocal->referenceImagePreviewUrl());

        // 4. Non-Image Reference Link (e.g. Figma file)
        $todoFigma = Todo::create([
            'user_id' => $user->id,
            'title' => 'Figma UI Flow',
            'priority' => 'medium',
            'status' => 'todo',
            'category' => 'UI/UX',
            'reference_url' => 'https://www.figma.com/design/abcdef123456/Mobile-App-Flow',
        ]);
        $this->assertFalse($todoFigma->isReferenceImage());

        // 5. Todo Index route renders view successfully
        $res = $this->actingAs($user)->get(route('todos.index'));
        $res->assertOk();
        $res->assertSee('Design Banner with Image Reference');
        $res->assertSee('Design with Google Drive Image');
    }

    public function test_sam_mete_login_with_name_or_email(): void
    {
        // 1. Can log in with Name 'Sam Mete'
        $resName = $this->post(route('login'), [
            'email' => 'Sam Mete',
            'password' => 'password',
        ]);
        $resName->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
        $this->assertEquals('super_admin', \Illuminate\Support\Facades\Auth::user()->role);

        \Illuminate\Support\Facades\Auth::logout();

        // 2. Can log in with Name 'Samir Mete' (case insensitive)
        $resName2 = $this->post(route('login'), [
            'email' => 'samir mete',
            'password' => 'password',
        ]);
        $resName2->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();

        \Illuminate\Support\Facades\Auth::logout();

        // 3. Can log in with Email 'samir@posterit.com'
        $resEmail = $this->post(route('login'), [
            'email' => 'samir@posterit.com',
            'password' => 'password',
        ]);
        $resEmail->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
    }
}
