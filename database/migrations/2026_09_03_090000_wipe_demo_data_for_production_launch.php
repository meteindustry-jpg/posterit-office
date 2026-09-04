<?php

use App\Models\AuditLog;
use App\Models\DailyAttendance;
use App\Models\DailyWorkEntry;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\PayrollRecord;
use App\Models\Todo;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Wipe all test transactional data
        DailyWorkEntry::truncate();
        DailyAttendance::truncate();
        LeaveRequest::truncate();
        if (Schema::hasTable('payroll_records')) {
            PayrollRecord::truncate();
        }
        Todo::truncate();

        // 2. Delete all demo employees (Rahul, Priya, Amit, Sneha, Vikram, Neha, etc.)
        Employee::query()->delete();

        // 3. Delete all demo/testing users, preserving ONLY Samir Mete / Sam Mete
        User::whereNotIn('email', ['samir@posterit.com', 'sam@posterit.com'])->delete();

        // 4. Ensure Samir Mete is pristine Super Admin with no employee association yet
        User::updateOrCreate(
            ['email' => 'samir@posterit.com'],
            [
                'name' => 'Samir Mete',
                'password' => Hash::make('password'),
                'role' => 'super_admin',
                'is_active' => true,
                'employee_id' => null,
            ]
        );

        // 5. Clean up old demo audit logs and write initial production launch log
        AuditLog::query()->delete();
        AuditLog::create([
            'user_id' => User::where('email', 'samir@posterit.com')->value('id') ?? 1,
            'action' => 'launch',
            'module' => 'System',
            'description' => 'System initialized for live production. Demo data purged and privacy security enforced.',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Production Launch Seeder',
        ]);
    }

    public function down(): void
    {
        // Irreversible clean production wipe
    }
};
