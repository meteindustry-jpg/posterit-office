<?php

use App\Models\DailyWorkEntry;
use App\Models\Employee;
use App\Models\Todo;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Reassign all converted Todo work entries to the employee who owned/created the task
        $todos = Todo::whereNotNull('work_entry_id')->with(['workEntry', 'user', 'assignedTo'])->get();

        foreach ($todos as $todo) {
            $targetUserId = $todo->assigned_to_user_id ?: $todo->user_id;
            if (! $targetUserId) {
                continue;
            }

            $targetUser = User::find($targetUserId);
            $employee = Employee::where('user_id', $targetUserId)->first()
                     ?? Employee::where('email', $targetUser?->email)->first();

            if ($todo->workEntry && $employee) {
                $todo->workEntry->update([
                    'employee_id' => $employee->id,
                ]);
            }
        }

        // 2. Fix known unlinked duplicate entries by matching remarks to employee
        $sukanta = Employee::where('email', 'sukantaoffice25@gmail.com')->orWhere('name', 'like', '%Sukanta%')->first();
        if ($sukanta) {
            DailyWorkEntry::where('remarks', 'like', '%Dhunuchi illustration%')
                ->where('employee_id', '!=', $sukanta->id)
                ->update(['employee_id' => $sukanta->id]);
        }

        $suman = Employee::where('email', 'sumanoffice931@gmail.com')->orWhere('name', 'like', '%Suman%')->first();
        if ($suman) {
            DailyWorkEntry::where('remarks', 'like', '%Mondal Bastralaya%')
                ->where('remarks', 'not like', '%&%')
                ->where('employee_id', '!=', $suman->id)
                ->update(['employee_id' => $suman->id]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No down needed for data correction
    }
};
