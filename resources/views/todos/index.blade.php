@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto space-y-5" x-data="todoApp()">

    <!-- Pro Header & Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2.5">
                <h1 class="text-xl font-bold text-slate-900 tracking-tight">Tasks</h1>
                <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                    {{ $counts['all'] }} Active
                </span>
            </div>
            <p class="text-xs text-slate-500 mt-0.5">
                Studio deliverables, team assignments, and production backlog
            </p>
        </div>

        <div class="flex items-center gap-2.5">
            <button type="button" @click="addModalOpen = true" 
                    class="inline-flex items-center gap-2 px-3.5 py-2 bg-[#0071e3] hover:bg-[#0062c4] active:scale-[0.98] text-white text-xs font-semibold rounded-xl shadow-xs transition cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span>New Task</span>
            </button>
        </div>
    </div>

    <!-- Vibrant Dribbble / Arcade Style Tasks Metric Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        
        <!-- Total Active Tasks (Cyan / Sky Blue Gradient) -->
        <a href="{{ route('todos.index', ['tab' => 'all']) }}" 
           class="relative overflow-hidden p-5 rounded-2xl bg-gradient-to-br from-[#0284c7] via-[#0ea5e9] to-[#38bdf8] text-white shadow-[0_8px_22px_rgba(14,165,233,0.25)] hover:scale-[1.02] transition-all duration-200 flex flex-col justify-between group cursor-pointer">
            <!-- Large Watermark Illustration -->
            <svg class="absolute -right-3 -bottom-3 w-28 h-28 text-white/15 pointer-events-none transition-transform group-hover:scale-110 duration-300" fill="currentColor" viewBox="0 0 24 24">
                <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-9 14l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
            </svg>

            <div class="relative z-10">
                <span class="text-[11px] font-extrabold uppercase tracking-wider text-white/90">All Active Tasks</span>
                <div class="w-6 h-0.5 bg-white/30 rounded-full mt-1"></div>
            </div>
            <div class="mt-4 relative z-10">
                <div class="text-3xl font-black text-white tracking-tight">{{ $counts['all'] }}</div>
                <div class="text-[11px] text-white/85 font-medium mt-1">Active deliverables</div>
            </div>
        </a>

        <!-- Due Today (Orange / Amber Gradient) -->
        <a href="{{ route('todos.index', ['tab' => 'today']) }}" 
           class="relative overflow-hidden p-5 rounded-2xl bg-gradient-to-br from-[#f59e0b] via-[#f97316] to-[#fb923c] text-white shadow-[0_8px_22px_rgba(249,115,22,0.25)] hover:scale-[1.02] transition-all duration-200 flex flex-col justify-between group cursor-pointer">
            <!-- Large Watermark Illustration -->
            <svg class="absolute -right-3 -bottom-3 w-28 h-28 text-white/15 pointer-events-none transition-transform group-hover:scale-110 duration-300" fill="currentColor" viewBox="0 0 24 24">
                <path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z"/>
            </svg>

            <div class="relative z-10">
                <span class="text-[11px] font-extrabold uppercase tracking-wider text-white/90">Due Today</span>
                <div class="w-6 h-0.5 bg-white/30 rounded-full mt-1"></div>
            </div>
            <div class="mt-4 relative z-10">
                <div class="text-3xl font-black text-white tracking-tight">{{ $counts['today'] }}</div>
                <div class="text-[11px] text-white/85 font-medium mt-1">Action items for today</div>
            </div>
        </a>

        <!-- High Priority (Rose / Magenta Gradient) -->
        <a href="{{ route('todos.index', ['tab' => 'high_priority']) }}" 
           class="relative overflow-hidden p-5 rounded-2xl bg-gradient-to-br from-[#ec4899] via-[#f43f5e] to-[#fb7185] text-white shadow-[0_8px_22px_rgba(244,63,94,0.25)] hover:scale-[1.02] transition-all duration-200 flex flex-col justify-between group cursor-pointer">
            <!-- Large Watermark Illustration -->
            <svg class="absolute -right-3 -bottom-3 w-28 h-28 text-white/15 pointer-events-none transition-transform group-hover:scale-110 duration-300" fill="currentColor" viewBox="0 0 24 24">
                <path d="M13.5.67s.74 2.65.74 4.8c0 2.06-1.35 3.73-3.41 3.73-2.07 0-3.63-1.67-3.63-3.73l.03-.36C5.21 7.51 4 10.62 4 14c0 4.42 3.58 8 8 8s8-3.58 8-8C20 8.61 17.41 3.8 13.5.67zM11.71 19c-1.78 0-3.22-1.4-3.22-3.14 0-1.62 1.05-2.76 2.81-3.12 1.77-.36 3.6-1.21 4.62-2.58.39 1.29.59 2.65.59 4.04 0 2.65-2.15 4.8-4.8 4.8z"/>
            </svg>

            <div class="relative z-10">
                <span class="text-[11px] font-extrabold uppercase tracking-wider text-white/90">High Priority</span>
                <div class="w-6 h-0.5 bg-white/30 rounded-full mt-1"></div>
            </div>
            <div class="mt-4 relative z-10">
                <div class="text-3xl font-black text-white tracking-tight">{{ $counts['high'] }}</div>
                <div class="text-[11px] text-white/85 font-medium mt-1">Urgent attention</div>
            </div>
        </a>

        <!-- Completed Tasks (Emerald / Mint Gradient) -->
        <a href="{{ route('todos.index', ['tab' => 'completed']) }}" 
           class="relative overflow-hidden p-5 rounded-2xl bg-gradient-to-br from-[#059669] via-[#10b981] to-[#34d399] text-white shadow-[0_8px_22px_rgba(16,185,129,0.25)] hover:scale-[1.02] transition-all duration-200 flex flex-col justify-between group cursor-pointer">
            <!-- Large Watermark Illustration -->
            <svg class="absolute -right-3 -bottom-3 w-28 h-28 text-white/15 pointer-events-none transition-transform group-hover:scale-110 duration-300" fill="currentColor" viewBox="0 0 24 24">
                <path d="M19 5h-2V3H7v2H5c-1.1 0-2 .9-2 2v1c0 2.55 1.92 4.63 4.39 4.94A5.01 5.01 0 0011 15.9V19H7v2h10v-2h-4v-3.1a5.01 5.01 0 003.61-2.96C19.08 12.63 21 10.55 21 8V7c0-1.1-.9-2-2-2zM5 8V7h2v3.82C5.84 10.4 5 9.3 5 8zm14 0c0 1.3-.84 2.4-2 2.82V7h2v1z"/>
            </svg>

            <div class="relative z-10">
                <span class="text-[11px] font-extrabold uppercase tracking-wider text-white/90">Completed</span>
                <div class="w-6 h-0.5 bg-white/30 rounded-full mt-1"></div>
            </div>
            <div class="mt-4 relative z-10">
                <div class="text-3xl font-black text-white tracking-tight">{{ $counts['completed'] }}</div>
                <div class="text-[11px] text-white/85 font-medium mt-1">Successfully finished</div>
            </div>
        </a>

    </div>

    <!-- Unified Filter & Controls Toolbar -->
    <div class="p-1.5 bg-white rounded-2xl border border-slate-200/90 shadow-2xs flex flex-col md:flex-row md:items-center justify-between gap-3">
        
        <!-- Segmented Tab Pills -->
        <div class="inline-flex p-1 bg-slate-100 rounded-xl overflow-x-auto">
            <a href="{{ route('todos.index', ['tab' => 'all']) }}" 
               class="px-3 py-1.5 rounded-lg text-xs font-medium transition whitespace-nowrap {{ $tab === 'all' ? 'bg-white text-slate-900 shadow-2xs font-semibold' : 'text-slate-600 hover:text-slate-900' }}">
                <span>All Active</span>
                <span class="ml-1 text-[11px] {{ $tab === 'all' ? 'text-slate-900 font-bold' : 'text-slate-400' }}">{{ $counts['all'] }}</span>
            </a>

            <a href="{{ route('todos.index', ['tab' => 'today']) }}" 
               class="px-3 py-1.5 rounded-lg text-xs font-medium transition whitespace-nowrap {{ $tab === 'today' ? 'bg-white text-slate-900 shadow-2xs font-semibold' : 'text-slate-600 hover:text-slate-900' }}">
                <span>Today</span>
                <span class="ml-1 text-[11px] {{ $tab === 'today' ? 'text-amber-600 font-bold' : 'text-slate-400' }}">{{ $counts['today'] }}</span>
            </a>

            <a href="{{ route('todos.index', ['tab' => 'upcoming']) }}" 
               class="px-3 py-1.5 rounded-lg text-xs font-medium transition whitespace-nowrap {{ $tab === 'upcoming' ? 'bg-white text-slate-900 shadow-2xs font-semibold' : 'text-slate-600 hover:text-slate-900' }}">
                <span>Upcoming</span>
                <span class="ml-1 text-[11px] text-slate-400">{{ $counts['upcoming'] }}</span>
            </a>

            <a href="{{ route('todos.index', ['tab' => 'high_priority']) }}" 
               class="px-3 py-1.5 rounded-lg text-xs font-medium transition whitespace-nowrap {{ $tab === 'high_priority' ? 'bg-white text-rose-600 font-bold shadow-2xs' : 'text-slate-600 hover:text-slate-900' }}">
                <span>High Priority</span>
                <span class="ml-1 text-[11px] {{ $tab === 'high_priority' ? 'text-rose-600 font-bold' : 'text-slate-400' }}">{{ $counts['high'] }}</span>
            </a>

            <a href="{{ route('todos.index', ['tab' => 'completed']) }}" 
               class="px-3 py-1.5 rounded-lg text-xs font-medium transition whitespace-nowrap {{ $tab === 'completed' ? 'bg-white text-emerald-700 font-bold shadow-2xs' : 'text-slate-600 hover:text-slate-900' }}">
                <span>Completed</span>
                <span class="ml-1 text-[11px] text-slate-400">{{ $counts['completed'] }}</span>
            </a>
        </div>

        <!-- Search & 3-Way View Switcher -->
        <div class="flex items-center gap-2 px-1">
            <form method="GET" action="{{ route('todos.index') }}" class="relative w-full md:w-52">
                <input type="hidden" name="tab" value="{{ $tab }}">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search tasks..." 
                       class="w-full pl-8 pr-7 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-normal focus:outline-hidden focus:bg-white focus:border-[#0071e3] focus:ring-1 focus:ring-[#0071e3] text-slate-800 placeholder:text-slate-400 transition">
                <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                @if(request('search'))
                <a href="{{ route('todos.index', ['tab' => $tab]) }}" class="absolute right-2.5 top-1.5 text-xs text-slate-400 hover:text-slate-600">✕</a>
                @endif
            </form>

            <div class="inline-flex p-1 bg-slate-100 rounded-xl border border-slate-200/80 shrink-0">
                <button type="button" 
                        @click="setView('list')" 
                        title="List View"
                        :class="view === 'list' ? 'bg-white text-slate-900 shadow-2xs font-semibold' : 'text-slate-500 hover:text-slate-800'"
                        class="px-2.5 py-1 rounded-lg text-xs transition cursor-pointer flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    <span>List</span>
                </button>

                <button type="button" 
                        @click="setView('grid')" 
                        title="Grid View"
                        :class="view === 'grid' ? 'bg-white text-slate-900 shadow-2xs font-semibold' : 'text-slate-500 hover:text-slate-800'"
                        class="px-2.5 py-1 rounded-lg text-xs transition cursor-pointer flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                    <span>Grid</span>
                </button>

                <button type="button" 
                        @click="setView('kanban')" 
                        title="Board View"
                        :class="view === 'kanban' ? 'bg-white text-slate-900 shadow-2xs font-semibold' : 'text-slate-500 hover:text-slate-800'"
                        class="px-2.5 py-1 rounded-lg text-xs transition cursor-pointer flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"></path></svg>
                    <span>Board</span>
                </button>
            </div>
        </div>
    </div>

    <!-- 1. LINEAR / THINGS 3 LIST VIEW -->
    <div x-show="view === 'list'" class="ui-panel divide-y divide-slate-100 overflow-hidden">
        @forelse($todos as $todo)
        <div class="p-3.5 sm:px-5 sm:py-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:bg-slate-50/80 transition group {{ $todo->is_completed ? 'bg-slate-50/40' : '' }}"
             x-data="{ showSubtasks: false }">
            
            <div class="flex items-start gap-3 flex-1 min-w-0">
                <!-- Checkbox -->
                <form method="POST" action="{{ route('todos.toggle', $todo) }}" class="mt-0.5 shrink-0">
                    @csrf
                    @method('PATCH')
                    <button type="submit" 
                            title="{{ $todo->is_completed ? 'Mark as active' : 'Mark as completed' }}"
                            class="w-4.5 h-4.5 rounded-md border flex items-center justify-center transition cursor-pointer {{ $todo->is_completed ? 'bg-emerald-600 border-emerald-600 text-white' : 'border-slate-300 hover:border-[#0071e3] bg-white text-transparent hover:text-slate-300' }}">
                        <svg class="w-3 h-3 fill-current" viewBox="0 0 20 20"><path d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/></svg>
                    </button>
                </form>

                <div class="flex-1 min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <!-- Priority Indicator Dot -->
                        @if($todo->priority === 'high')
                            <span class="w-2 h-2 rounded-full bg-rose-500 shrink-0" title="High Priority"></span>
                        @elseif($todo->priority === 'medium')
                            <span class="w-2 h-2 rounded-full bg-amber-400 shrink-0" title="Medium Priority"></span>
                        @else
                            <span class="w-2 h-2 rounded-full bg-slate-300 shrink-0" title="Low Priority"></span>
                        @endif

                        <span class="text-xs sm:text-[13px] font-semibold {{ $todo->is_completed ? 'line-through text-slate-400' : 'text-slate-900' }}">
                            {{ $todo->title }}
                        </span>

                        <span class="px-2 py-0.5 rounded-md text-[11px] font-medium bg-slate-100 text-slate-600 border border-slate-200/70">
                            {{ $todo->category }}
                        </span>

                        @if($todo->work_entry_id)
                            <span class="px-1.5 py-0.5 rounded text-[10px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center gap-1">
                                <span>✓ Logged</span>
                            </span>
                        @endif
                    </div>

                    @if($todo->description)
                    <p class="text-xs text-slate-500 mt-1 font-normal line-clamp-2 {{ $todo->is_completed ? 'line-through text-slate-400' : '' }}">
                        {{ $todo->description }}
                    </p>
                    @endif

                    <!-- Subtasks Checklist Dropdown -->
                    @if($todo->totalSubtasksCount() > 0)
                    <div class="mt-1.5">
                        <button type="button" @click="showSubtasks = !showSubtasks" class="inline-flex items-center gap-1 text-[11px] font-medium text-slate-500 hover:text-slate-800 cursor-pointer">
                            <span class="w-3.5 h-3.5 rounded bg-slate-100 border border-slate-200 text-slate-700 flex items-center justify-center text-[9px] font-bold">
                                {{ $todo->completedSubtasksCount() }}/{{ $todo->totalSubtasksCount() }}
                            </span>
                            <span>Checklist</span>
                            <svg class="w-3 h-3 text-slate-400 transition-transform" :class="showSubtasks ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>

                        <div x-show="showSubtasks" class="mt-2 pl-2.5 border-l-2 border-slate-200 space-y-1 text-xs" style="display: none;">
                            @foreach($todo->subtasks as $sIndex => $subtask)
                            <form method="POST" action="{{ route('todos.toggleSubtask', $todo) }}" class="flex items-center gap-2">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="index" value="{{ $sIndex }}">
                                <button type="submit" class="w-3.5 h-3.5 rounded border flex items-center justify-center transition cursor-pointer {{ !empty($subtask['completed']) ? 'bg-emerald-600 border-emerald-600 text-white' : 'border-slate-300 bg-white' }}">
                                    @if(!empty($subtask['completed']))
                                        <svg class="w-2.5 h-2.5 fill-current" viewBox="0 0 20 20"><path d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/></svg>
                                    @endif
                                </button>
                                <span class="{{ !empty($subtask['completed']) ? 'line-through text-slate-400' : 'text-slate-700' }}">{{ $subtask['title'] }}</span>
                            </form>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <!-- Metadata -->
                    <div class="flex flex-wrap items-center gap-3 mt-1.5 text-[11px] text-slate-500 font-normal">
                        @if($todo->due_date)
                        <div class="flex items-center gap-1 {{ $todo->isOverdue() ? 'text-rose-600 font-semibold' : ($todo->due_date->isToday() ? 'text-amber-700 font-medium' : 'text-slate-500') }}">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            <span>{{ $todo->due_date->isToday() ? 'Today' : $todo->due_date->format('d M') }}</span>
                            @if($todo->due_time)
                                <span>{{ date('H:i', strtotime($todo->due_time)) }}</span>
                            @endif
                        </div>
                        @endif

                        @if($todo->reference_url)
                        <a href="{{ $todo->reference_url }}" target="_blank" class="inline-flex items-center gap-1 text-[#0071e3] hover:underline font-medium">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                            <span>Link</span>
                        </a>
                        @endif

                        @if($todo->assignedTo)
                        <div class="flex items-center gap-1 text-slate-600">
                            <span class="w-4 h-4 rounded-full bg-slate-200 text-slate-700 flex items-center justify-center font-bold text-[9px]">
                                {{ substr($todo->assignedTo->name, 0, 1) }}
                            </span>
                            <span>{{ $todo->assignedTo->name }}</span>
                        </div>
                        @endif

                        <span class="text-slate-300">•</span>
                        <span>{{ $todo->user->name }}</span>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center gap-1.5 shrink-0 self-end sm:self-center opacity-80 group-hover:opacity-100 transition">
                @if(!$todo->work_entry_id && auth()->user()->isManager())
                <button type="button" @click="openConvertModal(@js($todo))" 
                        class="inline-flex items-center gap-1 px-2.5 py-1 bg-amber-50 hover:bg-amber-100 text-amber-900 border border-amber-200/80 rounded-lg text-[11px] font-semibold transition cursor-pointer">
                    <span>⚡ Log Work</span>
                </button>
                @endif

                <button type="button" @click="openEdit(@js($todo))" 
                        class="p-1.5 text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded-lg transition cursor-pointer" title="Edit">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                </button>

                <form method="POST" action="{{ route('todos.destroy', $todo) }}" onsubmit="return confirm('Delete task?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition cursor-pointer" title="Delete">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    </button>
                </form>
            </div>

        </div>
        @empty
        <div class="py-16 text-center">
            <svg class="w-10 h-10 text-slate-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
            <div class="font-semibold text-slate-700 text-xs">No tasks in this view</div>
            <p class="text-[11px] text-slate-400 mt-0.5">Create a task to get started.</p>
        </div>
        @endforelse
    </div>

    <!-- 2. GRID VIEW -->
    <div x-show="view === 'grid'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4" style="display: none;">
        @forelse($todos as $todo)
        <div class="ui-panel p-4 flex flex-col justify-between hover:border-slate-300 hover:shadow-md transition group {{ $todo->is_completed ? 'bg-slate-50/60' : 'bg-white' }}">
            
            <div class="space-y-2">
                <div class="flex items-center justify-between gap-2">
                    <div class="flex items-center gap-2">
                        <form method="POST" action="{{ route('todos.toggle', $todo) }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit" 
                                    class="w-4.5 h-4.5 rounded-md border flex items-center justify-center transition cursor-pointer {{ $todo->is_completed ? 'bg-emerald-600 border-emerald-600 text-white' : 'border-slate-300 hover:border-[#0071e3] bg-white text-transparent hover:text-slate-300' }}">
                                <svg class="w-3 h-3 fill-current" viewBox="0 0 20 20"><path d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/></svg>
                            </button>
                        </form>

                        <span class="px-2 py-0.5 rounded-md text-[11px] font-medium bg-slate-100 text-slate-600 border border-slate-200/70">
                            {{ $todo->category }}
                        </span>
                    </div>

                    <div class="flex items-center gap-1">
                        @if($todo->priority === 'high')
                            <span class="px-1.5 py-0.2 rounded text-[10px] font-semibold bg-rose-50 text-rose-600 border border-rose-200">High</span>
                        @elseif($todo->priority === 'medium')
                            <span class="px-1.5 py-0.2 rounded text-[10px] font-medium bg-amber-50 text-amber-700 border border-amber-200">Medium</span>
                        @else
                            <span class="px-1.5 py-0.2 rounded text-[10px] font-medium bg-slate-100 text-slate-500 border border-slate-200">Low</span>
                        @endif

                        <button type="button" @click="openEdit(@js($todo))" class="p-1 text-slate-400 hover:text-slate-700 rounded transition cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        </button>
                    </div>
                </div>

                <div class="text-[13px] font-semibold text-slate-900 leading-snug {{ $todo->is_completed ? 'line-through text-slate-400' : '' }}">
                    {{ $todo->title }}
                </div>

                @if($todo->description)
                <p class="text-xs text-slate-500 font-normal line-clamp-2 {{ $todo->is_completed ? 'line-through text-slate-400' : '' }}">
                    {{ $todo->description }}
                </p>
                @endif

                @if($todo->reference_url)
                <div class="pt-0.5">
                    <a href="{{ $todo->reference_url }}" target="_blank" class="inline-flex items-center gap-1 text-[11px] text-[#0071e3] hover:underline font-medium">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                        <span>Reference</span>
                    </a>
                </div>
                @endif

                @if($todo->totalSubtasksCount() > 0)
                <div class="pt-2 border-t border-slate-100">
                    <div class="flex items-center justify-between text-[10px] text-slate-500 font-medium mb-1">
                        <span>Subtasks</span>
                        <span>{{ $todo->completedSubtasksCount() }}/{{ $todo->totalSubtasksCount() }}</span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                        <div class="bg-emerald-500 h-1.5 rounded-full" style="width: {{ round(($todo->completedSubtasksCount() / $todo->totalSubtasksCount()) * 100) }}%"></div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Card Footer -->
            <div class="mt-3.5 pt-2.5 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500">
                @if($todo->due_date)
                <div class="flex items-center gap-1 {{ $todo->isOverdue() ? 'text-rose-600 font-semibold' : ($todo->due_date->isToday() ? 'text-amber-700 font-medium' : 'text-slate-500') }}">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    <span>{{ $todo->due_date->isToday() ? 'Today' : $todo->due_date->format('d M') }}</span>
                </div>
                @else
                <span class="text-slate-400">No date</span>
                @endif

                <div class="flex items-center gap-1.5">
                    @if(!$todo->work_entry_id && auth()->user()->isManager())
                    <button type="button" @click="openConvertModal(@js($todo))" 
                            class="px-2 py-0.5 bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 rounded text-[10px] font-semibold transition cursor-pointer">
                        ⚡ Log
                    </button>
                    @endif

                    @if($todo->assignedTo)
                    <div class="w-4.5 h-4.5 rounded-full bg-slate-200 text-slate-700 flex items-center justify-center font-bold text-[9px]" title="{{ $todo->assignedTo->name }}">
                        {{ substr($todo->assignedTo->name, 0, 1) }}
                    </div>
                    @endif
                </div>
            </div>

        </div>
        @empty
        <div class="col-span-full py-16 text-center ui-panel">
            <svg class="w-10 h-10 text-slate-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
            <div class="font-semibold text-slate-700 text-xs">No tasks found</div>
            <p class="text-[11px] text-slate-400 mt-0.5">Create a task to get started.</p>
        </div>
        @endforelse
    </div>

    <!-- 3. PRO DRAG & DROP KANBAN BOARD -->
    <div x-show="view === 'kanban'" class="grid grid-cols-1 md:grid-cols-4 gap-4" style="display: none;">
        @php
            $columns = [
                'todo' => [
                    'name' => 'To Do', 
                    'bg' => 'bg-[#f4f5f7]/90', 
                    'border' => 'border-slate-200/90',
                    'dot' => 'bg-slate-400',
                    'badge' => 'bg-white text-slate-700 border border-slate-200 shadow-2xs'
                ],
                'in_progress' => [
                    'name' => 'In Progress', 
                    'bg' => 'bg-[#f0f7ff]', 
                    'border' => 'border-blue-200/80',
                    'dot' => 'bg-blue-500',
                    'badge' => 'bg-white text-blue-800 border border-blue-200 shadow-2xs'
                ],
                'in_review' => [
                    'name' => 'In Review', 
                    'bg' => 'bg-[#fbf7ff]', 
                    'border' => 'border-purple-200/80',
                    'dot' => 'bg-purple-500',
                    'badge' => 'bg-white text-purple-800 border border-purple-200 shadow-2xs'
                ],
                'completed' => [
                    'name' => 'Done', 
                    'bg' => 'bg-[#f2faf5]', 
                    'border' => 'border-emerald-200/80',
                    'dot' => 'bg-emerald-500',
                    'badge' => 'bg-white text-emerald-800 border border-emerald-200 shadow-2xs'
                ],
            ];
        @endphp

        @foreach($columns as $cKey => $col)
        <div class="flex flex-col rounded-2xl {{ $col['bg'] }} border {{ $col['border'] }} p-3 min-h-[540px]"
             @dragover.prevent="dragOverColumn = '{{ $cKey }}'"
             @dragleave="if(dragOverColumn === '{{ $cKey }}') dragOverColumn = null"
             @drop.prevent="dropTask('{{ $cKey }}')"
             :class="dragOverColumn === '{{ $cKey }}' ? 'ring-2 ring-[#0071e3] bg-blue-50/50' : ''">
            
            <!-- Column Header -->
            <div class="flex items-center justify-between pb-2.5 mb-2.5 border-b border-black/[0.05] px-1">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full {{ $col['dot'] }}"></span>
                    <span class="font-bold text-xs text-slate-800 tracking-tight">
                        {{ $col['name'] }}
                    </span>
                </div>
                <span class="px-2 py-0.2 rounded-md text-[11px] font-bold {{ $col['badge'] }}">
                    {{ $allTodosForKanban->where('status', $cKey)->count() }}
                </span>
            </div>

            <!-- Cards Stack -->
            <div class="space-y-2.5 flex-1 overflow-y-auto max-h-[660px] pr-0.5">
                @forelse($allTodosForKanban->where('status', $cKey) as $kTodo)
                <div class="bg-white rounded-xl border border-slate-200/90 p-3.5 shadow-2xs hover:shadow-md hover:border-slate-300 transition-all cursor-grab active:cursor-grabbing space-y-2 group"
                     draggable="true"
                     @dragstart="dragStart($event, @js($kTodo))"
                     @dragend="dragEnd($event)">
                    
                    <!-- Card Top: Category + Status Dropdown Button -->
                    <div class="flex items-center justify-between gap-1.5">
                        <span class="px-2 py-0.5 rounded-md text-[10px] font-semibold bg-slate-100 text-slate-600 border border-slate-200/60">
                            {{ $kTodo->category }}
                        </span>

                        <div class="flex items-center gap-1">
                            @if($kTodo->priority === 'high')
                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500" title="High Priority"></span>
                            @elseif($kTodo->priority === 'medium')
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-400" title="Medium Priority"></span>
                            @endif

                            <!-- Quick Status Menu -->
                            <div class="relative" x-data="{ menuOpen: false }">
                                <button type="button" @click.stop="menuOpen = !menuOpen" class="text-slate-400 hover:text-slate-700 p-1 rounded transition cursor-pointer">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path></svg>
                                </button>

                                <div x-show="menuOpen" @click.outside="menuOpen = false" class="absolute right-0 mt-1 w-32 bg-white rounded-xl shadow-lg border border-slate-200 p-1 z-20 text-[11px]" style="display: none;">
                                    <button type="button" @click="moveTaskStatus(@js($kTodo), 'todo'); menuOpen = false" class="w-full text-left px-2.5 py-1 rounded hover:bg-slate-100 font-medium text-slate-700">➔ To Do</button>
                                    <button type="button" @click="moveTaskStatus(@js($kTodo), 'in_progress'); menuOpen = false" class="w-full text-left px-2.5 py-1 rounded hover:bg-slate-100 font-medium text-blue-700">➔ In Progress</button>
                                    <button type="button" @click="moveTaskStatus(@js($kTodo), 'in_review'); menuOpen = false" class="w-full text-left px-2.5 py-1 rounded hover:bg-slate-100 font-medium text-purple-700">➔ In Review</button>
                                    <button type="button" @click="moveTaskStatus(@js($kTodo), 'completed'); menuOpen = false" class="w-full text-left px-2.5 py-1 rounded hover:bg-slate-100 font-medium text-emerald-700">➔ Done</button>
                                    <div class="my-1 border-t border-slate-100"></div>
                                    <button type="button" @click="openEdit(@js($kTodo)); menuOpen = false" class="w-full text-left px-2.5 py-1 rounded hover:bg-slate-100 text-slate-600">Edit Task</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Title -->
                    <div class="font-semibold text-xs text-slate-900 leading-snug {{ $kTodo->is_completed ? 'line-through text-slate-400' : '' }}">
                        {{ $kTodo->title }}
                    </div>

                    @if($kTodo->description)
                    <p class="text-[11px] text-slate-500 font-normal line-clamp-2 leading-relaxed {{ $kTodo->is_completed ? 'line-through text-slate-400' : '' }}">
                        {{ $kTodo->description }}
                    </p>
                    @endif

                    <!-- Subtasks Checklist Count -->
                    @if($kTodo->totalSubtasksCount() > 0)
                    <div class="flex items-center gap-1.5 text-[10px] text-slate-500 font-medium">
                        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                        <span>{{ $kTodo->completedSubtasksCount() }}/{{ $kTodo->totalSubtasksCount() }} Subtasks</span>
                    </div>
                    @endif

                    <!-- Card Footer: Due Date & Assignee -->
                    <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-[10px] text-slate-400">
                        @if($kTodo->due_date)
                            <span class="flex items-center gap-1 {{ $kTodo->isOverdue() ? 'text-rose-600 font-bold' : ($kTodo->due_date->isToday() ? 'text-amber-700 font-semibold' : 'text-slate-500') }}">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                <span>{{ $kTodo->due_date->isToday() ? 'Today' : $kTodo->due_date->format('d M') }}</span>
                            </span>
                        @else
                            <span>No date</span>
                        @endif

                        @if($kTodo->assignedTo)
                            <span class="font-medium text-slate-700 truncate max-w-[90px]">{{ $kTodo->assignedTo->name }}</span>
                        @endif
                    </div>

                </div>
                @empty
                <div class="py-14 text-center border-2 border-dashed border-black/[0.06] rounded-xl flex flex-col items-center justify-center text-slate-400 text-xs">
                    <span>Drop tasks here</span>
                </div>
                @endforelse
            </div>

        </div>
        @endforeach
    </div>

    @if($todos->hasPages())
    <div class="pt-2">
        {{ $todos->links() }}
    </div>
    @endif

    <!-- Add Task Modal -->
    <div x-show="addModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-xs" style="display: none;">
        <div @click.outside="addModalOpen = false" class="w-full max-w-lg ui-panel p-6 shadow-xl space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="font-bold text-sm text-slate-900">New Task</h3>
                <button @click="addModalOpen = false" class="text-slate-400 hover:text-slate-600 cursor-pointer">✕</button>
            </div>

            <form method="POST" action="{{ route('todos.store') }}" class="space-y-3.5 text-xs">
                @csrf
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Title *</label>
                    <input type="text" name="title" required placeholder="What needs to be done?" 
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 focus:outline-hidden focus:border-[#0071e3] focus:ring-1 focus:ring-[#0071e3]">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Category</label>
                        <select name="category" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-800">
                            @foreach($categories as $cat)
                                <option value="{{ $cat }}">{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Priority</label>
                        <select name="priority" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 font-medium">
                            <option value="low">Low Priority</option>
                            <option value="medium" selected>Medium Priority</option>
                            <option value="high">High Priority</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Due Date</label>
                        <input type="date" name="due_date" value="{{ now()->format('Y-m-d') }}" 
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-800">
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Due Time (Optional)</label>
                        <input type="time" name="due_time" value="18:00" 
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-800">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Status / Workflow</label>
                        <select name="status" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 font-medium">
                            <option value="todo" selected>To Do</option>
                            <option value="in_progress">In Progress</option>
                            <option value="in_review">In Review</option>
                            <option value="completed">Completed</option>
                        </select>
                    </div>

                    @if(auth()->user()->isManager())
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Assign to Member</label>
                        <select name="assigned_to_user_id" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-800">
                            <option value="">Myself (Personal Task)</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->role }})</option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Figma / Drive Reference URL (Optional)</label>
                    <input type="url" name="reference_url" placeholder="https://www.figma.com/file/..." 
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 focus:outline-hidden focus:border-[#0071e3]">
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Subtasks Checklist (1 per line)</label>
                    <textarea name="subtasks_text" rows="2" placeholder="Rough sketch&#10;Color grading&#10;Export 1080p" 
                              class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 font-mono text-[11px]"></textarea>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Description</label>
                    <textarea name="description" rows="2" placeholder="Additional details or notes..." 
                              class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-800"></textarea>
                </div>

                <div class="pt-2 flex items-center justify-end gap-2 border-t border-slate-100">
                    <button type="button" @click="addModalOpen = false" class="px-3.5 py-1.5 rounded-xl font-medium text-slate-600 hover:bg-slate-100">Cancel</button>
                    <button type="submit" class="px-4 py-1.5 bg-[#0071e3] hover:bg-[#0062c4] text-white font-semibold rounded-xl shadow-xs cursor-pointer">Create Task</button>
                </div>
            </form>
        </div>
    </div>

    <!-- 1-Click Convert to Work Entry Modal -->
    <div x-show="convertModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-xs" style="display: none;">
        <div @click.outside="convertModalOpen = false" class="w-full max-w-md ui-panel p-6 shadow-xl space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <span class="w-6 h-6 rounded-md bg-amber-100 text-amber-800 flex items-center justify-center font-bold text-xs">⚡</span>
                    <h3 class="font-bold text-sm text-slate-900">Log to Daily Work Entry</h3>
                </div>
                <button @click="convertModalOpen = false" class="text-slate-400 hover:text-slate-600 cursor-pointer">✕</button>
            </div>

            <p class="text-xs text-slate-500">
                Record this completed task directly into the studio deliverable log & leaderboard.
            </p>

            <form method="POST" :action="convertUrl" class="space-y-3.5 text-xs">
                @csrf
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Work Category *</label>
                    <select name="work_category_id" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 font-medium">
                        @foreach($workCategories as $wc)
                            <option value="{{ $wc->id }}">{{ $wc->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Quantity *</label>
                        <input type="number" name="quantity" value="1" min="1" required 
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 font-bold text-center">
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Work Date *</label>
                        <input type="date" name="date" value="{{ now()->format('Y-m-d') }}" required 
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-800">
                    </div>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Remarks / Note</label>
                    <input type="text" name="remarks" x-model="convertTitle" 
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-900">
                </div>

                <div class="pt-2 flex items-center justify-end gap-2 border-t border-slate-100">
                    <button type="button" @click="convertModalOpen = false" class="px-3.5 py-1.5 rounded-xl font-medium text-slate-600 hover:bg-slate-100">Cancel</button>
                    <button type="submit" class="px-4 py-1.5 bg-[#0071e3] hover:bg-[#0062c4] text-white font-semibold rounded-xl shadow-xs cursor-pointer">
                        ⚡ Save to Work History
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Task Modal -->
    <div x-show="editModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-xs" style="display: none;">
        <div @click.outside="editModalOpen = false" class="w-full max-w-lg ui-panel p-6 shadow-xl space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="font-bold text-sm text-slate-900">Edit Task</h3>
                <button @click="editModalOpen = false" class="text-slate-400 hover:text-slate-600 cursor-pointer">✕</button>
            </div>

            <form method="POST" :action="editData.url" class="space-y-3.5 text-xs">
                @csrf
                @method('PUT')
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Title</label>
                    <input type="text" name="title" x-model="editData.title" required 
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 focus:outline-hidden focus:border-[#0071e3]">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Category</label>
                        <select name="category" x-model="editData.category" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-800">
                            @foreach($categories as $cat)
                                <option value="{{ $cat }}">{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Priority</label>
                        <select name="priority" x-model="editData.priority" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 font-medium">
                            <option value="low">Low Priority</option>
                            <option value="medium">Medium Priority</option>
                            <option value="high">High Priority</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Due Date</label>
                        <input type="date" name="due_date" x-model="editData.due_date" 
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-800">
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Due Time</label>
                        <input type="time" name="due_time" x-model="editData.due_time" 
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-800">
                    </div>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Figma / Drive Reference URL</label>
                    <input type="url" name="reference_url" x-model="editData.reference_url" 
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-900">
                </div>

                @if(auth()->user()->isManager())
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Assign to Member</label>
                    <select name="assigned_to_user_id" x-model="editData.assigned_to_user_id" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-800">
                        <option value="">Myself (Personal Task)</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->role }})</option>
                        @endforeach
                    </select>
                </div>
                @endif

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Description</label>
                    <textarea name="description" x-model="editData.description" rows="2" 
                              class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-800"></textarea>
                </div>

                <div class="pt-2 flex items-center justify-end gap-2 border-t border-slate-100">
                    <button type="button" @click="editModalOpen = false" class="px-3.5 py-1.5 rounded-xl font-medium text-slate-600 hover:bg-slate-100">Cancel</button>
                    <button type="submit" class="px-4 py-1.5 bg-[#0071e3] hover:bg-[#0062c4] text-white font-semibold rounded-xl shadow-xs cursor-pointer">Update Task</button>
                </div>
            </form>
        </div>
    </div>

</div>

@push('scripts')
<script>
    function todoApp() {
        return {
            view: localStorage.getItem('posterit_todo_view') || 'kanban',
            addModalOpen: false,
            editModalOpen: false,
            convertModalOpen: false,
            convertUrl: '',
            convertTitle: '',
            draggedTask: null,
            dragOverColumn: null,
            editData: { id: null, title: '', description: '', priority: 'medium', category: 'General', due_date: '', due_time: '', reference_url: '', assigned_to_user_id: '', url: '' },
            setView(v) {
                this.view = v;
                localStorage.setItem('posterit_todo_view', v);
            },
            dragStart(e, task) {
                this.draggedTask = task;
                e.dataTransfer.effectAllowed = 'move';
                e.dataTransfer.setData('text/plain', task.id);
            },
            dragEnd(e) {
                this.dragOverColumn = null;
            },
            dropTask(targetStatus) {
                if (!this.draggedTask) return;
                const taskId = this.draggedTask.id;
                this.moveTaskStatus(this.draggedTask, targetStatus);
                this.draggedTask = null;
                this.dragOverColumn = null;
            },
            moveTaskStatus(task, newStatus) {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `{{ url('todos') }}/${task.id}/status`;
                
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = token;
                form.appendChild(csrfInput);

                const methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                methodInput.value = 'PATCH';
                form.appendChild(methodInput);

                const statusInput = document.createElement('input');
                statusInput.type = 'hidden';
                statusInput.name = 'status';
                statusInput.value = newStatus;
                form.appendChild(statusInput);

                document.body.appendChild(form);
                form.submit();
            },
            openEdit(t) {
                this.editData = {
                    id: t.id,
                    title: t.title,
                    description: t.description || '',
                    priority: t.priority,
                    category: t.category,
                    due_date: t.due_date ? t.due_date.split('T')[0] : '',
                    due_time: t.due_time ? t.due_time.substring(0, 5) : '',
                    reference_url: t.reference_url || '',
                    assigned_to_user_id: t.assigned_to_user_id || '',
                    url: `{{ url('todos') }}/${t.id}`
                };
                this.editModalOpen = true;
            },
            openConvertModal(t) {
                this.convertTitle = t.title;
                this.convertUrl = `{{ url('todos') }}/${t.id}/convert-work-entry`;
                this.convertModalOpen = true;
            }
        };
    }
</script>
@endpush
@endsection
