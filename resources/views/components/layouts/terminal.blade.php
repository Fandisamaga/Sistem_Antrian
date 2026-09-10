<!doctype html>
<html lang="id" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $title ?? 'Sistem Antrian Dukcapil' }}</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700,800" rel="stylesheet" />
        <script>
            // Terapkan pilihan sebelum halaman dirender agar tidak terjadi kilatan warna.
            if (localStorage.getItem('theme') === 'light') {
                document.documentElement.classList.remove('dark');
            }
        </script>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="flex min-h-screen flex-col bg-slate-100 text-slate-900 antialiased selection:bg-blue-500 selection:text-white transition-colors duration-300 dark:bg-slate-950 dark:text-slate-100 font-sans">
        <div class="pointer-events-none fixed inset-0 -z-10 bg-[radial-gradient(circle_at_12%_8%,rgba(59,130,246,0.10),transparent_28%),radial-gradient(circle_at_88%_90%,rgba(16,185,129,0.08),transparent_30%)] dark:bg-[radial-gradient(circle_at_12%_8%,rgba(59,130,246,0.20),transparent_28%),radial-gradient(circle_at_88%_90%,rgba(16,185,129,0.14),transparent_30%)]"></div>
        <header class="no-print flex items-center justify-between border-b border-slate-200/80 bg-white/70 px-4 py-3 backdrop-blur-sm transition-colors duration-300 sm:px-6 lg:px-8 dark:border-blue-400/15 dark:bg-slate-950/55">
            <!-- Nama Dinas dan Logo di Pojok Kiri Atas -->
            <div class="flex items-center gap-3.5">
                <img src="{{ asset('Logo.png') }}" alt="Logo Dinas" class="h-12 w-auto object-contain drop-shadow-md">
                <div>
                    <h1 class="text-base font-black leading-tight tracking-wide text-slate-900 uppercase sm:text-lg dark:text-white">
                        DINAS KEPENDUDUKAN & PENCATATAN SIPIL
                    </h1>
                    <p class="text-xs font-bold text-blue-400 tracking-wider uppercase">
                        KOTA PALU
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2 sm:gap-3">
                @auth
                    <div class="hidden sm:flex items-center gap-2 rounded-2xl border border-slate-200 bg-slate-50/80 px-3 py-1.5 text-xs dark:border-slate-800 dark:bg-slate-900/70">
                        @if(auth()->user()->isAdmin())
                            <a href="{{ route('admin.dashboard') }}" class="font-bold text-indigo-600 dark:text-indigo-400 hover:underline">Admin</a>
                            <span class="text-slate-300 dark:text-slate-700">&bull;</span>
                            <a href="{{ route('cs.index') }}" class="font-bold text-slate-600 dark:text-slate-300 hover:underline">CS</a>
                            <span class="text-slate-300 dark:text-slate-700">&bull;</span>
                            <a href="{{ route('display.index') }}" class="font-bold text-slate-600 dark:text-slate-300 hover:underline">Display</a>
                        @elseif(auth()->user()->isOperator())
                            <a href="{{ route('operator.index') }}" class="font-bold text-blue-600 dark:text-blue-400">Panel Operator</a>
                        @else
                            <a href="{{ route('cs.index') }}" class="font-bold text-blue-600 dark:text-blue-400">Loket CS</a>
                        @endif
                        <span class="text-slate-300 dark:text-slate-700">&bull;</span>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="text-rose-500 font-bold hover:underline cursor-pointer">Keluar</button>
                        </form>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="hidden sm:inline-block text-xs font-bold text-slate-600 dark:text-slate-400 hover:text-blue-500 px-2 py-1">
                        Login Petugas
                    </a>
                @endauth

                <button id="btn-theme-toggle" type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-slate-50 text-slate-600 shadow-sm transition hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:border-slate-700 dark:bg-slate-900/70 dark:text-amber-300 dark:hover:bg-slate-800 dark:focus:ring-offset-slate-950" aria-label="Ganti ke mode terang" title="Ganti tema">
                    <svg id="theme-icon-sun" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2m0 14v2m9-9h-2M5 12H3m15.364 6.364l-1.414-1.414M7.05 7.05 5.636 5.636m12.728 0L16.95 7.05M7.05 16.95l-1.414 1.414M16 12a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                    <svg id="theme-icon-moon" class="hidden h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" /></svg>
                </button>
                <div class="flex flex-col items-end rounded-2xl border border-slate-200 bg-slate-50/80 px-4 py-1.5 shadow-inner dark:border-slate-800 dark:bg-slate-900/70">
                    <div id="live-clock" class="font-mono text-sm font-black tracking-wider text-emerald-600 sm:text-base dark:text-emerald-400">--:--:-- WIB</div>
                    <div id="live-date" class="text-[10px] font-semibold text-slate-500 sm:text-xs dark:text-slate-400">Memuat tanggal...</div>
                </div>
            </div>
        </header>

        <main class="flex-1 flex flex-col">{{ $slot }}</main>
    </body>
</html>
