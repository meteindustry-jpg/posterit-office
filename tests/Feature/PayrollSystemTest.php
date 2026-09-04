<?php

namespace Tests\Feature;

use App\Models\DailyAttendance;
use App\Models\Employee;
use App\Models\PayrollRun;
use App\Models\Payslip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PayrollSystemTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_manager_can_access_payroll_command_center(): void
    {
        $manager = User::where('role', 'manager')->first();

        $response = $this->actingAs($manager)->get('/payroll');
        $response->assertStatus(200);
        $response->assertSee('Payroll', false);
    }

    public function test_manager_can_generate_monthly_payroll(): void
    {
        $manager = User::where('role', 'manager')->first();
        $employee = Employee::first();

        // Mark some attendance
        DailyAttendance::updateOrCreate(
            [
                'employee_id' => $employee->id,
                'date' => now()->startOfMonth()->format('Y-m-d 00:00:00'),
            ],
            [
                'status' => 'present',
                'check_in' => '09:00',
                'check_out' => '18:00',
            ]
        );

        $response = $this->actingAs($manager)->post('/payroll/generate', [
            'month' => now()->month,
            'year' => now()->year,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('payroll_runs', [
            'month' => now()->month,
            'year' => now()->year,
        ]);
        $this->assertDatabaseHas('payslips', [
            'employee_id' => $employee->id,
            'month' => now()->month,
            'year' => now()->year,
        ]);
    }

    public function test_manager_can_adjust_bonus_and_deductions(): void
    {
        $manager = User::where('role', 'manager')->first();

        // Generate payroll
        $this->actingAs($manager)->post('/payroll/generate', [
            'month' => now()->month,
            'year' => now()->year,
        ]);

        $payslip = Payslip::first();

        $updateResponse = $this->actingAs($manager)->put("/payroll/payslips/{$payslip->id}", [
            'bonus_amount' => 5000,
            'deductions_amount' => 1000,
            'payment_status' => 'paid',
            'payment_mode' => 'bank_transfer',
            'payment_reference' => 'TEST_UTR_123',
        ]);

        $updateResponse->assertRedirect();
        $payslip->refresh();
        $this->assertEquals(5000, $payslip->bonus_amount);
        $this->assertEquals(1000, $payslip->deductions_amount);
        $this->assertEquals('paid', $payslip->payment_status);
    }

    public function test_manager_can_bulk_mark_all_paid(): void
    {
        $manager = User::where('role', 'manager')->first();

        $this->actingAs($manager)->post('/payroll/generate', [
            'month' => now()->month,
            'year' => now()->year,
        ]);

        $run = PayrollRun::first();

        $response = $this->actingAs($manager)->post("/payroll/runs/{$run->id}/mark-paid", [
            'payment_mode' => 'bank_transfer',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseMissing('payslips', [
            'payroll_run_id' => $run->id,
            'payment_status' => 'pending',
        ]);
    }

    public function test_employee_can_view_own_payslip_and_my_payslips(): void
    {
        $manager = User::where('role', 'manager')->first();
        $employeeUser = User::where('role', 'employee')->first();
        $employee = $employeeUser->employee;

        // Generate payroll
        $this->actingAs($manager)->post('/payroll/generate', [
            'month' => now()->month,
            'year' => now()->year,
        ]);

        $mySlip = Payslip::where('employee_id', $employee->id)->first();

        // Employee accesses my-payslips
        $myListRes = $this->actingAs($employeeUser)->get('/my-payslips');
        $myListRes->assertStatus(200);
        $myListRes->assertSee('My Payslips');

        // Employee accesses own payslip
        $slipRes = $this->actingAs($employeeUser)->get("/payroll/payslips/{$mySlip->id}");
        $slipRes->assertStatus(200);
        $slipRes->assertSee('Salary Payslip');

        // Employee cannot access other employee's payslip
        $otherEmployee = Employee::where('id', '!=', $employee->id)->first();
        $otherSlip = Payslip::where('employee_id', $otherEmployee->id)->first();

        $unauthorizedRes = $this->actingAs($employeeUser)->get("/payroll/payslips/{$otherSlip->id}");
        $unauthorizedRes->assertStatus(403);
    }

    public function test_bank_payout_csv_export(): void
    {
        $manager = User::where('role', 'manager')->first();

        $this->actingAs($manager)->post('/payroll/generate', [
            'month' => now()->month,
            'year' => now()->year,
        ]);

        $run = PayrollRun::first();

        $csvRes = $this->actingAs($manager)->get("/payroll/runs/{$run->id}/export-bank-csv");
        $csvRes->assertStatus(200);
        $this->assertTrue(str_contains($csvRes->headers->get('content-type') ?? '', 'text/csv'));
    }
}
