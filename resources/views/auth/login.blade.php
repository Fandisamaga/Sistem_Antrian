<x-layouts.terminal title="Masuk Sistem">
    <section class="flex flex-1 items-center justify-center p-4 sm:p-6">
        <div class="w-full max-w-md rounded-3xl border border-blue-200 bg-white/85 p-6 shadow-2xl shadow-blue-950/15 backdrop-blur-xl dark:border-blue-400/20 dark:bg-slate-900/80 dark:shadow-blue-950/30 sm:p-8">
        <p class="text-xs font-black tracking-[0.2em] text-blue-400">SISTEM ANTRIAN DUKCAPIL</p>
        <h1 class="mt-2 mb-8 text-3xl font-black tracking-tight text-slate-900 dark:text-white sm:text-4xl">MASUK PETUGAS</h1>

        <form method="POST" action="{{ route('login.store') }}" class="space-y-5">
            @csrf
            <label class="block text-sm font-bold text-slate-700 dark:text-slate-200">
                Email
                <input required type="email" name="email" value="{{ old('email') }}" class="mt-2 w-full rounded-2xl border border-slate-300 bg-white p-4 text-lg text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-400 focus:ring-2 focus:ring-blue-400/25 dark:border-slate-700 dark:bg-slate-950/80 dark:text-white dark:placeholder:text-slate-600">
            </label>
            <label class="block text-sm font-bold text-slate-700 dark:text-slate-200">
                Kata sandi
                <input required type="password" name="password" class="mt-2 w-full rounded-2xl border border-slate-300 bg-white p-4 text-lg text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-400 focus:ring-2 focus:ring-blue-400/25 dark:border-slate-700 dark:bg-slate-950/80 dark:text-white dark:placeholder:text-slate-600">
            </label>
            @error('email')
                <p class="font-bold text-red-300">{{ $message }}</p>
            @enderror
            <button class="w-full rounded-2xl bg-gradient-to-r from-blue-600 to-cyan-600 px-8 py-4 text-lg font-black text-white shadow-lg shadow-blue-500/25 transition hover:from-blue-500 hover:to-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-400 focus:ring-offset-2 focus:ring-offset-white dark:shadow-blue-950/40 dark:focus:ring-cyan-300 dark:focus:ring-offset-slate-900">MASUK</button>
        </form>

        <p class="mt-6 border-t border-slate-200 pt-5 text-sm text-slate-500 dark:border-slate-800 dark:text-slate-400">Akun awal: cs@dukcapil.test / password dan operator01@dukcapil.test / password.</p>
        </div>
    </section>
</x-layouts.terminal>
