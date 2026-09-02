@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="{
    activeTab: 'personal',
    avatarPreview: '{{ $user->avatar ? asset('storage/' . $user->avatar) : ($user->employee?->photo_url ?? '') }}',
    handleAvatarUpload(e) {
        const file = e.target.files[0];
        if (file) {
            this.avatarPreview = URL.createObjectURL(file);
        }
    }
}">

    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-400 mb-1">
                <span>Account</span>
                <span>/</span>
                <span class="text-slate-700 dark:text-slate-300">My Settings</span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white font-display">Employee Profile & Settings</h1>
            <p class="text-xs text-slate-500 mt-0.5">Manage personal information, contact numbers, salary bank details, and account security.</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                {{ strtoupper(str_replace('_', ' ', $user->role)) }}
            </span>
        </div>
    </div>

    @if(session('success'))
    <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center justify-between shadow-2xs animate-fade-in">
        <div class="flex items-center gap-2.5">
            <div class="w-6 h-6 rounded-lg bg-emerald-500 text-white flex items-center justify-center shrink-0">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
            </div>
            <span>{{ session('success') }}</span>
        </div>
        <span class="text-[10px] text-emerald-600 uppercase font-bold tracking-wider">Saved</span>
    </div>
    @endif

    <!-- Apple ID / Employee Identity Banner Card -->
    <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-xs flex flex-col sm:flex-row items-center sm:items-start gap-6">
        <div class="relative group shrink-0">
            <template x-if="avatarPreview">
                <img :src="avatarPreview" alt="Avatar" class="w-24 h-24 rounded-3xl object-cover border-2 border-white dark:border-slate-800 shadow-md">
            </template>
            <template x-if="!avatarPreview">
                <div class="w-24 h-24 rounded-3xl bg-gradient-to-tr from-[#0071E3] to-[#AF52DE] text-white text-3xl font-black flex items-center justify-center border-2 border-white shadow-md">
                    {{ substr($user->name, 0, 1) }}
                </div>
            </template>
            <label for="profile_avatar_input" class="absolute -bottom-1.5 -right-1.5 w-8 h-8 rounded-full bg-[#0071E3] text-white shadow-sm flex items-center justify-center cursor-pointer hover:bg-[#0062C4] transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </label>
        </div>

        <div class="flex-1 text-center sm:text-left space-y-2">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                <div>
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white font-display">{{ $user->name }}</h2>
                    <p class="text-xs text-slate-500">{{ $user->email }}</p>
                </div>
                @if($user->employee)
                <span class="inline-flex items-center px-3 py-1 rounded-xl text-xs font-mono font-bold bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-200 border border-slate-200/60">
                    {{ $user->employee->employee_code }}
                </span>
                @endif
            </div>

            @if($user->employee)
            <div class="pt-2 flex flex-wrap items-center justify-center sm:justify-start gap-3 text-xs">
                <div class="flex items-center gap-1.5 px-3 py-1 rounded-lg bg-slate-50 dark:bg-slate-800/80 border border-slate-200/60 text-slate-600 dark:text-slate-300">
                    <svg class="w-3.5 h-3.5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    <span>{{ $user->employee->department->name ?? 'General' }}</span>
                </div>
                <div class="flex items-center gap-1.5 px-3 py-1 rounded-lg bg-slate-50 dark:bg-slate-800/80 border border-slate-200/60 text-slate-600 dark:text-slate-300">
                    <svg class="w-3.5 h-3.5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    <span>{{ $user->employee->designation }}</span>
                </div>
                <div class="flex items-center gap-1.5 px-3 py-1 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 text-emerald-700 dark:text-emerald-400 font-semibold">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span>{{ $user->employee->remaining_leaves }} Leaves Available</span>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Cupertino Segmented Tab Controls -->
    <div class="bg-slate-200/70 dark:bg-slate-800/80 p-1 rounded-2xl flex items-center gap-1 border border-slate-200/60 dark:border-slate-700/60 shadow-2xs text-xs font-semibold">
        <button type="button" @click="activeTab = 'personal'"
                :class="activeTab === 'personal' ? 'bg-white dark:bg-slate-900 text-slate-900 dark:text-white shadow-xs font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900'"
                class="flex-1 py-2 px-3 rounded-xl transition-all flex items-center justify-center gap-2 cursor-pointer">
            <svg class="w-4 h-4 text-[#0071E3]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            <span>Personal & Contact</span>
        </button>

        <button type="button" @click="activeTab = 'banking'"
                :class="activeTab === 'banking' ? 'bg-white dark:bg-slate-900 text-slate-900 dark:text-white shadow-xs font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900'"
                class="flex-1 py-2 px-3 rounded-xl transition-all flex items-center justify-center gap-2 cursor-pointer">
            <svg class="w-4 h-4 text-[#34C759]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
            <span>Bank & Payout</span>
        </button>

        <button type="button" @click="activeTab = 'security'"
                :class="activeTab === 'security' ? 'bg-white dark:bg-slate-900 text-slate-900 dark:text-white shadow-xs font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900'"
                class="flex-1 py-2 px-3 rounded-xl transition-all flex items-center justify-center gap-2 cursor-pointer">
            <svg class="w-4 h-4 text-[#FF9500]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            <span>Security & Password</span>
        </button>
    </div>

    <!-- Form 1: Personal, Contact & Banking Details -->
    <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data"
          class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-xs overflow-hidden text-xs">
        @csrf
        @method('PUT')

        <input id="profile_avatar_input" type="file" name="avatar" accept="image/*" class="hidden" @change="handleAvatarUpload($event)">

        <div class="p-6 sm:p-8 space-y-6">

            <!-- TAB: PERSONAL & CONTACT -->
            <div x-show="activeTab === 'personal'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-6">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider mb-4 pb-2 border-b border-slate-100 dark:border-slate-800">
                        1. Basic Information
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Full Name *</label>
                            <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                                   class="w-full px-4 py-2.5 bg-slate-50/70 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 rounded-xl font-bold text-slate-900 dark:text-white focus:bg-white focus:border-[#0071E3] focus:ring-2 focus:ring-[#0071E3]/20 transition outline-none">
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Email Address *</label>
                            <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                                   class="w-full px-4 py-2.5 bg-slate-50/70 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 rounded-xl font-medium text-slate-900 dark:text-white focus:bg-white focus:border-[#0071E3] focus:ring-2 focus:ring-[#0071E3]/20 transition outline-none">
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Mobile Number</label>
                            <input type="text" name="mobile_number" value="{{ old('mobile_number', $user->employee?->mobile_number) }}" placeholder="+91 98765 43210"
                                   class="w-full px-4 py-2.5 bg-slate-50/70 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 rounded-xl font-medium text-slate-900 dark:text-white focus:bg-white focus:border-[#0071E3] focus:ring-2 focus:ring-[#0071E3]/20 transition outline-none">
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">System Access Role</label>
                            <div class="px-4 py-2.5 bg-slate-100 dark:bg-slate-800/60 border border-slate-200/60 dark:border-slate-700 rounded-xl font-bold uppercase text-[#0071E3] text-xs">
                                {{ str_replace('_', ' ', $user->role) }}
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider mb-4 pb-2 border-b border-slate-100 dark:border-slate-800">
                        2. Emergency Contact Information
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Emergency Contact Person</label>
                            <input type="text" name="emergency_contact_name" value="{{ old('emergency_contact_name', $user->employee?->emergency_contact_name) }}" placeholder="e.g. Parent, Spouse or Guardian"
                                   class="w-full px-4 py-2.5 bg-slate-50/70 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 rounded-xl font-medium text-slate-900 dark:text-white focus:bg-white focus:border-[#0071E3] focus:ring-2 focus:ring-[#0071E3]/20 transition outline-none">
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Emergency Phone Number</label>
                            <input type="text" name="emergency_contact_phone" value="{{ old('emergency_contact_phone', $user->employee?->emergency_contact_phone) }}" placeholder="+91 98765 00000"
                                   class="w-full px-4 py-2.5 bg-slate-50/70 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 rounded-xl font-medium text-slate-900 dark:text-white focus:bg-white focus:border-[#0071E3] focus:ring-2 focus:ring-[#0071E3]/20 transition outline-none">
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB: BANK & PAYOUT -->
            <div x-show="activeTab === 'banking'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-6">
                <div class="p-4 rounded-2xl bg-emerald-50/60 dark:bg-emerald-950/20 border border-emerald-200 dark:border-emerald-800/60 text-emerald-800 dark:text-emerald-300 flex items-center gap-3">
                    <svg class="w-5 h-5 shrink-0 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Your salary and reimbursement disbursements are directly credited to these bank account details.</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Bank Name</label>
                        <input type="text" name="bank_name" value="{{ old('bank_name', $user->employee?->bank_name) }}" placeholder="e.g. HDFC Bank, State Bank of India"
                               class="w-full px-4 py-2.5 bg-slate-50/70 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 rounded-xl font-medium text-slate-900 dark:text-white focus:bg-white focus:border-[#0071E3] focus:ring-2 focus:ring-[#0071E3]/20 transition outline-none">
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Bank Account Number</label>
                        <input type="text" name="bank_account_no" value="{{ old('bank_account_no', $user->employee?->bank_account_no) }}" placeholder="e.g. 50100234567890"
                               class="w-full px-4 py-2.5 bg-slate-50/70 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 rounded-xl font-mono font-semibold text-slate-900 dark:text-white focus:bg-white focus:border-[#0071E3] focus:ring-2 focus:ring-[#0071E3]/20 transition outline-none">
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">IFSC Code</label>
                        <input type="text" name="bank_ifsc" value="{{ old('bank_ifsc', $user->employee?->bank_ifsc) }}" placeholder="e.g. HDFC0001234"
                               class="w-full px-4 py-2.5 bg-slate-50/70 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 rounded-xl font-mono uppercase font-semibold text-slate-900 dark:text-white focus:bg-white focus:border-[#0071E3] focus:ring-2 focus:ring-[#0071E3]/20 transition outline-none">
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">UPI ID (VPA)</label>
                        <input type="text" name="upi_id" value="{{ old('upi_id', $user->employee?->upi_id) }}" placeholder="e.g. yourname@okhdfcbank"
                               class="w-full px-4 py-2.5 bg-slate-50/70 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 rounded-xl font-medium text-slate-900 dark:text-white focus:bg-white focus:border-[#0071E3] focus:ring-2 focus:ring-[#0071E3]/20 transition outline-none">
                    </div>
                </div>
            </div>

        </div>

        <div x-show="activeTab === 'personal' || activeTab === 'banking'" class="p-5 bg-slate-50/80 dark:bg-slate-800/50 border-t border-slate-200/80 dark:border-slate-800 flex items-center justify-between">
            <span class="text-[11px] text-slate-400">Keep emergency and payout info updated for seamless studio ops</span>
            <button type="submit" class="px-6 py-2.5 bg-[#0071E3] hover:bg-[#0062C4] text-white font-bold rounded-xl shadow-xs transition active:scale-95 cursor-pointer flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>Save Profile Changes</span>
            </button>
        </div>
    </form>

    <!-- TAB: SECURITY & PASSWORD (SEPARATE FORM) -->
    <div x-show="activeTab === 'security'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
        <form method="POST" action="{{ route('profile.password') }}"
              class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-xs overflow-hidden text-xs">
            @csrf
            @method('PUT')

            <div class="p-6 sm:p-8 space-y-4">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider pb-2 border-b border-slate-100 dark:border-slate-800">
                    Change Account Password
                </h3>

                <div class="space-y-4 max-w-lg">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Current Password *</label>
                        <input type="password" name="current_password" required
                               placeholder="Enter your existing password"
                               class="w-full px-4 py-2.5 bg-slate-50/70 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 rounded-xl font-mono text-slate-900 dark:text-white focus:bg-white focus:border-[#0071E3] focus:ring-2 focus:ring-[#0071E3]/20 transition outline-none">
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">New Password *</label>
                        <input type="password" name="password" required
                               placeholder="Minimum 8 characters"
                               class="w-full px-4 py-2.5 bg-slate-50/70 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 rounded-xl font-mono text-slate-900 dark:text-white focus:bg-white focus:border-[#0071E3] focus:ring-2 focus:ring-[#0071E3]/20 transition outline-none">
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Confirm New Password *</label>
                        <input type="password" name="password_confirmation" required
                               placeholder="Repeat new password"
                               class="w-full px-4 py-2.5 bg-slate-50/70 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 rounded-xl font-mono text-slate-900 dark:text-white focus:bg-white focus:border-[#0071E3] focus:ring-2 focus:ring-[#0071E3]/20 transition outline-none">
                    </div>
                </div>
            </div>

            <div class="p-5 bg-slate-50/80 dark:bg-slate-800/50 border-t border-slate-200/80 dark:border-slate-800 flex items-center justify-between">
                <span class="text-[11px] text-slate-400">You will need to sign in again after updating your password</span>
                <button type="submit" class="px-6 py-2.5 bg-slate-900 hover:bg-black text-white font-bold rounded-xl shadow-xs transition active:scale-95 cursor-pointer flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    <span>Update Security Password</span>
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
