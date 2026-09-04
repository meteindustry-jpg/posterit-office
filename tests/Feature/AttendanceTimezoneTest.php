<?php

namespace Tests\Feature;

use App\Models\CompanySetting;
use App\Models\DailyAttendance;
use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AttendanceTimezoneTest extends TestCase
{
    use RefreshDatabase;

    protected User $employeeUser;

    protected Employee $employee;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $dept = Department::first();

        $this->employeeUser = User::firstOrCreate(
            ['email' => 'employee_tz@posterit.com'],
            ['name' => 'Aarav Patel', 'password' => Hash::make('password'), 'role' => 'employee', 'is_active' => true]
        );

        $this->employee = Employee::firstOrCreate(
            ['email' => 'employee_tz@posterit.com'],
            [
                'employee_code' => 'EMP-TZ-01',
                'name' => 'Aarav Patel',
                'department_id' => $dept?->id,
                'designation' => 'Visual Designer',
                'joining_date' => '2025-01-01',
                'user_id' => $this->employeeUser->id,
                'employment_status' => 'active',
            ]
        );
    }

    public function test_clock_in_records_accurate_local_time_in_configured_timezone(): void
    {
        // Freeze time at 11:20 AM IST
        $frozenTime = Carbon::create(2026, 9, 4, 11, 20, 0, 'Asia/Kolkata');
        Carbon::setTestNow($frozenTime);

        $response = $this->actingAs($this->employeeUser)->post('/attendance/clock-in');
        $response->assertRedirect();
        $response->assertSessionHas('success');

        $record = DailyAttendance::where('employee_id', $this->employee->id)
            ->whereDate('date', '2026-09-04')
            ->first();

        $this->assertNotNull($record, 'Attendance record should be created');
        $this->assertEquals('11:20', substr($record->check_in, 0, 5), 'Check-in time should match IST 11:20 instead of UTC 05:50');
        $this->assertEquals('present', $record->status);

        // Flash message should display 11:20 AM
        $flashMsg = session('success');
        $this->assertStringContainsString('11:20 AM', $flashMsg);

        Carbon::setTestNow();
    }

    public function test_clock_out_records_accurate_local_time_in_configured_timezone(): void
    {
        $frozenClockIn = Carbon::create(2026, 9, 4, 10, 0, 0, 'Asia/Kolkata');
        Carbon::setTestNow($frozenClockIn);
        $this->actingAs($this->employeeUser)->post('/attendance/clock-in');

        // Clock out at 6:30 PM IST (18:30)
        $frozenClockOut = Carbon::create(2026, 9, 4, 18, 30, 0, 'Asia/Kolkata');
        Carbon::setTestNow($frozenClockOut);

        $response = $this->actingAs($this->employeeUser)->post('/attendance/clock-out');
        $response->assertRedirect();

        $record = DailyAttendance::where('employee_id', $this->employee->id)
            ->whereDate('date', '2026-09-04')
            ->first();

        $this->assertNotNull($record);
        $this->assertEquals('18:30', substr($record->check_out, 0, 5), 'Check-out time should match IST 18:30');

        $flashMsg = session('success');
        $this->assertStringContainsString('06:30 PM', $flashMsg);

        Carbon::setTestNow();
    }

    public function test_settings_timezone_can_be_updated_and_persisted(): void
    {
        $superAdmin = User::where('role', 'super_admin')->first();

        $response = $this->actingAs($superAdmin)->post('/settings', [
            'company_name' => 'Posterit Office India',
            'company_tagline' => 'High-Performance Graphic Studio',
            'working_days' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'],
            'timezone' => 'Asia/Kolkata',
            'office_timing_start' => '09:30',
            'office_timing_end' => '18:30',
            'late_grace_minutes' => 15,
            'half_day_hours' => 4.5,
            'default_leave_count' => 18,
            'currency_symbol' => '₹',
            'theme_mode' => 'light',
        ]);

        $response->assertRedirect();
        $this->assertEquals('Asia/Kolkata', CompanySetting::get('timezone'));
    }

    public function test_clock_in_with_client_time_preserves_employee_device_arrival_time(): void
    {
        $frozenTime = Carbon::create(2026, 9, 4, 10, 26, 0, 'Asia/Kolkata');
        Carbon::setTestNow($frozenTime);

        $response = $this->actingAs($this->employeeUser)->post('/attendance/clock-in', [
            'client_time' => '10:26',
        ]);
        $response->assertRedirect();

        $record = DailyAttendance::where('employee_id', $this->employee->id)
            ->whereDate('date', '2026-09-04')
            ->first();

        $this->assertNotNull($record);
        $this->assertEquals('10:26', substr($record->check_in, 0, 5));
        $this->assertNull($record->check_out, 'Shift should be active without automatic check_out');

        Carbon::setTestNow();
    }

    public function test_store_batch_does_not_force_checkout_on_active_shift(): void
    {
        $superAdmin = User::where('role', 'super_admin')->first();

        // Create an active shift (checked in at 10:26, no checkout)
        $record = DailyAttendance::create([
            'employee_id' => $this->employee->id,
            'date' => '2026-09-04 00:00:00',
            'status' => 'present',
            'check_in' => '10:26',
            'check_out' => null,
            'remarks' => 'Self Clock-In',
            'recorded_by_user_id' => $this->employeeUser->id,
        ]);

        // Submit batch attendance without specifying check_out
        $response = $this->actingAs($superAdmin)->post('/attendance/batch', [
            'date' => '2026-09-04',
            'attendances' => [
                [
                    'employee_id' => $this->employee->id,
                    'status' => 'present',
                    'check_in' => '10:26',
                    'check_out' => '', // blank check_out
                    'remarks' => 'Office Shift',
                ],
            ],
        ]);

        $response->assertRedirect();

        $record->refresh();
        $this->assertNull($record->check_out, 'Blank checkout in batch should not force 18:30 on active shift');
    }
}
