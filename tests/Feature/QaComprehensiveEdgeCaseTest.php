<?php

namespace Tests\Feature;

use App\Models\DailyAttendance;
use App\Models\DailyWorkEntry;
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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class QaComprehensiveEdgeCaseTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    /**
     * TEST SUITE 1: SECURITY & RBAC PERMISSION BOUNDARIES
     */
    public function test_qa_employee_cannot_access_restricted_admin_routes(): void
    {
        $employee = User::where('role', 'employee')->first();

        // 1. Employee cannot access Company Settings
        $this->actingAs($employee)->get('/settings')->assertStatus(403);

        // 2. Employee cannot access User Management
        $this->actingAs($employee)->get('/users')->assertStatus(403);

        // 3. Employee cannot access Audit Trail
        $this->actingAs($employee)->get('/audit-logs')->assertStatus(403);

        // 4. Employee cannot access Employee Management CRUD
        $this->actingAs($employee)->get('/employees')->assertStatus(403);
        $this->actingAs($employee)->get('/employees/create')->assertStatus(403);

        // 5. Employee cannot generate payroll
        $this->actingAs($employee)->post('/payroll/generate', [
            'month' => 9,
            'year' => 2026,
        ])->assertStatus(403);
    }

    public function test_qa_inactive_user_cannot_login(): void
    {
        $user = User::where('role', 'employee')->first();
        $user->update(['is_active' => false]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    /**
     * TEST SUITE 2: ATTENDANCE RESILIENCE & EDGE CASES
     */
    public function test_qa_repeated_clock_in_does_not_create_duplicate_attendance_records(): void
    {
        $employee = User::where('role', 'employee')->first();
        $empRecord = $employee->employee;

        // First Clock-in
        $this->actingAs($employee)->post('/attendance/clock-in')->assertRedirect();
        
        // Second Clock-in immediately after
        $this->actingAs($employee)->post('/attendance/clock-in')->assertRedirect();

        $records = DailyAttendance::where('employee_id', $empRecord->id)
            ->whereDate('date', now()->format('Y-m-d'))
            ->get();

        // Must maintain exactly 1 record for the date
        $this->assertCount(1, $records);
    }

    public function test_qa_clock_out_without_prior_clock_in_is_handled_gracefully(): void
    {
        $employee = User::where('role', 'employee')->first();
        $empRecord = $employee->employee;

        // Clock-out without clocking in first
        $this->actingAs($employee)->post('/attendance/clock-out')->assertRedirect();

        $record = DailyAttendance::where('employee_id', $empRecord->id)
            ->whereDate('date', now()->format('Y-m-d'))
            ->first();

        $this->assertNotNull($record);
        $this->assertNotNull($record->check_out);
        $this->assertEquals('present', $record->status);
    }

    /**
     * TEST SUITE 3: INPUT SANITIZATION & XSS DEFENSE
     */
    public function test_qa_xss_payload_in_todo_and_work_entry_is_safely_persisted_and_escaped(): void
    {
        $employee = User::where('role', 'employee')->first();
        $xssPayload = '<script>alert("XSS-TEST")</script>';

        // Submit Todo with script tags
        $this->actingAs($employee)->post('/todos', [
            'title' => $xssPayload,
            'description' => 'Test XSS sanitization',
            'priority' => 'high',
        ])->assertRedirect();

        $todo = Todo::where('user_id', $employee->id)->latest('id')->first();
        $this->assertEquals($xssPayload, $todo->title);

        // Rendering page should not execute script (Blade escapes with {{ }})
        $res = $this->actingAs($employee)->get('/todos');
        $res->assertStatus(200);
        $res->assertSee('&lt;script&gt;alert(&quot;XSS-TEST&quot;)&lt;/script&gt;', false);
    }

    /**
     * TEST SUITE 4: DATA INTEGRITY & BOUNDARY VALIDATION
     */
    public function test_qa_leave_request_cannot_have_end_date_prior_to_start_date(): void
    {
        $employee = User::where('role', 'employee')->first();
        $leaveType = LeaveType::first();

        $res = $this->actingAs($employee)->post('/leaves', [
            'leave_type_id' => $leaveType->id,
            'start_date' => now()->addDays(5)->format('Y-m-d'),
            'end_date' => now()->addDays(2)->format('Y-m-d'), // Invalid: 3 days BEFORE start
            'reason' => 'Invalid date test',
        ]);

        $res->assertSessionHasErrors('end_date');
    }

    public function test_qa_file_upload_validation_rejects_non_image_files(): void
    {
        $admin = User::where('role', 'admin')->first();
        $dept = Department::first();

        $maliciousFile = UploadedFile::fake()->create('malicious.sh', 100, 'application/x-sh');

        $res = $this->actingAs($admin)->post('/employees', [
            'employee_code' => 'EMP-TEST-QA',
            'name' => 'QA Upload Test',
            'email' => 'qaupload@test.com',
            'designation' => 'QA Specialist',
            'department_id' => $dept->id,
            'employment_status' => 'active',
            'leave_quota' => 15,
            'photo' => $maliciousFile,
        ]);

        $res->assertSessionHasErrors('photo');
    }

    public function test_qa_payroll_calculation_accurately_deducts_unpaid_days(): void
    {
        $admin = User::where('role', 'admin')->first();
        $employee = Employee::first();
        $employee->update(['salary' => 30000.00]);

        $month = now()->month;
        $year = now()->year;

        // Record 2 explicit absences
        DailyAttendance::updateOrCreate(
            ['employee_id' => $employee->id, 'date' => now()->startOfMonth()->format('Y-m-d 00:00:00')],
            ['status' => 'absent', 'recorded_by_user_id' => $admin->id]
        );
        DailyAttendance::updateOrCreate(
            ['employee_id' => $employee->id, 'date' => now()->startOfMonth()->addDay()->format('Y-m-d 00:00:00')],
            ['status' => 'absent', 'recorded_by_user_id' => $admin->id]
        );

        // Generate payroll
        $this->actingAs($admin)->post('/payroll/generate', [
            'month' => $month,
            'year' => $year,
        ])->assertRedirect();

        $payslip = Payslip::where('employee_id', $employee->id)
            ->where('month', $month)
            ->where('year', $year)
            ->first();

        $this->assertNotNull($payslip);
        $this->assertGreaterThanOrEqual(2, $payslip->unpaid_days);
        $this->assertLessThan(30000.00, $payslip->net_salary);
    }
}
