@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold font-display text-slate-900 dark:text-white tracking-tight">
                Performance & Rankings
            </h1>
            <p class="text-xs text-slate-600 dark:text-slate-400 font-medium mt-0.5">
                Automated monthly evaluation, leaderboard medals, and output statistics.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <span class="px-3.5 py-1.5 bg-amber-500/10 text-amber-700 dark:text-amber-400 font-extrabold text-xs rounded-full border border-amber-500/20 shadow-xs">
                🏆 {{ date('F Y', mktime(0, 0, 0, $month, 1, $year)) }}
            </span>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="p-4 rounded-3xl glass-panel flex flex-wrap items-center justify-between gap-4 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
        <form method="GET" action="{{ route('performance.index') }}" class="flex flex-wrap items-center gap-3 text-xs">
            <div class="flex items-center gap-2">
                <label class="font-bold text-slate-700 dark:text-slate-300 uppercase text-[11px] tracking-wider">Month</label>
                <select name="month" onchange="this.form.submit()" class="px-3 py-1.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-bold text-slate-800 dark:text-slate-200">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                            {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                        </option>
                    @endfor
                </select>
            </div>

            <div class="flex items-center gap-2">
                <label class="font-bold text-slate-700 dark:text-slate-300 uppercase text-[11px] tracking-wider">Year</label>
                <select name="year" onchange="this.form.submit()" class="px-3 py-1.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-bold text-slate-800 dark:text-slate-200">
                    @for($y = now()->year - 2; $y <= now()->year + 1; $y++)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>

            <div class="flex items-center gap-2">
                <label class="font-bold text-slate-700 dark:text-slate-300 uppercase text-[11px] tracking-wider">Dept</label>
                <select name="department_id" onchange="this.form.submit()" class="px-3 py-1.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-bold text-slate-800 dark:text-slate-200">
                    <option value="">All Departments</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ $departmentId == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>
        </form>

        <!-- Rating Criteria Legend -->
        <div class="flex flex-wrap items-center gap-2 text-[10px] font-extrabold uppercase">
            <span class="px-2.5 py-1 rounded-full bg-emerald-100 dark:bg-emerald-950/40 text-emerald-800 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800">🌟 Excellent</span>
            <span class="px-2.5 py-1 rounded-full bg-blue-100 dark:bg-blue-950/40 text-blue-800 dark:text-blue-300 border border-blue-300 dark:border-blue-800">🟢 Good</span>
            <span class="px-2.5 py-1 rounded-full bg-amber-100 dark:bg-amber-950/40 text-amber-800 dark:text-amber-300 border border-amber-300 dark:border-amber-800">🟡 Average</span>
            <span class="px-2.5 py-1 rounded-full bg-rose-100 dark:bg-rose-950/40 text-rose-800 dark:text-rose-300 border border-rose-300 dark:border-rose-800">🔴 Needs Improvement</span>
        </div>
    </div>

    <!-- Performance Leaderboard Table -->
    <div class="rounded-3xl glass-panel overflow-hidden bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 uppercase text-[11px] font-bold tracking-wider">
                        <th class="py-3.5 px-4 w-16 text-center">Rank</th>
                        <th class="py-3.5 px-4">Employee</th>
                        <th class="py-3.5 px-4">Department</th>
                        <th class="py-3.5 px-4 text-center">Completed Tasks</th>
                        <th class="py-3.5 px-4 text-center">Days Active</th>
                        <th class="py-3.5 px-4 text-center">Daily Average</th>
                        <th class="py-3.5 px-4 text-center">Attendance %</th>
                        <th class="py-3.5 px-4">Top Category</th>
                        <th class="py-3.5 px-4 text-center">Grade</th>
                        <th class="py-3.5 px-4 text-right"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($performanceData as $data)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition {{ $data['rank'] === 1 ? 'bg-amber-500/[0.04]' : '' }}">
                        <td class="py-3.5 px-4 text-center">
                            @if($data['rank'] === 1)
                                <span class="inline-flex items-center justify-center w-7 h-7 rounded-2xl bg-gradient-to-tr from-amber-400 to-amber-600 text-white font-black text-xs shadow-sm">🥇</span>
                            @elseif($data['rank'] === 2)
                                <span class="inline-flex items-center justify-center w-7 h-7 rounded-2xl bg-gradient-to-tr from-slate-300 to-slate-400 text-slate-900 font-black text-xs shadow-2xs">🥈</span>
                            @elseif($data['rank'] === 3)
                                <span class="inline-flex items-center justify-center w-7 h-7 rounded-2xl bg-gradient-to-tr from-amber-600 to-amber-800 text-white font-black text-xs shadow-2xs">🥉</span>
                            @else
                                <span class="font-bold text-slate-600 dark:text-slate-400 text-xs">#{{ $data['rank'] }}</span>
                            @endif
                        </td>

                        <td class="py-3.5 px-4">
                            <div class="flex items-center gap-3">
                                <img src="{{ $data['employee']->photo_url }}" class="w-8 h-8 rounded-full object-cover ring-1 ring-black/10">
                                <div>
                                    <div class="font-bold text-slate-900 dark:text-white">{{ $data['employee']->name }}</div>
                                    <div class="text-[11px] text-slate-600 dark:text-slate-400 font-medium">{{ $data['employee']->employee_code }} • {{ $data['employee']->designation }}</div>
                                </div>
                            </div>
                        </td>

                        <td class="py-3.5 px-4 text-slate-700 dark:text-slate-300 font-medium whitespace-nowrap">
                            {{ $data['employee']->department->name ?? 'N/A' }}
                        </td>

                        <td class="py-3.5 px-4 text-center">
                            <span class="font-extrabold text-base text-[#0071e3] font-display">
                                {{ $data['total_works'] }}
                            </span>
                        </td>

                        <td class="py-3.5 px-4 text-center font-semibold text-slate-700 dark:text-slate-300">
                            {{ $data['days_worked'] }} days
                        </td>

                        <td class="py-3.5 px-4 text-center font-bold text-slate-900 dark:text-white">
                            {{ $data['avg_daily_work'] }} / day
                        </td>

                        <td class="py-3.5 px-4 text-center">
                            <div class="font-extrabold text-xs {{ $data['attendance_rate'] >= 90 ? 'text-emerald-700 dark:text-emerald-400' : ($data['attendance_rate'] >= 80 ? 'text-[#0071e3]' : 'text-amber-700 dark:text-amber-400') }}">
                                {{ $data['attendance_rate'] }}%
                            </div>
                        </td>

                        <td class="py-3.5 px-4">
                            @if($data['top_category'] !== 'N/A')
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold border" style="background-color: {{ $data['top_category_color'] }}20; color: {{ $data['top_category_color'] }}; border-color: {{ $data['top_category_color'] }}40;">
                                <span class="w-2 h-2 rounded-full" style="background-color: {{ $data['top_category_color'] }}"></span>
                                {{ $data['top_category'] }}
                            </span>
                            @else
                                <span class="text-slate-400 text-[11px] font-semibold">-</span>
                            @endif
                        </td>

                        <td class="py-3.5 px-4 text-center">
                            <span class="px-3 py-1 rounded-full text-[10px] font-extrabold uppercase border
                                {{ $data['rating'] === 'Excellent' ? 'bg-emerald-100 text-emerald-800 border-emerald-300 dark:bg-emerald-950/50 dark:text-emerald-300 dark:border-emerald-800' : '' }}
                                {{ $data['rating'] === 'Good' ? 'bg-blue-100 text-blue-800 border-blue-300 dark:bg-blue-950/50 dark:text-blue-300 dark:border-blue-800' : '' }}
                                {{ $data['rating'] === 'Average' ? 'bg-amber-100 text-amber-800 border-amber-300 dark:bg-amber-950/50 dark:text-amber-300 dark:border-amber-800' : '' }}
                                {{ $data['rating'] === 'Needs Improvement' ? 'bg-rose-100 text-rose-800 border-rose-300 dark:bg-rose-950/50 dark:text-rose-300 dark:border-rose-800' : '' }}">
                                {{ $data['rating'] }}
                            </span>
                        </td>

                        <td class="py-3.5 px-4 text-right">
                            <a href="{{ route('employees.show', $data['employee']) }}" class="px-3 py-1.5 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 text-xs font-bold transition">
                                Profile →
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="py-12 text-center text-slate-600 dark:text-slate-400 font-medium">No performance records for this period.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
