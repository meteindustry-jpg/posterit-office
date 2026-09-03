<!DOCTYPE html>
<html lang="en" x-data="{
    darkMode: localStorage.getItem('theme') === 'dark',
    fillCreds(email, pass) {
        document.getElementById('email').value = email;
        document.getElementById('password').value = pass;
    }
}" :class="{ 'dark': darkMode }" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Posterit — Sign In</title>
    
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
<body class="h-full flex flex-col justify-center py-12 sm:px-6 lg:px-8 bg-[#f5f5f7] dark:bg-[#000000] text-[#1d1d1f] dark:text-[#f5f5f7] antialiased relative overflow-hidden selection:bg-[#0071e3] selection:text-white">

    <!-- Ambient Apple Soft Glow -->
    <div class="absolute -top-40 -left-40 w-96 h-96 bg-[#0071e3]/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-40 -right-40 w-96 h-96 bg-[#5856d6]/10 rounded-full blur-3xl pointer-events-none"></div>

    @php
        $brandLogo = \App\Models\CompanySetting::get('company_logo');
        $brandName = \App\Models\CompanySetting::get('company_name', config('app.name', 'Posterit'));
        $brandTagline = \App\Models\CompanySetting::get('company_tagline', 'Work Management & Performance');
    @endphp
    <div class="w-full max-w-md mx-auto px-4 text-center z-10">
        @if($brandLogo && file_exists(public_path('storage/' . $brandLogo)))
            <img src="{{ asset('storage/' . $brandLogo) }}" alt="{{ $brandName }}" 
                 width="64" height="64" style="max-width:64px;max-height:64px;"
                 class="w-16 h-16 rounded-3xl object-contain mb-4 mx-auto">
        @else
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-3xl bg-gradient-to-b from-[#0071e3] to-[#005bb5] shadow-[0_8px_32px_rgba(0,113,227,0.3)] mb-4 text-white font-extrabold text-2xl">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
            </div>
        @endif
        <h2 class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white font-display">{{ $brandName }}</h2>
        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400 font-medium">{{ $brandTagline }}</p>
    </div>

    <div class="mt-8 w-full max-w-md mx-auto px-4 z-10">
        <div class="glass-panel py-8 px-6 sm:px-10 rounded-3xl border border-black/[0.08] dark:border-white/[0.1] shadow-[0_20px_60px_rgba(0,0,0,0.06)] dark:shadow-[0_20px_60px_rgba(0,0,0,0.6)]">
            
            @if($errors->any())
            <div class="mb-6 p-3.5 rounded-2xl bg-rose-500/15 border border-rose-500/30 text-rose-600 dark:text-rose-300 text-xs">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
            @endif

            <form class="space-y-4 text-xs" method="POST" action="{{ route('login') }}">
                @csrf

                <div>
                    <label for="email" class="block font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-[10px] mb-1.5">Apple ID / Work Email</label>
                    <input id="email" name="email" type="email" autocomplete="email" required value="{{ old('email', 'manager@posterit.com') }}"
                           class="w-full px-4 py-3 bg-black/[0.03] dark:bg-white/[0.06] border border-black/[0.08] dark:border-white/[0.1] rounded-2xl text-[#1d1d1f] dark:text-white placeholder-slate-400 text-sm focus:outline-hidden focus:ring-2 focus:ring-[#0071e3] transition">
                </div>

                <div>
                    <label for="password" class="block font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-[10px] mb-1.5">Password</label>
                    <input id="password" name="password" type="password" autocomplete="current-password" required value="password"
                           class="w-full px-4 py-3 bg-black/[0.03] dark:bg-white/[0.06] border border-black/[0.08] dark:border-white/[0.1] rounded-2xl text-[#1d1d1f] dark:text-white placeholder-slate-400 text-sm focus:outline-hidden focus:ring-2 focus:ring-[#0071e3] transition">
                </div>

                <div class="flex items-center justify-between text-xs pt-1">
                    <label class="flex items-center text-slate-500 dark:text-slate-400 cursor-pointer">
                        <input type="checkbox" name="remember" class="rounded border-slate-300 text-[#0071e3] focus:ring-[#0071e3]">
                        <span class="ml-2 text-[11px]">Remember me</span>
                    </label>
                    <span class="text-slate-400 text-[11px]">Pass: <code class="text-[#0071e3]">password</code></span>
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full py-3 px-4 rounded-2xl text-sm font-bold text-white bg-[#0071e3] hover:bg-[#0077ed] active:scale-[0.98] shadow-[0_4px_20px_rgba(0,113,227,0.35)] transition-all cursor-pointer">
                        Sign In
                    </button>
                </div>

                <div class="pt-3 text-center text-xs text-slate-500 dark:text-slate-400">
                    New employee? 
                    <a href="{{ route('register') }}" class="font-bold text-[#0071e3] hover:underline">
                        Register Employee Account &rarr;
                    </a>
                </div>
            </form>

            <!-- Apple Quick Switcher -->
            <div class="mt-8 pt-6 border-t border-black/[0.06] dark:border-white/[0.08]">
                <div class="text-center text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-3">Quick Demo Accounts</div>
                <div class="grid grid-cols-2 gap-2 text-xs">
                    <button type="button" @click="fillCreds('superadmin@posterit.com', 'password')" class="p-2.5 rounded-2xl bg-black/[0.02] dark:bg-white/[0.04] hover:bg-black/[0.05] dark:hover:bg-white/[0.08] border border-black/[0.06] dark:border-white/[0.06] text-left transition hover:border-[#0071e3]/40 cursor-pointer">
                        <div class="font-bold text-[#1d1d1f] dark:text-white text-[11px]">Super Admin</div>
                        <div class="text-[10px] text-[#0071e3] truncate">superadmin@posterit.com</div>
                    </button>

                    <button type="button" @click="fillCreds('admin@posterit.com', 'password')" class="p-2.5 rounded-2xl bg-black/[0.02] dark:bg-white/[0.04] hover:bg-black/[0.05] dark:hover:bg-white/[0.08] border border-black/[0.06] dark:border-white/[0.06] text-left transition hover:border-[#0071e3]/40 cursor-pointer">
                        <div class="font-bold text-[#1d1d1f] dark:text-white text-[11px]">Admin</div>
                        <div class="text-[10px] text-[#0071e3] truncate">admin@posterit.com</div>
                    </button>

                    <button type="button" @click="fillCreds('manager@posterit.com', 'password')" class="p-2.5 rounded-2xl bg-black/[0.02] dark:bg-white/[0.04] hover:bg-black/[0.05] dark:hover:bg-white/[0.08] border border-black/[0.06] dark:border-white/[0.06] text-left transition hover:border-[#0071e3]/40 cursor-pointer">
                        <div class="font-bold text-[#1d1d1f] dark:text-white text-[11px]">Manager</div>
                        <div class="text-[10px] text-[#30d158] truncate">manager@posterit.com</div>
                    </button>

                    <button type="button" @click="fillCreds('rahul@posterit.com', 'password')" class="p-2.5 rounded-2xl bg-black/[0.02] dark:bg-white/[0.04] hover:bg-black/[0.05] dark:hover:bg-white/[0.08] border border-black/[0.06] dark:border-white/[0.06] text-left transition hover:border-[#0071e3]/40 cursor-pointer">
                        <div class="font-bold text-[#1d1d1f] dark:text-white text-[11px]">Employee</div>
                        <div class="text-[10px] text-[#bf5af2] truncate">rahul@posterit.com</div>
                    </button>
                </div>
            </div>

        </div>
    </div>
</body>
</html>
