@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold font-display text-[#1d1d1f] dark:text-white tracking-tight">
                Performance & Rankings
            </h1>
            <p class="text-xs text-slate-400">
                Automated monthly evaluation, leaderboard medals, and output statistics.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <span class="px-3.5 py-1.5 bg-amber-500/10 text-amber-600 dark:text-amber-400 font-bold text-xs rounded-full border border-amber-500/20">
                🏆 {{ date('F Y', mktime(0, 0, 0, $month, 1, $year)) }}
            </span>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="p-4 rounded-3xl glass-panel flex flex-wrap items-center justify-between gap-4">
        <form method="GET" action="{{ route('performance.index') }}" class="flex flex-wrap items-center gap-3 text-xs">
            <div class="flex items-center gap-2">
                <label class="font-bold text-slate-400 uppercase text-[10px]">Month</label>
                <select name="month" onchange="this.form.submit()" class="px-3 py-1.5 bg-black/[0.03] dark:bg-white/[0.06] border border-black/[0.05] dark:border-white/[0.08] rounded-xl font-semibold">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                            {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                        </option>
                    @endfor
                </select>
            </div>

            <div class="flex items-center gap-2">
                <label class="font-bold text-slate-400 uppercase text-[10px]">Year</label>
                <select name="year" onchange="this.form.submit()" class="px-3 py-1.5 bg-black/[0.03] dark:bg-white/[0.06] border border-black/[0.05] dark:border-white/[0.08] rounded-xl font-semibold">
                    @for($y = now()->year - 2; $y <= now()->year + 1; $y++)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>

            <div class="flex items-center gap-2">
                <label class="font-bold text-slate-400 uppercase text-[10px]">Dept</label>
                <select name="department_id" onchange="this.form.submit()" class="px-3 py-1.5 bg-black/[0.03] dark:bg-white/[0.06] border border-black/[0.05] dark:border-white/[0.08] rounded-xl font-semibold">
                    <option value="">All Departments</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ $departmentId == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>
        </form>

        <!-- Rating Criteria Legend -->
        <div class="flex flex-wrap items-center gap-2 text-[10px] font-bold">
            <span class="px-2.5 py-1 rounded-full bg-[#30d158]/15 text-[#30d158]">🌟 Excellent</span>
            <span class="px-2.5 py-1 rounded-full bg-[#0071e3]/15 text-[#0071e3]">🟢 Good</span>
            <span class="px-2.5 py-1 rounded-full bg-[#ff9f0a]/15 text-[#ff9f0a]">🟡 Average</span>
            <span class="px-2.5 py-1 rounded-full bg-[#ff375f]/15 text-[#ff375f]">🔴 Needs Improvement</span>
        </div>
    </div>

    <!-- Performance Leaderboard Table -->
    <div class="rounded-3xl glass-panel overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-black/[0.05] dark:border-white/[0.07] text-slate-400 uppercase text-[10px]">
                        <th class="py-4 px-4 w-16 text-center">Rank</th>
                        <th class="py-4 px-4">Employee</th>
                        <th class="py-4 px-4">Department</th>
                        <th class="py-4 px-4 text-center">Completed Tasks</th>
                        <th class="py-4 px-4 text-center">Days Active</th>
                        <th class="py-4 px-4 text-center">Daily Average</th>
                        <th class="py-4 px-4 text-center">Attendance %</th>
                        <th class="py-4 px-4">Top Category</th>
                        <th class="py-4 px-4 text-center">Grade</th>
                        <th class="py-4 px-4 text-right"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/[0.03] dark:divide-white/[0.04]">
                    @forelse($performanceData as $data)
                    <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition {{ $data['rank'] === 1 ? 'bg-amber-500/[0.03]' : '' }}">
                        <td class="py-3.5 px-4 text-center">
                            @if($data['rank'] === 1)
                                <span class="inline-flex items-center justify-center w-7 h-7 rounded-2xl bg-gradient-to-tr from-amber-400 to-amber-600 text-white font-black text-xs shadow-sm">🥇</span>
                            @elseif($data['rank'] === 2)
                                <span class="inline-flex items-center justify-center w-7 h-7 rounded-2xl bg-gradient-to-tr from-slate-300 to-slate-400 text-slate-900 font-black text-xs shadow-2xs">🥈</span>
                            @elseif($data['rank'] === 3)
                                <span class="inline-flex items-center justify-center w-7 h-7 rounded-2xl bg-gradient-to-tr from-amber-600 to-amber-800 text-white font-black text-xs shadow-2xs">🥉</span>
                            @else
                                <span class="font-bold text-slate-400 text-xs">#{{ $data['rank'] }}</span>
                            @endif
                        </td>

                        <td class="py-3.5 px-4">
                            <div class="flex items-center gap-3">
                                <img src="{{ $data['employee']->photo_url }}" class="w-8 h-8 rounded-full object-cover">
                                <div>
                                    <div class="font-bold text-[#1d1d1f] dark:text-white">{{ $data['employee']->name }}</div>
                                    <div class="text-[10px] text-slate-400">{{ $data['employee']->employee_code }} • {{ $data['employee']->designation }}</div>
                                </div>
                            </div>
                        </td>

                        <td class="py-3.5 px-4 text-slate-500 whitespace-nowrap">
                            {{ $data['employee']->department->name ?? 'N/A' }}
                        </td>

                        <td class="py-3.5 px-4 text-center">
                            <span class="font-black text-base text-[#0071e3] font-display">
                                {{ $data['total_works'] }}
                            </span>
                        </td>

                        <td class="py-3.5 px-4 text-center font-medium text-slate-500">
                            {{ $data['days_worked'] }} days
                        </td>

                        <td class="py-3.5 px-4 text-center font-bold text-[#1d1d1f] dark:text-white">
                            {{ $data['avg_daily_work'] }} / day
                        </td>

                        <td class="py-3.5 px-4 text-center">
                            <div class="font-black text-xs {{ $data['attendance_rate'] >= 90 ? 'text-[#30d158]' : ($data['attendance_rate'] >= 80 ? 'text-[#0071e3]' : 'text-amber-500') }}">
                                {{ $data['attendance_rate'] }}%
                            </div>
                        </td>

                        <td class="py-3.5 px-4">
                            @if($data['top_category'] !== 'N/A')
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold" style="background-color: {{ $data['top_category_color'] }}15; color: {{ $data['top_category_color'] }}">
                                <span class="w-1.5 h-1.5 rounded-full" style="background-color: {{ $data['top_category_color'] }}"></span>
                                {{ $data['top_category'] }}
                            </span>
                            @else
                                <span class="text-slate-400 text-[11px]">-</span>
                            @endif
                        </td>

                        <td class="py-3.5 px-4 text-center">
                            <span class="px-3 py-1 rounded-full text-[10px] font-bold
                                {{ $data['rating'] === 'Excellent' ? 'bg-[#30d158]/15 text-[#30d158]' : '' }}
                                {{ $data['rating'] === 'Good' ? 'bg-[#0071e3]/15 text-[#0071e3]' : '' }}
                                {{ $data['rating'] === 'Average' ? 'bg-[#ff9f0a]/15 text-[#ff9f0a]' : '' }}
                                {{ $data['rating'] === 'Needs Improvement' ? 'bg-[#ff375f]/15 text-[#ff375f]' : '' }}">
                                {{ $data['rating'] }}
                            </span>
                        </td>

                        <td class="py-3.5 px-4 text-right">
                            <a href="{{ route('employees.show', $data['employee']) }}" class="px-3 py-1.5 rounded-xl glass-card text-xs font-semibold hover:bg-black/[0.04] dark:hover:bg-white/[0.08] transition">
                                Profile →
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="py-12 text-center text-slate-400">No performance records for this period.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
