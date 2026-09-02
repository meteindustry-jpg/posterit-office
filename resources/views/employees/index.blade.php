@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold font-display text-slate-900 dark:text-white tracking-tight">
                Employee Management
            </h1>
            <p class="text-xs text-slate-500">
                Directory of team members, designations, departments, and status.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('employees.create') }}" 
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-[#0071e3] hover:bg-[#0062c4] text-white text-xs font-bold rounded-xl shadow-xs transition active:scale-95 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                <span>+ Add New Employee</span>
            </a>
        </div>
    </div>

    <!-- Vibrant Dribbble / Arcade Style Employee Metric Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        
        <!-- Total Team (Cyan / Sky Blue Gradient) -->
        <a href="{{ route('employees.index') }}" 
           class="relative overflow-hidden p-5 rounded-2xl bg-gradient-to-br from-[#0284c7] via-[#0ea5e9] to-[#38bdf8] text-white shadow-[0_8px_22px_rgba(14,165,233,0.25)] hover:scale-[1.02] transition-all duration-200 flex flex-col justify-between group cursor-pointer">
            <!-- Large Watermark Illustration -->
            <svg class="absolute -right-3 -bottom-3 w-28 h-28 text-white/15 pointer-events-none transition-transform group-hover:scale-110 duration-300" fill="currentColor" viewBox="0 0 24 24">
                <path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/>
            </svg>

            <div class="relative z-10">
                <span class="text-[11px] font-extrabold uppercase tracking-wider text-white/90">Total Team</span>
                <div class="w-6 h-0.5 bg-white/30 rounded-full mt-1"></div>
            </div>
            <div class="mt-4 relative z-10">
                <div class="text-3xl font-black text-white tracking-tight">{{ $stats['total'] }}</div>
                <div class="text-[11px] text-white/85 font-medium mt-1">Registered members</div>
            </div>
        </a>

        <!-- Active Members (Emerald / Mint Gradient) -->
        <a href="{{ route('employees.index', ['status' => 'active']) }}" 
           class="relative overflow-hidden p-5 rounded-2xl bg-gradient-to-br from-[#059669] via-[#10b981] to-[#34d399] text-white shadow-[0_8px_22px_rgba(16,185,129,0.25)] hover:scale-[1.02] transition-all duration-200 flex flex-col justify-between group cursor-pointer">
            <!-- Large Watermark Illustration -->
            <svg class="absolute -right-3 -bottom-3 w-28 h-28 text-white/15 pointer-events-none transition-transform group-hover:scale-110 duration-300" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
            </svg>

            <div class="relative z-10">
                <span class="text-[11px] font-extrabold uppercase tracking-wider text-white/90">Active Members</span>
                <div class="w-6 h-0.5 bg-white/30 rounded-full mt-1"></div>
            </div>
            <div class="mt-4 relative z-10">
                <div class="text-3xl font-black text-white tracking-tight">{{ $stats['active'] }}</div>
                <div class="text-[11px] text-white/85 font-medium mt-1">Currently on duty</div>
            </div>
        </a>

        <!-- Inactive / Paused (Orange / Amber Gradient) -->
        <a href="{{ route('employees.index', ['status' => 'inactive']) }}" 
           class="relative overflow-hidden p-5 rounded-2xl bg-gradient-to-br from-[#f59e0b] via-[#f97316] to-[#fb923c] text-white shadow-[0_8px_22px_rgba(249,115,22,0.25)] hover:scale-[1.02] transition-all duration-200 flex flex-col justify-between group cursor-pointer">
            <!-- Large Watermark Illustration -->
            <svg class="absolute -right-3 -bottom-3 w-28 h-28 text-white/15 pointer-events-none transition-transform group-hover:scale-110 duration-300" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 14h-2v-2h2v2zm0-4h-2V7h2v5z"/>
            </svg>

            <div class="relative z-10">
                <span class="text-[11px] font-extrabold uppercase tracking-wider text-white/90">Inactive</span>
                <div class="w-6 h-0.5 bg-white/30 rounded-full mt-1"></div>
            </div>
            <div class="mt-4 relative z-10">
                <div class="text-3xl font-black text-white tracking-tight">{{ $stats['inactive'] }}</div>
                <div class="text-[11px] text-white/85 font-medium mt-1">Temporarily off-duty</div>
            </div>
        </a>

        <!-- Resigned (Violet / Purple Gradient) -->
        <a href="{{ route('employees.index', ['status' => 'resigned']) }}" 
           class="relative overflow-hidden p-5 rounded-2xl bg-gradient-to-br from-[#6366f1] via-[#7c3aed] to-[#8b5cf6] text-white shadow-[0_8px_22px_rgba(99,102,241,0.25)] hover:scale-[1.02] transition-all duration-200 flex flex-col justify-between group cursor-pointer">
            <!-- Large Watermark Illustration -->
            <svg class="absolute -right-3 -bottom-3 w-28 h-28 text-white/15 pointer-events-none transition-transform group-hover:scale-110 duration-300" fill="currentColor" viewBox="0 0 24 24">
                <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-2 10H7v-2h10v2z"/>
            </svg>

            <div class="relative z-10">
                <span class="text-[11px] font-extrabold uppercase tracking-wider text-white/90">Resigned</span>
                <div class="w-6 h-0.5 bg-white/30 rounded-full mt-1"></div>
            </div>
            <div class="mt-4 relative z-10">
                <div class="text-3xl font-black text-white tracking-tight">{{ $stats['resigned'] }}</div>
                <div class="text-[11px] text-white/85 font-medium mt-1">Archived records</div>
            </div>
        </a>

    </div>

    <!-- Filter Bar -->
    <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-xs">
        <form method="GET" action="{{ route('employees.index') }}" class="flex flex-wrap items-center justify-between gap-3 text-xs">
            <div class="flex flex-wrap items-center gap-3 flex-1 min-w-[280px]">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name, ID, email, designation..." 
                       class="px-4 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl w-72 focus:ring-2 focus:ring-indigo-500">
                
                <select name="department_id" onchange="this.form.submit()" class="px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-semibold">
                    <option value="">All Departments</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                    @endforeach
                </select>

                <select name="status" onchange="this.form.submit()" class="px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-semibold">
                    <option value="">All Statuses</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    <option value="resigned" {{ request('status') === 'resigned' ? 'selected' : '' }}>Resigned</option>
                </select>

                <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-xl transition cursor-pointer">
                    Search
                </button>
            </div>

            @if(request()->hasAny(['search', 'department_id', 'status']))
            <a href="{{ route('employees.index') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-700">
                Clear Filters ✕
            </a>
            @endif
        </form>
    </div>

    <!-- Employee Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($employees as $emp)
        <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-xs flex flex-col justify-between hover:shadow-md transition">
            <div>
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-3.5">
                        <img src="{{ $emp->photo_url }}" class="w-12 h-12 rounded-2xl object-cover ring-2 ring-indigo-500/20 shadow-xs">
                        <div>
                            <a href="{{ route('employees.show', $emp) }}" class="font-extrabold text-base text-slate-900 dark:text-white hover:text-indigo-600 transition font-display">
                                {{ $emp->name }}
                            </a>
                            <div class="text-xs text-slate-500 font-medium">{{ $emp->designation }}</div>
                        </div>
                    </div>

                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase
                        {{ $emp->employment_status === 'active' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' : '' }}
                        {{ $emp->employment_status === 'inactive' ? 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300' : '' }}
                        {{ $emp->employment_status === 'resigned' ? 'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300' : '' }}">
                        {{ $emp->employment_status }}
                    </span>
                </div>

                <div class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-800 space-y-2 text-xs text-slate-600 dark:text-slate-400">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400">Employee ID:</span>
                        <span class="font-mono font-bold text-slate-800 dark:text-slate-200">{{ $emp->employee_code }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400">Department:</span>
                        <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $emp->department->name ?? 'N/A' }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400">Email:</span>
                        <span class="truncate max-w-[170px]">{{ $emp->email }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400">Mobile:</span>
                        <span>{{ $emp->mobile_number ?? '-' }}</span>
                    </div>
                </div>
            </div>

            <!-- Card Action Footer -->
            <div class="mt-5 pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <a href="{{ route('employees.show', $emp) }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-500">
                    View Profile & Stats →
                </a>

                <div class="flex items-center gap-1">
                    <a href="{{ route('employees.edit', $emp) }}" class="p-1.5 rounded-lg text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 dark:hover:bg-slate-800">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                    </a>

                    <form method="POST" action="{{ route('employees.destroy', $emp) }}" onsubmit="return confirm('Delete employee {{ $emp->name }}?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full py-12 text-center text-slate-400">
            No employees found matching criteria.
        </div>
        @endforelse
    </div>

    @if($employees->hasPages())
    <div class="p-4 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800">
        {{ $employees->links() }}
    </div>
    @endif

</div>
@endsection
