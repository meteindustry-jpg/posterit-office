<?php

namespace Tests\Feature;

use App\Models\DailyAttendance;
use App\Models\Department;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\Todo;
use App\Models\User;
use App\Models\WorkCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class WorkManagementSystemTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $dept = Department::first();
        if ($dept) {
            $manager = User::firstOrCreate(
                ['email' => 'manager@posterit.com'],
                ['name' => 'Test Manager', 'password' => Hash::make('password'), 'role' => 'manager', 'is_active' => true]
            );

            $admin = User::firstOrCreate(
                ['email' => 'admin@posterit.com'],
                ['name' => 'Test Admin', 'password' => Hash::make('password'), 'role' => 'admin', 'is_active' => true]
            );

            $empUser = User::firstOrCreate(
                ['email' => 'rahul@posterit.com'],
                ['name' => 'Rahul Sharma', 'password' => Hash::make('password'), 'role' => 'employee', 'is_active' => true]
            );

            $emp = Employee::firstOrCreate(
                ['email' => 'rahul@posterit.com'],
                [
                    'employee_code' => 'EMP-001',
                    'user_id' => $empUser->id,
                    'name' => 'Rahul Sharma',
                    'mobile_number' => '+91 98234 11223',
                    'designation' => 'Senior Graphic Designer',
                    'department_id' => $dept->id,
                    'joining_date' => '2023-03-15',
                    'employment_status' => 'active',
                    'salary' => 45000.00,
                    'leave_quota' => 18,
                ]
            );
            $empUser->update(['employee_id' => $emp->id]);
        }
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');
    }

    public function test_user_can_login(): void
    {
        $response = $this->post('/login', [
            'email' => 'manager@posterit.com',
            'password' => 'password',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();
    }

    public function test_manager_can_access_dashboard_and_batch_work(): void
    {
        $manager = User::where('role', 'manager')->first();

        $response = $this->actingAs($manager)->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Posterit Operations');

        $batchResponse = $this->actingAs($manager)->get('/work-entries/batch');
        $batchResponse->assertStatus(200);
        $batchResponse->assertSee('Daily Work Entry Batch');
    }

    public function test_manager_can_store_batch_work_entries(): void
    {
        $manager = User::where('role', 'manager')->first();
        $employee = Employee::first();
        $category = WorkCategory::first();
        $date = now()->format('Y-m-d');

        $response = $this->actingAs($manager)->post('/work-entries/batch', [
            'date' => $date,
            'entries' => [
                [
                    'employee_id' => $employee->id,
                    'work_category_id' => $category->id,
                    'quantity' => 7,
                    'remarks' => 'Automated test task',
                ],
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('daily_work_entries', [
            'employee_id' => $employee->id,
            'work_category_id' => $category->id,
            'quantity' => 7,
            'remarks' => 'Automated test task',
        ]);
    }

    public function test_manager_can_mark_bulk_attendance(): void
    {
        $manager = User::where('role', 'manager')->first();
        $employee = Employee::first();
        $date = now()->format('Y-m-d');

        $response = $this->actingAs($manager)->post('/attendance/batch', [
            'date' => $date,
            'attendances' => [
                [
                    'employee_id' => $employee->id,
                    'status' => 'present',
                    'check_in' => '09:30',
                    'check_out' => '18:30',
                    'remarks' => 'On time',
                ],
            ],
        ]);

        $response->assertRedirect();
        $this->assertNotNull(
            DailyAttendance::where('employee_id', $employee->id)
                ->whereDate('date', $date)
                ->where('status', 'present')
                ->first()
        );
    }

    public function test_leave_approval_workflow(): void
    {
        $manager = User::where('role', 'manager')->first();
        $leave = LeaveRequest::where('status', 'pending')->first();

        $response = $this->actingAs($manager)->patch("/leaves/{$leave->id}/status", [
            'status' => 'approved',
            'action_remarks' => 'Approved by manager test',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('leave_requests', [
            'id' => $leave->id,
            'status' => 'approved',
            'action_remarks' => 'Approved by manager test',
        ]);
    }

    public function test_leave_approval_without_remarks(): void
    {
        $manager = User::where('role', 'manager')->first();
        $leave = LeaveRequest::where('status', 'pending')->first();

        $response = $this->actingAs($manager)->patch("/leaves/{$leave->id}/status", [
            'status' => 'approved',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('leave_requests', [
            'id' => $leave->id,
            'status' => 'approved',
        ]);
    }

    public function test_performance_scorecard_loads_successfully(): void
    {
        $manager = User::where('role', 'manager')->first();

        $response = $this->actingAs($manager)->get('/performance');
        $response->assertStatus(200);
        $response->assertSee('Rankings');
    }

    public function test_reports_and_csv_export(): void
    {
        $manager = User::where('role', 'manager')->first();

        $response = $this->actingAs($manager)->get('/reports?type=daily_work');
        $response->assertStatus(200);

        $exportResponse = $this->actingAs($manager)->get('/reports/export/csv?type=daily_work');
        $exportResponse->assertStatus(200);
        $exportResponse->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_global_search_api(): void
    {
        $manager = User::where('role', 'manager')->first();

        $response = $this->actingAs($manager)->getJson('/api/search?q=Rahul');
        $response->assertStatus(200);
        $response->assertJsonStructure(['employees', 'categories', 'work_entries']);
    }

    public function test_employee_role_cannot_access_admin_settings(): void
    {
        $employeeUser = User::where('role', 'employee')->first();

        $response = $this->actingAs($employeeUser)->get('/settings');
        $response->assertStatus(403);
    }

    public function test_user_can_access_and_create_todo(): void
    {
        $manager = User::where('role', 'manager')->first();

        $response = $this->actingAs($manager)->get('/todos');
        $response->assertStatus(200);
        $response->assertSee('Tasks');

        $createResponse = $this->actingAs($manager)->post('/todos', [
            'title' => 'Test Automated Todo Task',
            'priority' => 'high',
            'category' => 'Design',
            'due_date' => now()->format('Y-m-d'),
        ]);

        $createResponse->assertRedirect();
        $this->assertDatabaseHas('todos', [
            'title' => 'Test Automated Todo Task',
            'priority' => 'high',
            'category' => 'Design',
            'is_completed' => false,
        ]);
    }

    public function test_user_can_toggle_todo(): void
    {
        $manager = User::where('role', 'manager')->first();
        $todo = Todo::where('is_completed', false)->first();

        $response = $this->actingAs($manager)->patch("/todos/{$todo->id}/toggle");
        $response->assertRedirect();

        $this->assertDatabaseHas('todos', [
            'id' => $todo->id,
            'is_completed' => true,
        ]);
    }

    public function test_user_can_update_task_status(): void
    {
        $manager = User::where('role', 'manager')->first();
        $todo = Todo::first();

        $response = $this->actingAs($manager)->patch("/todos/{$todo->id}/status", [
            'status' => 'in_progress',
        ]);
        $response->assertRedirect();

        $this->assertDatabaseHas('todos', [
            'id' => $todo->id,
            'status' => 'in_progress',
        ]);
    }

    public function test_user_can_convert_task_to_daily_work_entry(): void
    {
        $manager = User::where('role', 'manager')->first();
        $todo = Todo::first();
        $category = WorkCategory::first();

        $response = $this->actingAs($manager)->post("/todos/{$todo->id}/convert-work-entry", [
            'work_category_id' => $category->id,
            'quantity' => 5,
            'date' => now()->format('Y-m-d'),
            'remarks' => 'Converted from task',
        ]);
        $response->assertRedirect();

        $this->assertDatabaseHas('daily_work_entries', [
            'work_category_id' => $category->id,
            'quantity' => 5,
            'remarks' => 'Converted from task',
        ]);

        $this->assertDatabaseHas('todos', [
            'id' => $todo->id,
            'is_completed' => true,
            'status' => 'completed',
        ]);
    }

    public function test_manager_can_view_monthly_attendance_grid(): void
    {
        $manager = User::where('role', 'manager')->first();

        $response = $this->actingAs($manager)->get('/attendance/monthly-grid');
        $response->assertStatus(200);
        $response->assertSee('Monthly Attendance Matrix');
    }

    public function test_manager_can_export_monthly_attendance_csv(): void
    {
        $manager = User::where('role', 'manager')->first();

        $response = $this->actingAs($manager)->get('/attendance/export-monthly');
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_employee_can_clock_in_and_out(): void
    {
        $employeeUser = User::where('role', 'employee')->first();

        $clockInResponse = $this->actingAs($employeeUser)->post('/attendance/clock-in', [
            'status' => 'present',
        ]);
        $clockInResponse->assertRedirect();

        $clockOutResponse = $this->actingAs($employeeUser)->post('/attendance/clock-out');
        $clockOutResponse->assertRedirect();
    }

    public function test_employee_can_access_daily_and_monthly_attendance(): void
    {
        $employeeUser = User::where('role', 'employee')->first();

        $dailyResponse = $this->actingAs($employeeUser)->get('/attendance');
        $dailyResponse->assertStatus(200);
        $dailyResponse->assertSee('Daily Attendance');

        $monthlyResponse = $this->actingAs($employeeUser)->get('/attendance/monthly-grid');
        $monthlyResponse->assertStatus(200);
        $monthlyResponse->assertSee('Monthly Attendance Matrix');
    }

    public function test_employee_can_view_leave_center(): void
    {
        $employeeUser = User::where('role', 'employee')->first();

        $response = $this->actingAs($employeeUser)->get('/leaves');
        $response->assertStatus(200);
        $response->assertSee('Leave Quota', false);
    }

    public function test_employee_dashboard_reflects_today_clock_in(): void
    {
        $employeeUser = User::where('role', 'employee')->first();

        // Initially dashboard loads
        $initialRes = $this->actingAs($employeeUser)->get('/dashboard');
        $initialRes->assertStatus(200);

        // Clock In
        $this->actingAs($employeeUser)->post('/attendance/clock-in', ['mode' => 'present']);

        // Dashboard now reflects active shift
        $afterRes = $this->actingAs($employeeUser)->get('/dashboard');
        $afterRes->assertStatus(200);
        $afterRes->assertDontSee('Shift not started yet');
        $afterRes->assertSee('Clock-Out');
    }

    public function test_reports_endpoints_and_exports(): void
    {
        $manager = User::where('role', 'manager')->first();

        $response = $this->actingAs($manager)->get('/reports');
        $response->assertStatus(200);
        $response->assertSee('Reports', false);

        $csvResponse = $this->actingAs($manager)->get('/reports/export?format=csv&type=daily_work');
        $csvResponse->assertStatus(200);
        $this->assertTrue(str_contains($csvResponse->headers->get('content-type') ?? '', 'text/csv') || str_contains($csvResponse->headers->get('content-disposition') ?? '', 'attachment'));
    }

    public function test_performance_scorecard_and_leaderboard(): void
    {
        $manager = User::where('role', 'manager')->first();

        $response = $this->actingAs($manager)->get('/performance');
        $response->assertStatus(200);
        $response->assertSee('Performance', false);
    }

    public function test_department_and_category_views(): void
    {
        $admin = User::where('role', 'admin')->first();

        $deptRes = $this->actingAs($admin)->get('/departments');
        $deptRes->assertStatus(200);
        $deptRes->assertSee('Department Management');

        $catRes = $this->actingAs($admin)->get('/categories');
        $catRes->assertStatus(200);
        $catRes->assertSee('Work Categories');
    }
}
