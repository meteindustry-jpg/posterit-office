<?php

use App\Models\CompanySetting;
use App\Models\DailyAttendance;
use App\Models\Employee;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations to fix production attendance records.
     */
    public function up(): void
    {
        // 1. Ensure system timezone is strictly Indian Standard Time
        CompanySetting::updateOrCreate(
            ['key' => 'timezone'],
            ['value' => 'Asia/Kolkata']
        );

        // 2. Remove false attendance for employees who are NOT present today (Suman and Tithi)
        $notPresentEmployees = Employee::whereIn('employee_code', ['EMP-008', 'EMP-011'])->pluck('id');
        DailyAttendance::whereIn('employee_id', $notPresentEmployees)
            ->whereDate('date', '2026-09-04')
            ->delete();

        // 3. Fix real arrival times for employees who self clocked in under UTC (+5h 30m correction)
        // and clear the false automatic 06:30 PM checkout so their active shift stays running
        $utcFixes = [
            'EMP-001' => '10:26', // Biswajit Mete (clocked in at 10:26 AM IST, recorded as 04:56 AM UTC)
            'EMP-009' => '10:26', // Riya Mete (clocked in at 10:26 AM IST, recorded as 04:56 AM UTC)
            'EMP-010' => '10:31', // Sukanta Das (clocked in at 10:31 AM IST, recorded as 05:01 AM UTC)
            'EMP-013' => '10:54', // Souvik Chowdhury (clocked in at 10:54 AM IST, recorded as 05:24 AM UTC)
        ];

        foreach ($utcFixes as $code => $realTime) {
            $emp = Employee::where('employee_code', $code)->first();
            if ($emp) {
                DailyAttendance::updateOrCreate(
                    [
                        'employee_id' => $emp->id,
                        'date' => '2026-09-04 00:00:00',
                    ],
                    [
                        'status' => 'present',
                        'check_in' => $realTime,
                        'check_out' => null, // Active shift in progress
                        'remarks' => in_array($code, ['EMP-009', 'EMP-010', 'EMP-013']) ? 'Self Clock-In (Late Arrival)' : 'Self Clock-In',
                    ]
                );
            }
        }

        // 4. Pradip (EMP-012) was auto-filled with 18:30 checkout; keep him present without false checkout
        $pradip = Employee::where('employee_code', 'EMP-012')->first();
        if ($pradip) {
            $pradipAtt = DailyAttendance::where('employee_id', $pradip->id)
                ->whereDate('date', '2026-09-04')
                ->first();

            if ($pradipAtt && $pradipAtt->check_out === '18:30') {
                $pradipAtt->update(['check_out' => null]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Non-destructive
    }
};
