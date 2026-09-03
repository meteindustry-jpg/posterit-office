<!DOCTYPE html>
<html lang="en" x-data="{ darkMode: localStorage.getItem('theme') === 'dark' }" :class="{ 'dark': darkMode }" class="h-full">
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
            
            @if(session('success'))
            <div class="mb-6 p-4 rounded-2xl bg-emerald-500/15 border border-emerald-500/30 text-emerald-700 dark:text-emerald-300 text-xs font-medium flex items-start gap-2.5">
                <svg class="w-4 h-4 text-emerald-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                <div>{{ session('success') }}</div>
            </div>
            @endif

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
                    <label for="email" class="block font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-[10px] mb-1.5">Email or Username</label>
                    <input id="email" name="email" type="text" autocomplete="username" required value="{{ old('email') }}" placeholder="Enter your work email or username"
                           class="w-full px-4 py-3 bg-black/[0.03] dark:bg-white/[0.06] border border-black/[0.08] dark:border-white/[0.1] rounded-2xl text-[#1d1d1f] dark:text-white placeholder-slate-400 text-sm focus:outline-hidden focus:ring-2 focus:ring-[#0071e3] transition">
                </div>

                <div>
                    <label for="password" class="block font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-[10px] mb-1.5">Password</label>
                    <input id="password" name="password" type="password" autocomplete="current-password" required placeholder="••••••••"
                           class="w-full px-4 py-3 bg-black/[0.03] dark:bg-white/[0.06] border border-black/[0.08] dark:border-white/[0.1] rounded-2xl text-[#1d1d1f] dark:text-white placeholder-slate-400 text-sm focus:outline-hidden focus:ring-2 focus:ring-[#0071e3] transition">
                </div>

                <div class="flex items-center justify-between text-xs pt-1">
                    <label class="flex items-center text-slate-500 dark:text-slate-400 cursor-pointer">
                        <input type="checkbox" name="remember" class="rounded border-slate-300 text-[#0071e3] focus:ring-[#0071e3]">
                        <span class="ml-2 text-[11px]">Remember me on this device</span>
                    </label>
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full py-3 px-4 rounded-2xl text-sm font-bold text-white bg-[#0071e3] hover:bg-[#0077ed] active:scale-[0.98] shadow-[0_4px_20px_rgba(0,113,227,0.35)] transition-all cursor-pointer">
                        Sign In
                    </button>
                </div>

                <div class="pt-3 text-center text-xs text-slate-500 dark:text-slate-400">
                    New team member? 
                    <a href="{{ route('register') }}" class="font-bold text-[#0071e3] hover:underline">
                        Register Account &rarr;
                    </a>
                </div>
            </form>

        </div>
    </div>
</body>
</html>
