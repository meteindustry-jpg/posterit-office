<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="layoutApp()" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5, viewport-fit=cover">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="theme-color" content="#f6f8fa">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @php
        $siteLogo = \App\Models\CompanySetting::get('company_logo');
        $siteName = \App\Models\CompanySetting::get('company_name', config('app.name', 'Posterit'));
    @endphp
    <title>{{ $siteName }} — Work Management</title>
    @if($siteLogo && file_exists(public_path('storage/' . $siteLogo)))
        <link rel="icon" type="image/png" href="{{ asset('storage/' . $siteLogo) }}">
    @endif

    <!-- SF Pro / Plus Jakarta Sans font styles -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script>
        function layoutApp() {
            return {
                sidebarOpen: false,
                searchOpen: false,
                searchQuery: '',
                searchResults: { employees: [], categories: [], work_entries: [] },
                isSearching: false,
                async doSearch() {
                    if (this.searchQuery.length < 2) {
                        this.searchResults = { employees: [], categories: [], work_entries: [] };
                        return;
                    }
                    this.isSearching = true;
                    try {
                        const res = await fetch(`{{ route('api.search') }}?q=${encodeURIComponent(this.searchQuery)}`);
                        this.searchResults = await res.json();
                    } catch(e) {
                        console.error(e);
                    } finally {
                        this.isSearching = false;
                    }
                }
            };
        }

        function liveStopwatch() {
            return {
                seconds: parseInt(localStorage.getItem('posterit_timer_sec') || 0),
                isRunning: localStorage.getItem('posterit_timer_running') === 'true',
                interval: null,

                init() {
                    if (this.isRunning) {
                        this.start();
                    }
                },

                get formattedTime() {
                    const hrs = String(Math.floor(this.seconds / 3600)).padStart(2, '0');
                    const mins = String(Math.floor((this.seconds % 3600) / 60)).padStart(2, '0');
                    const secs = String(this.seconds % 60).padStart(2, '0');
                    return `${hrs}:${mins}:${secs}`;
                },

                toggle() {
                    if (this.isRunning) {
                        this.pause();
                    } else {
                        this.start();
                    }
                },

                start() {
                    this.isRunning = true;
                    localStorage.setItem('posterit_timer_running', 'true');
                    if (this.interval) clearInterval(this.interval);
                    this.interval = setInterval(() => {
                        this.seconds++;
                        localStorage.setItem('posterit_timer_sec', this.seconds);
                    }, 1000);
                },

                pause() {
                    this.isRunning = false;
                    localStorage.setItem('posterit_timer_running', 'false');
                    if (this.interval) clearInterval(this.interval);
                },

                reset() {
                    this.pause();
                    this.seconds = 0;
                    localStorage.setItem('posterit_timer_sec', 0);
                }
            };
        }
    </script>
</head>
<body class="h-full antialiased text-[#1d1d1f] selection:bg-[#0071e3] selection:text-white font-sans bg-[#f6f8fa]"
      @keydown.window.ctrl.k.prevent="searchOpen = true"
      @keydown.window.meta.k.prevent="searchOpen = true">

    <div class="h-screen flex flex-col md:flex-row overflow-hidden bg-[#f6f8fa]">
        
        <!-- Mobile Sidebar Backdrop -->
        <div x-show="sidebarOpen" 
             x-transition:enter="transition-opacity ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="sidebarOpen = false" 
             class="fixed inset-0 z-40 bg-black/30 backdrop-blur-xs md:hidden" 
             style="display: none;"></div>

        <!-- Apple-style Clean Sidebar (Fixed & Stable) -->
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" 
               class="fixed inset-y-0 left-0 z-50 w-72 glass-sidebar flex flex-col transition-transform duration-300 ease-out md:static md:translate-x-0 shrink-0 h-full border-r border-slate-200/80">
            
            <!-- App Brand Header -->
            @php
                $brandLogo = \App\Models\CompanySetting::get('company_logo');
                $brandName = \App\Models\CompanySetting::get('company_name', config('app.name', 'Posterit'));
                $brandTagline = \App\Models\CompanySetting::get('company_tagline', 'Design Operations');
            @endphp
            <div class="h-20 flex items-center justify-between px-6 border-b border-slate-200/80 shrink-0">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3.5 group min-w-0">
                    @if($brandLogo && file_exists(public_path('storage/' . $brandLogo)))
                        <img src="{{ asset('storage/' . $brandLogo) }}" alt="{{ $brandName }}" 
                             class="w-10 h-10 rounded-2xl object-contain group-hover:scale-105 transition-transform duration-200 shrink-0">
                    @else
                        <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-[#0071e3] to-[#4299e1] flex items-center justify-center text-white font-black text-lg shadow-[0_4px_12px_rgba(0,113,227,0.3)] group-hover:scale-105 transition-transform duration-200 shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                        </div>
                    @endif
                    <div class="flex flex-col min-w-0">
                        <span class="font-extrabold text-base tracking-tight leading-tight text-slate-900 truncate">{{ $brandName }}</span>
                        <span class="text-[11px] font-semibold text-slate-400 truncate mt-0.5">{{ $brandTagline }}</span>
                    </div>
                </a>
                <button @click="sidebarOpen = false" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 md:hidden shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <!-- Navigation Links (Scrollable) -->
            <div class="flex-1 overflow-y-auto px-4 py-5 space-y-6">
                
                <!-- Main Operations -->
                <div class="space-y-1">
                    <div class="px-3 text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">Main Menu</div>
                    
                    <a href="{{ route('dashboard') }}" 
                       class="flex items-center gap-3 px-3 py-2.5 rounded-2xl font-semibold text-xs transition-all {{ request()->routeIs('dashboard') ? 'bg-[#0071e3] text-white shadow-[0_4px_14px_rgba(0,113,227,0.35)]' : 'text-slate-600 hover:bg-slate-100/80 hover:text-slate-900' }}">
                        <div class="w-7 h-7 rounded-xl flex items-center justify-center {{ request()->routeIs('dashboard') ? 'bg-white/20 text-white' : 'bg-blue-50 text-[#0071e3]' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                        </div>
                        <span class="text-sm">Dashboard</span>
                    </a>

                    @php
                        $userPendingTodosCount = \App\Models\Todo::where(function($q) {
                            $q->where('user_id', auth()->id())->orWhere('assigned_to_user_id', auth()->id());
                        })->where('is_completed', false)->count();
                    @endphp
                    <a href="{{ route('todos.index') }}" 
                       class="flex items-center justify-between px-3 py-2.5 rounded-2xl font-semibold text-xs transition-all {{ request()->routeIs('todos.*') ? 'bg-[#0071e3] text-white shadow-[0_4px_14px_rgba(0,113,227,0.35)]' : 'text-slate-600 hover:bg-slate-100/80 hover:text-slate-900' }}">
                        <div class="flex items-center gap-3">
                            <div class="w-7 h-7 rounded-xl flex items-center justify-center {{ request()->routeIs('todos.*') ? 'bg-white/20 text-white' : 'bg-amber-50 text-[#ff9500]' }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <span class="text-sm">Tasks</span>
                        </div>
                        @if($userPendingTodosCount > 0)
                            <span class="px-2 py-0.5 text-[10px] font-bold rounded-full {{ request()->routeIs('todos.*') ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600' }}">{{ $userPendingTodosCount }}</span>
                        @endif
                    </a>

                    @if(auth()->user()->isManager())
                    <a href="{{ route('work-entries.batch') }}" 
                       class="flex items-center justify-between px-3 py-2.5 rounded-2xl font-semibold text-xs transition-all {{ request()->routeIs('work-entries.batch') ? 'bg-[#0071e3] text-white shadow-[0_4px_14px_rgba(0,113,227,0.35)]' : 'text-slate-600 hover:bg-slate-100/80 hover:text-slate-900' }}">
                        <div class="flex items-center gap-3">
                            <div class="w-7 h-7 rounded-xl flex items-center justify-center {{ request()->routeIs('work-entries.batch') ? 'bg-white/20 text-white' : 'bg-cyan-50 text-[#0071e3]' }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                            </div>
                            <span class="text-sm">Daily Work Entry</span>
                        </div>
                    </a>
                    @endif

                    <a href="{{ route('work-entries.index') }}" 
                       class="flex items-center gap-3 px-3 py-2.5 rounded-2xl font-semibold text-xs transition-all {{ request()->routeIs('work-entries.index*') && !request()->routeIs('work-entries.batch') ? 'bg-[#0071e3] text-white shadow-[0_4px_14px_rgba(0,113,227,0.35)]' : 'text-slate-600 hover:bg-slate-100/80 hover:text-slate-900' }}">
                        <div class="w-7 h-7 rounded-xl flex items-center justify-center {{ request()->routeIs('work-entries.index*') && !request()->routeIs('work-entries.batch') ? 'bg-white/20 text-white' : 'bg-indigo-50 text-[#5856d6]' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                        </div>
                        <span class="text-sm">Work History</span>
                    </a>

                    <a href="{{ route('attendance.index') }}" 
                       class="flex items-center gap-3 px-3 py-2.5 rounded-2xl font-semibold text-xs transition-all {{ request()->routeIs('attendance.index') ? 'bg-[#0071e3] text-white shadow-[0_4px_14px_rgba(0,113,227,0.35)]' : 'text-slate-600 hover:bg-slate-100/80 hover:text-slate-900' }}">
                        <div class="w-7 h-7 rounded-xl flex items-center justify-center {{ request()->routeIs('attendance.index') ? 'bg-white/20 text-white' : 'bg-emerald-50 text-[#34c759]' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        </div>
                        <span class="text-sm">Daily Attendance</span>
                    </a>

                    <a href="{{ route('attendance.monthlyGrid') }}" 
                       class="flex items-center gap-3 px-3 py-2.5 rounded-2xl font-semibold text-xs transition-all {{ request()->routeIs('attendance.monthlyGrid') ? 'bg-[#0071e3] text-white shadow-[0_4px_14px_rgba(0,113,227,0.35)]' : 'text-slate-600 hover:bg-slate-100/80 hover:text-slate-900' }}">
                        <div class="w-7 h-7 rounded-xl flex items-center justify-center {{ request()->routeIs('attendance.monthlyGrid') ? 'bg-white/20 text-white' : 'bg-teal-50 text-[#30b0c7]' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                        </div>
                        <span class="text-sm">Attendance Matrix</span>
                    </a>
                </div>

                <!-- Team & Analytics -->
                <div class="space-y-1">
                    <div class="px-3 text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">Team & Analytics</div>

                    @if(auth()->user()->isAdmin())
                    <a href="{{ route('employees.index') }}" 
                       class="flex items-center gap-3 px-3 py-2.5 rounded-2xl font-semibold text-xs transition-all {{ request()->routeIs('employees.*') ? 'bg-[#0071e3] text-white shadow-[0_4px_14px_rgba(0,113,227,0.35)]' : 'text-slate-600 hover:bg-slate-100/80 hover:text-slate-900' }}">
                        <div class="w-7 h-7 rounded-xl flex items-center justify-center {{ request()->routeIs('employees.*') ? 'bg-white/20 text-white' : 'bg-indigo-50 text-[#5856d6]' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                        </div>
                        <span class="text-sm">Employees</span>
                    </a>

                    <a href="{{ route('departments.index') }}" 
                       class="flex items-center gap-3 px-3 py-2.5 rounded-2xl font-semibold text-xs transition-all {{ request()->routeIs('departments.*') ? 'bg-[#0071e3] text-white shadow-[0_4px_14px_rgba(0,113,227,0.35)]' : 'text-slate-600 hover:bg-slate-100/80 hover:text-slate-900' }}">
                        <div class="w-7 h-7 rounded-xl flex items-center justify-center {{ request()->routeIs('departments.*') ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-700' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                        </div>
                        <span class="text-sm">Departments</span>
                    </a>
                    @endif

                    <a href="{{ route('leaves.index') }}" 
                       class="flex items-center justify-between px-3 py-2.5 rounded-2xl font-semibold text-xs transition-all {{ request()->routeIs('leaves.*') ? 'bg-[#0071e3] text-white shadow-[0_4px_14px_rgba(0,113,227,0.35)]' : 'text-slate-600 hover:bg-slate-100/80 hover:text-slate-900' }}">
                        <div class="flex items-center gap-3">
                            <div class="w-7 h-7 rounded-xl flex items-center justify-center {{ request()->routeIs('leaves.*') ? 'bg-white/20 text-white' : 'bg-rose-50 text-[#ff3b30]' }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            </div>
                            <span class="text-sm">Leave Center</span>
                        </div>
                        @php
                            $pendingLeavesCount = \App\Models\LeaveRequest::where('status', 'pending')->count();
                        @endphp
                        @if($pendingLeavesCount > 0 && auth()->user()->isManager())
                            <span class="px-2 py-0.5 text-[10px] font-extrabold bg-[#ff3b30] text-white rounded-full">{{ $pendingLeavesCount }}</span>
                        @endif
                    </a>

                    <a href="{{ route('holidays.index') }}" 
                       class="flex items-center gap-3 px-3 py-2.5 rounded-2xl font-semibold text-xs transition-all {{ request()->routeIs('holidays.*') ? 'bg-[#0071e3] text-white shadow-[0_4px_14px_rgba(0,113,227,0.35)]' : 'text-slate-600 hover:bg-slate-100/80 hover:text-slate-900' }}">
                        <div class="w-7 h-7 rounded-xl flex items-center justify-center {{ request()->routeIs('holidays.*') ? 'bg-white/20 text-white' : 'bg-emerald-50 text-[#34c759]' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path></svg>
                        </div>
                        <span class="text-sm">Holidays</span>
                    </a>

                    @if(auth()->user()->isManager())
                    <a href="{{ route('payroll.index') }}" 
                       class="flex items-center gap-3 px-3 py-2.5 rounded-2xl font-semibold text-xs transition-all {{ request()->routeIs('payroll.*') ? 'bg-[#0071e3] text-white shadow-[0_4px_14px_rgba(0,113,227,0.35)]' : 'text-slate-600 hover:bg-slate-100/80 hover:text-slate-900' }}">
                        <div class="w-7 h-7 rounded-xl flex items-center justify-center {{ request()->routeIs('payroll.*') ? 'bg-white/20 text-white' : 'bg-emerald-50 text-[#34c759]' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <span class="text-sm">Payroll & Salary</span>
                    </a>
                    @endif

                    @if(auth()->user()->isEmployee())
                    <a href="{{ route('payroll.myPayslips') }}" 
                       class="flex items-center gap-3 px-3 py-2.5 rounded-2xl font-semibold text-xs transition-all {{ request()->routeIs('payroll.myPayslips') ? 'bg-[#0071e3] text-white shadow-[0_4px_14px_rgba(0,113,227,0.35)]' : 'text-slate-600 hover:bg-slate-100/80 hover:text-slate-900' }}">
                        <div class="w-7 h-7 rounded-xl flex items-center justify-center {{ request()->routeIs('payroll.myPayslips') ? 'bg-white/20 text-white' : 'bg-emerald-50 text-[#34c759]' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        </div>
                        <span class="text-sm">My Payslips</span>
                    </a>
                    @endif

                    @if(auth()->user()->isManager())
                    <a href="{{ route('performance.index') }}" 
                       class="flex items-center gap-3 px-3 py-2.5 rounded-2xl font-semibold text-xs transition-all {{ request()->routeIs('performance.*') ? 'bg-[#0071e3] text-white shadow-[0_4px_14px_rgba(0,113,227,0.35)]' : 'text-slate-600 hover:bg-slate-100/80 hover:text-slate-900' }}">
                        <div class="w-7 h-7 rounded-xl flex items-center justify-center {{ request()->routeIs('performance.*') ? 'bg-white/20 text-white' : 'bg-amber-50 text-[#ff9500]' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path></svg>
                        </div>
                        <span class="text-sm">Performance Scorecard</span>
                    </a>

                    <a href="{{ route('reports.index') }}" 
                       class="flex items-center gap-3 px-3 py-2.5 rounded-2xl font-semibold text-xs transition-all {{ request()->routeIs('reports.*') ? 'bg-[#0071e3] text-white shadow-[0_4px_14px_rgba(0,113,227,0.35)]' : 'text-slate-600 hover:bg-slate-100/80 hover:text-slate-900' }}">
                        <div class="w-7 h-7 rounded-xl flex items-center justify-center {{ request()->routeIs('reports.*') ? 'bg-white/20 text-white' : 'bg-blue-50 text-[#0071e3]' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        </div>
                        <span class="text-sm">Reports & Export</span>
                    </a>
                    @endif
                </div>

                <!-- Preferences -->
                @if(auth()->user()->isAdmin())
                <div class="space-y-1">
                    <div class="px-3 text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">Preferences</div>

                    <a href="{{ route('categories.index') }}" 
                       class="flex items-center gap-3 px-3 py-2.5 rounded-2xl font-semibold text-xs transition-all {{ request()->routeIs('categories.*') ? 'bg-[#0071e3] text-white shadow-[0_4px_14px_rgba(0,113,227,0.35)]' : 'text-slate-600 hover:bg-slate-100/80 hover:text-slate-900' }}">
                        <div class="w-7 h-7 rounded-xl flex items-center justify-center {{ request()->routeIs('categories.*') ? 'bg-white/20 text-white' : 'bg-pink-50 text-[#ff2d55]' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                        </div>
                        <span class="text-sm">Work Categories</span>
                    </a>

                    @if(auth()->user()->isSuperAdmin())
                    <a href="{{ route('users.index') }}" 
                       class="flex items-center gap-3 px-3 py-2.5 rounded-2xl font-semibold text-xs transition-all {{ request()->routeIs('users.*') ? 'bg-[#0071e3] text-white shadow-[0_4px_14px_rgba(0,113,227,0.35)]' : 'text-slate-600 hover:bg-slate-100/80 hover:text-slate-900' }}">
                        <div class="w-7 h-7 rounded-xl flex items-center justify-center {{ request()->routeIs('users.*') ? 'bg-white/20 text-white' : 'bg-purple-50 text-[#af52de]' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        </div>
                        <span class="text-sm">User & Roles</span>
                    </a>

                    <a href="{{ route('audit-logs.index') }}" 
                       class="flex items-center gap-3 px-3 py-2.5 rounded-2xl font-semibold text-xs transition-all {{ request()->routeIs('audit-logs.*') ? 'bg-[#0071e3] text-white shadow-[0_4px_14px_rgba(0,113,227,0.35)]' : 'text-slate-600 hover:bg-slate-100/80 hover:text-slate-900' }}">
                        <div class="w-7 h-7 rounded-xl flex items-center justify-center {{ request()->routeIs('audit-logs.*') ? 'bg-white/20 text-white' : 'bg-amber-50 text-[#ff9500]' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <span class="text-sm">Audit Trail</span>
                    </a>

                    <a href="{{ route('settings.index') }}" 
                       class="flex items-center gap-3 px-3 py-2.5 rounded-2xl font-semibold text-xs transition-all {{ request()->routeIs('settings.*') ? 'bg-[#0071e3] text-white shadow-[0_4px_14px_rgba(0,113,227,0.35)]' : 'text-slate-600 hover:bg-slate-100/80 hover:text-slate-900' }}">
                        <div class="w-7 h-7 rounded-xl flex items-center justify-center {{ request()->routeIs('settings.*') ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-700' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path></svg>
                        </div>
                        <span class="text-sm">Settings</span>
                    </a>
                    @endif
                </div>
                @endif

            </div>

            <!-- Profile User Footer -->
            <div class="p-4 border-t border-slate-200/80 bg-slate-50/50">
                <div class="flex items-center gap-3 p-2 rounded-2xl bg-white border border-slate-200/90 shadow-2xs">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-[#0071e3] to-[#5856d6] flex items-center justify-center font-bold text-white uppercase text-xs shadow-xs">
                        {{ substr(auth()->user()->name, 0, 2) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-xs font-bold truncate text-slate-900">{{ auth()->user()->name }}</div>
                        <div class="text-[11px] text-slate-400 truncate capitalize">{{ str_replace('_', ' ', auth()->user()->role) }}</div>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Workspace -->
        <div class="flex-1 flex flex-col min-w-0 h-full overflow-hidden">
            
            <!-- Apple-style Top Bar -->
            <header class="h-16 sm:h-18 glass-nav px-3 sm:px-8 flex items-center justify-between shrink-0 border-b border-slate-200/80 z-30">
                
                <!-- Left: Mobile Toggle & Spotlight Search Input -->
                <div class="flex items-center gap-2 sm:gap-4">
                    <button @click="sidebarOpen = true" class="p-2.5 rounded-xl text-slate-600 hover:bg-slate-100 md:hidden" title="Open Navigation">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    </button>

                    <!-- Mobile 1-Tap Search Icon -->
                    <button @click="searchOpen = true" type="button" class="sm:hidden p-2.5 rounded-xl text-slate-600 hover:bg-slate-100" title="Quick Search">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </button>

                    <!-- Spotlight Search Trigger (Desktop) -->
                    <button @click="searchOpen = true" 
                            type="button" 
                            class="hidden sm:flex items-center gap-3 px-4 py-2 bg-slate-100/90 hover:bg-slate-200/80 text-slate-500 rounded-full text-xs font-medium border border-slate-200/80 transition-all w-64 md:w-80 shadow-2xs">
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        <span class="flex-1 text-left text-xs text-slate-500">Search anything...</span>
                        <kbd class="hidden md:inline-block px-2 py-0.5 text-[10px] font-semibold bg-white text-slate-600 rounded-md border border-slate-200 shadow-2xs">⌘K</kbd>
                    </button>
                </div>

                <!-- Right Controls: Date, Fast Work CTA, Notifications & Profile -->
                <div class="flex items-center gap-3 sm:gap-4">
                    
                    <!-- Date badge -->
                    <div class="hidden lg:flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-slate-100 border border-slate-200 text-xs font-semibold text-slate-700">
                        <span class="w-2 h-2 rounded-full bg-[#34c759]"></span>
                        <span>{{ now()->format('D, d M Y') }}</span>
                    </div>

                    @php
                        $headerUser = auth()->user();
                        $showAttendanceWidget = $headerUser && ! $headerUser->isSuperAdmin();
                        $headerEmp = $showAttendanceWidget ? ($headerUser->employee ?: \App\Models\Employee::where('user_id', $headerUser->id)->orWhere('email', $headerUser->email)->first()) : null;
                        $headerAtt = $headerEmp ? \App\Models\DailyAttendance::where('employee_id', $headerEmp->id)->whereDate('date', now()->format('Y-m-d'))->first() : null;
                    @endphp

                    @if($showAttendanceWidget && $headerEmp)
                        @if($headerAtt && $headerAtt->check_in && !$headerAtt->check_out)
                            <!-- On Duty Pill -->
                            <div class="hidden sm:flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-50 border border-emerald-200 text-xs font-semibold text-emerald-800 shadow-2xs">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                <span class="font-mono text-xs font-bold">{{ \Carbon\Carbon::parse(now()->format('Y-m-d').' '.$headerAtt->check_in)->format('h:i A') }}</span>
                                <form method="POST" action="{{ route('attendance.clockOut') }}" @submit="$refs.headerClockOutTime.value = new Date().toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit', hour12: false })" class="inline">
                                    @csrf
                                    <input type="hidden" name="client_time" x-ref="headerClockOutTime" value="">
                                    <button type="submit" class="ml-1 px-2 py-0.5 rounded-full bg-[#ff3b30] hover:bg-[#e0342b] text-white text-[10px] font-bold transition cursor-pointer" title="Clock-Out">
                                        Out
                                    </button>
                                </form>
                            </div>
                        @elseif($headerAtt && $headerAtt->check_out)
                            <!-- Shift Completed Pill -->
                            <div class="hidden sm:flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-100 border border-slate-200 text-xs font-semibold text-slate-600">
                                <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                <span class="text-[11px] font-bold">Shift Done</span>
                            </div>
                        @else
                            <!-- Not Clocked In Quick CTA -->
                            <form method="POST" action="{{ route('attendance.clockIn') }}" @submit="$refs.headerClockInTime.value = new Date().toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit', hour12: false })" class="hidden sm:inline-flex">
                                @csrf
                                <input type="hidden" name="client_time" x-ref="headerClockInTime" value="">
                                <button type="submit" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-[#0071e3] hover:bg-[#0062c4] text-white text-xs font-bold shadow-2xs transition active:scale-95 cursor-pointer">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    <span>Clock In</span>
                                </button>
                            </form>
                        @endif
                    @endif

                    <!-- Live Task Stopwatch Widget -->
                    <div x-data="liveStopwatch()" class="hidden md:flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-slate-100 border border-slate-200/90 shadow-2xs text-xs">
                        <div class="flex items-center gap-1.5 font-mono font-bold text-slate-800 tracking-wider">
                            <span class="w-2 h-2 rounded-full" :class="isRunning ? 'bg-rose-500 animate-pulse' : 'bg-slate-400'"></span>
                            <span x-text="formattedTime">00:00:00</span>
                        </div>
                        
                        <div class="flex items-center gap-1">
                            <button type="button" @click="toggle()" 
                                    :title="isRunning ? 'Pause Timer' : 'Start Timer'"
                                    class="p-1 rounded-full text-slate-600 hover:text-slate-900 hover:bg-white transition cursor-pointer">
                                <svg x-show="!isRunning" class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                <svg x-show="isRunning" class="w-3.5 h-3.5 text-rose-500" fill="currentColor" viewBox="0 0 24 24" style="display: none;"><path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/></svg>
                            </button>
                            <button type="button" @click="reset()" title="Reset Timer" x-show="seconds > 0"
                                    class="p-1 rounded-full text-slate-400 hover:text-rose-600 hover:bg-white transition cursor-pointer">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            </button>
                            <a href="{{ route('work-entries.index') }}" x-show="seconds > 60" title="Log in Work History" 
                               class="px-2 py-0.5 rounded-md bg-[#0071e3] text-white text-[10px] font-bold shadow-2xs">
                                Log
                            </a>
                        </div>
                    </div>

                    @if(auth()->user()->isManager())
                    <a href="{{ route('work-entries.batch') }}" 
                       class="hidden sm:inline-flex items-center gap-1.5 px-4 py-2 bg-[#0071e3] hover:bg-[#0077ed] text-white text-xs font-bold rounded-full shadow-[0_4px_12px_rgba(0,113,227,0.35)] transition-all hover:scale-[1.02] active:scale-[0.98]">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                        <span>+ Log Work</span>
                    </a>
                    @endif

                    <!-- Notifications Dropdown -->
                    @php
                        $todayStr = now()->format('Y-m-d');
                        $activeEmpCount = \App\Models\Employee::where('employment_status', 'active')->count();
                        $todayAttRecorded = \App\Models\DailyAttendance::whereDate('date', $todayStr)->count();
                        $missingAtt = max(0, $activeEmpCount - $todayAttRecorded);
                        $pendingLeavesCount = \App\Models\LeaveRequest::where('status', 'pending')->count();
                        $upcomingHol = \App\Models\Holiday::whereDate('date', '>=', $todayStr)->whereDate('date', '<=', now()->addDays(7)->format('Y-m-d'))->first();
                        $notifCount = ($missingAtt > 0 ? 1 : 0) + ($pendingLeavesCount > 0 ? 1 : 0) + ($upcomingHol ? 1 : 0);
                    @endphp
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="relative p-2 rounded-full text-slate-500 hover:text-slate-900 hover:bg-slate-100 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                            @if($notifCount > 0)
                            <span class="absolute top-1.5 right-1.5 w-2.5 h-2.5 bg-[#ff3b30] rounded-full ring-2 ring-white"></span>
                            @endif
                        </button>

                        <div x-show="open" 
                             @click.outside="open = false" 
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             class="absolute right-0 mt-2 w-80 sm:w-96 glass-panel p-3 z-50 shadow-2xl" 
                             style="display: none;">
                            <div class="px-3 py-2 border-b border-slate-100 flex items-center justify-between">
                                <span class="font-bold text-xs text-slate-800">Alerts & Reminders</span>
                                <span class="text-[10px] px-2 py-0.5 rounded-full bg-blue-100 text-[#0071e3] font-bold">{{ $notifCount }}</span>
                            </div>
                            <div class="max-h-72 overflow-y-auto divide-y divide-slate-100 text-xs">
                                @if($missingAtt > 0 && auth()->user()->isManager())
                                <a href="{{ route('attendance.index') }}" class="p-3 flex gap-3 hover:bg-slate-50 rounded-2xl transition">
                                    <div class="w-8 h-8 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center shrink-0">⚠️</div>
                                    <div>
                                        <div class="font-bold text-slate-800">Attendance Pending</div>
                                        <div class="text-slate-500 text-[11px] mt-0.5">{{ $missingAtt }} employee(s) attendance not yet marked today.</div>
                                    </div>
                                </a>
                                @endif

                                @if($pendingLeavesCount > 0 && auth()->user()->isManager())
                                <a href="{{ route('leaves.index', ['status' => 'pending']) }}" class="p-3 flex gap-3 hover:bg-slate-50 rounded-2xl transition">
                                    <div class="w-8 h-8 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center shrink-0">📋</div>
                                    <div>
                                        <div class="font-bold text-slate-800">Leave Approvals Pending</div>
                                        <div class="text-slate-500 text-[11px] mt-0.5">{{ $pendingLeavesCount }} leave request(s) awaiting review.</div>
                                    </div>
                                </a>
                                @endif

                                @if($upcomingHol)
                                <a href="{{ route('holidays.index') }}" class="p-3 flex gap-3 hover:bg-slate-50 rounded-2xl transition">
                                    <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0">🎉</div>
                                    <div>
                                        <div class="font-bold text-slate-800">Upcoming Holiday: {{ $upcomingHol->name }}</div>
                                        <div class="text-slate-500 text-[11px] mt-0.5">{{ $upcomingHol->date->format('d M, Y') }}</div>
                                    </div>
                                </a>
                                @endif

                                @if($notifCount == 0)
                                <div class="p-6 text-center text-slate-400 text-xs">
                                    No pending alerts.
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Profile Menu Dropdown -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center gap-2 p-1 rounded-full hover:bg-slate-100 transition cursor-pointer">
                            <div class="w-9 h-9 rounded-full bg-gradient-to-tr from-[#0071e3] to-[#5856d6] flex items-center justify-center text-white font-black text-xs shadow-xs">
                                {{ substr(auth()->user()->name, 0, 2) }}
                            </div>
                        </button>

                        <div x-show="open" 
                             @click.outside="open = false" 
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             class="absolute right-0 mt-2 w-56 glass-panel p-2 z-50 text-xs shadow-2xl" 
                             style="display: none;">
                            <div class="px-3 py-2 border-b border-slate-100">
                                <div class="font-extrabold text-slate-900">{{ auth()->user()->name }}</div>
                                <div class="text-[11px] text-slate-400 truncate">{{ auth()->user()->email }}</div>
                            </div>

                            <a href="{{ route('profile') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl hover:bg-slate-100 text-slate-700 transition">
                                <span>Account & Password</span>
                            </a>

                            @if(auth()->user()->isSuperAdmin())
                            <a href="{{ route('settings.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl hover:bg-slate-100 text-slate-700 transition">
                                <span>Settings</span>
                            </a>
                            @endif

                            <div class="my-1 border-t border-slate-100"></div>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full text-left flex items-center gap-2.5 px-3 py-2 text-[#ff3b30] hover:bg-rose-50 rounded-xl transition cursor-pointer font-bold">
                                    <span>Sign Out</span>
                                </button>
                            </form>
                        </div>
                    </div>

                </div>
            </header>

            <!-- Main Workspace Body -->
            <main class="flex-1 overflow-y-auto p-3 sm:p-6 lg:p-8 pb-24 md:pb-8 touch-scroll">
                
                <!-- Flash Alerts (Animated Auto-Dismissing Toasts) -->
                @if(session('success'))
                <div x-data="{ show: true }" 
                     x-init="setTimeout(() => show = false, 5000)"
                     x-show="show" 
                     x-transition:enter="transition ease-out duration-300 transform"
                     x-transition:enter-start="opacity-0 -translate-y-2 scale-98"
                     x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                     x-transition:leave="transition ease-in duration-200 transform"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     class="mb-6 p-4 rounded-2xl bg-emerald-500 text-white shadow-lg flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <span class="w-6 h-6 rounded-full bg-white/20 flex items-center justify-center font-black text-xs shrink-0">✓</span>
                        <span class="font-bold text-xs tracking-tight">{{ session('success') }}</span>
                    </div>
                    <button @click="show = false" class="text-white/80 hover:text-white text-xs">✕</button>
                </div>
                @endif

                @if(session('error'))
                <div x-data="{ show: true }" 
                     x-show="show" 
                     class="mb-6 p-4 rounded-2xl bg-rose-500 text-white shadow-lg flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <span class="w-6 h-6 rounded-full bg-white/20 flex items-center justify-center font-black text-xs shrink-0">✕</span>
                        <span class="font-bold text-xs tracking-tight">{{ session('error') }}</span>
                    </div>
                    <button @click="show = false" class="text-white/80 hover:text-white text-xs">✕</button>
                </div>
                @endif

                @if($errors->any())
                <div class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs">
                    <div class="flex items-center gap-2 font-bold">
                        <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <span>Please correct the errors below:</span>
                    </div>
                    <ul class="list-disc list-inside space-y-0.5 ml-2 mt-2 font-medium">
                        @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                @yield('content')
            </main>

            <!-- Native Mobile Bottom Navigation Bar (Phone & Handhelds) -->
            <nav class="md:hidden fixed bottom-0 inset-x-0 z-40 bg-white/95 backdrop-blur-xl border-t border-slate-200/90 mobile-bottom-nav shadow-[0_-4px_24px_rgba(0,0,0,0.06)]">
                <div class="grid grid-cols-5 h-16 items-center px-1">
                    <!-- 1. Home -->
                    <a href="{{ route('dashboard') }}" 
                       class="flex flex-col items-center justify-center py-1 rounded-xl transition-all {{ request()->routeIs('dashboard') ? 'text-[#0071e3] font-bold' : 'text-slate-500 hover:text-slate-800' }}">
                        <div class="p-1 rounded-xl {{ request()->routeIs('dashboard') ? 'bg-blue-50 text-[#0071e3]' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                        </div>
                        <span class="text-[10px] tracking-tight mt-0.5">Home</span>
                    </a>

                    <!-- 2. Tasks -->
                    <a href="{{ route('todos.index') }}" 
                       class="relative flex flex-col items-center justify-center py-1 rounded-xl transition-all {{ request()->routeIs('todos.*') ? 'text-[#0071e3] font-bold' : 'text-slate-500 hover:text-slate-800' }}">
                        <div class="relative p-1 rounded-xl {{ request()->routeIs('todos.*') ? 'bg-blue-50 text-[#0071e3]' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            @if(isset($userPendingTodosCount) && $userPendingTodosCount > 0)
                                <span class="absolute -top-1 -right-1.5 min-w-[16px] h-4 px-1 rounded-full bg-[#ff9500] text-white text-[9px] font-black flex items-center justify-center ring-2 ring-white">
                                    {{ $userPendingTodosCount > 99 ? '99+' : $userPendingTodosCount }}
                                </span>
                            @endif
                        </div>
                        <span class="text-[10px] tracking-tight mt-0.5">Tasks</span>
                    </a>

                    <!-- 3. Work Logs -->
                    <a href="{{ auth()->user()->isManager() ? route('work-entries.batch') : route('work-entries.index') }}" 
                       class="flex flex-col items-center justify-center py-1 rounded-xl transition-all {{ request()->routeIs('work-entries.*') ? 'text-[#0071e3] font-bold' : 'text-slate-500 hover:text-slate-800' }}">
                        <div class="p-1 rounded-xl {{ request()->routeIs('work-entries.*') ? 'bg-blue-50 text-[#0071e3]' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                        </div>
                        <span class="text-[10px] tracking-tight mt-0.5">Work</span>
                    </a>

                    <!-- 4. Attendance -->
                    <a href="{{ route('attendance.index') }}" 
                       class="flex flex-col items-center justify-center py-1 rounded-xl transition-all {{ request()->routeIs('attendance.*') ? 'text-[#0071e3] font-bold' : 'text-slate-500 hover:text-slate-800' }}">
                        <div class="p-1 rounded-xl {{ request()->routeIs('attendance.*') ? 'bg-blue-50 text-[#0071e3]' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        </div>
                        <span class="text-[10px] tracking-tight mt-0.5">Attendance</span>
                    </a>

                    <!-- 5. Menu Drawer Trigger -->
                    <button @click="sidebarOpen = !sidebarOpen" 
                            type="button"
                            class="flex flex-col items-center justify-center py-1 rounded-xl text-slate-500 hover:text-slate-800 transition-all cursor-pointer">
                        <div class="p-1 rounded-xl">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                        </div>
                        <span class="text-[10px] tracking-tight mt-0.5">Menu</span>
                    </button>
                </div>
            </nav>

        </div>
    </div>

    <!-- Spotlight Search Modal (⌘K) -->
    <div x-show="searchOpen" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         @keydown.escape.window="searchOpen = false"
         class="fixed inset-0 z-50 flex items-start justify-center pt-24 px-4 bg-black/40 backdrop-blur-xs"
         style="display: none;">
        
        <div @click.outside="searchOpen = false" class="w-full max-w-2xl glass-panel overflow-hidden shadow-2xl">
            <div class="p-4 border-b border-slate-200 flex items-center gap-3 bg-slate-50/50">
                <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                <input type="text" 
                       x-model="searchQuery" 
                       @input.debounce.250ms="doSearch()"
                       placeholder="Spotlight Search: Employee, Category, Date..." 
                       class="w-full bg-transparent border-none text-sm focus:outline-hidden text-slate-900 placeholder-slate-400 font-medium">
                <kbd class="px-2 py-0.5 text-[10px] bg-slate-200 text-slate-600 rounded-md">ESC</kbd>
            </div>

            <div class="max-h-96 overflow-y-auto p-4 space-y-4 text-xs">
                <template x-if="isSearching">
                    <div class="py-8 text-center text-slate-400">Searching...</div>
                </template>

                <template x-if="!isSearching && searchResults.employees.length === 0 && searchResults.categories.length === 0 && searchResults.work_entries.length === 0 && searchQuery.length >= 2">
                    <div class="py-8 text-center text-slate-400">No results found for "<span x-text="searchQuery"></span>"</div>
                </template>

                <template x-if="searchResults.employees.length > 0">
                    <div>
                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Employees</div>
                        <div class="space-y-1">
                            <template x-for="emp in searchResults.employees" :key="emp.id">
                                <a :href="emp.url" class="flex items-center gap-3 p-2.5 rounded-2xl hover:bg-slate-100 transition">
                                    <img :src="emp.avatar" class="w-8 h-8 rounded-full object-cover">
                                    <div>
                                        <div class="font-bold text-slate-900" x-text="emp.title"></div>
                                        <div class="text-[11px] text-slate-500" x-text="emp.subtitle"></div>
                                    </div>
                                </a>
                            </template>
                        </div>
                    </div>
                </template>

                <template x-if="searchResults.categories.length > 0">
                    <div>
                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Work Categories</div>
                        <div class="grid grid-cols-2 gap-2">
                            <template x-for="cat in searchResults.categories" :key="cat.id">
                                <a :href="cat.url" class="flex items-center gap-2.5 p-2 rounded-2xl hover:bg-slate-100 transition border border-slate-200">
                                    <span class="w-3 h-3 rounded-full shrink-0" :style="`background-color: ${cat.color}`"></span>
                                    <div class="font-semibold text-slate-800 truncate" x-text="cat.title"></div>
                                </a>
                            </template>
                        </div>
                    </div>
                </template>

                <template x-if="searchResults.work_entries.length > 0">
                    <div>
                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Work Entries</div>
                        <div class="space-y-1">
                            <template x-for="item in searchResults.work_entries" :key="item.id">
                                <a :href="item.url" class="flex items-center justify-between p-2.5 rounded-2xl hover:bg-slate-100 transition">
                                    <div>
                                        <div class="font-bold text-slate-900" x-text="item.title"></div>
                                        <div class="text-[11px] text-slate-500" x-text="item.subtitle"></div>
                                    </div>
                                    <span class="text-[#0071e3] font-bold text-xs">View →</span>
                                </a>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
