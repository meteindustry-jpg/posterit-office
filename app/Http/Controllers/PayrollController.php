<?php

namespace App\Http\Controllers;

use App\Models\DailyAttendance;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\PayrollRun;
use App\Models\Payslip;
use App\Models\CompanySetting;
use App\Services\AuditService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PayrollController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // If employee visits /payroll, automatically redirect to their personal payslip portal
        if ($user && $user->isEmployee()) {
            return redirect()->route('payroll.myPayslips');
        }

        $month = (int) $request->get('month', now()->month);
        $year = (int) $request->get('year', now()->year);

        $payrollRun = PayrollRun::with(['payslips.employee.department', 'processedBy'])
            ->where('month', $month)
            ->where('year', $year)
            ->first();

        $activeEmployees = Employee::where('employment_status', 'active')->count();

        // Calculate summary stats
        $totalNetPayroll = $payrollRun ? $payrollRun->payslips->sum('net_salary') : 0;
        $totalBonus = $payrollRun ? $payrollRun->payslips->sum('bonus_amount') : 0;
        $totalDeductions = $payrollRun ? $payrollRun->payslips->sum('deductions_amount') : 0;
        $paidCount = $payrollRun ? $payrollRun->payslips->where('payment_status', 'paid')->count() : 0;
        $pendingCount = $payrollRun ? $payrollRun->payslips->where('payment_status', 'pending')->count() : 0;

        $currency = CompanySetting::get('currency_symbol', '₹');

        return view('payroll.index', compact(
            'month',
            'year',
            'payrollRun',
            'activeEmployees',
            'totalNetPayroll',
            'totalBonus',
            'totalDeductions',
            'paidCount',
            'pendingCount',
            'currency'
        ));
    }

    public function generate(Request $request)
    {
        $month = (int) $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020|max:2030',
        ])['month'];
        $year = (int) $request->input('year');

        $user = Auth::user();
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();
        $daysInMonth = $startDate->daysInMonth;

        // Calculate working days (excluding Sundays)
        $workingDays = 0;
        $tempDate = $startDate->copy();
        while ($tempDate->lte($endDate)) {
            if ($tempDate->dayOfWeek !== Carbon::SUNDAY) {
                $workingDays++;
            }
            $tempDate->addDay();
        }
        $workingDays = max(1, $workingDays);

        $userId = $user ? $user->id : User::first()?->id;

        $payrollRun = PayrollRun::firstOrCreate(
            ['month' => $month, 'year' => $year],
            [
                'status' => 'draft',
                'processed_by_user_id' => $userId,
            ]
        );

        $employees = Employee::where('employment_status', 'active')->get();
        $totalRunAmount = 0;

        foreach ($employees as $employee) {
            $basicSalary = $employee->salary && $employee->salary > 0 ? (float) $employee->salary : 35000.00;

            // Query attendance in this month for employee
            $attendances = DailyAttendance::where('employee_id', $employee->id)
                ->whereBetween('date', [$startDate->format('Y-m-d 00:00:00'), $endDate->format('Y-m-d 23:59:59')])
                ->get();

            $presentDays = $attendances->filter(fn($a) => in_array($a->status, ['present', 'wfh']))->count();
            $halfDays = $attendances->where('status', 'half_day')->count();
            $leaveDays = $attendances->where('status', 'leave')->count();
            $absentDays = $attendances->where('status', 'absent')->count();

            // Check if leaves were approved paid leaves
            $approvedLeaveDays = LeaveRequest::where('employee_id', $employee->id)
                ->where('status', 'approved')
                ->where(function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('start_date', [$startDate, $endDate])
                      ->orWhereBetween('end_date', [$startDate, $endDate]);
                })
                ->sum('total_days');

            $paidLeaves = max($leaveDays, (float) $approvedLeaveDays);

            // Unpaid absences (explicit absent + half day penalty)
            $unpaidDays = $absentDays + ($halfDays * 0.5);
            $payableDays = max(0, $workingDays - $unpaidDays);

            $perDayRate = round($basicSalary / $workingDays, 2);
            $unpaidDeduction = round($unpaidDays * $perDayRate, 2);
            $earnedSalary = max(0, round($basicSalary - $unpaidDeduction, 2));

            $payslip = Payslip::firstOrNew([
                'payroll_run_id' => $payrollRun->id,
                'employee_id' => $employee->id,
            ]);

            // Keep existing bonuses/deductions if already adjusted, else default
            $bonus = $payslip->exists ? $payslip->bonus_amount : 0;
            $allowances = $payslip->exists ? $payslip->allowances_amount : 0;
            $deductions = $payslip->exists ? $payslip->deductions_amount : 0;
            $tax = $payslip->exists ? $payslip->tax_deduction : 0;

            $netSalary = max(0, round($earnedSalary + $bonus + $allowances - $deductions - $tax, 2));

            $payslip->fill([
                'month' => $month,
                'year' => $year,
                'basic_salary' => $basicSalary,
                'total_days_in_month' => $daysInMonth,
                'working_days' => $workingDays,
                'present_days' => $presentDays,
                'half_days' => $halfDays,
                'paid_leaves' => $paidLeaves,
                'unpaid_days' => $unpaidDays,
                'per_day_rate' => $perDayRate,
                'earned_salary' => $earnedSalary,
                'bonus_amount' => $bonus,
                'allowances_amount' => $allowances,
                'deductions_amount' => $deductions,
                'tax_deduction' => $tax,
                'net_salary' => $netSalary,
                'payment_status' => $payslip->exists ? $payslip->payment_status : 'pending',
            ])->save();

            $totalRunAmount += $netSalary;
        }

        $payrollRun->update([
            'total_amount' => $totalRunAmount,
            'total_employees' => $employees->count(),
            'processed_by_user_id' => $userId,
        ]);

        AuditService::log('payroll_generate', 'Payroll', "Generated payroll for {$payrollRun->period_label} ({$employees->count()} employees, Total: {$totalRunAmount})");

        return back()->with('success', "Payroll for {$payrollRun->period_label} calculated successfully for {$employees->count()} team members.");
    }

    public function updatePayslip(Request $request, Payslip $payslip)
    {
        $validated = $request->validate([
            'basic_salary' => 'nullable|numeric|min:0',
            'bonus_amount' => 'nullable|numeric|min:0',
            'allowances_amount' => 'nullable|numeric|min:0',
            'deductions_amount' => 'nullable|numeric|min:0',
            'tax_deduction' => 'nullable|numeric|min:0',
            'payment_status' => 'required|in:pending,paid',
            'payment_mode' => 'nullable|string|max:50',
            'payment_reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:500',
        ]);

        if (isset($validated['basic_salary']) && $validated['basic_salary'] !== null) {
            $payslip->basic_salary = (float) $validated['basic_salary'];
        }

        $payslip->bonus_amount = $validated['bonus_amount'] ?? 0;
        $payslip->allowances_amount = $validated['allowances_amount'] ?? 0;
        $payslip->deductions_amount = $validated['deductions_amount'] ?? 0;
        $payslip->tax_deduction = $validated['tax_deduction'] ?? 0;
        $payslip->payment_status = $validated['payment_status'];
        $payslip->payment_mode = $validated['payment_mode'] ?? $payslip->payment_mode;
        $payslip->payment_reference = $validated['payment_reference'] ?? $payslip->payment_reference;
        $payslip->notes = $validated['notes'] ?? $payslip->notes;

        if ($payslip->payment_status === 'paid' && ! $payslip->paid_at) {
            $payslip->paid_at = now();
        }

        $payslip->recalculate();

        // Update run total
        $run = $payslip->payrollRun;
        $run->update([
            'total_amount' => $run->payslips->sum('net_salary'),
        ]);

        AuditService::log('payroll_update', 'Payroll', "Updated payslip for {$payslip->employee->name} - Net: {$payslip->net_salary}");

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'payslip' => $payslip]);
        }

        return back()->with('success', "Payslip for {$payslip->employee->name} updated successfully.");
    }

    public function bulkMarkPaid(Request $request, PayrollRun $payrollRun)
    {
        $paymentMode = $request->input('payment_mode', 'bank_transfer');

        $payrollRun->payslips()->where('payment_status', 'pending')->update([
            'payment_status' => 'paid',
            'payment_mode' => $paymentMode,
            'paid_at' => now(),
        ]);

        $payrollRun->update([
            'status' => 'paid',
            'payment_date' => now(),
        ]);

        AuditService::log('payroll_bulk_paid', 'Payroll', "Marked all payslips as paid for {$payrollRun->period_label}");

        return back()->with('success', "All payslips for {$payrollRun->period_label} have been marked as PAID.");
    }

    public function showPayslip(Payslip $payslip)
    {
        $user = Auth::user();

        // Authorization: Employee can only see own payslip; Managers/Admins can see any
        if ($user->isEmployee()) {
            $employee = $user->employee;
            if (! $employee || $employee->id !== $payslip->employee_id) {
                abort(403, 'Unauthorized access to this payslip.');
            }
        }

        $currency = CompanySetting::get('currency_symbol', '₹');
        $companyName = CompanySetting::get('company_name', 'Posterit Studio');
        $companyEmail = CompanySetting::get('company_email', 'ops@posterit.com');
        $companyAddress = CompanySetting::get('company_address', 'Creative Hub, Level 4, Bandra West, Mumbai 400050');

        return view('payroll.show', compact(
            'payslip',
            'currency',
            'companyName',
            'companyEmail',
            'companyAddress'
        ));
    }

    public function myPayslips(Request $request)
    {
        $user = Auth::user();
        $employee = $user->employee;

        if (! $employee) {
            return redirect()->route('dashboard')->with('error', 'No linked employee record found for your account.');
        }

        $payslips = Payslip::with('payrollRun')
            ->where('employee_id', $employee->id)
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->paginate(12);

        $currency = CompanySetting::get('currency_symbol', '₹');

        return view('payroll.my-payslips', compact('employee', 'payslips', 'currency'));
    }

    public function exportBankCsv(PayrollRun $payrollRun): StreamedResponse
    {
        $payslips = $payrollRun->payslips()->with('employee.department')->get();
        $filename = "Posterit_Bank_Payroll_{$payrollRun->month}_{$payrollRun->year}.csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        return response()->stream(function () use ($payslips) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Employee Code',
                'Employee Name',
                'Department',
                'Designation',
                'Bank Name',
                'Bank Account No',
                'IFSC Code',
                'UPI ID',
                'Basic Salary',
                'Working Days',
                'Payable Days',
                'Bonus',
                'Allowances',
                'Deductions',
                'Net Payout Amount',
                'Payment Status',
                'Payment Mode',
                'Reference / Account Notes',
            ]);

            foreach ($payslips as $ps) {
                fputcsv($handle, [
                    $ps->employee->employee_code,
                    $ps->employee->name,
                    $ps->employee->department->name ?? 'Design',
                    $ps->employee->designation ?? 'Team Member',
                    $ps->employee->bank_name ?? 'N/A',
                    $ps->employee->bank_account_no ? "'" . $ps->employee->bank_account_no : 'N/A',
                    $ps->employee->bank_ifsc ?? 'N/A',
                    $ps->employee->upi_id ?? 'N/A',
                    $ps->basic_salary,
                    $ps->working_days,
                    $ps->present_days + $ps->paid_leaves + ($ps->half_days * 0.5),
                    $ps->bonus_amount,
                    $ps->allowances_amount,
                    $ps->deductions_amount + $ps->tax_deduction,
                    $ps->net_salary,
                    strtoupper($ps->payment_status),
                    strtoupper($ps->payment_mode ?? 'BANK_TRANSFER'),
                    $ps->payment_reference ?? 'DIRECT_DEPOSIT',
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }
}
