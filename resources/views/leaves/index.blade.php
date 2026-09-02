@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{
    approvalModalOpen: false,
    selectedLeaveId: null,
    selectedLeaveStatus: '',
    remarks: '',
    openApproval(id, status) {
        this.selectedLeaveId = id;
        this.selectedLeaveStatus = status;
        this.approvalModalOpen = true;
    }
}">

    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold font-display text-slate-900 dark:text-white tracking-tight">
                Leave Management
            </h1>
            <p class="text-xs text-slate-500">
                Track employee leave quotas, applications, and manager approvals.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('leaves.create') }}" 
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 text-white text-xs font-bold rounded-xl shadow-md transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span>+ Apply for Leave</span>
            </a>
        </div>
    </div>

    <!-- Vibrant Dribbble / Arcade Style Leave Metric Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        
        <!-- All Applications (Cyan / Sky Blue Gradient) -->
        <a href="{{ route('leaves.index') }}" 
           class="relative overflow-hidden p-5 rounded-2xl bg-gradient-to-br from-[#0284c7] via-[#0ea5e9] to-[#38bdf8] text-white shadow-[0_8px_22px_rgba(14,165,233,0.25)] hover:scale-[1.02] transition-all duration-200 flex flex-col justify-between group cursor-pointer">
            <!-- Large Watermark Illustration -->
            <svg class="absolute -right-3 -bottom-3 w-28 h-28 text-white/15 pointer-events-none transition-transform group-hover:scale-110 duration-300" fill="currentColor" viewBox="0 0 24 24">
                <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/>
            </svg>

            <div class="relative z-10">
                <span class="text-[11px] font-extrabold uppercase tracking-wider text-white/90">Total Requests</span>
                <div class="w-6 h-0.5 bg-white/30 rounded-full mt-1"></div>
            </div>
            <div class="mt-4 relative z-10">
                <div class="text-3xl font-black text-white tracking-tight">{{ $stats['all'] }}</div>
                <div class="text-[11px] text-white/85 font-medium mt-1">All leave records</div>
            </div>
        </a>

        <!-- Pending Review (Orange / Amber Gradient) -->
        <a href="{{ route('leaves.index', ['status' => 'pending']) }}" 
           class="relative overflow-hidden p-5 rounded-2xl bg-gradient-to-br from-[#f59e0b] via-[#f97316] to-[#fb923c] text-white shadow-[0_8px_22px_rgba(249,115,22,0.25)] hover:scale-[1.02] transition-all duration-200 flex flex-col justify-between group cursor-pointer">
            <!-- Large Watermark Illustration -->
            <svg class="absolute -right-3 -bottom-3 w-28 h-28 text-white/15 pointer-events-none transition-transform group-hover:scale-110 duration-300" fill="currentColor" viewBox="0 0 24 24">
                <path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z"/>
            </svg>

            <div class="relative z-10">
                <span class="text-[11px] font-extrabold uppercase tracking-wider text-white/90">Pending Review</span>
                <div class="w-6 h-0.5 bg-white/30 rounded-full mt-1"></div>
            </div>
            <div class="mt-4 relative z-10">
                <div class="text-3xl font-black text-white tracking-tight">{{ $stats['pending'] }}</div>
                <div class="text-[11px] text-white/85 font-medium mt-1">Awaiting decision</div>
            </div>
        </a>

        <!-- Approved This Year (Emerald / Mint Gradient) -->
        <a href="{{ route('leaves.index', ['status' => 'approved']) }}" 
           class="relative overflow-hidden p-5 rounded-2xl bg-gradient-to-br from-[#059669] via-[#10b981] to-[#34d399] text-white shadow-[0_8px_22px_rgba(16,185,129,0.25)] hover:scale-[1.02] transition-all duration-200 flex flex-col justify-between group cursor-pointer">
            <!-- Large Watermark Illustration -->
            <svg class="absolute -right-3 -bottom-3 w-28 h-28 text-white/15 pointer-events-none transition-transform group-hover:scale-110 duration-300" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
            </svg>

            <div class="relative z-10">
                <span class="text-[11px] font-extrabold uppercase tracking-wider text-white/90">Approved ({{ now()->year }})</span>
                <div class="w-6 h-0.5 bg-white/30 rounded-full mt-1"></div>
            </div>
            <div class="mt-4 relative z-10">
                <div class="text-3xl font-black text-white tracking-tight">{{ $stats['approved'] }}</div>
                <div class="text-[11px] text-white/85 font-medium mt-1">Authorized leaves</div>
            </div>
        </a>

        <!-- Rejected This Year (Rose / Magenta Gradient) -->
        <a href="{{ route('leaves.index', ['status' => 'rejected']) }}" 
           class="relative overflow-hidden p-5 rounded-2xl bg-gradient-to-br from-[#ec4899] via-[#f43f5e] to-[#fb7185] text-white shadow-[0_8px_22px_rgba(244,63,94,0.25)] hover:scale-[1.02] transition-all duration-200 flex flex-col justify-between group cursor-pointer">
            <!-- Large Watermark Illustration -->
            <svg class="absolute -right-3 -bottom-3 w-28 h-28 text-white/15 pointer-events-none transition-transform group-hover:scale-110 duration-300" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 2C6.47 2 2 6.47 2 12s4.47 10 10 10 10-4.47 10-10S17.53 2 12 2zm5 13.59L15.59 17 12 13.41 8.41 17 7 15.59 10.59 12 7 8.41 8.41 7 12 10.59 15.59 7 17 8.41 13.41 12 17 15.59z"/>
            </svg>

            <div class="relative z-10">
                <span class="text-[11px] font-extrabold uppercase tracking-wider text-white/90">Rejected ({{ now()->year }})</span>
                <div class="w-6 h-0.5 bg-white/30 rounded-full mt-1"></div>
            </div>
            <div class="mt-4 relative z-10">
                <div class="text-3xl font-black text-white tracking-tight">{{ $stats['rejected'] }}</div>
                <div class="text-[11px] text-white/85 font-medium mt-1">Declined requests</div>
            </div>
        </a>

    </div>

    @if(auth()->user()->isEmployee() && auth()->user()->employee)
    @php
        $emp = auth()->user()->employee;
        $totalQuota = (int) ($emp->leave_quota ?? 18);
        $usedLeaves = $emp->used_leaves;
        $remainingLeaves = $emp->remaining_leaves;
        $pctUtilized = $totalQuota > 0 ? min(100, round(($usedLeaves / $totalQuota) * 100)) : 0;
    @endphp
    <!-- Annual Leave Quota & Balance Bar -->
    <div class="p-5 bg-white rounded-3xl border border-slate-200/90 shadow-2xs">
        <div class="flex items-center justify-between mb-3">
            <div>
                <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Your {{ now()->year }} Leave Quota & Balance</h3>
                <p class="text-[11px] text-slate-400">Available annual leave allocations & usage summary</p>
            </div>
            <span class="text-xs font-semibold text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-full border border-emerald-200/60">
                {{ $remainingLeaves }} Days Available
            </span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200/70">
                <div class="flex items-center justify-between text-xs mb-1.5">
                    <span class="font-bold text-slate-800">Annual Quota</span>
                    <span class="font-black text-slate-900">{{ $totalQuota }} <span class="text-[10px] text-slate-400 font-normal">days/year</span></span>
                </div>
                <div class="w-full bg-slate-200 rounded-full h-1.5 overflow-hidden">
                    <div class="bg-[#0071e3] h-1.5 rounded-full" style="width: 100%"></div>
                </div>
                <div class="mt-1.5 flex justify-between text-[10px] text-slate-400">
                    <span>Base annual allocation</span>
                </div>
            </div>

            <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200/70">
                <div class="flex items-center justify-between text-xs mb-1.5">
                    <span class="font-bold text-slate-800">Approved Leaves</span>
                    <span class="font-black text-amber-700">{{ $usedLeaves }} <span class="text-[10px] text-slate-400 font-normal">days used</span></span>
                </div>
                <div class="w-full bg-slate-200 rounded-full h-1.5 overflow-hidden">
                    <div class="bg-amber-500 h-1.5 rounded-full" style="width: {{ $pctUtilized }}%"></div>
                </div>
                <div class="mt-1.5 flex justify-between text-[10px] text-slate-400">
                    <span>{{ $pctUtilized }}% quota utilized</span>
                </div>
            </div>

            <div class="p-3.5 rounded-2xl bg-emerald-50/60 border border-emerald-200/60">
                <div class="flex items-center justify-between text-xs mb-1.5">
                    <span class="font-bold text-emerald-900">Remaining Balance</span>
                    <span class="font-black text-emerald-700">{{ $remainingLeaves }} <span class="text-[10px] text-emerald-600 font-normal">days left</span></span>
                </div>
                <div class="w-full bg-emerald-200 rounded-full h-1.5 overflow-hidden">
                    <div class="bg-emerald-500 h-1.5 rounded-full" style="width: {{ 100 - $pctUtilized }}%"></div>
                </div>
                <div class="mt-1.5 flex justify-between text-[10px] text-emerald-600 font-medium">
                    <span>Ready for new requests</span>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Filter Bar -->
    <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-xs">
        <form method="GET" action="{{ route('leaves.index') }}" class="flex flex-wrap items-center justify-between gap-3 text-xs">
            <div class="flex flex-wrap items-center gap-3">
                <select name="status" onchange="this.form.submit()" class="px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-semibold">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>

                @if(!auth()->user()->isEmployee())
                <select name="employee_id" onchange="this.form.submit()" class="px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-semibold">
                    <option value="">All Employees</option>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>{{ $emp->name }}</option>
                    @endforeach
                </select>
                @endif

                <select name="leave_type_id" onchange="this.form.submit()" class="px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-semibold">
                    <option value="">All Leave Types</option>
                    @foreach($leaveTypes as $lt)
                        <option value="{{ $lt->id }}" {{ request('leave_type_id') == $lt->id ? 'selected' : '' }}>{{ $lt->name }}</option>
                    @endforeach
                </select>
            </div>

            @if(request()->hasAny(['status', 'employee_id', 'leave_type_id']))
            <a href="{{ route('leaves.index') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-700">
                Clear Filters ✕
            </a>
            @endif
        </form>
    </div>

    <!-- Table -->
    <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800 text-slate-400 uppercase tracking-wider text-[10px]">
                        <th class="py-3.5 px-4 font-bold">Employee</th>
                        <th class="py-3.5 px-4 font-bold">Leave Type</th>
                        <th class="py-3.5 px-4 font-bold">Dates</th>
                        <th class="py-3.5 px-4 font-bold text-center">Days</th>
                        <th class="py-3.5 px-4 font-bold">Reason</th>
                        <th class="py-3.5 px-4 font-bold">Status</th>
                        <th class="py-3.5 px-4 font-bold">Decision Remarks</th>
                        @if(auth()->user()->isManager())
                        <th class="py-3.5 px-4 font-bold text-right">Actions</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                    @forelse($leaves as $leave)
                    <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/30 transition">
                        <td class="py-3 px-4">
                            <div class="flex items-center gap-2.5">
                                <img src="{{ $leave->employee->photo_url }}" class="w-7 h-7 rounded-full object-cover">
                                <div>
                                    <div class="font-bold text-slate-900 dark:text-white">{{ $leave->employee->name }}</div>
                                    <div class="text-[10px] text-slate-400">{{ $leave->employee->department->name ?? 'N/A' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="py-3 px-4 font-semibold text-slate-700 dark:text-slate-300">
                            {{ $leave->leaveType->name }}
                        </td>
                        <td class="py-3 px-4 font-semibold whitespace-nowrap">
                            {{ $leave->start_date->format('d M') }} - {{ $leave->end_date->format('d M, Y') }}
                        </td>
                        <td class="py-3 px-4 text-center font-extrabold text-slate-900 dark:text-white">
                            {{ $leave->total_days }}
                        </td>
                        <td class="py-3 px-4 text-slate-600 dark:text-slate-400 max-w-xs">
                            {{ $leave->reason }}
                        </td>
                        <td class="py-3 px-4">
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase
                                {{ $leave->status === 'approved' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' : '' }}
                                {{ $leave->status === 'pending' ? 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300' : '' }}
                                {{ $leave->status === 'rejected' ? 'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300' : '' }}">
                                {{ $leave->status }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-slate-500 text-[11px]">
                            @if($leave->action_remarks)
                                <span>{{ $leave->action_remarks }}</span>
                                @if($leave->actionBy)
                                <div class="text-[10px] text-slate-400 mt-0.5">by {{ $leave->actionBy->name }}</div>
                                @endif
                            @else
                                <span>-</span>
                            @endif
                        </td>

                        @if(auth()->user()->isManager())
                        <td class="py-3 px-4 text-right whitespace-nowrap">
                            @if($leave->status === 'pending')
                            <div class="flex items-center justify-end gap-2">
                                <form method="POST" action="{{ route('leaves.updateStatus', $leave) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="approved">
                                    <button type="submit" class="px-3 py-1 bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg text-xs font-bold transition">
                                        Approve
                                    </button>
                                </form>

                                <form method="POST" action="{{ route('leaves.updateStatus', $leave) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="rejected">
                                    <button type="submit" class="px-3 py-1 bg-rose-600 hover:bg-rose-500 text-white rounded-lg text-xs font-bold transition">
                                        Reject
                                    </button>
                                </form>
                            </div>
                            @else
                                <span class="text-[11px] text-slate-400">Processed</span>
                            @endif
                        </td>
                        @endif
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-10 text-center text-slate-400">No leave records found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($leaves->hasPages())
        <div class="p-4 border-t border-slate-200 dark:border-slate-800">
            {{ $leaves->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
