<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payslip - {{ $payslip->employee->name }} - {{ $payslip->period_label }}</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; padding: 0 !important; }
            .print-shadow-none { box-shadow: none !important; border: 1px solid #e2e8f0 !important; }
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-900 min-h-screen py-10 px-4">

    <div class="max-w-3xl mx-auto space-y-4">

        <!-- Top Action Bar (hidden when printing) -->
        <div class="no-print flex items-center justify-between">
            <a href="javascript:history.back()" class="text-xs font-semibold text-slate-500 hover:text-slate-800 flex items-center gap-1">
                ← Back
            </a>
            <div class="flex items-center gap-2">
                <button onclick="window.print()" class="px-4 py-2 bg-[#0071e3] hover:bg-[#0062c4] text-white text-xs font-bold rounded-xl shadow-xs transition flex items-center gap-1.5 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    <span>Print / Save PDF</span>
                </button>
            </div>
        </div>

        <!-- Official Payslip Document -->
        <div class="bg-white rounded-3xl p-8 md:p-12 shadow-[0_4px_30px_rgba(0,0,0,0.06)] border border-slate-200/80 print-shadow-none space-y-8">
            
            <!-- Letterhead & Company Header -->
            <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-6 pb-6 border-b border-slate-200">
                <div class="space-y-1">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-xl bg-[#0071e3] text-white font-black text-base flex items-center justify-center shadow-xs">
                            P
                        </div>
                        <span class="text-xl font-black tracking-tight text-slate-900">{{ $companyName }}</span>
                    </div>
                    <p class="text-xs text-slate-500 max-w-sm pt-1">
                        {{ $companyAddress }}<br>
                        Email: {{ $companyEmail }}
                    </p>
                </div>

                <div class="text-left sm:text-right space-y-1">
                    <span class="inline-block px-3 py-1 bg-blue-50 text-[#0071e3] font-bold text-xs rounded-full border border-blue-100 uppercase tracking-wider">
                        Salary Payslip
                    </span>
                    <div class="text-lg font-black text-slate-900">{{ $payslip->period_label }}</div>
                    <div class="text-xs font-mono text-slate-400">SLIP-{{ str_pad($payslip->id, 5, '0', STR_PAD_LEFT) }}</div>
                </div>
            </div>

            <!-- Employee & Shift Meta Details -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 p-5 rounded-2xl bg-slate-50 border border-slate-200/80 text-xs">
                <div>
                    <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider block">Employee Name</span>
                    <span class="font-extrabold text-slate-900 text-sm">{{ $payslip->employee->name }}</span>
                    <span class="text-[11px] text-slate-500 block font-mono">{{ $payslip->employee->employee_code }}</span>
                </div>
                <div>
                    <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider block">Department & Role</span>
                    <span class="font-bold text-slate-900 block">{{ $payslip->employee->department->name ?? 'Design Studio' }}</span>
                    <span class="text-[11px] text-slate-500 block">{{ $payslip->employee->designation ?? 'Team Member' }}</span>
                </div>
                <div>
                    <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider block">Working Days</span>
                    <span class="font-bold text-slate-900 block">{{ $payslip->working_days }} Scheduled</span>
                    <span class="text-[11px] text-emerald-600 font-semibold block">{{ $payslip->present_days }} Present • {{ $payslip->paid_leaves }} Leave</span>
                </div>
                <div>
                    <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider block">Payment Status</span>
                    @if($payslip->payment_status === 'paid')
                        <span class="inline-flex items-center gap-1 text-emerald-600 font-extrabold mt-0.5">
                            ✓ PAID ({{ strtoupper($payslip->payment_mode ?? 'BANK') }})
                        </span>
                        @if($payslip->paid_at)
                            <span class="text-[10px] text-slate-400 block font-mono">{{ $payslip->paid_at->format('d M Y') }}</span>
                        @endif
                    @else
                        <span class="inline-flex items-center gap-1 text-amber-600 font-extrabold mt-0.5">
                            ⏳ PENDING
                        </span>
                    @endif
                </div>
            </div>

            <!-- Earnings vs Deductions Breakdown Table -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                <!-- Earnings Column -->
                <div class="space-y-3">
                    <h3 class="font-extrabold text-xs text-slate-500 uppercase tracking-wider pb-2 border-b border-slate-200 flex justify-between">
                        <span>Earnings</span>
                        <span>Amount ({{ $currency }})</span>
                    </h3>
                    <div class="space-y-2 text-xs">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-600">Basic Monthly Rate</span>
                            <span class="font-semibold text-slate-800 tabular-nums">{{ number_format($payslip->basic_salary, 2) }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-600">Earned Attendance Salary</span>
                            <span class="font-bold text-slate-900 tabular-nums">{{ number_format($payslip->earned_salary, 2) }}</span>
                        </div>
                        @if($payslip->bonus_amount > 0)
                        <div class="flex items-center justify-between text-emerald-600">
                            <span>Performance Bonus</span>
                            <span class="font-bold tabular-nums">+{{ number_format($payslip->bonus_amount, 2) }}</span>
                        </div>
                        @endif
                        @if($payslip->allowances_amount > 0)
                        <div class="flex items-center justify-between text-emerald-600">
                            <span>Studio Allowances</span>
                            <span class="font-bold tabular-nums">+{{ number_format($payslip->allowances_amount, 2) }}</span>
                        </div>
                        @endif
                    </div>
                    <div class="pt-3 border-t border-slate-200 flex items-center justify-between text-xs font-bold">
                        <span class="text-slate-900">Total Gross Earnings</span>
                        <span class="text-slate-900 tabular-nums">{{ $currency }}{{ number_format($payslip->earned_salary + $payslip->bonus_amount + $payslip->allowances_amount, 2) }}</span>
                    </div>
                </div>

                <!-- Deductions Column -->
                <div class="space-y-3">
                    <h3 class="font-extrabold text-xs text-slate-500 uppercase tracking-wider pb-2 border-b border-slate-200 flex justify-between">
                        <span>Deductions</span>
                        <span>Amount ({{ $currency }})</span>
                    </h3>
                    <div class="space-y-2 text-xs">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-600">Unpaid Absences ({{ $payslip->unpaid_days }}d)</span>
                            <span class="font-semibold text-slate-800 tabular-nums">
                                {{ number_format(max(0, $payslip->basic_salary - $payslip->earned_salary), 2) }}
                            </span>
                        </div>
                        @if($payslip->deductions_amount > 0)
                        <div class="flex items-center justify-between text-rose-600">
                            <span>Custom Deductions</span>
                            <span class="font-bold tabular-nums">-{{ number_format($payslip->deductions_amount, 2) }}</span>
                        </div>
                        @endif
                        @if($payslip->tax_deduction > 0)
                        <div class="flex items-center justify-between text-rose-600">
                            <span>Tax / TDS</span>
                            <span class="font-bold tabular-nums">-{{ number_format($payslip->tax_deduction, 2) }}</span>
                        </div>
                        @endif
                    </div>
                    <div class="pt-3 border-t border-slate-200 flex items-center justify-between text-xs font-bold">
                        <span class="text-slate-900">Total Deductions</span>
                        <span class="text-rose-600 tabular-nums">{{ $currency }}{{ number_format($payslip->deductions_amount + $payslip->tax_deduction, 2) }}</span>
                    </div>
                </div>

            </div>

            <!-- Net Salary Highlight Banner -->
            <div class="p-6 rounded-2xl bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 text-white flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-lg">
                <div>
                    <span class="text-[10px] font-extrabold uppercase tracking-widest text-slate-400">Total Net Take-Home Pay</span>
                    <div class="text-2xl sm:text-3xl font-black tracking-tight text-white mt-0.5 tabular-nums">
                        {{ $currency }}{{ number_format($payslip->net_salary, 2) }}
                    </div>
                </div>

                <div class="text-left sm:text-right text-xs">
                    <span class="text-slate-400 block font-medium">Calculation based on</span>
                    <span class="font-bold text-slate-200">{{ $payslip->present_days + $payslip->paid_leaves + ($payslip->half_days * 0.5) }} / {{ $payslip->working_days }} Payable Days</span>
                </div>
            </div>

            <!-- Footer & Verification Notice -->
            <div class="pt-6 border-t border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4 text-[11px] text-slate-400">
                <div>
                    <span>This is an official system-generated payslip from Posterit Operations.</span>
                </div>
                <div class="font-mono text-right">
                    Generated: {{ now()->format('d M Y, h:i A') }}
                </div>
            </div>

        </div>

    </div>

</body>
</html>
