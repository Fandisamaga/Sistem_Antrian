<x-layouts.terminal title="Super Admin - Dashboard Antrian">
    <section class="flex-1 p-4 sm:p-6 lg:p-8">
        <div class="mx-auto max-w-7xl">
            <!-- Header Admin -->
            <div class="mb-7 rounded-3xl border border-blue-200 bg-white/80 p-6 shadow-2xl shadow-blue-950/10 backdrop-blur-xl dark:border-blue-400/20 dark:bg-slate-900/75 dark:shadow-blue-950/20 sm:p-7">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="rounded-lg bg-indigo-500/15 px-2.5 py-1 text-xs font-black tracking-widest text-indigo-600 dark:text-indigo-400 uppercase">
                                SUPER ADMIN PANEL
                            </span>
                            <span class="text-xs font-bold text-slate-500 dark:text-slate-400">
                                Selamat datang, {{ auth()->user()->name }}
                            </span>
                        </div>
                        <h1 class="mt-2 text-3xl font-black tracking-tight text-slate-900 dark:text-white sm:text-4xl">
                            DASHBOARD MONITORING
                        </h1>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                            Ringkasan performa dan pemantauan antrean pelayanan hari ini.
                        </p>
                    </div>

                    <!-- Navigasi Cepat Admin -->
                    <div class="flex flex-wrap items-center gap-2">
                        <a href="{{ route('admin.layanans.index') }}" class="rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-black text-slate-700 shadow-sm transition hover:border-blue-500 hover:text-blue-600 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-200 dark:hover:border-blue-400">
                            Kelola Layanan
                        </a>
                        <a href="{{ route('admin.operators.index') }}" class="rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-black text-slate-700 shadow-sm transition hover:border-blue-500 hover:text-blue-600 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-200 dark:hover:border-blue-400">
                            Operator & Meja
                        </a>
                        <a href="{{ route('admin.reports.index') }}" class="rounded-2xl border border-blue-600 bg-blue-600 px-4 py-2.5 text-xs font-black text-white shadow-lg shadow-blue-500/20 transition hover:bg-blue-500">
                            Arsip & Laporan
                        </a>
                    </div>
                </div>
            </div>

            <!-- Kartu Metrik Hari Ini -->
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-2 lg:grid-cols-4 mb-7">
                <div class="rounded-3xl border border-slate-200 bg-white/80 p-5 shadow-lg dark:border-slate-800 dark:bg-slate-900/80 backdrop-blur-xl">
                    <span class="text-xs font-black text-slate-500 uppercase tracking-wider">Total Antrean Hari Ini</span>
                    <p class="mt-2 text-4xl font-black text-blue-600 dark:text-blue-400">{{ $totalToday }}</p>
                    <p class="mt-1 text-xs text-slate-400">{{ $waitingToday }} warga sedang menunggu</p>
                </div>

                <div class="rounded-3xl border border-slate-200 bg-white/80 p-5 shadow-lg dark:border-slate-800 dark:bg-slate-900/80 backdrop-blur-xl">
                    <span class="text-xs font-black text-slate-500 uppercase tracking-wider">Selesai Dilayani</span>
                    <p class="mt-2 text-4xl font-black text-emerald-600 dark:text-emerald-400">{{ $completedToday }}</p>
                    <p class="mt-1 text-xs text-slate-400">{{ $skippedToday }} dilewati / batal</p>
                </div>

                <div class="rounded-3xl border border-slate-200 bg-white/80 p-5 shadow-lg dark:border-slate-800 dark:bg-slate-900/80 backdrop-blur-xl">
                    <span class="text-xs font-black text-slate-500 uppercase tracking-wider">Rata-Rata Waktu Layanan</span>
                    <p class="mt-2 text-4xl font-black text-indigo-600 dark:text-indigo-400">{{ $avgServe }}</p>
                    <p class="mt-1 text-xs text-slate-400">Durasi operator melayani</p>
                </div>

                <div class="rounded-3xl border border-slate-200 bg-white/80 p-5 shadow-lg dark:border-slate-800 dark:bg-slate-900/80 backdrop-blur-xl">
                    <span class="text-xs font-black text-slate-500 uppercase tracking-wider">Rata-Rata Waktu Tunggu</span>
                    <p class="mt-2 text-4xl font-black text-amber-600 dark:text-amber-400">{{ $avgWait }}</p>
                    <p class="mt-1 text-xs text-slate-400">Sebelum dipanggil loket</p>
                </div>
            </div>

            <!-- Grid Ringkasan Sumber Daya & Antrean Terkini -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <!-- Info Master Data -->
                <div class="rounded-3xl border border-slate-200 bg-white/80 p-6 shadow-xl dark:border-slate-800 dark:bg-slate-900/80 backdrop-blur-xl">
                    <h2 class="text-lg font-black text-slate-900 dark:text-white mb-4">Master Data Loket</h2>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-950/60 border border-slate-200/60 dark:border-slate-800/80">
                            <div>
                                <p class="text-xs font-black text-slate-500 uppercase">Jenis Layanan Aktif</p>
                                <p class="text-xl font-black text-slate-900 dark:text-white">{{ $totalLayanans }} Layanan</p>
                            </div>
                            <a href="{{ route('admin.layanans.index') }}" class="text-xs font-bold text-blue-600 hover:underline">Kelola &rarr;</a>
                        </div>

                        <div class="flex items-center justify-between p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-950/60 border border-slate-200/60 dark:border-slate-800/80">
                            <div>
                                <p class="text-xs font-black text-slate-500 uppercase">Total Meja Loket</p>
                                <p class="text-xl font-black text-slate-900 dark:text-white">{{ $totalMejas }} Meja</p>
                            </div>
                            <a href="{{ route('admin.operators.index') }}" class="text-xs font-bold text-blue-600 hover:underline">Kelola &rarr;</a>
                        </div>

                        <div class="flex items-center justify-between p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-950/60 border border-slate-200/60 dark:border-slate-800/80">
                            <div>
                                <p class="text-xs font-black text-slate-500 uppercase">Akun Operator</p>
                                <p class="text-xl font-black text-slate-900 dark:text-white">{{ $totalOperators }} Operator</p>
                            </div>
                            <a href="{{ route('admin.operators.index') }}" class="text-xs font-bold text-blue-600 hover:underline">Kelola &rarr;</a>
                        </div>
                    </div>

                    <div class="mt-6 pt-5 border-t border-slate-200 dark:border-slate-800">
                        <div class="flex items-center gap-3">
                            <a href="{{ route('cs.index') }}" target="_blank" class="flex-1 text-center rounded-xl bg-slate-100 dark:bg-slate-800 py-2.5 text-xs font-bold text-slate-700 dark:text-slate-200 hover:bg-slate-200 dark:hover:bg-slate-700 transition">
                                Buka Loket CS
                            </a>
                            <a href="{{ route('display.index') }}" target="_blank" class="flex-1 text-center rounded-xl bg-blue-500/15 border border-blue-500/30 py-2.5 text-xs font-black text-blue-600 dark:text-blue-400 hover:bg-blue-500/25 transition">
                                Buka Display TV
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Antrean Terkini Hari Ini -->
                <div class="lg:col-span-2 rounded-3xl border border-slate-200 bg-white/80 p-6 shadow-xl dark:border-slate-800 dark:bg-slate-900/80 backdrop-blur-xl">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-black text-slate-900 dark:text-white">Aktivitas Antrean Terkini</h2>
                        <a href="{{ route('admin.reports.index') }}" class="text-xs font-bold text-blue-600 dark:text-blue-400 hover:underline">Lihat Semua Arsip &rarr;</a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead>
                                <tr class="border-b border-slate-200 dark:border-slate-800 text-xs font-black text-slate-500 uppercase">
                                    <th class="py-3 px-2">No. Antrean</th>
                                    <th class="py-3 px-2">Meja Tujuan</th>
                                    <th class="py-3 px-2">Layanan</th>
                                    <th class="py-3 px-2">Status</th>
                                    <th class="py-3 px-2">Operator</th>
                                    <th class="py-3 px-2 text-right">Waktu</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                                @forelse ($recentQueues as $q)
                                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition">
                                        <td class="py-3 px-2 font-black text-base text-slate-900 dark:text-white">{{ $q->queue_number }}</td>
                                        <td class="py-3 px-2 font-bold">{{ $q->meja?->nama_meja ?? '-' }}</td>
                                        <td class="py-3 px-2 text-xs text-slate-500 dark:text-slate-400">{{ $q->layanan?->nama_layanan ?? '-' }}</td>
                                        <td class="py-3 px-2">
                                            @if($q->status === 'completed')
                                                <span class="rounded-lg bg-emerald-500/15 px-2 py-0.5 text-xs font-black text-emerald-600 dark:text-emerald-400">Selesai</span>
                                            @elseif($q->status === 'called')
                                                <span class="rounded-lg bg-blue-500/15 px-2 py-0.5 text-xs font-black text-blue-600 dark:text-blue-400">Dipanggil</span>
                                            @elseif($q->status === 'skipped')
                                                <span class="rounded-lg bg-rose-500/15 px-2 py-0.5 text-xs font-black text-rose-600 dark:text-rose-400">Dilewati</span>
                                            @else
                                                <span class="rounded-lg bg-amber-500/15 px-2 py-0.5 text-xs font-black text-amber-600 dark:text-amber-400">Menunggu</span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-2 text-xs">{{ $q->operator?->name ?? '-' }}</td>
                                        <td class="py-3 px-2 text-right text-xs text-slate-400">{{ $q->created_at->format('H:i') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="py-8 text-center text-slate-500">Belum ada aktivitas antrean hari ini.</td>
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

