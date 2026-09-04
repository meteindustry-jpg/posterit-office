@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ activeTab: 'overview' }">

    <!-- Profile Header Card -->
    <div class="p-6 sm:p-8 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-xs flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
        <div class="flex items-center gap-5">
            <img src="{{ $employee->photo_url }}" class="w-20 h-20 rounded-2xl object-cover ring-4 ring-indigo-500/20 shadow-md">
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-extrabold font-display text-slate-900 dark:text-white">{{ $employee->name }}</h1>
                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase
                        {{ $employee->employment_status === 'active' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' : '' }}
                        {{ $employee->employment_status === 'inactive' ? 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300' : '' }}
                        {{ $employee->employment_status === 'resigned' ? 'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300' : '' }}">
                        {{ $employee->employment_status }}
                    </span>
                </div>
                <div class="text-xs text-slate-500 mt-1 flex flex-wrap items-center gap-3">
                    <span class="font-mono font-bold text-indigo-600 dark:text-indigo-400">{{ $employee->employee_code }}</span> •
                    <span>{{ $employee->designation }}</span> •
                    <span>{{ $employee->department->name ?? 'No Dept' }}</span>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('employees.edit', $employee) }}" class="px-4 py-2.5 bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-100 font-bold text-xs rounded-xl transition flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                <span>Edit Profile</span>
            </a>
            <a href="{{ route('employees.index') }}" class="px-4 py-2.5 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-semibold text-xs rounded-xl hover:bg-slate-200 transition">
                ← All Employees
            </a>
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-xs">
            <span class="text-[11px] font-bold text-slate-400 uppercase">Monthly Output</span>
            <div class="text-2xl font-extrabold text-indigo-600 dark:text-indigo-400 mt-1 font-display">{{ $monthlyWorks }}</div>
            <div class="text-[11px] text-slate-500 mt-0.5">{{ now()->format('F Y') }} tasks</div>
        </div>

        <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-xs">
            <span class="text-[11px] font-bold text-slate-400 uppercase">Total Lifetime Works</span>
            <div class="text-2xl font-extrabold text-slate-900 dark:text-white mt-1 font-display">{{ $totalWorks }}</div>
            <div class="text-[11px] text-slate-500 mt-0.5">Tasks completed</div>
        </div>

        <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-xs">
            <span class="text-[11px] font-bold text-slate-400 uppercase">Attendance Rate</span>
            <div class="text-2xl font-extrabold text-emerald-600 dark:text-emerald-400 mt-1 font-display">{{ $attendanceRate }}%</div>
            <div class="text-[11px] text-slate-500 mt-0.5">This month</div>
        </div>

        <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-xs">
            <span class="text-[11px] font-bold text-slate-400 uppercase">Leave Balance</span>
            <div class="text-2xl font-extrabold text-purple-600 dark:text-purple-400 mt-1 font-display">{{ $employee->remaining_leaves }}</div>
            <div class="text-[11px] text-slate-500 mt-0.5">/ {{ $employee->leave_quota }} days remaining</div>
        </div>
    </div>

    <!-- Tab Navigation -->
    <div class="flex items-center gap-2 border-b border-slate-200 dark:border-slate-800 text-xs font-bold">
        <button @click="activeTab = 'overview'" 
                :class="activeTab === 'overview' ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400 border-b-2' : 'text-slate-500 hover:text-slate-700'"
                class="pb-3 px-4 transition cursor-pointer">
            Overview & Details
        </button>
        <button @click="activeTab = 'attendance'" 
                :class="activeTab === 'attendance' ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400 border-b-2' : 'text-slate-500 hover:text-slate-700'"
                class="pb-3 px-4 transition cursor-pointer">
            Attendance Log ({{ $attendances->count() }})
        </button>
        <button @click="activeTab = 'works'" 
                :class="activeTab === 'works' ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400 border-b-2' : 'text-slate-500 hover:text-slate-700'"
                class="pb-3 px-4 transition cursor-pointer">
            Work Entries History ({{ $workEntries->count() }})
        </button>
        <button @click="activeTab = 'leaves'" 
                :class="activeTab === 'leaves' ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400 border-b-2' : 'text-slate-500 hover:text-slate-700'"
                class="pb-3 px-4 transition cursor-pointer">
            Leave Requests ({{ $leaveRequests->count() }})
        </button>
    </div>

    <!-- Tab Contents -->
    
    <!-- Tab 1: Overview -->
    <div x-show="activeTab === 'overview'" class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-xs space-y-6 text-xs">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-3">
                <h4 class="font-bold text-sm text-slate-900 dark:text-white uppercase tracking-wider">Contact & Personal Details</h4>
                <div class="space-y-2 text-slate-600 dark:text-slate-400">
                    <div class="flex justify-between py-1.5 border-b border-slate-100 dark:border-slate-800">
                        <span class="text-slate-400">Full Name</span>
                        <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $employee->name }}</span>
                    </div>
                    <div class="flex justify-between py-1.5 border-b border-slate-100 dark:border-slate-800">
                        <span class="text-slate-400">Email Address</span>
                        <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $employee->email }}</span>
                    </div>
                    <div class="flex justify-between py-1.5 border-b border-slate-100 dark:border-slate-800">
                        <span class="text-slate-400">Mobile Number</span>
                        <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $employee->mobile_number ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between py-1.5 border-b border-slate-100 dark:border-slate-800">
                        <span class="text-slate-400">Joining Date</span>
                        <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $employee->joining_date ? $employee->joining_date->format('d M, Y') : '-' }}</span>
                    </div>
                </div>
            </div>

            <div class="space-y-3">
                <h4 class="font-bold text-sm text-slate-900 dark:text-white uppercase tracking-wider">Employment & Compensation</h4>
                <div class="space-y-2 text-slate-600 dark:text-slate-400">
                    <div class="flex justify-between py-1.5 border-b border-slate-100 dark:border-slate-800">
                        <span class="text-slate-400">Department</span>
                        <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $employee->department->name ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between py-1.5 border-b border-slate-100 dark:border-slate-800">
                        <span class="text-slate-400">Designation</span>
                        <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $employee->designation }}</span>
                    </div>
                    @if(auth()->user()->isAdmin())
                    <div class="flex justify-between py-1.5 border-b border-slate-100 dark:border-slate-800">
                        <span class="text-slate-400">Monthly Salary</span>
                        <span class="font-bold text-emerald-600 dark:text-emerald-400">{{ $employee->salary ? '₹' . number_format($employee->salary, 2) : '-' }}</span>
                    </div>
                    @endif
                    <div class="flex justify-between py-1.5 border-b border-slate-100 dark:border-slate-800">
                        <span class="text-slate-400">Leave Quota (Annual)</span>
                        <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $employee->leave_quota }} Days</span>
                    </div>
                </div>
            </div>

            <!-- Emergency & Bank Details -->
            <div class="space-y-3">
                <h4 class="font-bold text-sm text-slate-900 dark:text-white uppercase tracking-wider">Emergency Contact</h4>
                <div class="space-y-2 text-slate-600 dark:text-slate-400">
                    <div class="flex justify-between py-1.5 border-b border-slate-100 dark:border-slate-800">
                        <span class="text-slate-400">Contact Person</span>
                        <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $employee->emergency_contact_name ?? 'Not Provided' }}</span>
                    </div>
                    <div class="flex justify-between py-1.5 border-b border-slate-100 dark:border-slate-800">
                        <span class="text-slate-400">Emergency Phone</span>
                        <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $employee->emergency_contact_phone ?? 'Not Provided' }}</span>
                    </div>
                </div>
            </div>

            <div class="space-y-3">
                <h4 class="font-bold text-sm text-slate-900 dark:text-white uppercase tracking-wider">Banking & Payout (Salary Deposit)</h4>
                <div class="space-y-2 text-slate-600 dark:text-slate-400">
                    <div class="flex justify-between py-1.5 border-b border-slate-100 dark:border-slate-800">
                        <span class="text-slate-400">Bank Name</span>
                        <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $employee->bank_name ?? 'Not Provided' }}</span>
                    </div>
                    <div class="flex justify-between py-1.5 border-b border-slate-100 dark:border-slate-800">
                        <span class="text-slate-400">Account Number</span>
                        <span class="font-mono font-semibold text-slate-800 dark:text-slate-200">{{ $employee->bank_account_no ?? 'Not Provided' }}</span>
                    </div>
                    <div class="flex justify-between py-1.5 border-b border-slate-100 dark:border-slate-800">
                        <span class="text-slate-400">IFSC Code</span>
                        <span class="font-mono font-semibold text-slate-800 dark:text-slate-200 uppercase">{{ $employee->bank_ifsc ?? 'Not Provided' }}</span>
                    </div>
                    <div class="flex justify-between py-1.5 border-b border-slate-100 dark:border-slate-800">
                        <span class="text-slate-400">UPI ID</span>
                        <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $employee->upi_id ?? 'Not Provided' }}</span>
                    </div>
                </div>
            </div>
        </div>

        @if($employee->notes)
        <div class="pt-4 border-t border-slate-100 dark:border-slate-800">
            <h4 class="font-bold text-slate-500 uppercase tracking-wider text-[11px] mb-1">Notes</h4>
            <p class="text-slate-600 dark:text-slate-400">{{ $employee->notes }}</p>
        </div>
        @endif
    </div>

    <!-- Tab 2: Attendance History -->
    <div x-show="activeTab === 'attendance'" class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-xs overflow-hidden" style="display: none;">
        <h3 class="font-bold text-base text-slate-900 dark:text-white mb-4">Past 30 Days Attendance</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-800 text-slate-400 text-[10px] uppercase">
                        <th class="py-2.5 px-3">Date</th>
                        <th class="py-2.5 px-3">Status</th>
                        <th class="py-2.5 px-3">Check In</th>
                        <th class="py-2.5 px-3">Check Out</th>
                        <th class="py-2.5 px-3">Remarks</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                    @forelse($attendances as $att)
                    <tr>
                        <td class="py-3 px-3 font-semibold">{{ $att->date->format('d M, Y') }} ({{ $att->date->format('D') }})</td>
                        <td class="py-3 px-3">
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase
                                {{ $att->status === 'present' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' : '' }}
                                {{ $att->status === 'wfh' ? 'bg-indigo-100 text-indigo-800 dark:bg-indigo-950 dark:text-indigo-300' : '' }}
                                {{ $att->status === 'half_day' ? 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300' : '' }}
                                {{ $att->status === 'leave' ? 'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300' : '' }}
                                {{ $att->status === 'absent' ? 'bg-slate-200 text-slate-800 dark:bg-slate-800 dark:text-slate-300' : '' }}">
                                {{ $att->status }}
                            </span>
                        </td>
                        <td class="py-3 px-3 text-slate-500 font-mono">{{ $att->check_in ? \Carbon\Carbon::parse($att->check_in)->format('h:i A') : '-' }}</td>
                        <td class="py-3 px-3 text-slate-500 font-mono">{{ $att->check_out ? \Carbon\Carbon::parse($att->check_out)->format('h:i A') : '-' }}</td>
                        <td class="py-3 px-3 text-slate-400">{{ $att->remarks ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-6 text-center text-slate-400">No attendance records found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Tab 3: Work Entries History -->
    <div x-show="activeTab === 'works'" class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-xs overflow-hidden" style="display: none;">
        <h3 class="font-bold text-base text-slate-900 dark:text-white mb-4">Past Work Logs</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-800 text-slate-400 text-[10px] uppercase">
                        <th class="py-2.5 px-3">Date</th>
                        <th class="py-2.5 px-3">Work Category</th>
                        <th class="py-2.5 px-3 text-center">Quantity</th>
                        <th class="py-2.5 px-3">Remarks</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                    @forelse($workEntries as $we)
                    <tr>
                        <td class="py-3 px-3 font-semibold">{{ $we->date->format('d M, Y') }}</td>
                        <td class="py-3 px-3">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-semibold" style="background-color: {{ $we->category->color }}20; color: {{ $we->category->color }}">
                                <span class="w-1.5 h-1.5 rounded-full" style="background-color: {{ $we->category->color }}"></span>
                                {{ $we->category->name }}
                            </span>
                        </td>
                        <td class="py-3 px-3 text-center font-extrabold text-sm text-slate-900 dark:text-white">{{ $we->quantity }}</td>
                        <td class="py-3 px-3 text-slate-500">{{ $we->remarks ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="py-6 text-center text-slate-400">No work entries recorded.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Tab 4: Leave Requests -->
    <div x-show="activeTab === 'leaves'" class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-xs overflow-hidden" style="display: none;">
        <h3 class="font-bold text-base text-slate-900 dark:text-white mb-4">Leave Records & Applications</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-800 text-slate-400 text-[10px] uppercase">
                        <th class="py-2.5 px-3">Leave Type</th>
                        <th class="py-2.5 px-3">Period</th>
                        <th class="py-2.5 px-3 text-center">Days</th>
                        <th class="py-2.5 px-3">Status</th>
                        <th class="py-2.5 px-3">Reason</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                    @forelse($leaveRequests as $lr)
                    <tr>
                        <td class="py-3 px-3 font-semibold">{{ $lr->leaveType->name }}</td>
                        <td class="py-3 px-3 text-slate-600 dark:text-slate-400">{{ $lr->start_date->format('d M') }} - {{ $lr->end_date->format('d M, Y') }}</td>
                        <td class="py-3 px-3 text-center font-bold">{{ $lr->total_days }}</td>
                        <td class="py-3 px-3">
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase
                                {{ $lr->status === 'approved' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' : '' }}
                                {{ $lr->status === 'pending' ? 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300' : '' }}
                                {{ $lr->status === 'rejected' ? 'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300' : '' }}">
                                {{ $lr->status }}
                            </span>
                        </td>
                        <td class="py-3 px-3 text-slate-500">{{ $lr->reason }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-6 text-center text-slate-400">No leave requests found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
