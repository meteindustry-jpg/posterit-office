@extends('layouts.app')

@section('content')
<div class="max-w-xl mx-auto space-y-5"
     x-data="{
         startDate: '{{ old('start_date', now()->format('Y-m-d')) }}',
         endDate: '{{ old('end_date', now()->format('Y-m-d')) }}',
         quota: {{ $currentEmployee ? (int)$currentEmployee->remaining_leaves : 18 }},
         setPreset(type) {
             const today = new Date();
             const fmt = (d) => d.toISOString().split('T')[0];
             
             if (type === 'today') {
                 this.startDate = fmt(today);
                 this.endDate = fmt(today);
             } else if (type === 'tomorrow') {
                 const tmrw = new Date(today);
                 tmrw.setDate(today.getDate() + 1);
                 this.startDate = fmt(tmrw);
                 this.endDate = fmt(tmrw);
             } else if (type === 'two_days') {
                 this.startDate = fmt(today);
                 const d2 = new Date(today);
                 d2.setDate(today.getDate() + 1);
                 this.endDate = fmt(d2);
             }
         },
         get totalDays() {
             if (!this.startDate || !this.endDate) return 1;
             const s = new Date(this.startDate);
             const e = new Date(this.endDate);
             if (isNaN(s) || isNaN(e) || e < s) return 1;
             const diffTime = Math.abs(e - s);
             return Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
         },
         get remainingAfter() {
             return Math.max(0, this.quota - this.totalDays);
         }
     }">

    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-400 mb-0.5">
                <span>Leave Center</span>
                <span>/</span>
                <span class="text-slate-700 dark:text-slate-300">New Request</span>
            </div>
            <h1 class="text-xl font-bold tracking-tight text-slate-900 dark:text-white font-display">Apply for Leave</h1>
            <p class="text-xs text-slate-500 mt-0.5">Submit planned time-off for manager review and attendance auto-sync.</p>
        </div>
        <a href="{{ route('leaves.index') }}" 
           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-50 transition shadow-2xs">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            <span>Back</span>
        </a>
    </div>

    <!-- Unified Apple-Grade Leave Card -->
    <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-xs overflow-hidden">
        
        <!-- Integrated Employee Quota Header -->
        @if($currentEmployee)
        <div class="p-5 bg-gradient-to-r from-blue-50/70 via-indigo-50/40 to-slate-50 dark:from-slate-800/60 dark:to-slate-800/30 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <div class="flex items-center gap-3.5">
                @if($currentEmployee->photo && file_exists(public_path('storage/' . $currentEmployee->photo)))
                    <img src="{{ asset('storage/' . $currentEmployee->photo) }}" class="w-10 h-10 rounded-xl object-cover ring-1 ring-white shadow-2xs">
                @else
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-[#0071E3] to-[#5856D6] text-white flex items-center justify-center font-bold text-xs shadow-2xs">
                        {{ strtoupper(substr($currentEmployee->name, 0, 2)) }}
                    </div>
                @endif
                <div>
                    <h3 class="text-xs font-bold text-slate-900 dark:text-white">{{ $currentEmployee->name }}</h3>
                    <p class="text-[11px] text-slate-500 font-normal">
                        Annual Quota: {{ $currentEmployee->leave_quota ?? 18 }}d &bull; Used: {{ $currentEmployee->used_leaves }}d
                    </p>
                </div>
            </div>
            <div class="text-right">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-white dark:bg-slate-800 text-[#0071E3] border border-blue-200/70 dark:border-blue-900/60 rounded-full font-bold text-xs shadow-2xs">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#0071E3] animate-pulse"></span>
                    {{ $currentEmployee->remaining_leaves }} Days Available
                </span>
            </div>
        </div>
        @endif

        <form method="POST" action="{{ route('leaves.store') }}" class="text-xs">
            @csrf

            <div class="p-6 sm:p-7 space-y-5">
                
                @if(!auth()->user()->isEmployee())
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Select Employee *</label>
                    <select name="employee_id" required 
                            class="w-full px-3.5 py-2.5 bg-slate-50/70 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 rounded-xl font-semibold text-xs text-slate-900 dark:text-white focus:bg-white focus:border-[#0071E3] focus:ring-2 focus:ring-[#0071E3]/20 transition outline-none">
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->name }} ({{ $emp->employee_code }}) &bull; Balance: {{ $emp->remaining_leaves }}d</option>
                        @endforeach
                    </select>
                </div>
                @endif

                <!-- Leave Type Dropdown -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Leave Type *</label>
                    <select name="leave_type_id" required 
                            class="w-full px-3.5 py-2.5 bg-slate-50/70 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 rounded-xl font-semibold text-xs text-slate-900 dark:text-white focus:bg-white focus:border-[#0071E3] focus:ring-2 focus:ring-[#0071E3]/20 transition outline-none">
                        @foreach($leaveTypes as $lt)
                            <option value="{{ $lt->id }}">{{ $lt->name }} ({{ $lt->is_paid ? 'Paid' : 'Unpaid' }})</option>
                        @endforeach
                    </select>
                </div>

                <!-- Quick Date Selection Shortcuts -->
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Leave Dates *</label>
                        <div class="flex items-center gap-1.5">
                            <span class="text-[10px] text-slate-400 font-medium">Quick Select:</span>
                            <button type="button" @click="setPreset('today')" class="px-2 py-0.5 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-700 dark:text-slate-300 text-[10px] font-semibold transition cursor-pointer">
                                Today
                            </button>
                            <button type="button" @click="setPreset('tomorrow')" class="px-2 py-0.5 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-700 dark:text-slate-300 text-[10px] font-semibold transition cursor-pointer">
                                Tomorrow
                            </button>
                            <button type="button" @click="setPreset('two_days')" class="px-2 py-0.5 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-700 dark:text-slate-300 text-[10px] font-semibold transition cursor-pointer">
                                2 Days
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        <div>
                            <label class="block text-[10px] text-slate-400 font-semibold mb-1">Start Date</label>
                            <input type="date" name="start_date" x-model="startDate" required
                                   class="w-full px-3.5 py-2.5 bg-slate-50/70 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 rounded-xl font-semibold text-xs text-slate-900 dark:text-white focus:bg-white focus:border-[#0071E3] focus:ring-2 focus:ring-[#0071E3]/20 transition outline-none">
                        </div>

                        <div>
                            <label class="block text-[10px] text-slate-400 font-semibold mb-1">End Date</label>
                            <input type="date" name="end_date" x-model="endDate" required
                                   class="w-full px-3.5 py-2.5 bg-slate-50/70 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 rounded-xl font-semibold text-xs text-slate-900 dark:text-white focus:bg-white focus:border-[#0071E3] focus:ring-2 focus:ring-[#0071E3]/20 transition outline-none">
                        </div>
                    </div>
                </div>

                <!-- Dynamic Duration & Quota Impact Callout -->
                <div class="p-3.5 rounded-2xl bg-blue-50/50 dark:bg-slate-800/50 border border-blue-100/80 dark:border-slate-700 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="w-6 h-6 rounded-lg bg-blue-100 text-[#0071E3] flex items-center justify-center font-bold text-xs">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                        <span class="font-semibold text-slate-700 dark:text-slate-300">
                            Requested Duration: <strong class="text-[#0071E3]" x-text="totalDays + ' Day' + (totalDays > 1 ? 's' : '')">1 Day</strong>
                        </span>
                    </div>
                    @if($currentEmployee)
                    <div class="text-[11px] text-slate-500 font-medium">
                        Balance after approval: <strong class="text-slate-800 dark:text-white font-bold" x-text="remainingAfter + ' days'">18 days</strong>
                    </div>
                    @endif
                </div>

                <!-- Reason Textarea -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Reason for Leave *</label>
                    <textarea name="reason" rows="3" required placeholder="Briefly describe the reason for your time-off request..." 
                              class="w-full px-3.5 py-2.5 bg-slate-50/70 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 rounded-xl font-medium text-xs text-slate-900 dark:text-white focus:bg-white focus:border-[#0071E3] focus:ring-2 focus:ring-[#0071E3]/20 transition outline-none resize-none placeholder-slate-400">{{ old('reason') }}</textarea>
                </div>

            </div>

            <!-- Elevated Action Footer -->
            <div class="p-4 bg-slate-50/80 dark:bg-slate-800/50 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <span class="text-[11px] text-slate-400">Leaves auto-sync with attendance on manager approval</span>
                <div class="flex items-center gap-2.5">
                    <a href="{{ route('leaves.index') }}" 
                       class="px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 font-semibold rounded-xl hover:bg-slate-100 transition cursor-pointer shadow-2xs">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="px-5 py-2 bg-[#0071E3] hover:bg-[#0062C4] text-white font-bold rounded-xl shadow-[0_4px_12px_rgba(0,113,227,0.25)] transition active:scale-95 cursor-pointer flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                        <span>Submit Request</span>
                    </button>
                </div>
            </div>

        </form>

    </div>

</div>
@endsection

