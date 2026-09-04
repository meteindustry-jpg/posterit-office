@extends('layouts.app')

@section('content')
@php
    $currentDateCarbon = Carbon\Carbon::parse($date);
    $prevDateStr = $currentDateCarbon->copy()->subDay()->format('Y-m-d');
    $nextDateStr = $currentDateCarbon->copy()->addDay()->format('Y-m-d');
    $isToday = $date === now()->format('Y-m-d');

    $initialPresent = $stats['present'];
    $initialWfh = $stats['wfh'];
    $initialLeave = $stats['leave'];
    $initialHalfDay = $stats['half_day'];
    $initialAbsent = $stats['absent'];
    $initialPending = $stats['pending'];
    $totalEmployees = count($employees);
@endphp

<div class="space-y-6" 
     x-data="{
         searchQuery: '',
         deptFilter: '{{ $departmentId ?? '' }}',
         liveStats: {
             present: {{ $initialPresent }},
             wfh: {{ $initialWfh }},
             leave: {{ $initialLeave }},
             half_day: {{ $initialHalfDay }},
             absent: {{ $initialAbsent }},
             pending: {{ $initialPending }},
             total: {{ $totalEmployees }}
         },
         updateLiveStats() {
             let p = 0, w = 0, l = 0, hd = 0, ab = 0;
             document.querySelectorAll('.attendance-status-select').forEach(el => {
                 if (el.value === 'present') p++;
                 else if (el.value === 'wfh') w++;
                 else if (el.value === 'half_day') hd++;
                 else if (el.value === 'leave') l++;
                 else if (el.value === 'absent') ab++;
             });
             this.liveStats.present = p;
             this.liveStats.wfh = w;
             this.liveStats.leave = l;
             this.liveStats.half_day = hd;
             this.liveStats.absent = ab;
             this.liveStats.pending = Math.max(0, this.liveStats.total - (p + w + l + hd + ab));
         },
         markAllPresent() {
             document.querySelectorAll('.attendance-row').forEach(row => {
                  if (row.style.display !== 'none') {
                      const select = row.querySelector('.attendance-status-select');
                      if (select) {
                          select.value = 'present';
                          select.dispatchEvent(new Event('change'));
                      }
                  }
              });
              this.updateLiveStats();
          },
          markAllWfh() {
              document.querySelectorAll('.attendance-row').forEach(row => {
                  if (row.style.display !== 'none') {
                      const select = row.querySelector('.attendance-status-select');
                      if (select) {
                          select.value = 'wfh';
                          select.dispatchEvent(new Event('change'));
                      }
                  }
              });
              this.updateLiveStats();
          },
          fillStandardTimes() {
              const officeStart = '{{ \App\Models\CompanySetting::get('office_timing_start', '09:30') }}';
              const officeEnd = '{{ \App\Models\CompanySetting::get('office_timing_end', '18:30') }}';
              document.querySelectorAll('.attendance-row').forEach(row => {
                  if (row.style.display !== 'none') {
                      const inInput = row.querySelector('input[name*=\'[check_in]\']');
                      const outInput = row.querySelector('input[name*=\'[check_out]\']');
                      if (inInput && (!inInput.value || inInput.value === '00:00')) inInput.value = officeStart;
                      if (outInput && (!outInput.value || outInput.value === '00:00')) outInput.value = officeEnd;
                  }
              });
          },
         filterRow(name, code, deptId) {
             const query = this.searchQuery.toLowerCase().trim();
             const matchesQuery = !query || name.toLowerCase().includes(query) || code.toLowerCase().includes(query);
             const matchesDept = !this.deptFilter || deptId == this.deptFilter;
             return matchesQuery && matchesDept;
         }
     }"
     x-init="updateLiveStats()">

    <!-- Header & Action Presets -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2.5">
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">
                    Daily Attendance Sheet
                </h1>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200">
                    {{ $currentDateCarbon->format('l, d M Y') }}
                </span>
            </div>
            <p class="text-xs text-slate-500 font-normal mt-1">
                Log, review, and manage daily employee attendance, shift hours, and remote work.
            </p>
        </div>

        <!-- Header Actions -->
        <div class="flex flex-wrap items-center gap-2.5">
            <a href="{{ route('attendance.monthlyGrid') }}" class="px-4 py-2.5 rounded-xl bg-white border border-slate-200 text-xs font-bold text-slate-700 hover:bg-slate-50 shadow-2xs transition flex items-center gap-1.5 active:scale-95">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <span>Monthly Matrix</span>
            </a>

            @if(auth()->user()->isManager())
            <!-- Mark All Present -->
            <button type="button" @click="markAllPresent()" 
                    class="px-4 py-2.5 rounded-xl bg-[#34c759] hover:bg-[#2fb350] text-white text-xs font-bold shadow-sm transition active:scale-95 flex items-center gap-1.5 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                <span>Mark All Present</span>
            </button>

            <!-- Mark All WFH -->
            <button type="button" @click="markAllWfh()" 
                    class="px-3.5 py-2.5 rounded-xl bg-[#5856d6] hover:bg-[#4d4ac7] text-white text-xs font-bold shadow-sm transition active:scale-95 flex items-center gap-1.5 cursor-pointer">
                <span>🏠 All WFH</span>
            </button>

            <!-- 09:30 - 18:30 Preset -->
            <button type="button" @click="fillStandardTimes()" 
                    class="px-3.5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition active:scale-95 cursor-pointer flex items-center gap-1.5">
                <span>⏱️ Set 09:30–18:30</span>
            </button>
            @endif
        </div>
    </div>

    <!-- Live Reactive KPI Metric Cards (Dribbble/Apple Arcade Style) -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        
        <!-- Present Today (Emerald / Mint) -->
        <div class="relative overflow-hidden p-5 rounded-2xl bg-gradient-to-br from-[#059669] via-[#10b981] to-[#34d399] text-white shadow-[0_8px_22px_rgba(16,185,129,0.25)] hover:scale-[1.02] transition-all duration-200 flex flex-col justify-between group">
            <svg class="absolute -right-3 -bottom-3 w-28 h-28 text-white/15 pointer-events-none transition-transform group-hover:scale-110 duration-300" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
            </svg>
            
            <div class="relative z-10">
                <span class="text-[11px] font-extrabold uppercase tracking-wider text-white/90">Present Today</span>
                <div class="w-6 h-0.5 bg-white/30 rounded-full mt-1"></div>
            </div>
            <div class="mt-4 relative z-10">
                <div class="text-3xl font-black text-white tracking-tight" x-text="liveStats.present + liveStats.wfh">
                    {{ $initialPresent + $initialWfh }}
                </div>
                <div class="text-[11px] text-white/85 font-medium mt-1">
                    <span x-text="liveStats.present">{{ $initialPresent }}</span> Office • <span x-text="liveStats.wfh">{{ $initialWfh }}</span> WFH
                </div>
            </div>
        </div>

        <!-- Leave & Half Day (Orange / Amber) -->
        <div class="relative overflow-hidden p-5 rounded-2xl bg-gradient-to-br from-[#f59e0b] via-[#f97316] to-[#fb923c] text-white shadow-[0_8px_22px_rgba(249,115,22,0.25)] hover:scale-[1.02] transition-all duration-200 flex flex-col justify-between group">
            <svg class="absolute -right-3 -bottom-3 w-28 h-28 text-white/15 pointer-events-none transition-transform group-hover:scale-110 duration-300" fill="currentColor" viewBox="0 0 24 24">
                <path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z"/>
            </svg>

            <div class="relative z-10">
                <span class="text-[11px] font-extrabold uppercase tracking-wider text-white/90">Leave & Half Day</span>
                <div class="w-6 h-0.5 bg-white/30 rounded-full mt-1"></div>
            </div>
            <div class="mt-4 relative z-10">
                <div class="text-3xl font-black text-white tracking-tight" x-text="liveStats.leave + liveStats.half_day">
                    {{ $initialLeave + $initialHalfDay }}
                </div>
                <div class="text-[11px] text-white/85 font-medium mt-1">
                    <span x-text="liveStats.leave">{{ $initialLeave }}</span> Leave • <span x-text="liveStats.half_day">{{ $initialHalfDay }}</span> Half Day
                </div>
            </div>
        </div>

        <!-- Pending Unmarked (Cyan / Ocean) -->
        <div class="relative overflow-hidden p-5 rounded-2xl bg-gradient-to-br from-[#0284c7] via-[#0ea5e9] to-[#38bdf8] text-white shadow-[0_8px_22px_rgba(14,165,233,0.25)] hover:scale-[1.02] transition-all duration-200 flex flex-col justify-between group">
            <svg class="absolute -right-3 -bottom-3 w-28 h-28 text-white/15 pointer-events-none transition-transform group-hover:scale-110 duration-300" fill="currentColor" viewBox="0 0 24 24">
                <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/>
            </svg>

            <div class="relative z-10">
                <span class="text-[11px] font-extrabold uppercase tracking-wider text-white/90">Pending Unmarked</span>
                <div class="w-6 h-0.5 bg-white/30 rounded-full mt-1"></div>
            </div>
            <div class="mt-4 relative z-10">
                <div class="text-3xl font-black text-white tracking-tight" x-text="liveStats.pending">
                    {{ $initialPending }}
                </div>
                <div class="text-[11px] text-white/85 font-medium mt-1">
                    of {{ $totalEmployees }} active employees
                </div>
            </div>
        </div>

        <!-- Recorded Absent (Rose / Coral) -->
        <div class="relative overflow-hidden p-5 rounded-2xl bg-gradient-to-br from-[#e11d48] via-[#f43f5e] to-[#fb7185] text-white shadow-[0_8px_22px_rgba(244,63,94,0.25)] hover:scale-[1.02] transition-all duration-200 flex flex-col justify-between group">
            <svg class="absolute -right-3 -bottom-3 w-28 h-28 text-white/15 pointer-events-none transition-transform group-hover:scale-110 duration-300" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 2C6.47 2 2 6.47 2 12s4.47 10 10 10 10-4.47 10-10S17.53 2 12 2zm5 13.59L15.59 17 12 13.41 8.41 17 7 15.59 10.59 12 7 8.41 8.41 7 12 10.59 15.59 7 17 8.41 13.41 12 17 15.59z"/>
            </svg>

            <div class="relative z-10">
                <span class="text-[11px] font-extrabold uppercase tracking-wider text-white/90">Absent</span>
                <div class="w-6 h-0.5 bg-white/30 rounded-full mt-1"></div>
            </div>
            <div class="mt-4 relative z-10">
                <div class="text-3xl font-black text-white tracking-tight" x-text="liveStats.absent">
                    {{ $initialAbsent }}
                </div>
                <div class="text-[11px] text-white/85 font-medium mt-1">Recorded absent today</div>
            </div>
        </div>

    </div>

    <!-- Date Stepper, Search & Filter Toolbar -->
    <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        
        <!-- Left: Date Stepper -->
        <div class="flex items-center gap-2">
            <a href="{{ route('attendance.index', ['date' => $prevDateStr, 'department_id' => $departmentId]) }}" 
               class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 rounded-xl text-xs font-bold text-slate-700 transition" 
               title="Previous Day">
                ←
            </a>

            <form method="GET" action="{{ route('attendance.index') }}" class="flex items-center gap-2">
                <input type="hidden" name="department_id" value="{{ $departmentId }}">
                <input type="date" name="date" value="{{ $date }}" onchange="this.form.submit()" 
                       class="px-3.5 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-800 focus:ring-2 focus:ring-[#0071e3]">
            </form>

            <a href="{{ route('attendance.index', ['date' => $nextDateStr, 'department_id' => $departmentId]) }}" 
               class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 rounded-xl text-xs font-bold text-slate-700 transition" 
               title="Next Day">
                →
            </a>

            @if(!$isToday)
            <a href="{{ route('attendance.index', ['department_id' => $departmentId]) }}" 
               class="px-3 py-1.5 bg-blue-50 text-[#0071e3] hover:bg-blue-100 rounded-xl text-xs font-bold transition">
                Today
            </a>
            @endif
        </div>

        <!-- Right: Real-time Live Search & Department Filter -->
        <div class="flex flex-wrap items-center gap-3">
            <!-- Search input -->
            <div class="relative min-w-[240px]">
                <input type="text" 
                       x-model="searchQuery" 
                       placeholder="Search employee or code..." 
                       class="w-full pl-9 pr-8 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-[#0071e3] placeholder-slate-400">
                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <button type="button" x-show="searchQuery" @click="searchQuery = ''" class="absolute right-2.5 top-2 text-slate-400 hover:text-slate-600 text-xs cursor-pointer">
                    ✕
                </button>
            </div>

            @if(auth()->user()->isManager())
            <!-- Department dropdown -->
            <select x-model="deptFilter" 
                    class="px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-700 focus:ring-2 focus:ring-[#0071e3]">
                <option value="">All Departments</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                @endforeach
            </select>
            @endif
        </div>
    </div>

    <!-- Daily Attendance Batch Form -->
    <form method="POST" action="{{ route('attendance.storeBatch') }}">
        @csrf
        <input type="hidden" name="date" value="{{ $date }}">

        <div class="rounded-2xl bg-white border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200 bg-slate-50/80 text-slate-500 uppercase text-[10px]">
                            <th class="py-3.5 px-4 font-bold w-12">#</th>
                            <th class="py-3.5 px-4 font-bold min-w-[220px]">Employee</th>
                            <th class="py-3.5 px-4 font-bold min-w-[120px]">Department</th>
                            <th class="py-3.5 px-4 font-bold min-w-[160px]">Status</th>
                            <th class="py-3.5 px-4 font-bold min-w-[110px]">Check In</th>
                            <th class="py-3.5 px-4 font-bold min-w-[110px]">Check Out</th>
                            <th class="py-3.5 px-4 font-bold min-w-[200px]">Remarks / Note</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($employees as $index => $emp)
                        @php
                            $att = $existingAttendances->get($emp->id);
                            $currentStatus = $att ? $att->status : 'pending';
                            $checkIn = $att && $att->check_in ? date('H:i', strtotime($att->check_in)) : '';
                            $checkOut = $att && $att->check_out ? date('H:i', strtotime($att->check_out)) : '';
                            $remarks = $att ? $att->remarks : '';
                        @endphp
                        <tr class="attendance-row hover:bg-slate-50/80 transition" 
                            x-show="filterRow('{{ addslashes($emp->name) }}', '{{ addslashes($emp->employee_code) }}', '{{ $emp->department_id }}')"
                            x-data="{ 
                                status: '{{ $currentStatus }}',
                                onStatusChange(newVal) {
                                    this.status = newVal;
                                    const row = $el;
                                    const inInput = row.querySelector('input[name*=\'[check_in]\']');
                                    const outInput = row.querySelector('input[name*=\'[check_out]\']');
                                    if ((newVal === 'present' || newVal === 'wfh') && inInput && !inInput.value) {
                                        const now = new Date();
                                        inInput.value = now.toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit', hour12: false });
                                    }
                                    if ((newVal === 'absent' || newVal === 'leave' || newVal === 'pending') && inInput) {
                                        inInput.value = '';
                                        if (outInput) outInput.value = '';
                                    }
                                    $nextTick(() => { updateLiveStats(); });
                                }
                            }">
                            
                            <td class="py-3 px-4 text-slate-400 font-bold">{{ $index + 1 }}</td>
                            
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-3">
                                    <input type="hidden" name="attendances[{{ $index }}][employee_id]" value="{{ $emp->id }}">
                                    <img src="{{ $emp->photo_url }}" class="w-8 h-8 rounded-full object-cover border border-slate-200 shrink-0">
                                    <div>
                                        <div class="font-bold text-slate-900 text-xs">{{ $emp->name }}</div>
                                        <div class="text-[10px] text-slate-500 font-normal">{{ $emp->employee_code }} • {{ $emp->designation }}</div>
                                    </div>
                                </div>
                            </td>

                            <td class="py-3 px-4 text-slate-600 font-medium whitespace-nowrap">
                                {{ $emp->department->name ?? 'Office' }}
                            </td>

                            <!-- Semantic Color-Coded Status Select -->
                            <td class="py-3 px-4 whitespace-nowrap">
                                <select name="attendances[{{ $index }}][status]" 
                                        x-model="status"
                                        @change="onStatusChange($event.target.value)"
                                        class="attendance-status-select px-3 py-1.5 rounded-xl font-bold text-xs border transition cursor-pointer"
                                        :class="{
                                            'bg-emerald-50 text-emerald-800 border-emerald-300': status === 'present',
                                            'bg-indigo-50 text-indigo-800 border-indigo-300': status === 'wfh',
                                            'bg-amber-50 text-amber-800 border-amber-300': status === 'half_day',
                                            'bg-rose-50 text-rose-800 border-rose-300': status === 'leave',
                                            'bg-slate-100 text-slate-700 border-slate-300': status === 'absent',
                                            'bg-amber-50/70 text-amber-800 border-amber-200': status === 'pending'
                                        }">
                                    <option value="pending">⏳ Not Clocked In (Pending)</option>
                                    <option value="present">🟢 Present (Office)</option>
                                    <option value="wfh">🔵 WFH (Remote)</option>
                                    <option value="half_day">🟡 Half Day</option>
                                    <option value="leave">🔴 On Leave</option>
                                    <option value="absent">⚪ Absent</option>
                                </select>
                            </td>

                            <!-- Check In -->
                            <td class="py-3 px-4">
                                <input type="time" name="attendances[{{ $index }}][check_in]" value="{{ $checkIn }}"
                                       :disabled="status === 'absent' || status === 'leave' || status === 'pending'"
                                       class="px-2.5 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-[#0071e3] disabled:opacity-30 disabled:bg-slate-100">
                            </td>

                            <!-- Check Out -->
                            <td class="py-3 px-4">
                                <input type="time" name="attendances[{{ $index }}][check_out]" value="{{ $checkOut }}"
                                       :disabled="status === 'absent' || status === 'leave' || status === 'pending'"
                                       class="px-2.5 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-[#0071e3] disabled:opacity-30 disabled:bg-slate-100">
                            </td>

                            <!-- Remarks -->
                            <td class="py-3 px-4">
                                <input type="text" name="attendances[{{ $index }}][remarks]" value="{{ $remarks }}" placeholder="Optional note / reason..."
                                       class="w-full px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-[#0071e3] placeholder-slate-400">
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400">No active employees found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Footer Save Bar -->
            <div class="p-4 border-t border-slate-200 bg-slate-50/70 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="text-xs font-medium text-slate-600">
                    Showing <strong class="text-slate-900">{{ count($employees) }}</strong> active employees for <strong>{{ $currentDateCarbon->format('d M Y') }}</strong>
                </div>

                <div class="flex items-center gap-3">
                    <button type="submit" 
                            class="px-6 py-2.5 bg-[#0071e3] hover:bg-[#0062c4] text-white font-bold text-xs rounded-xl shadow-sm transition active:scale-95 flex items-center gap-2 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Save Daily Attendance</span>
                    </button>
                </div>
            </div>
        </div>
    </form>

</div>
@endsection
