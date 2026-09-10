<x-layouts.terminal title="Super Admin - Kelola Layanan">
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
                            MANAJEMEN PELAYANAN
                        </h1>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                            Tambah, ubah, dan atur status pelayanan kependudukan Dukcapil.
                        </p>
                    </div>

                    <div class="flex items-center gap-2">
                        <a href="{{ route('admin.operators.index') }}" class="rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-black text-slate-700 shadow-sm transition hover:border-blue-500 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-200">
                            Kelola Operator & Meja &rarr;
                        </a>
                    </div>
                </div>
            </div>

            @if(session('success'))
                <div class="mb-6 rounded-2xl border border-emerald-400/40 bg-emerald-500/15 p-4 text-sm font-bold text-emerald-700 dark:text-emerald-300">
                    {{ session('success') }}
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

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <!-- Form Tambah Layanan Baru -->
                <div class="rounded-3xl border border-slate-200 bg-white/80 p-6 shadow-xl dark:border-slate-800 dark:bg-slate-900/80 backdrop-blur-xl h-fit">
                    <h2 class="text-lg font-black text-slate-900 dark:text-white mb-1">Tambah Pelayanan Baru</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mb-5">Daftarkan jenis dokumen atau urusan kependudukan baru.</p>

                    <form method="POST" action="{{ route('admin.layanans.store') }}" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                                Nama Pelayanan
                            </label>
                            <input type="text" name="nama_layanan" value="{{ old('nama_layanan') }}" required placeholder="Contoh: Perekaman KTP-el" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm font-semibold text-slate-900 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-400/20 dark:border-slate-800 dark:bg-slate-950 dark:text-white">
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                                Kode Singkat (Maks. 10 Karakter)
                            </label>
                            <input type="text" name="kode_layanan" value="{{ old('kode_layanan') }}" required placeholder="Contoh: KTP, KK, AKTA" class="w-full uppercase rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm font-semibold text-slate-900 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-400/20 dark:border-slate-800 dark:bg-slate-950 dark:text-white">
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                                Deskripsi Singkat (Opsional)
                            </label>
                            <textarea name="deskripsi" rows="3" placeholder="Informasi berkas atau persyaratan terkait..." class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm font-semibold text-slate-900 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-400/20 dark:border-slate-800 dark:bg-slate-950 dark:text-white">{{ old('deskripsi') }}</textarea>
                        </div>

                        <div class="flex items-center gap-2 pt-2">
                            <input type="checkbox" name="is_active" id="is_active" value="1" checked class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 dark:border-slate-700">
                            <label for="is_active" class="text-xs font-bold text-slate-700 dark:text-slate-300">
                                Layanan Langsung Aktif
                            </label>
                        </div>

                        <button type="submit" class="w-full rounded-2xl bg-blue-600 px-4 py-3 text-sm font-black text-white shadow-lg shadow-blue-500/25 transition hover:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-400">
                            Simpan Layanan Baru
                        </button>
                    </form>
                </div>

                <!-- Tabel Daftar Layanan -->
                <div class="lg:col-span-2 rounded-3xl border border-slate-200 bg-white/80 p-6 shadow-xl dark:border-slate-800 dark:bg-slate-900/80 backdrop-blur-xl">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-black text-slate-900 dark:text-white">Daftar Pelayanan Terdaftar</h2>
                        <span class="rounded-xl bg-slate-100 dark:bg-slate-800 px-3 py-1 text-xs font-bold text-slate-600 dark:text-slate-300">
                            Total: {{ $layanans->count() }} Layanan
                        </span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead>
                                <tr class="border-b border-slate-200 dark:border-slate-800 text-xs font-black text-slate-500 uppercase">
                                    <th class="py-3 px-3">Kode</th>
                                    <th class="py-3 px-3">Nama Layanan</th>
                                    <th class="py-3 px-3">Meja Tertaut</th>
                                    <th class="py-3 px-3">Status</th>
                                    <th class="py-3 px-3 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                                @forelse ($layanans as $layanan)
                                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition">
                                        <td class="py-3 px-3">
                                            <span class="rounded-lg bg-blue-500/15 px-2.5 py-1 text-xs font-black text-blue-600 dark:text-blue-400">
                                                {{ $layanan->kode_layanan }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-3">
                                            <p class="font-black text-slate-900 dark:text-white">{{ $layanan->nama_layanan }}</p>
                                            <p class="text-xs text-slate-500 dark:text-slate-400 line-clamp-1">{{ $layanan->deskripsi ?: 'Tidak ada deskripsi' }}</p>
                                        </td>
                                        <td class="py-3 px-3 text-xs font-semibold">
                                            {{ $layanan->mejas_count }} Meja
                                        </td>
                                        <td class="py-3 px-3">
                                            @if($layanan->is_active)
                                                <span class="rounded-lg bg-emerald-500/15 px-2 py-0.5 text-xs font-black text-emerald-600 dark:text-emerald-400">Aktif</span>
                                            @else
                                                <span class="rounded-lg bg-slate-500/15 px-2 py-0.5 text-xs font-black text-slate-500">Nonaktif</span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-3 text-right">
                                            <form method="POST" action="{{ route('admin.layanans.destroy', $layanan) }}" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin mengubah status / menghapus layanan ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-xs font-bold text-rose-600 hover:text-rose-500 hover:underline">
                                                    {{ $layanan->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-8 text-center text-slate-500">Belum ada layanan yang ditambahkan.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-layouts.terminal>

