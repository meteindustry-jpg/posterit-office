@extends('layouts.app')

@section('content')
<div class="space-y-6">
    
    <!-- Page Header & Month Selector -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2.5">
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">
                    Monthly Attendance Matrix
                </h1>
                <span class="px-3 py-0.5 rounded-full text-xs font-extrabold bg-[#0071e3]/10 text-[#0071e3] border border-[#0071e3]/20">
                    {{ date('F Y', mktime(0, 0, 0, $month, 1, $year)) }}
                </span>
            </div>
            <p class="text-xs text-slate-500 font-normal mt-1">
                Comprehensive 31-day attendance grid, remote work tracking, and employee reliability rates.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <!-- CSV Export Button -->
            <a href="{{ route('attendance.exportMonthly', request()->all()) }}" 
               class="px-4 py-2 bg-[#34c759] hover:bg-[#2fb350] text-white rounded-xl text-xs font-bold shadow-sm transition flex items-center gap-1.5 active:scale-95 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Export CSV</span>
            </a>

            <!-- Print Button -->
            <button type="button" onclick="window.print()" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition flex items-center gap-1.5 active:scale-95 cursor-pointer">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Print</span>
            </button>

            <!-- Back to Daily Sheet -->
            <a href="{{ route('attendance.index') }}" class="px-4 py-2 bg-white border border-slate-200 rounded-xl text-xs font-bold text-slate-700 hover:bg-slate-50 transition shadow-2xs">
                ← Daily Sheet
            </a>
        </div>
    </div>

    <!-- Navigation, Filter Form & Status Legend -->
    <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        
        <!-- Left: Month Jump & Filters -->
        <form method="GET" action="{{ route('attendance.monthlyGrid') }}" class="flex flex-wrap items-center gap-2.5">
            <!-- Quick Stepper -->
            <a href="{{ route('attendance.monthlyGrid', ['month' => $prevDate->month, 'year' => $prevDate->year, 'department_id' => $departmentId]) }}" 
               class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 rounded-xl text-xs font-bold text-slate-700 transition" 
               title="Previous Month ({{ $prevDate->format('F') }})">
                ← {{ $prevDate->format('M') }}
            </a>

            <!-- Month Select -->
            <select name="month" onchange="this.form.submit()" class="px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-700 focus:ring-2 focus:ring-[#0071e3]">
                @for($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                        {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                    </option>
                @endfor
            </select>

            <!-- Year Select -->
            <select name="year" onchange="this.form.submit()" class="px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-700 focus:ring-2 focus:ring-[#0071e3]">
                @for($y = now()->year - 2; $y <= now()->year + 1; $y++)
                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>

            <!-- Next Month Stepper -->
            <a href="{{ route('attendance.monthlyGrid', ['month' => $nextDate->month, 'year' => $nextDate->year, 'department_id' => $departmentId]) }}" 
               class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 rounded-xl text-xs font-bold text-slate-700 transition" 
               title="Next Month ({{ $nextDate->format('F') }})">
                {{ $nextDate->format('M') }} →
            </a>

            <!-- Department Filter -->
            <div class="h-4 w-px bg-slate-200 mx-1 hidden sm:block"></div>
            <select name="department_id" onchange="this.form.submit()" class="px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-700 focus:ring-2 focus:ring-[#0071e3]">
                <option value="">All Departments</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept->id }}" {{ $departmentId == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                @endforeach
            </select>
        </form>

        <!-- Right: Apple-Style Status Legend -->
        <div class="flex flex-wrap items-center gap-1.5 text-[10px] font-bold text-slate-600">
            <span class="px-2 py-0.5 rounded-lg bg-emerald-50 text-emerald-800 border border-emerald-200">P = Present</span>
            <span class="px-2 py-0.5 rounded-lg bg-indigo-50 text-indigo-800 border border-indigo-200">W = WFH</span>
            <span class="px-2 py-0.5 rounded-lg bg-amber-50 text-amber-800 border border-amber-200">HD = Half Day</span>
            <span class="px-2 py-0.5 rounded-lg bg-rose-50 text-rose-800 border border-rose-200">L = Leave</span>
            <span class="px-2 py-0.5 rounded-lg bg-slate-100 text-slate-700 border border-slate-300">A = Absent</span>
            <span class="px-2 py-0.5 rounded-lg bg-purple-50 text-purple-700 border border-purple-200">🪔 Holiday</span>
        </div>
    </div>

    <!-- Matrix Table Container -->
    <div class="rounded-2xl bg-white border border-slate-200/80 shadow-xs overflow-hidden">
        <!-- Mobile Swipe Guide -->
        <div class="sm:hidden px-3 py-1.5 bg-blue-50/80 text-[11px] font-semibold text-[#0071e3] flex items-center justify-between border-b border-blue-100">
            <span class="flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                <span>Swipe horizontally to view days</span>
            </span>
            <span class="text-slate-500 font-normal">{{ $employees->count() }} Staff</span>
        </div>
        <div class="overflow-x-auto touch-scroll">
            <table class="w-full text-center text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200 text-[11px] text-slate-500">
                        <!-- Pinned Left Header -->
                        <th class="py-3 px-4 text-left font-bold sticky left-0 bg-slate-50 z-20 min-w-[200px] border-r border-slate-200 shadow-sm">
                            Employee
                        </th>

                        <!-- Days 1..31 Columns -->
                        @for($d = 1; $d <= $daysInMonth; $d++)
                            @php
                                $dayDate = Carbon\Carbon::createFromDate($year, $month, $d);
                                $isSunday = $dayDate->isSunday();
                                $holiday = $holidays->get($d);
                            @endphp
                            <th class="py-2 px-1 font-semibold min-w-[28px] border-r border-slate-100 {{ $isSunday ? 'bg-rose-50/80 text-rose-600 font-bold' : ($holiday ? 'bg-purple-50/80 text-purple-700 font-bold' : '') }}"
                                title="{{ $holiday ? $holiday->name : ($isSunday ? 'Sunday Weekend' : $dayDate->format('D, d M')) }}">
                                <div>{{ $d }}</div>
                                <div class="text-[9px] uppercase font-bold opacity-75">
                                    {{ substr($dayDate->format('D'), 0, 1) }}
                                </div>
                            </th>
                        @endfor

                        <!-- Summary Columns -->
                        <th class="py-3 px-2 font-bold bg-slate-50 text-slate-700 min-w-[50px] border-l border-slate-200">Pres.</th>
                        <th class="py-3 px-2 font-bold bg-slate-50 text-slate-700 min-w-[50px]">WFH</th>
                        <th class="py-3 px-2 font-bold bg-slate-50 text-slate-700 min-w-[50px]">Half</th>
                        <th class="py-3 px-2 font-bold bg-slate-50 text-slate-700 min-w-[50px]">Leave</th>
                        <th class="py-3 px-2 font-bold bg-slate-50 text-slate-700 min-w-[50px]">Abs.</th>
                        <th class="py-3 px-3 font-extrabold bg-indigo-50 text-indigo-700 min-w-[65px] border-l border-indigo-100">Rate %</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-[11px]">
                    @forelse($employees as $emp)
                    @php
                        $empAtts = ($attendances->get($emp->id) ?? collect())->keyBy(function($item) {
                            return (int) date('j', strtotime($item->date));
                        });
                        $presentCount = 0;
                        $wfhCount = 0;
                        $halfCount = 0;
                        $leaveCount = 0;
                        $absentCount = 0;
                        $totalRecorded = $empAtts->count();
                    @endphp
                    <tr class="hover:bg-slate-50/60 transition group">
                        <!-- Employee name pinned left -->
                        <td class="py-2.5 px-4 text-left font-semibold text-slate-900 sticky left-0 bg-white group-hover:bg-slate-50/90 z-10 border-r border-slate-200 shadow-sm whitespace-nowrap">
                            <div class="flex items-center gap-2.5">
                                <img src="{{ $emp->photo_url }}" class="w-7 h-7 rounded-full object-cover border border-slate-200 shrink-0">
                                <div>
                                    <div class="font-bold text-slate-900 text-xs">{{ $emp->name }}</div>
                                    <div class="text-[10px] text-slate-400 font-normal">{{ $emp->employee_code }} • {{ $emp->department->name ?? 'Office' }}</div>
                                </div>
                            </div>
                        </td>

                        <!-- Days Columns -->
                        @for($d = 1; $d <= $daysInMonth; $d++)
                            @php
                                $dayDate = Carbon\Carbon::createFromDate($year, $month, $d);
                                $isSunday = $dayDate->isSunday();
                                $holiday = $holidays->get($d);
                                $rec = $empAtts->get($d);
                                $st = $rec ? $rec->status : null;
                                
                                if ($st === 'present') $presentCount++;
                                elseif ($st === 'wfh') $wfhCount++;
                                elseif ($st === 'half_day') $halfCount++;
                                elseif ($st === 'leave') $leaveCount++;
                                elseif ($st === 'absent') $absentCount++;
                                
                                $tooltip = $rec ? "{$rec->date->format('d M')}: " . strtoupper($st) . ($rec->check_in ? " ({$rec->check_in} - {$rec->check_out})" : "") . ($rec->remarks ? " [{$rec->remarks}]" : "") : ($holiday ? $holiday->name : ($isSunday ? 'Sunday' : "Day {$d}"));
                            @endphp
                            <td class="p-1 border-r border-slate-50 {{ $isSunday ? 'bg-rose-50/40' : ($holiday ? 'bg-purple-50/40' : '') }}" title="{{ $tooltip }}">
                                @if($st === 'present')
                                    <span class="inline-flex items-center justify-center w-5 h-5 rounded-md bg-[#34c759] text-white font-bold text-[10px] shadow-2xs cursor-default">P</span>
                                @elseif($st === 'wfh')
                                    <span class="inline-flex items-center justify-center w-5 h-5 rounded-md bg-[#5856d6] text-white font-bold text-[10px] shadow-2xs cursor-default">W</span>
                                @elseif($st === 'half_day')
                                    <span class="inline-flex items-center justify-center w-5 h-5 rounded-md bg-[#ff9500] text-white font-bold text-[10px] shadow-2xs cursor-default">HD</span>
                                @elseif($st === 'leave')
                                    <span class="inline-flex items-center justify-center w-5 h-5 rounded-md bg-[#ff3b30] text-white font-bold text-[10px] shadow-2xs cursor-default">L</span>
                                @elseif($st === 'absent')
                                    <span class="inline-flex items-center justify-center w-5 h-5 rounded-md bg-slate-300 text-slate-700 font-bold text-[10px] cursor-default">A</span>
                                @elseif($holiday)
                                    <span class="inline-flex items-center justify-center w-5 h-5 rounded-md bg-purple-100 text-purple-700 font-bold text-[9px] cursor-default">H</span>
                                @else
                                    <span class="text-slate-300 text-xs select-none">•</span>
                                @endif
                            </td>
                        @endfor

                        <!-- Summary Column Values -->
                        @php
                            $effectivePresent = $presentCount + $wfhCount + ($halfCount * 0.5);
                            $rate = $totalRecorded > 0 ? round(($effectivePresent / $totalRecorded) * 100, 1) : 0;
                        @endphp
                        <td class="py-2 px-1.5 font-bold text-slate-800 border-l border-slate-200">
                            {{ $presentCount }}
                        </td>
                        <td class="py-2 px-1.5 font-semibold text-indigo-600">
                            {{ $wfhCount }}
                        </td>
                        <td class="py-2 px-1.5 font-semibold text-amber-600">
                            {{ $halfCount }}
                        </td>
                        <td class="py-2 px-1.5 font-semibold text-rose-600">
                            {{ $leaveCount }}
                        </td>
                        <td class="py-2 px-1.5 font-semibold text-slate-500">
                            {{ $absentCount }}
                        </td>
                        <td class="py-2 px-2 border-l border-indigo-100">
                            <span class="px-2 py-0.5 rounded-full font-extrabold text-[10px] {{ $rate >= 90 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : ($rate >= 75 ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-rose-50 text-rose-700 border border-rose-200') }}">
                                {{ $rate }}%
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ $daysInMonth + 7 }}" class="py-8 text-center text-slate-400">No active employees found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

