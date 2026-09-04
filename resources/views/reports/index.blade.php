@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold font-display text-slate-900 dark:text-white tracking-tight">
                Reports & Analytics
            </h1>
            <p class="text-xs text-slate-500">
                Generate and export detailed work, attendance, employee, and category productivity reports.
            </p>
        </div>

        <!-- Export Buttons -->
        @php
            $exportParams = request()->all();
        @endphp
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('reports.export', array_merge(['format' => 'csv'], $exportParams)) }}" 
               class="px-3.5 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 rounded-xl text-xs font-bold shadow-xs hover:bg-slate-50 flex items-center gap-1.5 transition">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                <span>Export CSV / Excel</span>
            </a>

            <a href="{{ route('reports.export', array_merge(['format' => 'pdf'], $exportParams)) }}" 
               class="px-3.5 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 rounded-xl text-xs font-bold shadow-xs hover:bg-slate-50 flex items-center gap-1.5 transition">
                <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                <span>Download PDF</span>
            </a>

            <a href="{{ route('reports.export', array_merge(['format' => 'print'], $exportParams)) }}" target="_blank"
               class="px-3.5 py-2 bg-[#0071e3] hover:bg-[#0062c4] text-white rounded-xl text-xs font-bold shadow-xs flex items-center gap-1.5 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                <span>Print View</span>
            </a>
        </div>
    </div>

    <!-- Report Type Switcher Tabs -->
    <div class="flex items-center gap-2 border-b border-slate-200 dark:border-slate-800 text-xs font-bold">
        <a href="{{ route('reports.index', array_merge($exportParams, ['type' => 'daily_work'])) }}" 
           class="pb-3 px-4 transition {{ $reportType === 'daily_work' ? 'border-[#0071e3] text-[#0071e3] border-b-2' : 'text-slate-500 hover:text-slate-700' }}">
            Daily Work Log Report
        </a>
        <a href="{{ route('reports.index', array_merge($exportParams, ['type' => 'attendance'])) }}" 
           class="pb-3 px-4 transition {{ $reportType === 'attendance' ? 'border-[#0071e3] text-[#0071e3] border-b-2' : 'text-slate-500 hover:text-slate-700' }}">
            Attendance Breakdown
        </a>
        <a href="{{ route('reports.index', array_merge($exportParams, ['type' => 'employee_summary'])) }}" 
           class="pb-3 px-4 transition {{ $reportType === 'employee_summary' ? 'border-[#0071e3] text-[#0071e3] border-b-2' : 'text-slate-500 hover:text-slate-700' }}">
            Employee Performance Summary
        </a>
        <a href="{{ route('reports.index', array_merge($exportParams, ['type' => 'category_summary'])) }}" 
           class="pb-3 px-4 transition {{ $reportType === 'category_summary' ? 'border-[#0071e3] text-[#0071e3] border-b-2' : 'text-slate-500 hover:text-slate-700' }}">
            Category Volume Summary
        </a>
    </div>

    <!-- Filter Toolbar -->
    <div class="p-5 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-xs space-y-4 text-xs">
        <form method="GET" action="{{ route('reports.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <input type="hidden" name="type" value="{{ $reportType }}">

            <div>
                <label class="block font-bold text-slate-500 uppercase mb-1">Preset Period</label>
                <select name="period" onchange="this.form.submit()" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-semibold">
                    <option value="daily" {{ $period === 'daily' ? 'selected' : '' }}>Today</option>
                    <option value="weekly" {{ $period === 'weekly' ? 'selected' : '' }}>This Week</option>
                    <option value="monthly" {{ $period === 'monthly' ? 'selected' : '' }}>This Month</option>
                    <option value="yearly" {{ $period === 'yearly' ? 'selected' : '' }}>This Year</option>
                    <option value="custom" {{ $period === 'custom' ? 'selected' : '' }}>Custom Date Range</option>
                </select>
            </div>

            <div>
                <label class="block font-bold text-slate-500 uppercase mb-1">Start Date</label>
                <input type="date" name="start_date" value="{{ $startDate }}" 
                       class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-medium">
            </div>

            <div>
                <label class="block font-bold text-slate-500 uppercase mb-1">End Date</label>
                <input type="date" name="end_date" value="{{ $endDate }}" 
                       class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-medium">
            </div>

            <div>
                <label class="block font-bold text-slate-500 uppercase mb-1">Department</label>
                <select name="department_id" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-medium">
                    <option value="">All Departments</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ $departmentId == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>

            @if($reportType === 'daily_work' || $reportType === 'attendance')
            <div>
                <label class="block font-bold text-slate-500 uppercase mb-1">Employee</label>
                <select name="employee_id" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-medium">
                    <option value="">All Employees</option>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}" {{ $employeeId == $emp->id ? 'selected' : '' }}>{{ $emp->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            @if($reportType === 'daily_work')
            <div>
                <label class="block font-bold text-slate-500 uppercase mb-1">Work Category</label>
                <select name="category_id" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-medium">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ $categoryId == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            @if($reportType === 'attendance')
            <div>
                <label class="block font-bold text-slate-500 uppercase mb-1">Attendance Status</label>
                <select name="attendance_status" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-medium">
                    <option value="">All Statuses</option>
                    <option value="present" {{ $attendanceStatus === 'present' ? 'selected' : '' }}>Present</option>
                    <option value="wfh" {{ $attendanceStatus === 'wfh' ? 'selected' : '' }}>WFH</option>
                    <option value="half_day" {{ $attendanceStatus === 'half_day' ? 'selected' : '' }}>Half Day</option>
                    <option value="leave" {{ $attendanceStatus === 'leave' ? 'selected' : '' }}>Leave</option>
                    <option value="absent" {{ $attendanceStatus === 'absent' ? 'selected' : '' }}>Absent</option>
                </select>
            </div>
            @endif

            <div class="flex items-end gap-2">
                <button type="submit" class="w-full py-2.5 px-4 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-xl transition cursor-pointer">
                    Apply
                </button>
            </div>
        </form>
    </div>

    <!-- Render Data Based on Active Report Type -->
    <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-xs overflow-hidden">
        
        <!-- 1. Daily Work Entries Report -->
        @if($reportType === 'daily_work')
        <div class="p-4 bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between text-xs">
            <span class="font-bold text-slate-700 dark:text-slate-300">Period: {{ $startDate }} to {{ $endDate }}</span>
            <span class="font-extrabold text-indigo-600 dark:text-indigo-400">Total Quantity Completed: {{ $data['total_quantity'] }} ({{ $data['total_entries'] }} entries)</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-800 text-slate-400 text-[10px] uppercase">
                        <th class="py-3 px-4">Date</th>
                        <th class="py-3 px-4">Employee</th>
                        <th class="py-3 px-4">Department</th>
                        <th class="py-3 px-4">Category</th>
                        <th class="py-3 px-4 text-center">Quantity</th>
                        <th class="py-3 px-4">Remarks</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                    @forelse($data['records'] as $r)
                    <tr>
                        <td class="py-3 px-4 font-semibold text-slate-600 dark:text-slate-400 whitespace-nowrap">{{ $r->date->format('d M, Y') }}</td>
                        <td class="py-3 px-4 font-bold text-slate-900 dark:text-white">{{ $r->employee->name }} ({{ $r->employee->employee_code }})</td>
                        <td class="py-3 px-4 text-slate-500">{{ $r->employee->department->name ?? 'N/A' }}</td>
                        <td class="py-3 px-4">
                            <span class="px-2 py-0.5 rounded-md font-semibold text-[11px]" style="background-color: {{ $r->category->color }}20; color: {{ $r->category->color }}">
                                {{ $r->category->name }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-center font-extrabold text-sm text-slate-900 dark:text-white">{{ $r->quantity }}</td>
                        <td class="py-3 px-4 text-slate-500">{{ $r->remarks ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-8 text-center text-slate-400">No records found for this period.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- 2. Attendance Report -->
        @elseif($reportType === 'attendance')
        <div class="p-4 bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800 flex flex-wrap items-center justify-between gap-2 text-xs">
            <span class="font-bold text-slate-700 dark:text-slate-300">Period: {{ $startDate }} to {{ $endDate }}</span>
            <div class="flex items-center gap-3 font-semibold text-[11px]">
                <span class="text-emerald-600">{{ $data['present_count'] }} Present</span> •
                <span class="text-indigo-600">{{ $data['wfh_count'] }} WFH</span> •
                <span class="text-amber-600">{{ $data['half_day_count'] }} Half Day</span> •
                <span class="text-rose-600">{{ $data['leave_count'] }} Leaves</span> •
                <span class="text-slate-500">{{ $data['absent_count'] }} Absents</span>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-800 text-slate-400 text-[10px] uppercase">
                        <th class="py-3 px-4">Date</th>
                        <th class="py-3 px-4">Employee</th>
                        <th class="py-3 px-4">Department</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Check In</th>
                        <th class="py-3 px-4">Check Out</th>
                        <th class="py-3 px-4">Remarks</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                    @forelse($data['records'] as $r)
                    <tr>
                        <td class="py-3 px-4 font-semibold whitespace-nowrap">{{ $r->date->format('d M, Y') }} ({{ $r->date->format('D') }})</td>
                        <td class="py-3 px-4 font-bold text-slate-900 dark:text-white">{{ $r->employee->name }} ({{ $r->employee->employee_code }})</td>
                        <td class="py-3 px-4 text-slate-500">{{ $r->employee->department->name ?? 'N/A' }}</td>
                        <td class="py-3 px-4">
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase
                                {{ $r->status === 'present' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' : '' }}
                                {{ $r->status === 'wfh' ? 'bg-indigo-100 text-indigo-800 dark:bg-indigo-950 dark:text-indigo-300' : '' }}
                                {{ $r->status === 'half_day' ? 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300' : '' }}
                                {{ $r->status === 'leave' ? 'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300' : '' }}
                                {{ $r->status === 'absent' ? 'bg-slate-200 text-slate-800 dark:bg-slate-800 dark:text-slate-300' : '' }}">
                                {{ $r->status }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-slate-500 font-mono">{{ $r->check_in ? \Carbon\Carbon::parse($r->check_in)->format('h:i A') : '-' }}</td>
                        <td class="py-3 px-4 text-slate-500 font-mono">{{ $r->check_out ? \Carbon\Carbon::parse($r->check_out)->format('h:i A') : '-' }}</td>
                        <td class="py-3 px-4 text-slate-400">{{ $r->remarks ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-slate-400">No attendance records found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- 3. Employee Summary Report -->
        @elseif($reportType === 'employee_summary')
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-800 text-slate-400 text-[10px] uppercase">
                        <th class="py-3 px-4">Employee</th>
                        <th class="py-3 px-4">Department</th>
                        <th class="py-3 px-4 text-center">Total Works Completed</th>
                        <th class="py-3 px-4 text-center">Present Days</th>
                        <th class="py-3 px-4 text-center">WFH Days</th>
                        <th class="py-3 px-4 text-center">Leave Days</th>
                        <th class="py-3 px-4 text-center">Absent Days</th>
                        <th class="py-3 px-4 text-center">Attendance %</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                    @foreach($data['summaries'] as $s)
                    <tr>
                        <td class="py-3 px-4">
                            <div class="font-bold text-slate-900 dark:text-white">{{ $s['employee']->name }}</div>
                            <div class="text-[10px] text-slate-400">{{ $s['employee']->employee_code }} • {{ $s['employee']->designation }}</div>
                        </td>
                        <td class="py-3 px-4 text-slate-500">{{ $s['employee']->department->name ?? 'N/A' }}</td>
                        <td class="py-3 px-4 text-center font-extrabold text-sm text-indigo-600 dark:text-indigo-400">{{ $s['total_works'] }}</td>
                        <td class="py-3 px-4 text-center font-semibold text-emerald-600">{{ $s['present_days'] }}</td>
                        <td class="py-3 px-4 text-center font-semibold text-indigo-600">{{ $s['wfh_days'] }}</td>
                        <td class="py-3 px-4 text-center font-semibold text-amber-600">{{ $s['leave_days'] }}</td>
                        <td class="py-3 px-4 text-center font-semibold text-rose-600">{{ $s['absent_days'] }}</td>
                        <td class="py-3 px-4 text-center font-black {{ $s['attendance_rate'] >= 85 ? 'text-emerald-600' : 'text-amber-600' }}">{{ $s['attendance_rate'] }}%</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- 4. Category Volume Summary Report -->
        @elseif($reportType === 'category_summary')
        <div class="p-4 bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between text-xs">
            <span class="font-bold text-slate-700 dark:text-slate-300">Period: {{ $startDate }} to {{ $endDate }}</span>
            <span class="font-extrabold text-indigo-600 dark:text-indigo-400">Total Output Across Categories: {{ $data['total_volume'] }}</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-800 text-slate-400 text-[10px] uppercase">
                        <th class="py-3 px-4">Work Category</th>
                        <th class="py-3 px-4 text-center">Total Task Entries</th>
                        <th class="py-3 px-4 text-center">Total Quantity Completed</th>
                        <th class="py-3 px-4 text-center">Share of Volume</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                    @foreach($data['records'] as $c)
                    @php
                        $percentage = $data['total_volume'] > 0 ? round(($c->total_qty / $data['total_volume']) * 100, 1) : 0;
                    @endphp
                    <tr>
                        <td class="py-3 px-4 font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full" style="background-color: {{ $c->category->color ?? '#6366f1' }}"></span>
                            <span>{{ $c->category->name ?? 'N/A' }}</span>
                        </td>
                        <td class="py-3 px-4 text-center font-semibold text-slate-600 dark:text-slate-300">{{ $c->total_tasks }} logs</td>
                        <td class="py-3 px-4 text-center font-extrabold text-sm text-indigo-600 dark:text-indigo-400">{{ $c->total_qty }}</td>
                        <td class="py-3 px-4 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <span class="font-bold text-xs">{{ $percentage }}%</span>
                                <div class="w-20 bg-slate-200 dark:bg-slate-700 h-2 rounded-full overflow-hidden">
                                    <div class="bg-indigo-600 h-full rounded-full" style="width: {{ $percentage }}%"></div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

    </div>

</div>
@endsection
