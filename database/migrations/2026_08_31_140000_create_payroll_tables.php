<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_runs', function (Blueprint $table) {
            $table->id();
            $table->integer('month');
            $table->integer('year');
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->integer('total_employees')->default(0);
            $table->string('status')->default('draft'); // draft, approved, paid
            $table->date('payment_date')->nullable();
            $table->foreignId('processed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['month', 'year']);
        });

        Schema::create('payslips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_run_id')->constrained('payroll_runs')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->integer('month');
            $table->integer('year');
            
            // Salary calculation stats
            $table->decimal('basic_salary', 12, 2)->default(0);
            $table->integer('total_days_in_month')->default(30);
            $table->integer('working_days')->default(26);
            $table->decimal('present_days', 5, 2)->default(0);
            $table->integer('half_days')->default(0);
            $table->decimal('paid_leaves', 5, 2)->default(0);
            $table->decimal('unpaid_days', 5, 2)->default(0);
            
            // Financial components
            $table->decimal('per_day_rate', 10, 2)->default(0);
            $table->decimal('earned_salary', 12, 2)->default(0);
            $table->decimal('bonus_amount', 10, 2)->default(0);
            $table->decimal('allowances_amount', 10, 2)->default(0);
            $table->decimal('deductions_amount', 10, 2)->default(0);
            $table->decimal('tax_deduction', 10, 2)->default(0);
            $table->decimal('net_salary', 12, 2)->default(0);
            
            // Payout Status
            $table->string('payment_status')->default('pending'); // pending, paid
            $table->string('payment_mode')->nullable(); // bank_transfer, upi, cash, cheque
            $table->string('payment_reference')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['payroll_run_id', 'employee_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payslips');
        Schema::dropIfExists('payroll_runs');
    }
};
