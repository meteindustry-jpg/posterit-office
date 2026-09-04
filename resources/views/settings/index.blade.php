@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto space-y-6" x-data="{ 
    activeTab: 'brand',
    logoPreview: '{{ !empty($settings['company_logo']) ? asset('storage/' . $settings['company_logo']) : '' }}',
    handleLogoUpload(event) {
        const file = event.target.files[0];
        if (file) {
            this.logoPreview = URL.createObjectURL(file);
        }
    }
}">

    <!-- Top Header & Breadcrumb -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-400 mb-1">
                <span>System</span>
                <span>/</span>
                <span class="text-slate-700 dark:text-slate-300">Preferences</span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white font-display">System Settings</h1>
            <p class="text-xs text-slate-500 mt-0.5">Customize office branding, attendance policies, shift schedules, and payroll rules.</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 shadow-2xs">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                Active Configuration
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
        <span class="text-[10px] text-emerald-600 uppercase font-bold tracking-wider">Updated</span>
    </div>
    @endif

    <!-- Cupertino Segmented Tab Navigation -->
    <div class="bg-slate-200/70 dark:bg-slate-800/80 p-1 rounded-2xl flex items-center gap-1 border border-slate-200/60 dark:border-slate-700/60 shadow-2xs text-xs font-semibold">
        <button type="button" @click="activeTab = 'brand'"
                :class="activeTab === 'brand' ? 'bg-white dark:bg-slate-900 text-slate-900 dark:text-white shadow-xs font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900'"
                class="flex-1 py-2 px-3 rounded-xl transition-all flex items-center justify-center gap-2 cursor-pointer">
            <svg class="w-4 h-4 text-[#0071E3]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            <span>Office & Brand</span>
        </button>

        <button type="button" @click="activeTab = 'shifts'"
                :class="activeTab === 'shifts' ? 'bg-white dark:bg-slate-900 text-slate-900 dark:text-white shadow-xs font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900'"
                class="flex-1 py-2 px-3 rounded-xl transition-all flex items-center justify-center gap-2 cursor-pointer">
            <svg class="w-4 h-4 text-[#AF52DE]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>Shifts & Timings</span>
        </button>

        <button type="button" @click="activeTab = 'rules'"
                :class="activeTab === 'rules' ? 'bg-white dark:bg-slate-900 text-slate-900 dark:text-white shadow-xs font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900'"
                class="flex-1 py-2 px-3 rounded-xl transition-all flex items-center justify-center gap-2 cursor-pointer">
            <svg class="w-4 h-4 text-[#34C759]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            <span>Attendance & Leaves</span>
        </button>

        <button type="button" @click="activeTab = 'payroll'"
                :class="activeTab === 'payroll' ? 'bg-white dark:bg-slate-900 text-slate-900 dark:text-white shadow-xs font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900'"
                class="flex-1 py-2 px-3 rounded-xl transition-all flex items-center justify-center gap-2 cursor-pointer">
            <svg class="w-4 h-4 text-[#FF9500]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            <span>Payroll & UI</span>
        </button>
    </div>

    <!-- Main Settings Form -->
    <form method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data" 
          class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-xs overflow-hidden">
        @csrf

        <div class="p-6 sm:p-8 space-y-8 text-xs">

            <!-- TAB 1: OFFICE & BRAND -->
            <div x-show="activeTab === 'brand'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-6">
                
                <!-- Logo & Brand Header Banner -->
                <div class="p-5 rounded-2xl bg-gradient-to-r from-blue-50/50 via-indigo-50/30 to-purple-50/30 dark:from-slate-800/40 dark:to-slate-800/20 border border-blue-100 dark:border-slate-800 flex flex-col sm:flex-row items-center gap-5">
                    <div class="relative group shrink-0">
                        <template x-if="logoPreview">
                            <img :src="logoPreview" alt="Office Logo" class="w-20 h-20 rounded-2xl object-contain">
                        </template>
                        <template x-if="!logoPreview">
                            <div class="w-20 h-20 rounded-2xl bg-gradient-to-tr from-[#0071E3] to-[#AF52DE] text-white font-black text-2xl flex items-center justify-center shadow-sm border-2 border-white">
                                {{ substr($settings['company_name'] ?? 'P', 0, 1) }}
                            </div>
                        </template>
                        <label for="company_logo_input" class="absolute -bottom-1 -right-1 w-7 h-7 rounded-full bg-white dark:bg-slate-800 border border-slate-200 shadow-xs flex items-center justify-center text-slate-700 cursor-pointer hover:bg-slate-50 transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </label>
                        <input id="company_logo_input" type="file" name="company_logo" accept="image/*" class="hidden" @change="handleLogoUpload($event)">
                    </div>

                    <div class="flex-1 text-center sm:text-left">
                        <h3 class="text-base font-bold text-slate-900 dark:text-white font-display">{{ $settings['company_name'] }}</h3>
                        <p class="text-xs text-slate-500 mt-0.5">{{ $settings['company_tagline'] ?: 'Set an office tagline or motto for official slips' }}</p>
                        <div class="mt-2.5 flex flex-wrap items-center justify-center sm:justify-start gap-2">
                            <label for="company_logo_input" class="px-3 py-1.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-50 cursor-pointer shadow-2xs transition">
                                Upload New Logo
                            </label>
                            <span class="text-[11px] text-slate-400">PNG, JPG, SVG up to 2MB</span>
                        </div>
                    </div>
                </div>

                <!-- Form Fields -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div class="sm:col-span-2">
                        <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Office / Organization Name *</label>
                        <input type="text" name="company_name" value="{{ old('company_name', $settings['company_name']) }}" required
                               placeholder="e.g. Posterit Digital Office"
                               class="w-full px-4 py-2.5 bg-slate-50/70 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 rounded-xl font-bold text-sm text-slate-900 dark:text-white focus:bg-white focus:border-[#0071E3] focus:ring-2 focus:ring-[#0071E3]/20 transition outline-none">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Office Tagline / Slogan</label>
                        <input type="text" name="company_tagline" value="{{ old('company_tagline', $settings['company_tagline']) }}"
                               placeholder="e.g. Creative Media, Branding & Post-Production Office"
                               class="w-full px-4 py-2.5 bg-slate-50/70 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 rounded-xl font-medium text-slate-900 dark:text-white focus:bg-white focus:border-[#0071E3] focus:ring-2 focus:ring-[#0071E3]/20 transition outline-none">
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Official Contact Email</label>
                        <input type="email" name="company_email" value="{{ old('company_email', $settings['company_email']) }}"
                               placeholder="contact@posterit.com"
                               class="w-full px-4 py-2.5 bg-slate-50/70 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 rounded-xl font-medium text-slate-900 dark:text-white focus:bg-white focus:border-[#0071E3] focus:ring-2 focus:ring-[#0071E3]/20 transition outline-none">
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Official Phone Number</label>
                        <input type="text" name="company_phone" value="{{ old('company_phone', $settings['company_phone']) }}"
                               placeholder="+91 98765 43210"
                               class="w-full px-4 py-2.5 bg-slate-50/70 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 rounded-xl font-medium text-slate-900 dark:text-white focus:bg-white focus:border-[#0071E3] focus:ring-2 focus:ring-[#0071E3]/20 transition outline-none">
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Office Website URL</label>
                        <input type="url" name="company_website" value="{{ old('company_website', $settings['company_website']) }}"
                               placeholder="https://posterit.com"
                               class="w-full px-4 py-2.5 bg-slate-50/70 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 rounded-xl font-medium text-slate-900 dark:text-white focus:bg-white focus:border-[#0071E3] focus:ring-2 focus:ring-[#0071E3]/20 transition outline-none">
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">GSTIN / Corporate Tax ID</label>
                        <input type="text" name="company_tax_id" value="{{ old('company_tax_id', $settings['company_tax_id']) }}"
                               placeholder="GSTIN27ABCDE1234F1Z5"
                               class="w-full px-4 py-2.5 bg-slate-50/70 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 rounded-xl font-mono uppercase font-medium text-slate-900 dark:text-white focus:bg-white focus:border-[#0071E3] focus:ring-2 focus:ring-[#0071E3]/20 transition outline-none">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Physical Office Address</label>
                        <textarea name="company_address" rows="2" placeholder="Suite, Floor, Street, City, Pincode"
                                  class="w-full px-4 py-2.5 bg-slate-50/70 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 rounded-xl font-medium text-slate-900 dark:text-white focus:bg-white focus:border-[#0071E3] focus:ring-2 focus:ring-[#0071E3]/20 transition outline-none resize-none">{{ old('company_address', $settings['company_address']) }}</textarea>
                    </div>
                </div>
            </div>

            <!-- TAB 2: SHIFTS & TIMINGS -->
            <div x-show="activeTab === 'shifts'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-6">
                
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Weekly Operational Days *</label>
                        <span class="text-[11px] text-slate-400">Select active office working days</span>
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-2.5">
                        @foreach(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] as $day)
                        @php $isSelected = in_array($day, $settings['working_days']); @endphp
                        <label class="p-3 rounded-2xl border transition-all cursor-pointer flex flex-col items-center justify-center gap-1.5 text-center group {{ $isSelected ? 'bg-blue-50/70 border-blue-200 text-[#0071E3] dark:bg-blue-900/20 dark:border-blue-800' : 'bg-slate-50/60 border-slate-200 dark:bg-slate-800/50 dark:border-slate-700 text-slate-600 dark:text-slate-400 hover:border-slate-300' }}">
                            <input type="checkbox" name="working_days[]" value="{{ $day }}" 
                                   {{ $isSelected ? 'checked' : '' }}
                                   class="w-4 h-4 rounded text-[#0071E3] focus:ring-[#0071E3] cursor-pointer">
                            <span class="text-xs font-bold">{{ substr($day, 0, 3) }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 pt-2">
                    <div class="p-5 rounded-2xl bg-slate-50/60 dark:bg-slate-800/40 border border-slate-200/80 dark:border-slate-700 space-y-3">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <div>
                                <h4 class="font-bold text-slate-800 dark:text-white">Shift Start Time *</h4>
                                <p class="text-[11px] text-slate-400">Official time when employees must arrive</p>
                            </div>
                        </div>
                        <input type="time" name="office_timing_start" value="{{ old('office_timing_start', $settings['office_timing_start']) }}" required
                               class="w-full px-4 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-bold font-mono text-sm text-slate-900 dark:text-white focus:border-[#0071E3] focus:ring-2 focus:ring-[#0071E3]/20 transition outline-none">
                    </div>

                    <div class="p-5 rounded-2xl bg-slate-50/60 dark:bg-slate-800/40 border border-slate-200/80 dark:border-slate-700 space-y-3">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                            </div>
                            <div>
                                <h4 class="font-bold text-slate-800 dark:text-white">Shift End Time *</h4>
                                <p class="text-[11px] text-slate-400">Standard departure time for team members</p>
                            </div>
                        </div>
                        <input type="time" name="office_timing_end" value="{{ old('office_timing_end', $settings['office_timing_end']) }}" required
                               class="w-full px-4 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-bold font-mono text-sm text-slate-900 dark:text-white focus:border-[#0071E3] focus:ring-2 focus:ring-[#0071E3]/20 transition outline-none">
                    </div>

                    <div class="p-5 rounded-2xl bg-slate-50/60 dark:bg-slate-800/40 border border-slate-200/80 dark:border-slate-700 space-y-3">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            </div>
                            <div>
                                <h4 class="font-bold text-slate-800 dark:text-white">Late Grace Period *</h4>
                                <p class="text-[11px] text-slate-400">Allowed arrival delay before marking Late</p>
                            </div>
                        </div>
                        <div class="relative">
                            <input type="number" name="late_grace_minutes" min="0" max="120" value="{{ old('late_grace_minutes', $settings['late_grace_minutes']) }}" required
                                   class="w-full px-4 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-bold font-mono text-sm text-amber-600 focus:border-[#0071E3] focus:ring-2 focus:ring-[#0071E3]/20 transition outline-none pr-14">
                            <span class="absolute right-3.5 top-2.5 text-xs text-slate-400 font-semibold">minutes</span>
                        </div>
                    </div>

                    <div class="p-5 rounded-2xl bg-slate-50/60 dark:bg-slate-800/40 border border-slate-200/80 dark:border-slate-700 space-y-3">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                            </div>
                            <div>
                                <h4 class="font-bold text-slate-800 dark:text-white">Half-Day Threshold *</h4>
                                <p class="text-[11px] text-slate-400">Minimum worked hours required for full day</p>
                            </div>
                        </div>
                        <div class="relative">
                            <input type="number" step="0.5" name="half_day_hours" min="1" max="12" value="{{ old('half_day_hours', $settings['half_day_hours']) }}" required
                                   class="w-full px-4 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-bold font-mono text-sm text-blue-600 focus:border-[#0071E3] focus:ring-2 focus:ring-[#0071E3]/20 transition outline-none pr-14">
                            <span class="absolute right-3.5 top-2.5 text-xs text-slate-400 font-semibold">hours</span>
                        </div>
                    <div class="sm:col-span-2 p-5 rounded-2xl bg-slate-50/60 dark:bg-slate-800/40 border border-slate-200/80 dark:border-slate-700 space-y-3">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </div>
                                <div>
                                    <h4 class="font-bold text-slate-800 dark:text-white">Office Timezone</h4>
                                    <p class="text-[11px] text-slate-400">Used for employee attendance clock-in, shift tracking, and late arrival detection</p>
                                </div>
                            </div>
                            <span class="px-2.5 py-1 bg-sky-100 dark:bg-sky-950/60 text-sky-800 dark:text-sky-300 text-[10px] font-bold rounded-lg uppercase tracking-wider self-start sm:self-auto">
                                Office Clock: {{ now()->format('h:i A T') }}
                            </span>
                        </div>
                        <select name="timezone" class="w-full px-4 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-bold text-sm text-slate-900 dark:text-white focus:border-[#0071E3] focus:ring-2 focus:ring-[#0071E3]/20 transition outline-none">
                            <option value="Asia/Kolkata" {{ old('timezone', $settings['timezone'] ?? 'Asia/Kolkata') === 'Asia/Kolkata' ? 'selected' : '' }}>Asia/Kolkata (IST - India Standard Time, UTC +5:30)</option>
                            <option value="UTC" {{ old('timezone', $settings['timezone'] ?? '') === 'UTC' ? 'selected' : '' }}>UTC (Coordinated Universal Time)</option>
                            <option value="Asia/Dubai" {{ old('timezone', $settings['timezone'] ?? '') === 'Asia/Dubai' ? 'selected' : '' }}>Asia/Dubai (GST, UTC +4:00)</option>
                            <option value="Asia/Singapore" {{ old('timezone', $settings['timezone'] ?? '') === 'Asia/Singapore' ? 'selected' : '' }}>Asia/Singapore (SGT, UTC +8:00)</option>
                            <option value="Europe/London" {{ old('timezone', $settings['timezone'] ?? '') === 'Europe/London' ? 'selected' : '' }}>Europe/London (GMT/BST)</option>
                            <option value="America/New_York" {{ old('timezone', $settings['timezone'] ?? '') === 'America/New_York' ? 'selected' : '' }}>America/New_York (EST/EDT)</option>
                            <option value="America/Los_Angeles" {{ old('timezone', $settings['timezone'] ?? '') === 'America/Los_Angeles' ? 'selected' : '' }}>America/Los_Angeles (PST/PDT)</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- TAB 3: ATTENDANCE & LEAVES -->
            <div x-show="activeTab === 'rules'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-6">
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div class="p-5 rounded-2xl bg-slate-50/60 dark:bg-slate-800/40 border border-slate-200/80 dark:border-slate-700 space-y-3">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>
                            <div>
                                <h4 class="font-bold text-slate-800 dark:text-white">Annual Leave Quota *</h4>
                                <p class="text-[11px] text-slate-400">Default paid leaves assigned to new team members</p>
                            </div>
                        </div>
                        <div class="relative">
                            <input type="number" name="default_leave_count" value="{{ old('default_leave_count', $settings['default_leave_count']) }}" required
                                   class="w-full px-4 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-bold font-mono text-sm text-emerald-600 focus:border-[#0071E3] focus:ring-2 focus:ring-[#0071E3]/20 transition outline-none pr-16">
                            <span class="absolute right-3.5 top-2.5 text-xs text-slate-400 font-semibold">days / yr</span>
                        </div>
                    </div>

                    <div class="p-5 rounded-2xl bg-slate-50/60 dark:bg-slate-800/40 border border-slate-200/80 dark:border-slate-700 space-y-3">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                            </div>
                            <div>
                                <h4 class="font-bold text-slate-800 dark:text-white">Attendance Reminders</h4>
                                <p class="text-[11px] text-slate-400">Notify employees if not clocked in by shift start</p>
                            </div>
                        </div>
                        <div class="flex items-center justify-between p-2 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700">
                            <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">Enable Daily Reminder Alert</span>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="attendance_reminder_enabled" value="1" {{ $settings['attendance_reminder_enabled'] ? 'checked' : '' }} class="sr-only peer">
                                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#0071E3]"></div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 4: PAYROLL & UI -->
            <div x-show="activeTab === 'payroll'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-6">
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Currency Symbol *</label>
                        <input type="text" name="currency_symbol" value="{{ old('currency_symbol', $settings['currency_symbol']) }}" required
                               placeholder="e.g. ₹ or $ or €"
                               class="w-full px-4 py-2.5 bg-slate-50/70 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 rounded-xl font-bold font-mono text-sm text-slate-900 dark:text-white focus:bg-white focus:border-[#0071E3] focus:ring-2 focus:ring-[#0071E3]/20 transition outline-none">
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Default Interface Theme *</label>
                        <select name="theme_mode" required 
                                class="w-full px-4 py-2.5 bg-slate-50/70 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 rounded-xl font-semibold text-slate-900 dark:text-white focus:bg-white focus:border-[#0071E3] focus:ring-2 focus:ring-[#0071E3]/20 transition outline-none">
                            <option value="light" {{ $settings['theme_mode'] === 'light' ? 'selected' : '' }}>Light Mode (Cupertino White)</option>
                            <option value="dark" {{ $settings['theme_mode'] === 'dark' ? 'selected' : '' }}>Dark Mode</option>
                            <option value="system" {{ $settings['theme_mode'] === 'system' ? 'selected' : '' }}>Match Operating System</option>
                        </select>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Payslip Legal Disclaimer / Footer Note</label>
                        <textarea name="payslip_footer_note" rows="2" placeholder="e.g. This is a system-generated salary slip and does not require a physical signature."
                                  class="w-full px-4 py-2.5 bg-slate-50/70 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 rounded-xl font-medium text-slate-900 dark:text-white focus:bg-white focus:border-[#0071E3] focus:ring-2 focus:ring-[#0071E3]/20 transition outline-none resize-none">{{ old('payslip_footer_note', $settings['payslip_footer_note']) }}</textarea>
                    </div>
                </div>
            </div>

        </div>

        <!-- Sticky Footer Action Bar -->
        <div class="p-5 bg-slate-50/80 dark:bg-slate-800/50 border-t border-slate-200/80 dark:border-slate-800 flex items-center justify-between">
            <div class="flex items-center gap-2 text-[11px] text-slate-500">
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Changes will immediately update across all user dashboards</span>
            </div>
            
            <button type="submit" 
                    class="px-6 py-2.5 bg-[#0071E3] hover:bg-[#0062C4] text-white font-bold rounded-xl shadow-xs transition-all active:scale-95 cursor-pointer flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>Save All Settings</span>
            </button>
        </div>

    </form>

</div>
@endsection

