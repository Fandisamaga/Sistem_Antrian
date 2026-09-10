<x-layouts.terminal title="Super Admin - Operator & Meja">
    <section class="flex-1 p-4 sm:p-6 lg:p-8">
        <div class="mx-auto max-w-7xl">
            <!-- Header -->
            <div class="mb-7 rounded-3xl border border-blue-200 bg-white/80 p-6 shadow-2xl shadow-blue-950/10 backdrop-blur-xl dark:border-blue-400/20 dark:bg-slate-900/75 dark:shadow-blue-950/20 sm:p-7">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('admin.dashboard') }}" class="text-xs font-bold text-blue-600 dark:text-blue-400 hover:underline">&larr; Kembali ke Dashboard</a>
                        </div>
                        <h1 class="mt-2 text-3xl font-black tracking-tight text-slate-900 dark:text-white sm:text-4xl">
                            OPERATOR & MEJA LOKET
                        </h1>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                            Kelola akun petugas operator loket, penugasan nomor meja, dan layanan terkait.
                        </p>
                    </div>

                    <div class="flex items-center gap-2">
                        <a href="{{ route('admin.layanans.index') }}" class="rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-black text-slate-700 shadow-sm transition hover:border-blue-500 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-200">
                            Kelola Pelayanan
                        </a>
                        <a href="{{ route('admin.reports.index') }}" class="rounded-2xl border border-blue-600 bg-blue-600 px-4 py-2.5 text-xs font-black text-white shadow-lg shadow-blue-500/20 transition hover:bg-blue-500">
                            Kinerja & Arsip &rarr;
                        </a>
                    </div>
                </div>
            </div>

            @if(session('success'))
                <div class="mb-6 rounded-2xl border border-emerald-400/40 bg-emerald-500/15 p-4 text-sm font-bold text-emerald-700 dark:text-emerald-300">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 rounded-2xl border border-rose-400/40 bg-rose-500/15 p-4 text-sm font-bold text-rose-700 dark:text-rose-300">
                    {{ session('error') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-6 rounded-2xl border border-rose-400/40 bg-rose-500/15 p-4 text-sm font-bold text-rose-700 dark:text-rose-300">
                    <ul class="list-disc pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Bagian 1: Manajemen Akun Operator -->
            <div class="mb-10">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-xl font-black text-slate-900 dark:text-white">Daftar Akun Operator</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Akun untuk masuk ke panel operator pemanggil antrean.</p>
                    </div>
                    <button type="button" onclick="document.querySelector('#modal-add-operator').classList.remove('hidden')" class="rounded-2xl bg-blue-600 px-4 py-2.5 text-xs font-black text-white shadow-lg shadow-blue-500/20 hover:bg-blue-500 transition">
                        + Tambah Akun Operator
                    </button>
                </div>

                <div class="rounded-3xl border border-slate-200 bg-white/80 p-6 shadow-xl dark:border-slate-800 dark:bg-slate-900/80 backdrop-blur-xl overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="border-b border-slate-200 dark:border-slate-800 text-xs font-black text-slate-500 uppercase">
                                <th class="py-3 px-3">Nama Petugas</th>
                                <th class="py-3 px-3">Email Login</th>
                                <th class="py-3 px-3">Meja Penugasan</th>
                                <th class="py-3 px-3">Fleksibilitas Pelayanan</th>
                                <th class="py-3 px-3 text-center">Hari Ini Selesai</th>
                                <th class="py-3 px-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                            @forelse ($operators as $op)
                                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition">
                                    <td class="py-3 px-3 font-black text-slate-900 dark:text-white">{{ $op->name }}</td>
                                    <td class="py-3 px-3 font-mono text-xs text-slate-500 dark:text-slate-400">{{ $op->email }}</td>
                                    <td class="py-3 px-3">
                                        @if($op->meja)
                                            <span class="rounded-lg bg-blue-500/15 px-2.5 py-1 text-xs font-black text-blue-600 dark:text-blue-400">
                                                {{ $op->meja->nama_meja }} ({{ $op->meja->formatted_nomor }})
                                            </span>
                                        @else
                                            <span class="rounded-lg bg-amber-500/15 px-2 py-0.5 text-xs font-bold text-amber-600">Belum Ditautkan</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-3 text-xs">
                                        <span class="rounded-lg bg-emerald-500/15 px-2 py-0.5 text-xs font-bold text-emerald-600 dark:text-emerald-400">Semua Layanan (Dinamis)</span>
                                    </td>
                                    <td class="py-3 px-3 text-center font-black text-emerald-600 dark:text-emerald-400">
                                        {{ $op->today_completed ?? 0 }}
                                    </td>
                                    <td class="py-3 px-3 text-right">
                                        <form method="POST" action="{{ route('admin.operators.destroy', $op) }}" class="inline" onsubmit="return confirm('Hapus akun operator ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-xs font-bold text-rose-600 hover:underline">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-8 text-center text-slate-500">Belum ada akun operator.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Bagian 2: Manajemen Meja Loket -->
            <div>
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-xl font-black text-slate-900 dark:text-white">Daftar Meja Loket Pelayanan</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Daftar meja fisik yang muncul pada tombol CS dan layar Display.</p>
                    </div>
                    <button type="button" onclick="document.querySelector('#modal-add-meja').classList.remove('hidden')" class="rounded-2xl bg-emerald-600 px-4 py-2.5 text-xs font-black text-white shadow-lg shadow-emerald-500/20 hover:bg-emerald-500 transition">
                        + Tambah Meja Baru
                    </button>
                </div>

                <div class="rounded-3xl border border-slate-200 bg-white/80 p-6 shadow-xl dark:border-slate-800 dark:bg-slate-900/80 backdrop-blur-xl overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="border-b border-slate-200 dark:border-slate-800 text-xs font-black text-slate-500 uppercase">
                                <th class="py-3 px-3">Nomor Meja</th>
                                <th class="py-3 px-3">Nama Meja</th>
                                <th class="py-3 px-3">Cakupan Pelayanan</th>
                                <th class="py-3 px-3">Petugas Operator</th>
                                <th class="py-3 px-3 text-center">Antrean Hari Ini</th>
                                <th class="py-3 px-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                            @forelse ($mejas as $meja)
                                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition">
                                    <td class="py-3 px-3 font-black text-base text-blue-600 dark:text-blue-400">
                                        {{ $meja->formatted_nomor }}
                                    </td>
                                    <td class="py-3 px-3 font-black text-slate-900 dark:text-white">{{ $meja->nama_meja }}</td>
                                    <td class="py-3 px-3 text-xs">
                                        <span class="rounded-lg bg-blue-500/15 px-2 py-0.5 text-xs font-bold text-blue-600 dark:text-blue-400">Fleksibel (Dipilih CS)</span>
                                    </td>
                                    <td class="py-3 px-3 text-xs">
                                        {{ $meja->user?->name ?? 'Belum Ada' }}
                                    </td>
                                    <td class="py-3 px-3 text-center font-black">
                                        {{ $meja->today_queues ?? 0 }}
                                    </td>
                                    <td class="py-3 px-3 text-right">
                                        <form method="POST" action="{{ route('admin.mejas.destroy', $meja) }}" class="inline" onsubmit="return confirm('Hapus meja loket ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-xs font-bold text-rose-600 hover:underline">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-8 text-center text-slate-500">Belum ada meja yang ditambahkan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

    <!-- Modal Tambah Operator -->
    <div id="modal-add-operator" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/70 p-4 backdrop-blur-sm flex">
        <div class="w-full max-w-md rounded-3xl border border-slate-200 bg-white p-6 shadow-2xl dark:border-slate-800 dark:bg-slate-900">
            <h3 class="text-lg font-black text-slate-900 dark:text-white mb-1">Tambah Akun Operator</h3>
            <p class="text-xs text-slate-500 mb-4">Buat kredensial login untuk petugas loket.</p>

            <form method="POST" action="{{ route('admin.operators.store') }}" class="space-y-3.5">
                @csrf
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-700 dark:text-slate-300 mb-1">Nama Lengkap</label>
                    <input type="text" name="name" required placeholder="Contoh: Budi Santoso" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2 text-sm dark:border-slate-800 dark:bg-slate-950 dark:text-white">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-700 dark:text-slate-300 mb-1">Email</label>
                    <input type="email" name="email" required placeholder="operator21@dukcapil.test" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2 text-sm dark:border-slate-800 dark:bg-slate-950 dark:text-white">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-700 dark:text-slate-300 mb-1">Kata Sandi</label>
                    <input type="password" name="password" required minlength="6" placeholder="Minimal 6 karakter" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2 text-sm dark:border-slate-800 dark:bg-slate-950 dark:text-white">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-700 dark:text-slate-300 mb-1">Tautkan ke Meja</label>
                    <select name="meja_id" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2 text-sm dark:border-slate-800 dark:bg-slate-950 dark:text-white">
                        <option value="">-- Pilih Meja --</option>
                        @foreach ($mejas as $m)
                            <option value="{{ $m->id }}">{{ $m->nama_meja }} ({{ $m->formatted_nomor }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex gap-2.5 pt-3">
                    <button type="button" onclick="document.querySelector('#modal-add-operator').classList.add('hidden')" class="flex-1 rounded-xl border border-slate-200 py-2.5 text-xs font-bold text-slate-700 dark:border-slate-700 dark:text-slate-300">
                        Batal
                    </button>
                    <button type="submit" class="flex-1 rounded-xl bg-blue-600 py-2.5 text-xs font-black text-white hover:bg-blue-500">
                        Simpan Akun
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Tambah Meja -->
    <div id="modal-add-meja" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/70 p-4 backdrop-blur-sm flex">
        <div class="w-full max-w-md rounded-3xl border border-slate-200 bg-white p-6 shadow-2xl dark:border-slate-800 dark:bg-slate-900">
            <h3 class="text-lg font-black text-slate-900 dark:text-white mb-1">Tambah Meja Loket Baru</h3>
            <p class="text-xs text-slate-500 mb-4">Daftarkan loket meja pelayanan baru di kantor dinas.</p>

            <form method="POST" action="{{ route('admin.mejas.store') }}" class="space-y-3.5">
                @csrf
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-700 dark:text-slate-300 mb-1">Nama Meja</label>
                    <input type="text" name="nama_meja" required placeholder="Contoh: Meja 21" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2 text-sm dark:border-slate-800 dark:bg-slate-950 dark:text-white">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-700 dark:text-slate-300 mb-1">Nomor Meja (Angka Urut)</label>
                    <input type="number" name="nomor_meja" required min="1" placeholder="21" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2 text-sm dark:border-slate-800 dark:bg-slate-950 dark:text-white">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-700 dark:text-slate-300 mb-1">Spesialisasi / Kategori Layanan (Opsional)</label>
                    <select name="layanan_id" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2 text-sm dark:border-slate-800 dark:bg-slate-950 dark:text-white">
                        <option value="">-- Fleksibel / Semua Layanan (Direkomendasikan) --</option>
                        @foreach ($layanans as $l)
                            <option value="{{ $l->id }}">{{ $l->nama_layanan }} ({{ $l->kode_layanan }})</option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-[11px] text-slate-400">Pilihan layanan ditentukan secara dinamis oleh petugas CS saat mencetak tiket antrean.</p>
                </div>

                <div class="flex gap-2.5 pt-3">
                    <button type="button" onclick="document.querySelector('#modal-add-meja').classList.add('hidden')" class="flex-1 rounded-xl border border-slate-200 py-2.5 text-xs font-bold text-slate-700 dark:border-slate-700 dark:text-slate-300">
                        Batal
                    </button>
                    <button type="submit" class="flex-1 rounded-xl bg-emerald-600 py-2.5 text-xs font-black text-white hover:bg-emerald-500">
                        Simpan Meja
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.terminal>

