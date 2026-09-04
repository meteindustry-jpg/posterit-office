<?php

namespace App\Console\Commands;

use App\Models\CompanySetting;
use App\Models\DailyAttendance;
use App\Models\Employee;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('attendance:fix-today {--date= : Date in YYYY-MM-DD format (defaults to today)} {--status=present : Attendance status (present, wfh)} {--check-in= : Check-in time (HH:MM)}') ]
#[Description('Mark or fix attendance records for all active employees for today with accurate office timings.')]
class FixTodayAttendanceCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $date = $this->option('date') ?: now()->format('Y-m-d');
        $defaultStatus = $this->option('status') ?: 'present';
        $officeStart = CompanySetting::get('office_timing_start', '09:30');
        $checkInTime = $this->option('check-in') ?: $officeStart;
        $superAdmin = User::whereIn('role', ['super_admin', 'admin'])->first();

        $employees = Employee::where('employment_status', 'active')->orderBy('name')->get();

        if ($employees->isEmpty()) {
            $this->warn('No active employees found in the database.');

            return self::FAILURE;
        }

        $this->info("Fixing attendance for {$employees->count()} active employees on {$date} (Check-in: {$checkInTime})...");

        $fixed = 0;
        foreach ($employees as $employee) {
            $existing = DailyAttendance::where('employee_id', $employee->id)
                ->whereDate('date', $date)
                ->first();

            $status = $existing && $existing->status ? $existing->status : $defaultStatus;
            $checkIn = $existing && $existing->check_in ? $existing->check_in : $checkInTime;

            DailyAttendance::updateOrCreate(
                [
                    'employee_id' => $employee->id,
                    'date' => Carbon::parse($date)->format('Y-m-d 00:00:00'),
                ],
                [
                    'status' => $status,
                    'check_in' => in_array($status, ['present', 'wfh']) ? $checkIn : null,
                    'check_out' => $existing?->check_out,
                    'remarks' => $existing && $existing->remarks ? $existing->remarks : 'Office Shift (On-Time)',
                    'recorded_by_user_id' => $superAdmin?->id,
                ]
            );

            $fixed++;
            $this->line("  ✓ [{$employee->employee_code}] {$employee->name} -> {$status} ({$checkIn})");
        }

        $this->info("Successfully fixed and updated attendance for all {$fixed} employees on {$date}.");

        return self::SUCCESS;
    }
}
