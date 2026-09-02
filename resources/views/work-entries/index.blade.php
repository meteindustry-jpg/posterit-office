@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold font-display text-slate-900 dark:text-white tracking-tight">
                Daily Work History & Logs
            </h1>
            <p class="text-xs text-slate-500">
                Explore detailed daily outputs, filter by employee, date range, department, and work category.
            </p>
        </div>

        <div class="flex items-center gap-3">
            @if(auth()->user()->isManager())
            <a href="{{ route('work-entries.batch') }}" 
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-[#0071e3] hover:bg-[#0062c4] text-white text-xs font-bold rounded-xl shadow-xs transition active:scale-95 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span>+ Batch Entry</span>
            </a>
            @endif
        </div>
    </div>

    <!-- Filter Form Toolbar -->
    <div class="p-5 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-xs space-y-4">
        <form method="GET" action="{{ route('work-entries.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 text-xs">
            <div>
                <label class="block font-bold text-slate-500 uppercase mb-1">Date</label>
                <input type="date" name="date" value="{{ $date }}" 
                       class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-medium focus:ring-2 focus:ring-[#0071e3]">
            </div>

            <div>
                <label class="block font-bold text-slate-500 uppercase mb-1">Employee</label>
                <select name="employee_id" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-medium focus:ring-2 focus:ring-indigo-500">
                    <option value="">All Employees</option>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}" {{ $employeeId == $emp->id ? 'selected' : '' }}>{{ $emp->name }} ({{ $emp->employee_code }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block font-bold text-slate-500 uppercase mb-1">Category</label>
                <select name="category_id" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-medium focus:ring-2 focus:ring-indigo-500">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ $categoryId == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block font-bold text-slate-500 uppercase mb-1">Department</label>
                <select name="department_id" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-medium focus:ring-2 focus:ring-indigo-500">
                    <option value="">All Departments</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ $departmentId == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="w-full py-2.5 px-4 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-xl transition cursor-pointer">
                    Apply Filter
                </button>
                <a href="{{ route('work-entries.index') }}" class="py-2.5 px-3 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 font-semibold rounded-xl text-center hover:bg-slate-200 transition">
                    Reset
                </a>
            </div>
        </form>

        <!-- Summary on Filter -->
        <div class="pt-3 border-t border-slate-100 dark:border-slate-800/80 flex flex-wrap items-center justify-between gap-3 text-xs">
            <div class="text-slate-500">
                Found <strong>{{ $entries->total() }}</strong> entries in this view
            </div>
            @if($date)
            <div class="flex items-center gap-3">
                <span class="text-slate-500">Output on {{ $date }}: <strong class="text-indigo-600 dark:text-indigo-400 text-sm font-extrabold">{{ $totalWorksOnDate }} tasks</strong></span>
                <span class="text-slate-500">Active logged: <strong>{{ $workedEmployeesCount }}/{{ $activeEmployeesCount }}</strong></span>
            </div>
            @endif
        </div>
    </div>

    <!-- Work Entries Table -->
    <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800 text-slate-400 uppercase tracking-wider text-[10px]">
                        <th class="py-3.5 px-4 font-bold">ID</th>
                        <th class="py-3.5 px-4 font-bold">Date</th>
                        <th class="py-3.5 px-4 font-bold">Employee</th>
                        <th class="py-3.5 px-4 font-bold">Department</th>
                        <th class="py-3.5 px-4 font-bold">Work Category</th>
                        <th class="py-3.5 px-4 font-bold text-center">Quantity</th>
                        <th class="py-3.5 px-4 font-bold">Remarks / Deliverables</th>
                        <th class="py-3.5 px-4 font-bold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                    @forelse($entries as $entry)
                    <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/30 transition">
                        <td class="py-3 px-4 font-mono text-slate-400 text-[11px]">#{{ $entry->id }}</td>
                        <td class="py-3 px-4 font-semibold text-slate-700 dark:text-slate-300 whitespace-nowrap">{{ $entry->date->format('d M, Y') }}</td>
                        <td class="py-3 px-4">
                            <div class="flex items-center gap-2.5">
                                <img src="{{ $entry->employee->photo_url }}" class="w-7 h-7 rounded-full object-cover">
                                <div>
                                    <div class="font-bold text-slate-900 dark:text-white">{{ $entry->employee->name }}</div>
                                    <div class="text-[10px] text-slate-400">{{ $entry->employee->employee_code }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="py-3 px-4 text-slate-500 whitespace-nowrap">{{ $entry->employee->department->name ?? 'N/A' }}</td>
                        <td class="py-3 px-4">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-semibold" style="background-color: {{ $entry->category->color }}20; color: {{ $entry->category->color }}">
                                <span class="w-1.5 h-1.5 rounded-full" style="background-color: {{ $entry->category->color }}"></span>
                                {{ $entry->category->name }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-center font-extrabold text-sm text-slate-900 dark:text-white">{{ $entry->quantity }}</td>
                        <td class="py-3 px-4 text-slate-600 dark:text-slate-400 max-w-sm">{{ $entry->remarks ?? '-' }}</td>
                        <td class="py-3 px-4 text-right whitespace-nowrap">
                            <div class="flex items-center justify-end gap-1.5">
                                <a href="{{ route('work-entries.edit', $entry) }}" class="p-1.5 rounded-lg text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 dark:hover:bg-slate-800 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                </a>

                                <form method="POST" action="{{ route('work-entries.destroy', $entry) }}" onsubmit="return confirm('Delete this work entry?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition cursor-pointer">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-10 text-center text-slate-400">No work entries matched your filter.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($entries->hasPages())
        <div class="p-4 border-t border-slate-200 dark:border-slate-800">
            {{ $entries->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
