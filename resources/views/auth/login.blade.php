<x-layouts.terminal title="Masuk Sistem">
    <section class="mx-auto mt-16 max-w-md p-6">
        <h1 class="mb-8 text-4xl font-black">MASUK PETUGAS</h1>

        <form method="POST" action="{{ route('login.store') }}" class="space-y-5">
            @csrf
            <label class="block font-bold">
                Email
                <input required type="email" name="email" value="{{ old('email') }}" class="mt-2 w-full rounded bg-white p-4 text-lg text-slate-950">
            </label>
            <label class="block font-bold">
                Kata sandi
                <input required type="password" name="password" class="mt-2 w-full rounded bg-white p-4 text-lg text-slate-950">
            </label>
            @error('email')
                <p class="font-bold text-red-300">{{ $message }}</p>
            @enderror
            <button class="w-full rounded bg-blue-600 px-8 py-5 text-2xl font-black">MASUK</button>
        </form>

        <p class="mt-6 text-sm text-slate-400">Akun awal: cs@dukcapil.test / password dan operator01@dukcapil.test / password.</p>
    </section>
</x-layouts.terminal>
