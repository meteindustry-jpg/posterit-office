<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Departments
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 2. Employees
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('employee_code')->unique();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->string('photo')->nullable();
            $table->string('mobile_number')->nullable();
            $table->string('email')->unique();
            $table->string('designation');
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->date('joining_date')->nullable();
            $table->enum('employment_status', ['active', 'inactive', 'resigned'])->default('active');
            $table->decimal('salary', 12, 2)->nullable();
            $table->text('notes')->nullable();
            $table->integer('leave_quota')->default(18);
            $table->timestamps();
        });

        // 3. Alter users table for role, avatar, employee_id
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['super_admin', 'admin', 'manager', 'employee'])->default('employee')->after('email');
            $table->string('avatar')->nullable()->after('role');
            $table->foreignId('employee_id')->nullable()->after('avatar')->constrained('employees')->nullOnDelete();
            $table->boolean('is_active')->default(true)->after('employee_id');
        });

        // 4. Work Categories
        Schema::create('work_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('color', 20)->default('#4F46E5');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 5. Daily Attendances
        Schema::create('daily_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->date('date');
            $table->enum('status', ['present', 'absent', 'half_day', 'leave', 'wfh'])->default('present');
            $table->time('check_in')->nullable();
            $table->time('check_out')->nullable();
            $table->string('remarks')->nullable();
            $table->foreignId('recorded_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['employee_id', 'date']);
        });

        // 6. Leave Types
        Schema::create('leave_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('default_days_per_year')->default(6);
            $table->boolean('is_paid')->default(true);
            $table->timestamps();
        });

        // 7. Leave Requests
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('leave_type_id')->constrained('leave_types')->cascadeOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('total_days', 4, 1)->default(1);
            $table->text('reason')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('action_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('action_remarks')->nullable();
            $table->timestamps();
        });

        // 8. Holidays
        Schema::create('holidays', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->date('date');
            $table->enum('type', ['national', 'religious', 'company', 'optional'])->default('company');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 9. Daily Work Entries
        Schema::create('daily_work_entries', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('work_category_id')->constrained('work_categories')->cascadeOnDelete();
            $table->integer('quantity')->default(1);
            $table->text('remarks')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 10. Audit Logs
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action');
            $table->string('module');
            $table->text('description');
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });

        // 11. Company Settings
        Schema::create('company_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->longText('value')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_settings');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('daily_work_entries');
        Schema::dropIfExists('holidays');
        Schema::dropIfExists('leave_requests');
        Schema::dropIfExists('leave_types');
        Schema::dropIfExists('daily_attendances');
        Schema::dropIfExists('work_categories');

        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropForeign(['employee_id']);
                $table->dropColumn(['role', 'avatar', 'employee_id', 'is_active']);
            });
        }

        Schema::dropIfExists('employees');
        Schema::dropIfExists('departments');
    }
};
