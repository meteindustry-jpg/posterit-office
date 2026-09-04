<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payslip extends Model
{
    use HasFactory;

    protected $fillable = [
        'payroll_run_id',
        'employee_id',
        'month',
        'year',
        'basic_salary',
        'total_days_in_month',
        'working_days',
        'present_days',
        'half_days',
        'paid_leaves',
        'unpaid_days',
        'per_day_rate',
        'earned_salary',
        'bonus_amount',
        'allowances_amount',
        'deductions_amount',
        'tax_deduction',
        'net_salary',
        'payment_status',
        'payment_mode',
        'payment_reference',
        'paid_at',
        'notes',
    ];

    protected $casts = [
        'basic_salary' => 'decimal:2',
        'present_days' => 'decimal:2',
        'paid_leaves' => 'decimal:2',
        'unpaid_days' => 'decimal:2',
        'per_day_rate' => 'decimal:2',
        'earned_salary' => 'decimal:2',
        'bonus_amount' => 'decimal:2',
        'allowances_amount' => 'decimal:2',
        'deductions_amount' => 'decimal:2',
        'tax_deduction' => 'decimal:2',
        'net_salary' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function payrollRun()
    {
        return $this->belongsTo(PayrollRun::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function getMonthNameAttribute(): string
    {
        return date('F', mktime(0, 0, 0, $this->month, 1));
    }

    public function getPeriodLabelAttribute(): string
    {
        return "{$this->month_name} {$this->year}";
    }

    public function recalculate(): self
    {
        $effectiveWorkingDays = max(1, $this->working_days);
        $this->per_day_rate = round($this->basic_salary / $effectiveWorkingDays, 2);

        $unpaidDeduction = round($this->unpaid_days * $this->per_day_rate, 2);
        $this->earned_salary = max(0, round($this->basic_salary - $unpaidDeduction, 2));

        $totalGross = $this->earned_salary + $this->bonus_amount + $this->allowances_amount;
        $totalDeductions = $this->deductions_amount + $this->tax_deduction;

        $this->net_salary = max(0, round($totalGross - $totalDeductions, 2));
        $this->save();

        return $this;
    }
}
