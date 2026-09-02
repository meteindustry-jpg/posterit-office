@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{
    diffModalOpen: false,
    diffData: { title: '', oldVals: null, newVals: null },
    openDiff(log) {
        this.diffData = {
            title: `${log.module} - ${log.action} (#${log.id})`,
            oldVals: log.old_values,
            newVals: log.new_values
        };
        this.diffModalOpen = true;
    }
}">

    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold font-display text-slate-900 dark:text-white tracking-tight">
                System Audit Trail
            </h1>
            <p class="text-xs text-slate-500">
                Track every administrative action, user modification, attendance update, and deleted item with timestamps and IP records.
            </p>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-xs">
        <form method="GET" action="{{ route('audit-logs.index') }}" class="flex flex-wrap items-center justify-between gap-3 text-xs">
            <div class="flex flex-wrap items-center gap-3">
                <select name="user_id" onchange="this.form.submit()" class="px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-semibold">
                    <option value="">All Users</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                    @endforeach
                </select>

                <select name="module" onchange="this.form.submit()" class="px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-semibold">
                    <option value="">All Modules</option>
                    @foreach($modules as $m)
                        <option value="{{ $m }}" {{ request('module') === $m ? 'selected' : '' }}>{{ $m }}</option>
                    @endforeach
                </select>

                <select name="action" onchange="this.form.submit()" class="px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-semibold">
                    <option value="">All Actions</option>
                    @foreach($actions as $a)
                        <option value="{{ $a }}" {{ request('action') === $a ? 'selected' : '' }}>{{ $a }}</option>
                    @endforeach
                </select>

                <input type="date" name="date" value="{{ request('date') }}" onchange="this.form.submit()" class="px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-medium">
            </div>

            @if(request()->hasAny(['user_id', 'module', 'action', 'date']))
            <a href="{{ route('audit-logs.index') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-700">
                Clear Filters ✕
            </a>
            @endif
        </form>
    </div>

    <!-- Audit Table -->
    <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800 text-slate-400 uppercase tracking-wider text-[10px]">
                        <th class="py-3 px-4">Timestamp</th>
                        <th class="py-3 px-4">User</th>
                        <th class="py-3 px-4">Module</th>
                        <th class="py-3 px-4">Action</th>
                        <th class="py-3 px-4">Description</th>
                        <th class="py-3 px-4">IP Address</th>
                        <th class="py-3 px-4 text-right">Details</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                    @forelse($logs as $log)
                    <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/30 transition">
                        <td class="py-3 px-4 font-mono text-slate-500 whitespace-nowrap">{{ $log->created_at->format('d M Y, H:i:s') }}</td>
                        <td class="py-3 px-4">
                            @if($log->user)
                                <span class="font-bold text-slate-900 dark:text-white">{{ $log->user->name }}</span>
                                <span class="text-[10px] text-indigo-400 block">{{ $log->user->role }}</span>
                            @else
                                <span class="text-slate-400 font-medium italic">System</span>
                            @endif
                        </td>
                        <td class="py-3 px-4">
                            <span class="px-2 py-0.5 rounded-md font-semibold text-[11px] bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                {{ $log->module }}
                            </span>
                        </td>
                        <td class="py-3 px-4">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold uppercase
                                {{ $log->action === 'create' || $log->action === 'bulk_create' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' : '' }}
                                {{ $log->action === 'update' || $log->action === 'bulk_update' ? 'bg-indigo-100 text-indigo-800 dark:bg-indigo-950 dark:text-indigo-300' : '' }}
                                {{ $log->action === 'delete' ? 'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300' : '' }}
                                {{ $log->action === 'login' || $log->action === 'logout' ? 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300' : '' }}">
                                {{ $log->action }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-slate-700 dark:text-slate-300 font-medium max-w-sm">{{ $log->description }}</td>
                        <td class="py-3 px-4 font-mono text-slate-400 text-[11px]">{{ $log->ip_address ?? '-' }}</td>
                        <td class="py-3 px-4 text-right">
                            @if($log->old_values || $log->new_values)
                            <button type="button" @click="openDiff(@js($log))" class="text-indigo-600 hover:text-indigo-500 font-bold text-xs cursor-pointer">
                                Diff View →
                            </button>
                            @else
                                <span class="text-slate-400 text-[11px]">-</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-10 text-center text-slate-400">No audit logs found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
        <div class="p-4 border-t border-slate-200 dark:border-slate-800">
            {{ $logs->links() }}
        </div>
        @endif
    </div>

    <!-- Diff Viewer Modal -->
    <div x-show="diffModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-xs" style="display: none;">
        <div @click.outside="diffModalOpen = false" class="w-full max-w-2xl bg-white dark:bg-slate-900 rounded-3xl p-6 shadow-2xl border border-slate-200 dark:border-slate-800 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <h3 class="font-bold text-base text-slate-900 dark:text-white" x-text="diffData.title"></h3>
                <button @click="diffModalOpen = false" class="text-slate-400 hover:text-slate-600">✕</button>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                <div>
                    <h4 class="font-bold text-rose-500 uppercase text-[10px] mb-1">Old Values</h4>
                    <pre class="p-3 bg-slate-50 dark:bg-slate-800/80 rounded-xl overflow-x-auto font-mono text-[11px] text-slate-700 dark:text-slate-300 max-h-60" x-text="JSON.stringify(diffData.oldVals, null, 2) || 'None'"></pre>
                </div>

                <div>
                    <h4 class="font-bold text-emerald-500 uppercase text-[10px] mb-1">New Values</h4>
                    <pre class="p-3 bg-slate-50 dark:bg-slate-800/80 rounded-xl overflow-x-auto font-mono text-[11px] text-slate-700 dark:text-slate-300 max-h-60" x-text="JSON.stringify(diffData.newVals, null, 2) || 'None'"></pre>
                </div>
            </div>

            <div class="pt-2 flex justify-end">
                <button type="button" @click="diffModalOpen = false" class="px-4 py-2 bg-slate-100 dark:bg-slate-800 rounded-xl font-semibold text-xs">Close</button>
            </div>
        </div>
    </div>

</div>
@endsection
