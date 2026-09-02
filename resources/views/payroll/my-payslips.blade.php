@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 text-[10px] font-extrabold bg-blue-50 text-[#0071e3] border border-blue-200 rounded-full uppercase tracking-wider">
                    💳 Salary & Payslips
                </span>
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight mt-1">
                My Payslips & Compensation History
            </h1>
            <p class="text-xs text-slate-500 font-medium">
                View your monthly salary statements, attendance deductions, bonus records, and download official payslip PDFs.
            </p>
        </div>

        <div class="p-3 bg-slate-50 rounded-2xl border border-slate-200/80 flex items-center gap-3 text-xs">
            <img src="{{ $employee->photo_url }}" class="w-9 h-9 rounded-xl object-cover border border-slate-200">
            <div>
                <div class="font-bold text-slate-900">{{ $employee->name }}</div>
                <div class="text-[11px] text-slate-500 font-mono">{{ $employee->employee_code }} • {{ $employee->department->name ?? 'Design' }}</div>
            </div>
        </div>
    </div>

    <!-- Payslips Grid / Table -->
    <div class="rounded-3xl bg-white border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50/70 text-slate-500 uppercase text-[10px]">
                        <th class="py-3.5 px-4 font-extrabold">Pay Period</th>
                        <th class="py-3.5 px-4 font-extrabold text-right">Base Rate</th>
                        <th class="py-3.5 px-4 font-extrabold text-center">Payable Days</th>
                        <th class="py-3.5 px-4 font-extrabold text-right">Earned</th>
                        <th class="py-3.5 px-4 font-extrabold text-right">Bonus</th>
                        <th class="py-3.5 px-4 font-extrabold text-right">Deductions</th>
                        <th class="py-3.5 px-4 font-extrabold text-right">Net Payout</th>
                        <th class="py-3.5 px-4 font-extrabold text-center">Status</th>
                        <th class="py-3.5 px-4 font-extrabold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($payslips as $slip)
                    <tr class="hover:bg-slate-50/80 transition">
                        <!-- Pay Period -->
                        <td class="py-3.5 px-4 font-bold text-slate-900">
                            {{ $slip->period_label }}
                        </td>

                        <!-- Base Salary -->
                        <td class="py-3.5 px-4 text-right font-semibold text-slate-700 tabular-nums">
                            {{ $currency }}{{ number_format($slip->basic_salary, 2) }}
                        </td>

                        <!-- Attendance / Payable Days -->
                        <td class="py-3.5 px-4 text-center">
                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 text-slate-700 border border-slate-200/80">
                                {{ $slip->present_days + $slip->paid_leaves + ($slip->half_days * 0.5) }} / {{ $slip->working_days }} Days
                            </span>
                        </td>

                        <!-- Earned Salary -->
                        <td class="py-3.5 px-4 text-right font-semibold text-slate-800 tabular-nums">
                            {{ $currency }}{{ number_format($slip->earned_salary, 2) }}
                        </td>

                        <!-- Bonus -->
                        <td class="py-3.5 px-4 text-right tabular-nums">
                            @if($slip->bonus_amount > 0)
                                <span class="font-bold text-emerald-600">+{{ $currency }}{{ number_format($slip->bonus_amount, 2) }}</span>
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

                        <!-- Net Take Home -->
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
                            <a href="{{ route('payroll.showPayslip', $slip) }}" target="_blank"
                               class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-blue-50 hover:bg-blue-100 text-[#0071e3] font-bold text-xs transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <span>Download PDF</span>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="py-12 text-center text-slate-400">
                            <div class="flex flex-col items-center justify-center">
                                <svg class="w-10 h-10 text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <span class="font-semibold text-sm text-slate-600">No payslips issued yet</span>
                                <span class="text-xs text-slate-400 mt-0.5">Your monthly salary statements will appear here once processed by studio management.</span>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($payslips->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $payslips->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
