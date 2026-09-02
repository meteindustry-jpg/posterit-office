@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{
    editModalOpen: false,
    selectedSlip: {
        id: null,
        name: '',
        code: '',
        basic_salary: 0,
        bonus_amount: 0,
        allowances_amount: 0,
        deductions_amount: 0,
        tax_deduction: 0,
        payment_status: 'pending',
        payment_mode: 'bank_transfer',
        payment_reference: '',
        notes: '',
        url: ''
    },
    openEdit(slip) {
        this.selectedSlip = {
            id: slip.id,
            name: slip.employee.name,
            code: slip.employee.employee_code,
            basic_salary: slip.basic_salary,
            bonus_amount: slip.bonus_amount,
            allowances_amount: slip.allowances_amount,
            deductions_amount: slip.deductions_amount,
            tax_deduction: slip.tax_deduction,
            payment_status: slip.payment_status,
            payment_mode: slip.payment_mode || 'bank_transfer',
            payment_reference: slip.payment_reference || '',
            notes: slip.notes || '',
            url: `{{ url('payroll/payslips') }}/${slip.id}`
        };
        this.editModalOpen = true;
    }
}">

    <!-- Header & Quick Actions -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 text-[10px] font-extrabold bg-emerald-50 text-emerald-600 border border-emerald-200 rounded-full uppercase tracking-wider">
                    💼 Studio Payroll
                </span>
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight mt-1">
                Payroll & Compensation Command Center
            </h1>
            <p class="text-xs text-slate-500 font-medium">
                Automated monthly salary calculation, attendance deductions, bonus allocations, and bank payout sheets.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            @if($payrollRun)
                <a href="{{ route('payroll.exportBankCsv', $payrollRun) }}" 
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-bold rounded-xl shadow-2xs transition">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Bank Payout CSV</span>
                </a>

                @if($pendingCount > 0)
                <form method="POST" action="{{ route('payroll.bulkMarkPaid', $payrollRun) }}">
                    @csrf
                    <button type="submit" onclick="return confirm('Are you sure you want to mark all pending payslips as PAID for this month?')"
                            class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white text-xs font-bold rounded-xl shadow-xs transition cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        <span>Mark All as Paid</span>
                    </button>
                </form>
                @endif
            @endif
        </div>
    </div>

    <!-- Vibrant Arcade KPI Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        
        <!-- Total Net Payroll (Emerald Gradient) -->
        <div class="relative overflow-hidden p-5 rounded-2xl bg-gradient-to-br from-[#059669] via-[#10b981] to-[#34d399] text-white shadow-[0_8px_22px_rgba(16,185,129,0.25)] hover:scale-[1.02] transition-all duration-200 flex flex-col justify-between group">
            <svg class="absolute -right-3 -bottom-3 w-28 h-28 text-white/15 pointer-events-none transition-transform group-hover:scale-110 duration-300" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1.41 16.09V20h-2.67v-1.93c-1.71-.36-3.16-1.46-3.27-3.4h1.96c.1 1.05.82 1.87 2.11 1.87 1.48 0 2.05-.88 2.05-1.57 0-.97-.73-1.46-2.58-1.96-2.02-.55-3.34-1.39-3.34-3.18 0-1.63 1.25-2.85 2.87-3.21V5h2.67v1.86c1.47.33 2.65 1.34 2.81 2.94h-1.97c-.16-.86-.77-1.48-1.84-1.48-1.12 0-1.81.65-1.81 1.47 0 .84.66 1.29 2.45 1.76 2.14.57 3.47 1.44 3.47 3.32 0 1.78-1.31 3.01-2.95 3.34z"/>
            </svg>
            <div class="relative z-10">
                <span class="text-[11px] font-extrabold uppercase tracking-wider text-white/90">Total Net Payout</span>
                <div class="w-6 h-0.5 bg-white/30 rounded-full mt-1"></div>
            </div>
            <div class="mt-4 relative z-10">
                <div class="text-3xl font-black text-white tracking-tight tabular-nums">{{ $currency }}{{ number_format($totalNetPayroll, 2) }}</div>
                <div class="text-[11px] text-white/85 font-medium mt-1">{{ $payrollRun ? $payrollRun->total_employees : 0 }} active team members</div>
            </div>
        </div>

        <!-- Paid vs Pending Progress (Sky Blue Gradient) -->
        <div class="relative overflow-hidden p-5 rounded-2xl bg-gradient-to-br from-[#0284c7] via-[#0ea5e9] to-[#38bdf8] text-white shadow-[0_8px_22px_rgba(14,165,233,0.25)] hover:scale-[1.02] transition-all duration-200 flex flex-col justify-between group">
            <svg class="absolute -right-3 -bottom-3 w-28 h-28 text-white/15 pointer-events-none transition-transform group-hover:scale-110 duration-300" fill="currentColor" viewBox="0 0 24 24">
                <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-9 14l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
            </svg>
            <div class="relative z-10">
                <span class="text-[11px] font-extrabold uppercase tracking-wider text-white/90">Payout Status</span>
                <div class="w-6 h-0.5 bg-white/30 rounded-full mt-1"></div>
            </div>
            <div class="mt-4 relative z-10">
                <div class="flex items-baseline gap-1.5">
                    <div class="text-3xl font-black text-white tracking-tight tabular-nums">{{ $paidCount }}</div>
                    <span class="text-xs text-white/85 font-medium">/ {{ $payrollRun ? $payrollRun->total_employees : 0 }} Paid</span>
                </div>
                <div class="text-[11px] text-white/90 font-medium mt-1">
                    @if($pendingCount > 0)
                        <span>{{ $pendingCount }} pending disbursement</span>
                    @else
                        <span>✓ All payouts completed</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Total Bonus & Incentives (Amber Gradient) -->
        <div class="relative overflow-hidden p-5 rounded-2xl bg-gradient-to-br from-[#f59e0b] via-[#f97316] to-[#fb923c] text-white shadow-[0_8px_22px_rgba(249,115,22,0.25)] hover:scale-[1.02] transition-all duration-200 flex flex-col justify-between group">
            <svg class="absolute -right-3 -bottom-3 w-28 h-28 text-white/15 pointer-events-none transition-transform group-hover:scale-110 duration-300" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/>
            </svg>
            <div class="relative z-10">
                <span class="text-[11px] font-extrabold uppercase tracking-wider text-white/90">Bonuses & Perks</span>
                <div class="w-6 h-0.5 bg-white/30 rounded-full mt-1"></div>
            </div>
            <div class="mt-4 relative z-10">
                <div class="text-3xl font-black text-white tracking-tight tabular-nums">{{ $currency }}{{ number_format($totalBonus, 2) }}</div>
                <div class="text-[11px] text-white/85 font-medium mt-1">Performance incentives</div>
            </div>
        </div>

        <!-- Total Deductions & Taxes (Violet Gradient) -->
        <div class="relative overflow-hidden p-5 rounded-2xl bg-gradient-to-br from-[#6366f1] via-[#7c3aed] to-[#8b5cf6] text-white shadow-[0_8px_22px_rgba(99,102,241,0.25)] hover:scale-[1.02] transition-all duration-200 flex flex-col justify-between group">
            <svg class="absolute -right-3 -bottom-3 w-28 h-28 text-white/15 pointer-events-none transition-transform group-hover:scale-110 duration-300" fill="currentColor" viewBox="0 0 24 24">
                <path d="M19 13H5v-2h14v2z"/>
            </svg>
            <div class="relative z-10">
                <span class="text-[11px] font-extrabold uppercase tracking-wider text-white/90">Total Deductions</span>
                <div class="w-6 h-0.5 bg-white/30 rounded-full mt-1"></div>
            </div>
            <div class="mt-4 relative z-10">
                <div class="text-3xl font-black text-white tracking-tight tabular-nums">{{ $currency }}{{ number_format($totalDeductions, 2) }}</div>
                <div class="text-[11px] text-white/85 font-medium mt-1">Unpaid leaves & tax hold</div>
            </div>
        </div>

    </div>

    <!-- Filter & Generator Toolbar -->
    <div class="p-4 rounded-3xl bg-white border border-slate-200/80 shadow-xs flex flex-wrap items-center justify-between gap-4">
        
        <!-- Month & Year Switcher -->
        <form method="GET" action="{{ route('payroll.index') }}" class="flex flex-wrap items-center gap-3 text-xs">
            <div class="flex items-center gap-2">
                <label class="font-bold text-slate-400 uppercase text-[10px]">Month</label>
                <select name="month" onchange="this.form.submit()" class="px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl font-bold text-slate-800 focus:ring-2 focus:ring-[#0071e3]">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $month === $m ? 'selected' : '' }}>
                            {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                        </option>
                    @endfor
                </select>
            </div>

            <div class="flex items-center gap-2">
                <label class="font-bold text-slate-400 uppercase text-[10px]">Year</label>
                <select name="year" onchange="this.form.submit()" class="px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl font-bold text-slate-800 focus:ring-2 focus:ring-[#0071e3]">
                    @for($y = now()->year - 2; $y <= now()->year + 1; $y++)
                        <option value="{{ $y }}" {{ $year === $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
        </form>

        <!-- Generate / Recalculate Button -->
        <form method="POST" action="{{ route('payroll.generate') }}">
            @csrf
            <input type="hidden" name="month" value="{{ $month }}">
            <input type="hidden" name="year" value="{{ $year }}">
            <button type="submit" class="px-5 py-2 bg-[#0071e3] hover:bg-[#0062c4] active:scale-95 text-white text-xs font-bold rounded-xl shadow-xs transition flex items-center gap-2 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                <span>{{ $payrollRun ? 'Recalculate with Live Attendance' : 'Generate Monthly Payroll' }}</span>
            </button>
        </form>

    </div>

    <!-- Payroll Runs Table -->
    <div class="rounded-3xl bg-white border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50/70 text-slate-500 uppercase text-[10px]">
                        <th class="py-3.5 px-4 font-extrabold">Employee</th>
                        <th class="py-3.5 px-4 font-extrabold text-right">Base Salary</th>
                        <th class="py-3.5 px-4 font-extrabold text-center">Attendance Log</th>
                        <th class="py-3.5 px-4 font-extrabold text-right">Earned</th>
                        <th class="py-3.5 px-4 font-extrabold text-right">Bonus / Allow</th>
                        <th class="py-3.5 px-4 font-extrabold text-right">Deductions</th>
                        <th class="py-3.5 px-4 font-extrabold text-right">Net Payout</th>
                        <th class="py-3.5 px-4 font-extrabold text-center">Status</th>
                        <th class="py-3.5 px-4 font-extrabold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @if($payrollRun && $payrollRun->payslips->count() > 0)
                        @foreach($payrollRun->payslips as $slip)
                        <tr class="hover:bg-slate-50/80 transition">
                            <!-- Employee Info -->
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-3">
                                    <img src="{{ $slip->employee->photo_url }}" class="w-8 h-8 rounded-full object-cover border border-slate-200 shrink-0">
                                    <div>
                                        <div class="font-bold text-slate-900">{{ $slip->employee->name }}</div>
                                        <div class="text-[11px] text-slate-500 font-mono">{{ $slip->employee->employee_code }} • {{ $slip->employee->department->name ?? 'Studio' }}</div>
                                    </div>
                                </div>
                            </td>

                            <!-- Base Salary -->
                            <td class="py-3.5 px-4 text-right font-semibold text-slate-700 tabular-nums">
                                {{ $currency }}{{ number_format($slip->basic_salary, 2) }}
                            </td>

                            <!-- Attendance Breakdown -->
                            <td class="py-3.5 px-4 text-center">
                                <div class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 text-slate-700 border border-slate-200/80">
                                    <span class="text-emerald-600">{{ $slip->present_days }}P</span> •
                                    <span class="text-blue-600">{{ $slip->paid_leaves }}L</span>
                                    @if($slip->unpaid_days > 0)
                                        • <span class="text-rose-600">{{ $slip->unpaid_days }} Absent</span>
                                    @endif
                                </div>
                            </td>

                            <!-- Earned Salary -->
                            <td class="py-3.5 px-4 text-right font-semibold text-slate-900 tabular-nums">
                                {{ $currency }}{{ number_format($slip->earned_salary, 2) }}
                            </td>

                            <!-- Bonus / Allowances -->
                            <td class="py-3.5 px-4 text-right tabular-nums">
                                @if($slip->bonus_amount + $slip->allowances_amount > 0)
                                    <span class="font-bold text-emerald-600">+{{ $currency }}{{ number_format($slip->bonus_amount + $slip->allowances_amount, 2) }}</span>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>

                            <!-- Deductions -->
                            <td class="py-3.5 px-4 text-right tabular-nums">
                                @if($slip->deductions_amount + $slip->tax_deduction > 0)
                                    <span class="font-bold text-rose-600">-{{ $currency }}{{ number_format($slip->deductions_amount + $slip->tax_deduction, 2) }}</span>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>

                            <!-- Net Payout -->
                            <td class="py-3.5 px-4 text-right font-black text-sm text-[#0071e3] tabular-nums">
                                {{ $currency }}{{ number_format($slip->net_salary, 2) }}
                            </td>

                            <!-- Status -->
                            <td class="py-3.5 px-4 text-center">
                                @if($slip->payment_status === 'paid')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                        PAID
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-amber-50 text-amber-700 border border-amber-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                        PENDING
                                    </span>
                                @endif
                            </td>

                            <!-- Actions -->
                            <td class="py-3.5 px-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button type="button" @click="openEdit(@js($slip))" 
                                            class="px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition cursor-pointer" title="Adjust Bonus / Deductions">
                                        Adjust
                                    </button>
                                    <a href="{{ route('payroll.showPayslip', $slip) }}" target="_blank"
                                       class="px-2.5 py-1.5 rounded-lg bg-blue-50 text-[#0071e3] hover:bg-blue-100 text-xs font-bold transition flex items-center gap-1" title="View / Print Payslip">
                                        <span>Payslip</span>
                                        <span>→</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="9" class="py-12 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="w-12 h-12 text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    <span class="font-semibold text-sm text-slate-600">No payroll calculated yet for {{ date('F Y', mktime(0, 0, 0, $month, 1, $year)) }}</span>
                                    <span class="text-xs text-slate-400 mt-0.5">Click "Generate Monthly Payroll" above to compute salaries from attendance.</span>
                                </div>
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    <!-- Edit / Adjust Payslip Modal -->
    <div x-show="editModalOpen" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-xs"
         style="display: none;">
        
        <div @click.outside="editModalOpen = false" class="w-full max-w-lg bg-white rounded-3xl shadow-2xl border border-slate-200/80 p-6 space-y-4 text-xs">
            
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div>
                    <h3 class="font-extrabold text-sm text-slate-900">Adjust Compensation & Payout</h3>
                    <p class="text-[11px] text-slate-500 mt-0.5">
                        <span x-text="selectedSlip.name" class="font-bold text-slate-800"></span> (<span x-text="selectedSlip.code"></span>)
                    </p>
                </div>
                <button type="button" @click="editModalOpen = false" class="text-slate-400 hover:text-slate-600 p-1 text-base">✕</button>
            </div>

            <form method="POST" :action="selectedSlip.url" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="block font-bold text-slate-500 uppercase mb-1">Monthly Base Salary ({{ $currency }})</label>
                    <input type="number" step="0.01" name="basic_salary" x-model="selectedSlip.basic_salary" 
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl font-bold text-slate-900 focus:ring-2 focus:ring-[#0071e3] text-sm">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-500 uppercase mb-1">Performance Bonus ({{ $currency }})</label>
                        <input type="number" step="0.01" name="bonus_amount" x-model="selectedSlip.bonus_amount" 
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl font-bold text-emerald-600 focus:ring-2 focus:ring-[#0071e3]">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-500 uppercase mb-1">Custom Allowances ({{ $currency }})</label>
                        <input type="number" step="0.01" name="allowances_amount" x-model="selectedSlip.allowances_amount" 
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl font-bold text-slate-800 focus:ring-2 focus:ring-[#0071e3]">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-500 uppercase mb-1">Custom Deductions ({{ $currency }})</label>
                        <input type="number" step="0.01" name="deductions_amount" x-model="selectedSlip.deductions_amount" 
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl font-bold text-rose-600 focus:ring-2 focus:ring-[#0071e3]">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-500 uppercase mb-1">Tax / TDS Hold ({{ $currency }})</label>
                        <input type="number" step="0.01" name="tax_deduction" x-model="selectedSlip.tax_deduction" 
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl font-bold text-slate-800 focus:ring-2 focus:ring-[#0071e3]">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 pt-1 border-t border-slate-100">
                    <div>
                        <label class="block font-bold text-slate-500 uppercase mb-1">Payout Status</label>
                        <select name="payment_status" x-model="selectedSlip.payment_status" 
                                class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl font-bold text-slate-800 focus:ring-2 focus:ring-[#0071e3]">
                            <option value="pending">Pending</option>
                            <option value="paid">Paid</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-500 uppercase mb-1">Payment Mode</label>
                        <select name="payment_mode" x-model="selectedSlip.payment_mode" 
                                class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl font-semibold text-slate-800 focus:ring-2 focus:ring-[#0071e3]">
                            <option value="bank_transfer">Bank Transfer / NEFT</option>
                            <option value="upi">UPI Instant</option>
                            <option value="cheque">Cheque</option>
                            <option value="cash">Cash</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-500 uppercase mb-1">Transaction Ref / Cheque No.</label>
                    <input type="text" name="payment_reference" x-model="selectedSlip.payment_reference" placeholder="e.g. UTR1284918239 or Notes" 
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl font-mono text-slate-800">
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                    <button type="button" @click="editModalOpen = false" class="px-4 py-2 bg-slate-100 text-slate-700 font-semibold rounded-xl hover:bg-slate-200">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2 bg-[#0071e3] hover:bg-[#0062c4] text-white font-bold rounded-xl shadow-xs transition active:scale-95 cursor-pointer">
                        Save Adjustments
                    </button>
                </div>

            </form>

        </div>
    </div>

</div>
@endsection
