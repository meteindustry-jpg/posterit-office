@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{
    addModalOpen: false,
    editModalOpen: false,
    editData: { id: null, name: '', email: '', role: 'employee', employee_id: '', is_active: true, url: '' },
    openEdit(u) {
        this.editData = {
            id: u.id,
            name: u.name,
            email: u.email,
            role: u.role,
            employee_id: u.employee_id || '',
            is_active: Boolean(u.is_active),
            url: `{{ url('users') }}/${u.id}`
        };
        this.editModalOpen = true;
    }
}">

    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold font-display text-slate-900 dark:text-white tracking-tight">
                User & Role Management
            </h1>
            <p class="text-xs text-slate-500">
                Manage system logins, access permissions, and assign roles (Super Admin, Admin, Manager, Employee).
            </p>
        </div>

        <div>
            <button type="button" @click="addModalOpen = true" 
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-[#0071e3] hover:bg-[#0062c4] text-white text-xs font-bold rounded-xl shadow-xs transition active:scale-95 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                <span>+ Add System User</span>
            </button>
        </div>
    </div>

    <!-- Pending Registrations Section (Awaiting Super Admin Approval) -->
    @if(isset($pendingUsers) && $pendingUsers->count() > 0)
    <div class="rounded-3xl bg-amber-500/10 border border-amber-500/30 p-5 shadow-xs space-y-3">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <span class="flex h-3 w-3 relative">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-amber-500"></span>
                </span>
                <h3 class="font-bold text-sm text-amber-900 dark:text-amber-200">
                    Pending Employee Registrations ({{ $pendingUsers->count() }} Awaiting Approval)
                </h3>
            </div>
            <span class="text-[11px] text-amber-700 dark:text-amber-300 font-medium">Approval Required for System Access</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
            @foreach($pendingUsers as $pu)
            <div class="p-3.5 bg-white dark:bg-slate-900 rounded-2xl border border-amber-200 dark:border-amber-800/60 shadow-2xs space-y-2.5">
                <div class="flex items-start justify-between">
                    <div>
                        <div class="font-bold text-xs text-slate-900 dark:text-white">{{ $pu->name }}</div>
                        <div class="text-[11px] text-slate-500">{{ $pu->email }}</div>
                    </div>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">Pending</span>
                </div>

                @if($pu->employee)
                <div class="text-[10px] text-slate-600 dark:text-slate-400 space-y-0.5">
                    <div><span class="font-semibold">Department:</span> {{ $pu->employee->department->name ?? 'General' }}</div>
                    <div><span class="font-semibold">Designation:</span> {{ $pu->employee->designation }}</div>
                    <div><span class="font-semibold">Registered:</span> {{ $pu->created_at->diffForHumans() }}</div>
                </div>
                @endif

                <div class="pt-2 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-2">
                    <form method="POST" action="{{ route('users.reject', $pu) }}" onsubmit="return confirm('Reject and delete this registration request?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-3 py-1 text-[11px] font-semibold text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-lg transition cursor-pointer">
                            Reject
                        </button>
                    </form>
                    <form method="POST" action="{{ route('users.approve', $pu) }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="px-3 py-1 text-[11px] font-bold bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg shadow-2xs transition cursor-pointer flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                            <span>Approve</span>
                        </button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Users Table -->
    <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800 text-slate-400 uppercase tracking-wider text-[10px]">
                        <th class="py-3.5 px-4 font-bold">User</th>
                        <th class="py-3.5 px-4 font-bold">Email</th>
                        <th class="py-3.5 px-4 font-bold">Role</th>
                        <th class="py-3.5 px-4 font-bold">Linked Employee</th>
                        <th class="py-3.5 px-4 font-bold">Status</th>
                        <th class="py-3.5 px-4 font-bold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                    @foreach($users as $user)
                    <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/30 transition">
                        <td class="py-3 px-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-indigo-600 to-purple-600 flex items-center justify-center font-bold text-white uppercase text-xs">
                                    {{ substr($user->name, 0, 2) }}
                                </div>
                                <div class="font-bold text-slate-900 dark:text-white">{{ $user->name }}</div>
                            </div>
                        </td>
                        <td class="py-3 px-4 font-mono text-slate-500">{{ $user->email }}</td>
                        <td class="py-3 px-4">
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase
                                {{ $user->role === 'super_admin' ? 'bg-purple-100 text-purple-800 dark:bg-purple-950 dark:text-purple-300' : '' }}
                                {{ $user->role === 'admin' ? 'bg-indigo-100 text-indigo-800 dark:bg-indigo-950 dark:text-indigo-300' : '' }}
                                {{ $user->role === 'manager' ? 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300' : '' }}
                                {{ $user->role === 'employee' ? 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300' : '' }}">
                                {{ str_replace('_', ' ', $user->role) }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-slate-600 dark:text-slate-400">
                            @if($user->employee)
                                <span>{{ $user->employee->name }} ({{ $user->employee->employee_code }})</span>
                            @else
                                <span class="text-slate-400">-</span>
                            @endif
                        </td>
                        <td class="py-3 px-4">
                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold {{ $user->is_active ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300' }}">
                                {{ $user->is_active ? 'Active' : 'Disabled' }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-right">
                            <div class="flex items-center justify-end gap-1">
                                <button type="button" @click="openEdit(@js($user))" class="p-1.5 rounded-lg text-slate-400 hover:text-indigo-600 hover:bg-slate-100 dark:hover:bg-slate-800 cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                </button>

                                @if($user->id !== auth()->id())
                                <form method="POST" action="{{ route('users.destroy', $user) }}" onsubmit="return confirm('Delete user account {{ $user->name }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 cursor-pointer">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add User Modal -->
    <div x-show="addModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-xs" style="display: none;">
        <div @click.outside="addModalOpen = false" class="w-full max-w-md bg-white dark:bg-slate-900 rounded-3xl p-6 shadow-2xl border border-slate-200 dark:border-slate-800 space-y-4">
            <h3 class="font-extrabold text-base text-slate-900 dark:text-white font-display">Add User Account</h3>
            
            <form method="POST" action="{{ route('users.store') }}" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-slate-500 uppercase mb-1">Full Name *</label>
                    <input type="text" name="name" required class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-medium">
                </div>

                <div>
                    <label class="block font-bold text-slate-500 uppercase mb-1">Email Address *</label>
                    <input type="email" name="email" required class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-medium">
                </div>

                <div>
                    <label class="block font-bold text-slate-500 uppercase mb-1">Password *</label>
                    <input type="password" name="password" required value="password" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-mono">
                </div>

                <div>
                    <label class="block font-bold text-slate-500 uppercase mb-1">User Role *</label>
                    <select name="role" required class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-semibold">
                        <option value="employee">Employee (Read Only)</option>
                        <option value="manager">Manager</option>
                        <option value="admin">Admin</option>
                        <option value="super_admin">Super Admin</option>
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-slate-500 uppercase mb-1">Link to Employee Profile</label>
                    <select name="employee_id" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-medium">
                        <option value="">None / System Operator</option>
                        @foreach($employees as $e)
                            <option value="{{ $e->id }}">{{ $e->name }} ({{ $e->employee_code }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" value="1" id="add_active" checked class="rounded text-indigo-600">
                    <label for="add_active" class="font-semibold text-slate-700 dark:text-slate-300">Account Active</label>
                </div>

                <div class="pt-3 flex items-center justify-end gap-2">
                    <button type="button" @click="addModalOpen = false" class="px-4 py-2 bg-slate-100 dark:bg-slate-800 rounded-xl font-semibold">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-xl shadow-md">Create User</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div x-show="editModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-xs" style="display: none;">
        <div @click.outside="editModalOpen = false" class="w-full max-w-md bg-white dark:bg-slate-900 rounded-3xl p-6 shadow-2xl border border-slate-200 dark:border-slate-800 space-y-4">
            <h3 class="font-extrabold text-base text-slate-900 dark:text-white font-display">Edit User Account</h3>
            
            <form method="POST" :action="editData.url" class="space-y-4 text-xs">
                @csrf
                @method('PUT')
                <div>
                    <label class="block font-bold text-slate-500 uppercase mb-1">Full Name *</label>
                    <input type="text" name="name" x-model="editData.name" required class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-medium">
                </div>

                <div>
                    <label class="block font-bold text-slate-500 uppercase mb-1">Email Address *</label>
                    <input type="email" name="email" x-model="editData.email" required class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-medium">
                </div>

                <div>
                    <label class="block font-bold text-slate-500 uppercase mb-1">Reset Password (leave empty to keep)</label>
                    <input type="password" name="password" placeholder="New password..." class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-mono">
                </div>

                <div>
                    <label class="block font-bold text-slate-500 uppercase mb-1">User Role *</label>
                    <select name="role" x-model="editData.role" required class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-semibold">
                        <option value="employee">Employee (Read Only)</option>
                        <option value="manager">Manager</option>
                        <option value="admin">Admin</option>
                        <option value="super_admin">Super Admin</option>
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-slate-500 uppercase mb-1">Linked Employee</label>
                    <select name="employee_id" x-model="editData.employee_id" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-medium">
                        <option value="">None / System Operator</option>
                        @foreach($employees as $e)
                            <option value="{{ $e->id }}">{{ $e->name }} ({{ $e->employee_code }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" value="1" id="edit_active" x-model="editData.is_active" class="rounded text-indigo-600">
                    <label for="edit_active" class="font-semibold text-slate-700 dark:text-slate-300">Account Active</label>
                </div>

                <div class="pt-3 flex items-center justify-end gap-2">
                    <button type="button" @click="editModalOpen = false" class="px-4 py-2 bg-slate-100 dark:bg-slate-800 rounded-xl font-semibold">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-xl shadow-md">Update User</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
