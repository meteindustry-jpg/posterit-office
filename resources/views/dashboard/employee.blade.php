@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-2 border-b border-slate-200/80">
        <div class="flex items-center gap-3.5">
            <img src="{{ $employee->photo_url }}" class="w-12 h-12 rounded-2xl object-cover border border-slate-200 shadow-xs">
            <div>
                <h1 class="text-xl font-bold text-slate-900 tracking-tight">
                    Hello, {{ $employee->name }} 👋
                </h1>
                <p class="text-xs text-slate-500 font-normal mt-0.5">
                    {{ $employee->designation }} • {{ $employee->department->name ?? 'Design' }} • <strong>{{ $employee->employee_code }}</strong>
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2.5">
            <a href="{{ route('todos.index') }}" 
               class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-white border border-slate-200 text-slate-700 text-xs font-semibold rounded-xl hover:bg-slate-50 shadow-2xs transition">
                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                <span>My Tasks</span>
            </a>

            <a href="{{ route('leaves.create') }}" 
               class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-[#0071e3] hover:bg-[#0062c4] text-white text-xs font-semibold rounded-xl shadow-xs transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span>Apply Leave</span>
            </a>
        </div>
    </div>

    <!-- Apple-Grade Self Attendance Console -->
    <div class="p-4 md:p-5 rounded-2xl bg-white border border-slate-200/80 shadow-[0_2px_12px_rgba(0,0,0,0.03)] transition-all"
         x-data="{
             timeZone: '{{ \App\Models\CompanySetting::get('timezone', config('app.timezone', 'Asia/Kolkata')) }}',
             timeStr: '',
             dateStr: '',
             checkInTs: {{ $todayCheckInTimestamp ?? 'null' }},
             isShiftActive: {{ ($myTodayAttendance && $myTodayAttendance->check_in && !$myTodayAttendance->check_out) ? 'true' : 'false' }},
             elapsedStr: '{{ $todayWorkedHours }}h {{ $todayWorkedMinutes }}m',
             updateClock() {
                 const now = new Date();
                 this.timeStr = now.toLocaleTimeString([], { timeZone: this.timeZone, hour: '2-digit', minute: '2-digit', second: '2-digit' });
                 this.dateStr = now.toLocaleDateString([], { timeZone: this.timeZone, weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' });
                 
                 if (this.isShiftActive && this.checkInTs) {
                     const diffSec = Math.max(0, Math.floor((now.getTime() - this.checkInTs) / 1000));
                     const h = Math.floor(diffSec / 3600);
                     const m = Math.floor((diffSec % 3600) / 60);
                     const s = diffSec % 60;
                     this.elapsedStr = `${h}h ${m}m ${s < 10 ? '0' : ''}${s}s`;
                 }
             }
         }"
         x-init="updateClock(); setInterval(() => updateClock(), 1000)">
        
        @if($myTodayAttendance && $myTodayAttendance->check_out)
            <!-- STATE 1: Shift Completed Today -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-center gap-3.5">
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 border border-emerald-100">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-sm font-bold text-slate-900 tracking-tight">Today's Shift Completed</h3>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                ✓ Done
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 font-medium mt-0.5">
                            Shift finished for today. All hours have been recorded accurately.
                        </p>
                    </div>
                </div>

                <!-- Telemetry Stats & Clock -->
                <div class="flex items-center flex-wrap gap-3 sm:gap-5">
                    <div class="flex items-center gap-4 px-3.5 py-2 bg-slate-50 rounded-xl border border-slate-200/70 text-xs font-medium">
                        <div>
                            <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider">Clock In</span>
                            <span class="font-mono font-bold text-slate-800">{{ $todayCheckInFormatted ?? $myTodayAttendance->check_in }}</span>
                        </div>
                        <div class="h-6 w-px bg-slate-200"></div>
                        <div>
                            <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider">Clock Out</span>
                            <span class="font-mono font-bold text-slate-800">{{ $todayCheckOutFormatted ?? $myTodayAttendance->check_out }}</span>
                        </div>
                        <div class="h-6 w-px bg-slate-200"></div>
                        <div>
                            <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider">Total Worked</span>
                            <span class="font-mono font-bold text-[#0071E3]">{{ $todayWorkedHours }}h {{ $todayWorkedMinutes }}m</span>
                        </div>
                    </div>

                    <div class="px-3 py-2 rounded-xl bg-slate-50 border border-slate-200/70 text-right hidden lg:block">
                        <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider">Current Time</span>
                        <span class="font-mono text-xs font-bold text-slate-700" x-text="timeStr"></span>
                    </div>
                </div>
            </div>

        @elseif($myTodayAttendance && $myTodayAttendance->check_in)
            <!-- STATE 2: Shift in Progress (Working) -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-center gap-3.5">
                    <div class="relative w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 border border-emerald-100">
                        <span class="absolute -top-0.5 -right-0.5 flex h-2.5 w-2.5">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                        </span>
                        <svg class="w-5 h-5 text-emerald-600 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-sm font-bold text-slate-900 tracking-tight">Shift in Progress</h3>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                ● Working in Office
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 font-medium mt-0.5">
                            Clocked in at <span class="font-mono font-bold text-slate-700">{{ $todayCheckInFormatted ?? $myTodayAttendance->check_in }}</span> • Office hours: {{ \Carbon\Carbon::parse(now()->format('Y-m-d').' '.($officeTimingStart ?? '09:30'))->format('h:i A') }} – {{ \Carbon\Carbon::parse(now()->format('Y-m-d').' '.($officeTimingEnd ?? '18:30'))->format('h:i A') }}
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <div class="px-3.5 py-2 bg-emerald-50/70 border border-emerald-200/80 rounded-xl flex items-center gap-2">
                        <span class="text-[10px] uppercase font-bold text-emerald-700 tracking-wider">Duration:</span>
                        <span class="font-mono text-sm font-bold text-emerald-800" x-text="elapsedStr">{{ $todayWorkedHours }}h {{ $todayWorkedMinutes }}m</span>
                    </div>

                    <form method="POST" action="{{ route('attendance.clockOut') }}" @submit="$refs.clientClockOutTime.value = new Date().toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit', hour12: false })">
                        @csrf
                        <input type="hidden" name="client_time" x-ref="clientClockOutTime" value="">
                        <button type="submit" class="px-5 py-2.5 bg-[#FF3B30] hover:bg-[#E0342B] text-white text-xs font-semibold rounded-xl shadow-xs transition-all active:scale-95 cursor-pointer flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                            <span>Clock-Out</span>
                        </button>
                    </form>
                </div>
            </div>

        @else
            <!-- STATE 3: Not Clocked In (Morning) -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-center gap-3.5">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 text-[#0071E3] flex items-center justify-center shrink-0 border border-blue-100">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-sm font-bold text-slate-900 tracking-tight">Daily Attendance</h3>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                                Not Clocked In
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 font-medium mt-0.5">
                            Good day! Office hours are {{ \Carbon\Carbon::parse(now()->format('Y-m-d').' '.($officeTimingStart ?? '09:30'))->format('h:i A') }} – {{ \Carbon\Carbon::parse(now()->format('Y-m-d').' '.($officeTimingEnd ?? '18:30'))->format('h:i A') }}. Tap to record your arrival.
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <div class="px-3 py-2 rounded-xl bg-slate-50 border border-slate-200/70 text-right hidden sm:block">
                        <span class="font-mono text-xs font-bold text-slate-700" x-text="timeStr"></span>
                        <span class="text-[10px] text-slate-400 block" x-text="dateStr"></span>
                    </div>

                    <form method="POST" action="{{ route('attendance.clockIn') }}" @submit="$refs.clientClockInTime.value = new Date().toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit', hour12: false })">
                        @csrf
                        <input type="hidden" name="client_time" x-ref="clientClockInTime" value="">
                        <button type="submit" class="px-5 py-2.5 bg-[#0071E3] hover:bg-[#0062C4] text-white text-xs font-semibold rounded-xl shadow-xs transition-all active:scale-95 cursor-pointer flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            <span>Mark Attendance (Clock In)</span>
                        </button>
                    </form>
                </div>
            </div>
        @endif
    </div>

    <!-- Total Time of Work & Productivity Statistics Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        
        <!-- Today's Working Time (Emerald / Mint Gradient) -->
        <div class="relative overflow-hidden p-5 rounded-2xl bg-gradient-to-br from-[#059669] via-[#10b981] to-[#34d399] text-white shadow-[0_8px_22px_rgba(16,185,129,0.25)] hover:scale-[1.02] transition-all duration-200 flex flex-col justify-between group">
            <svg class="absolute -right-3 -bottom-3 w-28 h-28 text-white/15 pointer-events-none transition-transform group-hover:scale-110 duration-300" fill="currentColor" viewBox="0 0 24 24">
                <path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z"/>
            </svg>
            
            <div class="relative z-10">
                <span class="text-[11px] font-extrabold uppercase tracking-wider text-white/90">Today's Work Time</span>
                <div class="w-6 h-0.5 bg-white/30 rounded-full mt-1"></div>
            </div>
            <div class="mt-4 relative z-10">
                <div class="text-3xl font-black text-white tracking-tight">
                    {{ $todayWorkedHours }}<span class="text-lg font-bold">h</span> {{ $todayWorkedMinutes }}<span class="text-lg font-bold">m</span>
                </div>
                <!-- Mini Benchmark Meter -->
                <div class="mt-2 w-full bg-black/20 rounded-full h-1.5 overflow-hidden">
                    <div class="bg-white h-1.5 rounded-full" style="width: {{ $todayProgressPct }}%"></div>
                </div>
                <div class="text-[10px] text-white/85 font-medium mt-1">
                    {{ $todayProgressPct }}% of standard 8.5h shift
                </div>
            </div>
        </div>

        <!-- Today's Deliverables (Cyan / Ocean Gradient) -->
        <div class="relative overflow-hidden p-5 rounded-2xl bg-gradient-to-br from-[#0089f8] via-[#05abfb] to-[#36cafd] text-white shadow-[0_8px_22px_rgba(5,171,251,0.25)] hover:scale-[1.02] transition-all duration-200 flex flex-col justify-between group">
            <svg class="absolute -right-3 -bottom-3 w-28 h-28 text-white/15 pointer-events-none transition-transform group-hover:scale-110 duration-300" fill="currentColor" viewBox="0 0 24 24">
                <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/>
            </svg>

            <div class="relative z-10">
                <span class="text-[11px] font-extrabold uppercase tracking-wider text-white/90">Today's Output</span>
                <div class="w-6 h-0.5 bg-white/30 rounded-full mt-1"></div>
            </div>
            <div class="mt-4 relative z-10">
                <div class="flex items-baseline gap-1.5">
                    <span class="text-3xl font-black text-white tracking-tight">{{ $myTodayWorks }}</span>
                    <span class="text-xs text-white/85 font-medium">deliverables</span>
                </div>
                <div class="text-[11px] text-white/85 font-medium mt-1">
                    {{ $myWeeklyWorks }} tasks this week
                </div>
            </div>
        </div>

        <!-- Monthly Work Output (Violet / Purple Gradient) -->
        <div class="relative overflow-hidden p-5 rounded-2xl bg-gradient-to-br from-[#6049f5] via-[#8659f7] to-[#887bf9] text-white shadow-[0_8px_22px_rgba(134,89,247,0.25)] hover:scale-[1.02] transition-all duration-200 flex flex-col justify-between group">
            <svg class="absolute -right-3 -bottom-3 w-28 h-28 text-white/15 pointer-events-none transition-transform group-hover:scale-110 duration-300" fill="currentColor" viewBox="0 0 24 24">
                <path d="M7 2v11h3v9l7-12h-4l4-8z"/>
            </svg>

            <div class="relative z-10">
                <span class="text-[11px] font-extrabold uppercase tracking-wider text-white/90">Monthly Logged</span>
                <div class="w-6 h-0.5 bg-white/30 rounded-full mt-1"></div>
            </div>
            <div class="mt-4 relative z-10">
                <div class="flex items-baseline gap-1.5">
                    <span class="text-3xl font-black text-white tracking-tight">{{ $myMonthlyWorks }}</span>
                    <span class="text-xs text-white/85 font-medium">tasks</span>
                </div>
                <div class="text-[11px] text-white/85 font-medium mt-1">
                    ~{{ $totalMonthHoursEst }} hrs worked in {{ now()->format('M') }}
                </div>
            </div>
        </div>

        <!-- Monthly Attendance % & Days (Orange / Amber Gradient) -->
        <div class="relative overflow-hidden p-5 rounded-2xl bg-gradient-to-br from-[#f87817] via-[#fa9519] to-[#fdb51b] text-white shadow-[0_8px_22px_rgba(250,149,25,0.25)] hover:scale-[1.02] transition-all duration-200 flex flex-col justify-between group">
            <svg class="absolute -right-3 -bottom-3 w-28 h-28 text-white/15 pointer-events-none transition-transform group-hover:scale-110 duration-300" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 14h-2v-2h2v2zm0-4h-2V7h2v5z"/>
            </svg>

            <div class="relative z-10">
                <span class="text-[11px] font-extrabold uppercase tracking-wider text-white/90">Attendance Rate</span>
                <div class="w-6 h-0.5 bg-white/30 rounded-full mt-1"></div>
            </div>
            <div class="mt-4 relative z-10">
                <div class="text-3xl font-black text-white tracking-tight">{{ $myAttendanceRate }}%</div>
                <div class="text-[11px] text-white/85 font-medium mt-1">
                    {{ $monthPresentDays }} present days • {{ $remainingLeaves }} leaves left
                </div>
            </div>
        </div>

    </div>

    <!-- Active Tasks & Deliverables Widget (Prominent on Dashboard) -->
    <div class="ui-panel p-5 space-y-3">
        <div class="flex items-center justify-between pb-2 border-b border-slate-100">
            <div class="flex items-center gap-2">
                <span class="w-6 h-6 rounded-lg bg-blue-50 text-[#0071e3] flex items-center justify-center font-bold text-xs">☑</span>
                <div>
                    <h3 class="font-bold text-sm text-slate-900">My Active Tasks</h3>
                    <p class="text-[11px] text-slate-500 font-normal">Assigned action items and deliverables to complete</p>
                </div>
            </div>
            <a href="{{ route('todos.index') }}" class="text-xs font-semibold text-[#0071e3] hover:underline">Open Task Board →</a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
            @forelse($myPendingTodos as $todo)
            <div class="p-3.5 rounded-xl bg-slate-50/80 border border-slate-200/80 flex flex-col justify-between hover:border-slate-300 hover:bg-white transition group">
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between gap-2">
                        <!-- 1-Click Checkbox -->
                        <div class="flex items-center gap-2">
                            <form method="POST" action="{{ route('todos.toggle', $todo) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" title="Mark as completed" class="w-4.5 h-4.5 rounded border border-slate-300 hover:border-[#0071e3] bg-white text-transparent hover:text-slate-300 flex items-center justify-center transition cursor-pointer">
                                    <svg class="w-3 h-3 fill-current" viewBox="0 0 20 20"><path d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/></svg>
                                </button>
                            </form>
                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-white text-slate-700 border border-slate-200">
                                {{ $todo->category }}
                            </span>
                        </div>

                        @if($todo->priority === 'high')
                            <span class="w-2 h-2 rounded-full bg-rose-500" title="High Priority"></span>
                        @elseif($todo->priority === 'medium')
                            <span class="w-2 h-2 rounded-full bg-amber-400" title="Medium Priority"></span>
                        @endif
                    </div>

                    <div class="font-semibold text-xs text-slate-900 leading-snug">
                        {{ $todo->title }}
                    </div>

                    @if($todo->description)
                    <p class="text-[11px] text-slate-500 font-normal line-clamp-2">
                        {{ $todo->description }}
                    </p>
                    @endif
                </div>

                <div class="mt-2.5 pt-2 border-t border-slate-200/60 flex items-center justify-between text-[10px] text-slate-400">
                    @if($todo->due_date)
                    <span class="flex items-center gap-1 {{ $todo->isOverdue() ? 'text-rose-600 font-bold' : ($todo->due_date->isToday() ? 'text-amber-700 font-semibold' : 'text-slate-500') }}">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        <span>{{ $todo->due_date->isToday() ? 'Due Today' : $todo->due_date->format('d M') }}</span>
                    </span>
                    @else
                    <span>No due date</span>
                    @endif

                    <span class="capitalize font-medium {{ $todo->status === 'in_progress' ? 'text-blue-600' : ($todo->status === 'in_review' ? 'text-purple-600' : 'text-slate-500') }}">
                        {{ str_replace('_', ' ', $todo->status) }}
                    </span>
                </div>
            </div>
            @empty
            <div class="col-span-full py-8 text-center text-xs text-slate-400">
                All caught up! No active tasks assigned right now.
            </div>
            @endforelse
        </div>
    </div>

    <!-- Tables & Holiday Widgets -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- My Recent Work History -->
        <div class="lg:col-span-2 p-5 ui-panel">
            <div class="flex items-center justify-between mb-4 pb-2 border-b border-slate-100">
                <h3 class="font-bold text-sm text-slate-900">My Recent Work Logs</h3>
                <span class="text-[11px] text-slate-400">Last 10 entries</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-slate-200 text-slate-400 text-[10px] uppercase bg-slate-50/50">
                            <th class="py-2.5 px-3 font-semibold">Date</th>
                            <th class="py-2.5 px-3 font-semibold">Category</th>
                            <th class="py-2.5 px-3 font-semibold text-center">Quantity</th>
                            <th class="py-2.5 px-3 font-semibold">Remarks</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($recentWorks as $work)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="py-2.5 px-3 text-slate-500 whitespace-nowrap">{{ $work->date->format('d M, Y') }}</td>
                            <td class="py-2.5 px-3">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-medium" style="background-color: {{ $work->category->color }}15; color: {{ $work->category->color }};">
                                    {{ $work->category->name }}
                                </span>
                            </td>
                            <td class="py-2.5 px-3 text-center font-bold text-slate-900">{{ $work->quantity }}</td>
                            <td class="py-2.5 px-3 text-slate-500 max-w-xs truncate">{{ $work->remarks ?? '-' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="py-6 text-center text-slate-400">No work logged yet.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Sidebar Widgets: Leaves & Holidays -->
        <div class="space-y-6">
            
            <!-- My Leaves -->
            <div class="p-5 ui-panel">
                <div class="flex items-center justify-between mb-3.5 pb-2 border-b border-slate-100">
                    <h3 class="font-bold text-sm text-slate-900">My Leave Requests</h3>
                    <a href="{{ route('leaves.create') }}" class="text-xs font-semibold text-[#0071e3] hover:underline">+ Apply</a>
                </div>

                <div class="space-y-2">
                    @forelse($myLeaves as $l)
                    <div class="p-2.5 rounded-xl bg-slate-50/70 border border-slate-200/60 space-y-1 text-xs">
                        <div class="flex items-center justify-between">
                            <span class="font-semibold text-slate-800">{{ $l->leaveType->name }}</span>
                            <span class="px-1.5 py-0.2 rounded text-[10px] font-bold uppercase
                                {{ $l->status === 'approved' ? 'bg-emerald-100 text-emerald-800' : '' }}
                                {{ $l->status === 'pending' ? 'bg-amber-100 text-amber-800' : '' }}
                                {{ $l->status === 'rejected' ? 'bg-rose-100 text-rose-800' : '' }}">
                                {{ $l->status }}
                            </span>
                        </div>
                        <div class="text-[11px] text-slate-500">
                            {{ $l->start_date->format('d M') }} - {{ $l->end_date->format('d M') }} ({{ $l->total_days }}d)
                        </div>
                    </div>
                    @empty
                    <div class="py-6 text-center text-xs text-slate-400">No leave requests.</div>
                    @endforelse
                </div>
            </div>

            <!-- Holidays Calendar -->
            <div class="p-5 ui-panel">
                <div class="flex items-center justify-between mb-3.5 pb-2 border-b border-slate-100">
                    <h3 class="font-bold text-sm text-slate-900">Upcoming Holidays</h3>
                </div>

                <div class="space-y-2">
                    @forelse($upcomingHolidays as $h)
                    <div class="p-2.5 rounded-xl bg-slate-50/70 border border-slate-200/60 flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-blue-50 text-[#0071e3] flex items-center justify-center font-bold text-xs shrink-0 border border-blue-100">
                            {{ $h->date->format('d') }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="font-semibold text-xs text-slate-900 truncate">{{ $h->name }}</div>
                            <div class="text-[11px] text-slate-500">{{ $h->date->format('l, d M') }}</div>
                        </div>
                    </div>
                    @empty
                    <div class="py-6 text-center text-xs text-slate-400">No upcoming holidays.</div>
                    @endforelse
                </div>
            </div>

        </div>

    </div>

</div>
@endsection
