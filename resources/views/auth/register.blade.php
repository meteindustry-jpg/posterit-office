<!DOCTYPE html>
<html lang="en" x-data="{
    darkMode: localStorage.getItem('theme') === 'dark',
}" :class="{ 'dark': darkMode }" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    @php
        $siteLogo = \App\Models\CompanySetting::get('company_logo');
        $siteName = \App\Models\CompanySetting::get('company_name', config('app.name', 'Posterit'));
        $siteTagline = \App\Models\CompanySetting::get('company_tagline', 'Design Operations');
    @endphp
    <title>{{ $siteName }} — Employee Registration</title>
    @if($siteLogo && file_exists(public_path('storage/' . $siteLogo)))
        <link rel="icon" type="image/png" href="{{ asset('storage/' . $siteLogo) }}">
    @endif
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        if (localStorage.getItem('theme') === 'dark') {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
</head>
<body class="min-h-full flex flex-col justify-center py-10 sm:px-6 lg:px-8 bg-[#f5f5f7] dark:bg-[#000000] text-[#1d1d1f] dark:text-[#f5f5f7] antialiased relative selection:bg-[#0071e3] selection:text-white">

    <!-- Ambient Apple Soft Glow -->
    <div class="absolute -top-40 -left-40 w-96 h-96 bg-[#0071e3]/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-40 -right-40 w-96 h-96 bg-[#5856d6]/10 rounded-full blur-3xl pointer-events-none"></div>

    <!-- Header Section -->
    <div class="w-full max-w-xl mx-auto px-4 text-center z-10 mb-8">
        @if($siteLogo && file_exists(public_path('storage/' . $siteLogo)))
            <img src="{{ asset('storage/' . $siteLogo) }}" alt="{{ $siteName }}" 
                 width="64" height="64" style="max-width:64px;max-height:64px;"
                 class="w-16 h-16 rounded-3xl object-contain mb-4 mx-auto">
        @else
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-3xl bg-gradient-to-b from-[#0071e3] to-[#005bb5] shadow-[0_8px_32px_rgba(0,113,227,0.3)] mb-4 text-white font-extrabold text-2xl">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
            </div>
        @endif
        <h2 class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white font-display">{{ $siteName }}</h2>
        <p class="mt-1.5 text-xs text-slate-500 dark:text-slate-400 font-medium">New Employee Onboarding & Self-Registration</p>
    </div>

    <!-- Registration Card -->
    <div class="w-full max-w-xl mx-auto px-4 z-10">
        <div class="bg-white dark:bg-slate-900 p-8 sm:p-10 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-[0_12px_40px_rgba(0,0,0,0.06)] dark:shadow-[0_20px_60px_rgba(0,0,0,0.5)]">
            
            @if($errors->any())
            <div class="mb-6 p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-800/60 text-rose-600 dark:text-rose-300 text-xs space-y-1">
                @foreach($errors->all() as $error)
                    <div class="flex items-center gap-1.5">
                        <span>&bull;</span>
                        <span>{{ $error }}</span>
                    </div>
                @endforeach
            </div>
            @endif

            <form method="POST" action="{{ route('register') }}" class="space-y-5">
                @csrf

                <!-- Name & Email -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="name" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Full Name *</label>
                        <input id="name" name="name" type="text" required value="{{ old('name') }}" placeholder="e.g. John Doe"
                               class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800/80 border border-slate-200/80 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:bg-white dark:focus:bg-slate-800 focus:border-[#0071e3] focus:ring-2 focus:ring-[#0071e3]/20 transition outline-none shadow-2xs">
                    </div>

                    <div>
                        <label for="email" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Work Email *</label>
                        <input id="email" name="email" type="email" autocomplete="email" required value="{{ old('email') }}" placeholder="john@posterit.com"
                               class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800/80 border border-slate-200/80 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:bg-white dark:focus:bg-slate-800 focus:border-[#0071e3] focus:ring-2 focus:ring-[#0071e3]/20 transition outline-none shadow-2xs">
                    </div>
                </div>

                <!-- Department & Designation -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="department_id" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Department *</label>
                        <select id="department_id" name="department_id" required
                                class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800/80 border border-slate-200/80 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white focus:bg-white dark:focus:bg-slate-800 focus:border-[#0071e3] focus:ring-2 focus:ring-[#0071e3]/20 transition outline-none shadow-2xs cursor-pointer">
                            <option value="" disabled {{ old('department_id') ? '' : 'selected' }} class="bg-white dark:bg-slate-800 text-slate-500">Select Department</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }} class="bg-white dark:bg-slate-800 text-slate-900 dark:text-white font-medium">
                                    {{ $dept->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="designation" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Designation *</label>
                        <input id="designation" name="designation" type="text" required value="{{ old('designation') }}" placeholder="e.g. Visual Designer"
                               class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800/80 border border-slate-200/80 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:bg-white dark:focus:bg-slate-800 focus:border-[#0071e3] focus:ring-2 focus:ring-[#0071e3]/20 transition outline-none shadow-2xs">
                    </div>
                </div>

                <!-- Mobile Number -->
                <div>
                    <label for="mobile_number" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Mobile Number (Optional)</label>
                    <input id="mobile_number" name="mobile_number" type="tel" value="{{ old('mobile_number') }}" placeholder="+91 98765 43210"
                           class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800/80 border border-slate-200/80 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:bg-white dark:focus:bg-slate-800 focus:border-[#0071e3] focus:ring-2 focus:ring-[#0071e3]/20 transition outline-none shadow-2xs">
                </div>

                <!-- Password & Confirm Password -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="password" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Password *</label>
                        <input id="password" name="password" type="password" required placeholder="Min 6 characters"
                               class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800/80 border border-slate-200/80 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:bg-white dark:focus:bg-slate-800 focus:border-[#0071e3] focus:ring-2 focus:ring-[#0071e3]/20 transition outline-none shadow-2xs">
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Confirm Password *</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" required placeholder="Repeat password"
                               class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800/80 border border-slate-200/80 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:bg-white dark:focus:bg-slate-800 focus:border-[#0071e3] focus:ring-2 focus:ring-[#0071e3]/20 transition outline-none shadow-2xs">
                    </div>
                </div>

                <div class="pt-3">
                    <button type="submit" class="w-full py-3.5 px-6 rounded-2xl text-sm font-bold text-white bg-[#0071e3] hover:bg-[#0077ed] active:scale-[0.98] shadow-[0_4px_16px_rgba(0,113,227,0.3)] hover:shadow-[0_6px_20px_rgba(0,113,227,0.4)] transition-all cursor-pointer flex items-center justify-center gap-2">
                        <span>Create Employee Account</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </button>
                </div>

                <div class="pt-4 text-center text-xs text-slate-500 dark:text-slate-400 border-t border-slate-100 dark:border-slate-800">
                    Already have an account? 
                    <a href="{{ route('login') }}" class="font-bold text-[#0071e3] hover:underline">
                        Sign In &rarr;
                    </a>
                </div>
            </form>

        </div>
    </div>
</body>
</html>
