<x-layouts.terminal title="Super Admin - Arsip & Laporan Analitik">
    <section class="flex-1 p-4 sm:p-6 lg:p-8">
        <div class="mx-auto max-w-7xl">
            <!-- Header & Filter Utama -->
            <div class="mb-7 rounded-3xl border border-blue-200 bg-white/80 p-6 shadow-2xl shadow-blue-950/10 backdrop-blur-xl dark:border-blue-400/20 dark:bg-slate-900/75 dark:shadow-blue-950/20 sm:p-7">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between mb-6">
                    <div>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('admin.dashboard') }}" class="text-xs font-bold text-blue-600 dark:text-blue-400 hover:underline">&larr; Kembali ke Dashboard</a>
                        </div>
                        <h1 class="mt-2 text-3xl font-black tracking-tight text-slate-900 dark:text-white sm:text-4xl">
                            ARSIP & LAPORAN ANALITIK
                        </h1>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                            Analisis komprehensif berdasarkan Jenis Layanan, Beban Meja Loket, Kinerja Operator, serta Arsip Transaksi.
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        <a href="{{ route('admin.reports.print', request()->query()) }}" target="_blank" class="flex items-center gap-2 rounded-2xl bg-emerald-600 px-5 py-3 text-xs font-black text-white shadow-lg shadow-emerald-500/25 hover:bg-emerald-500 transition">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                            <span>Cetak Laporan Resmi (PDF)</span>
                        </a>
                    </div>
                </div>

                <!-- Tombol Preset Periode Cepat -->
                <div class="mb-5 flex flex-wrap items-center gap-2 border-t border-slate-200/80 pt-5 dark:border-slate-800">
                    <span class="text-xs font-black uppercase text-slate-500 mr-2">Periode Waktu:</span>
                    
                    <a href="{{ route('admin.reports.index', array_merge(request()->except(['periode', 'date_from', 'date_to', 'page']), ['periode' => 'today'])) }}"
                        class="rounded-xl px-4 py-2 text-xs font-black transition {{ $periode === 'today' ? 'bg-blue-600 text-white shadow-md shadow-blue-500/20' : 'border border-slate-200 bg-white text-slate-600 hover:border-blue-400 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-300' }}">
                        Hari Ini
                    </a>

                    <a href="{{ route('admin.reports.index', array_merge(request()->except(['periode', 'date_from', 'date_to', 'page']), ['periode' => 'week'])) }}"
                        class="rounded-xl px-4 py-2 text-xs font-black transition {{ $periode === 'week' ? 'bg-blue-600 text-white shadow-md shadow-blue-500/20' : 'border border-slate-200 bg-white text-slate-600 hover:border-blue-400 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-300' }}">
                        Minggu Ini
                    </a>

                    <a href="{{ route('admin.reports.index', array_merge(request()->except(['periode', 'date_from', 'date_to', 'page']), ['periode' => 'month'])) }}"
                        class="rounded-xl px-4 py-2 text-xs font-black transition {{ $periode === 'month' ? 'bg-blue-600 text-white shadow-md shadow-blue-500/20' : 'border border-slate-200 bg-white text-slate-600 hover:border-blue-400 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-300' }}">
                        Bulan Ini
                    </a>

                    <span class="ml-auto inline-flex items-center rounded-xl bg-blue-500/10 px-3 py-1.5 text-xs font-bold text-blue-600 dark:text-blue-400">
                        Aktif: {{ $periodeLabel }}
                    </span>
                </div>

                <!-- Form Filter Parameter -->
                <form method="GET" action="{{ route('admin.reports.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-6 gap-3 border-t border-slate-200/80 pt-4 dark:border-slate-800">
                    <input type="hidden" name="periode" value="custom">

                    <div>
                        <label class="block text-[11px] font-bold uppercase text-slate-500 mb-1">Dari Tanggal</label>
                        <input type="date" name="date_from" value="{{ $dateFrom }}" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-bold dark:border-slate-800 dark:bg-slate-950 dark:text-white">
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold uppercase text-slate-500 mb-1">Sampai Tanggal</label>
                        <input type="date" name="date_to" value="{{ $dateTo }}" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-bold dark:border-slate-800 dark:bg-slate-950 dark:text-white">
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold uppercase text-slate-500 mb-1">Jenis Layanan</label>
                        <select name="layanan_id" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-bold dark:border-slate-800 dark:bg-slate-950 dark:text-white">
                            <option value="">Semua Layanan</option>
                            @foreach ($allLayanans as $l)
                                <option value="{{ $l->id }}" @selected($selectedLayananId == $l->id)>{{ $l->nama_layanan }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold uppercase text-slate-500 mb-1">Meja Loket</label>
                        <select name="meja_id" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-bold dark:border-slate-800 dark:bg-slate-950 dark:text-white">
                            <option value="">Semua Meja</option>
                            @foreach ($allMejas as $m)
                                <option value="{{ $m->id }}" @selected($selectedMejaId == $m->id)>{{ $m->nama_meja }} ({{ $m->formatted_nomor }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold uppercase text-slate-500 mb-1">Petugas Operator</label>
                        <select name="operator_id" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-bold dark:border-slate-800 dark:bg-slate-950 dark:text-white">
                            <option value="">Semua Operator</option>
                            @foreach ($allOperators as $op)
                                <option value="{{ $op->id }}" @selected($selectedOperatorId == $op->id)>{{ $op->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex items-end gap-2">
                        <button type="submit" class="flex-1 rounded-xl bg-blue-600 px-4 py-2 text-xs font-black text-white hover:bg-blue-500 transition">
                            Filter
                        </button>
                        <a href="{{ route('admin.reports.index') }}" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-bold text-slate-600 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-300">
                            Reset
                        </a>
                    </div>
                </form>
            </div>

            <!-- Ringkasan Periode Terpilih -->
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-2 lg:grid-cols-4 mb-8">
                <div class="rounded-3xl border border-slate-200 bg-white/80 p-5 shadow-lg dark:border-slate-800 dark:bg-slate-900/80 backdrop-blur-xl">
                    <span class="text-xs font-black text-slate-500 uppercase tracking-wider">Total Antrean Terdata</span>
                    <p class="mt-2 text-4xl font-black text-blue-600 dark:text-blue-400">{{ $totalPeriod }}</p>
                    <p class="mt-1 text-xs text-slate-400">Periode: {{ $dateFrom }} s/d {{ $dateTo }}</p>
                </div>

                <div class="rounded-3xl border border-slate-200 bg-white/80 p-5 shadow-lg dark:border-slate-800 dark:bg-slate-900/80 backdrop-blur-xl">
                    <span class="text-xs font-black text-slate-500 uppercase tracking-wider">Berhasil Dilayani</span>
                    <p class="mt-2 text-4xl font-black text-emerald-600 dark:text-emerald-400">{{ $completedPeriod }}</p>
                    <p class="mt-1 text-xs text-slate-400">{{ $skippedPeriod }} dilewati / tidak hadir</p>
                </div>

                <div class="rounded-3xl border border-slate-200 bg-white/80 p-5 shadow-lg dark:border-slate-800 dark:bg-slate-900/80 backdrop-blur-xl">
                    <span class="text-xs font-black text-slate-500 uppercase tracking-wider">Rata-Rata Waktu Layanan</span>
                    <p class="mt-2 text-4xl font-black text-indigo-600 dark:text-indigo-400">{{ $avgServeFormatted }}</p>
                    <p class="mt-1 text-xs text-slate-400">Kecepatan pelayanan loket</p>
                </div>

                <div class="rounded-3xl border border-slate-200 bg-white/80 p-5 shadow-lg dark:border-slate-800 dark:bg-slate-900/80 backdrop-blur-xl">
                    <span class="text-xs font-black text-slate-500 uppercase tracking-wider">Rata-Rata Waktu Tunggu</span>
                    <p class="mt-2 text-4xl font-black text-amber-600 dark:text-amber-400">{{ $avgWaitFormatted }}</p>
                    <p class="mt-1 text-xs text-slate-400">Waktu tunggu warga</p>
                </div>
            </div>

            <!-- SEKSI 1: REKAPITULASI BERDASARKAN JENIS LAYANAN -->
            <div class="mb-8 rounded-3xl border border-slate-200 bg-white/80 p-6 shadow-xl dark:border-slate-800 dark:bg-slate-900/80 backdrop-blur-xl">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-xl font-black text-slate-900 dark:text-white">1. Rekapitulasi Berdasarkan Jenis Layanan</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Distribusi volume permohonan, rasio penyelesaian, dan kecepatan per jenis dokumen.</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="border-b border-slate-200 dark:border-slate-800 text-xs font-black text-slate-500 uppercase">
                                <th class="py-3 px-3">Kode</th>
                                <th class="py-3 px-3">Nama Layanan</th>
                                <th class="py-3 px-3 text-center">Total Permohonan</th>
                                <th class="py-3 px-3 text-center text-emerald-600 dark:text-emerald-400">Selesai</th>
                                <th class="py-3 px-3 text-center text-rose-600 dark:text-rose-400">Dilewati</th>
                                <th class="py-3 px-3 text-center">Proporsi (% Share)</th>
                                <th class="py-3 px-3 text-center">Rata2 Layanan</th>
                                <th class="py-3 px-3 text-center">Rata2 Tunggu</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                            @forelse ($layananBreakdown as $lb)
                                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition">
                                    <td class="py-3 px-3 font-mono text-xs font-black text-blue-600 dark:text-blue-400">
                                        {{ $lb['layanan']->kode_layanan }}
                                    </td>
                                    <td class="py-3 px-3 font-bold text-slate-900 dark:text-white">
                                        {{ $lb['layanan']->nama_layanan }}
                                    </td>
                                    <td class="py-3 px-3 text-center font-black">{{ $lb['total'] }}</td>
                                    <td class="py-3 px-3 text-center font-bold text-emerald-600 dark:text-emerald-400">{{ $lb['completed'] }}</td>
                                    <td class="py-3 px-3 text-center font-bold text-rose-600 dark:text-rose-400">{{ $lb['skipped'] }}</td>
                                    <td class="py-3 px-3 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <div class="w-16 h-2 rounded-full bg-slate-100 dark:bg-slate-800 overflow-hidden">
                                                <div class="h-full bg-blue-600 rounded-full" style="width: {{ min($lb['share'], 100) }}%"></div>
                                            </div>
                                            <span class="text-xs font-black text-slate-700 dark:text-slate-300">{{ $lb['share'] }}%</span>
                                        </div>
                                    </td>
                                    <td class="py-3 px-3 text-center font-mono text-xs font-bold text-indigo-600 dark:text-indigo-400">
                                        {{ $lb['avg_serve_formatted'] }}
                                    </td>
                                    <td class="py-3 px-3 text-center font-mono text-xs text-slate-500 dark:text-slate-400">
                                        {{ $lb['avg_wait_formatted'] }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="py-8 text-center text-slate-500">Tidak ada data pelayanan untuk periode terpilih.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- SEKSI 2: REKAPITULASI BERDASARKAN MEJA LOKET -->
            <div class="mb-8 rounded-3xl border border-slate-200 bg-white/80 p-6 shadow-xl dark:border-slate-800 dark:bg-slate-900/80 backdrop-blur-xl">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-xl font-black text-slate-900 dark:text-white">2. Distribusi Beban Meja Loket Pelayanan</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Distribusi beban kerja antrean di masing-masing meja loket untuk pemerataan operasional.</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="border-b border-slate-200 dark:border-slate-800 text-xs font-black text-slate-500 uppercase">
                                <th class="py-3 px-3">Nomor Meja</th>
                                <th class="py-3 px-3">Nama Meja Loket</th>
                                <th class="py-3 px-3">Petugas Operator Ditugaskan</th>
                                <th class="py-3 px-3 text-center">Total Beban Antrean</th>
                                <th class="py-3 px-3 text-center text-emerald-600 dark:text-emerald-400">Selesai</th>
                                <th class="py-3 px-3 text-center text-rose-600 dark:text-rose-400">Dilewati</th>
                                <th class="py-3 px-3 text-center">Beban Loket (% Share)</th>
                                <th class="py-3 px-3 text-center">Rata2 Layanan</th>
                                <th class="py-3 px-3 text-center">Rata2 Tunggu</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                            @forelse ($mejaBreakdown as $mb)
                                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition">
                                    <td class="py-3 px-3 font-mono font-black text-blue-600 dark:text-blue-400">
                                        {{ $mb['meja']->formatted_nomor }}
                                    </td>
                                    <td class="py-3 px-3 font-black text-slate-900 dark:text-white">
                                        {{ $mb['meja']->nama_meja }}
                                    </td>
                                    <td class="py-3 px-3 text-xs font-semibold text-slate-600 dark:text-slate-300">
                                        {{ $mb['operator_name'] }}
                                    </td>
                                    <td class="py-3 px-3 text-center font-black">{{ $mb['total'] }}</td>
                                    <td class="py-3 px-3 text-center font-bold text-emerald-600 dark:text-emerald-400">{{ $mb['completed'] }}</td>
                                    <td class="py-3 px-3 text-center font-bold text-rose-600 dark:text-rose-400">{{ $mb['skipped'] }}</td>
                                    <td class="py-3 px-3 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <div class="w-16 h-2 rounded-full bg-slate-100 dark:bg-slate-800 overflow-hidden">
                                                <div class="h-full bg-indigo-600 rounded-full" style="width: {{ min($mb['share'], 100) }}%"></div>
                                            </div>
                                            <span class="text-xs font-black text-slate-700 dark:text-slate-300">{{ $mb['share'] }}%</span>
                                        </div>
                                    </td>
                                    <td class="py-3 px-3 text-center font-mono text-xs font-bold text-indigo-600 dark:text-indigo-400">
                                        {{ $mb['avg_serve_formatted'] }}
                                    </td>
                                    <td class="py-3 px-3 text-center font-mono text-xs text-slate-500 dark:text-slate-400">
                                        {{ $mb['avg_wait_formatted'] }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="py-8 text-center text-slate-500">Tidak ada data transaksi meja loket untuk periode ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- SEKSI 3: KINERJA OPERATOR -->
            <div class="mb-10 rounded-3xl border border-slate-200 bg-white/80 p-6 shadow-xl dark:border-slate-800 dark:bg-slate-900/80 backdrop-blur-xl">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-xl font-black text-slate-900 dark:text-white">3. Matriks Kinerja Masing-Masing Petugas Operator</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Evaluasi produktivitas, ketepatan waktu, dan tingkat penyelesaian antrean per petugas.</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="border-b border-slate-200 dark:border-slate-800 text-xs font-black text-slate-500 uppercase">
                                <th class="py-3 px-3">Peringkat</th>
                                <th class="py-3 px-3">Nama Petugas Operator</th>
                                <th class="py-3 px-3">Meja</th>
                                <th class="py-3 px-3 text-center">Total Ditangani</th>
                                <th class="py-3 px-3 text-center text-emerald-600 dark:text-emerald-400">Selesai</th>
                                <th class="py-3 px-3 text-center text-rose-600 dark:text-rose-400">Dilewati</th>
                                <th class="py-3 px-3 text-center">Tingkat Sukses</th>
                                <th class="py-3 px-3 text-center">Rata-Rata Layanan</th>
                                <th class="py-3 px-3 text-center">Rata-Rata Tunggu</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                            @forelse ($operatorPerformance as $index => $op)
                                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition">
                                    <td class="py-3 px-3 font-bold text-xs text-slate-400">#{{ $index + 1 }}</td>
                                    <td class="py-3 px-3">
                                        <p class="font-black text-slate-900 dark:text-white">{{ $op['operator']->name }}</p>
                                        <p class="text-xs text-slate-400">{{ $op['operator']->email }}</p>
                                    </td>
                                    <td class="py-3 px-3 font-semibold">{{ $op['meja'] }}</td>
                                    <td class="py-3 px-3 text-center font-black">{{ $op['total_handled'] }}</td>
                                    <td class="py-3 px-3 text-center font-black text-emerald-600 dark:text-emerald-400">{{ $op['completed'] }}</td>
                                    <td class="py-3 px-3 text-center font-black text-rose-600 dark:text-rose-400">{{ $op['skipped'] }}</td>
                                    <td class="py-3 px-3 text-center">
                                        <span class="rounded-lg bg-blue-500/15 px-2 py-0.5 text-xs font-black text-blue-600 dark:text-blue-400">
                                            {{ $op['completion_rate'] }}%
                                        </span>
                                    </td>
                                    <td class="py-3 px-3 text-center font-mono text-xs font-bold text-indigo-600 dark:text-indigo-400">
                                        {{ $op['avg_serve_formatted'] }}
                                    </td>
                                    <td class="py-3 px-3 text-center font-mono text-xs text-slate-500 dark:text-slate-400">
                                        {{ $op['avg_wait_formatted'] }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="py-8 text-center text-slate-500">Belum ada data aktivitas operator pada periode ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- SEKSI 4: ARSIP SELURUH ANTREAN -->
            <div class="rounded-3xl border border-slate-200 bg-white/80 p-6 shadow-xl dark:border-slate-800 dark:bg-slate-900/80 backdrop-blur-xl">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-xl font-black text-slate-900 dark:text-white">4. Arsip Riwayat Transaksi Antrean</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Data mentah transaksi antrean untuk keperluan audit dan pembukuan dinas.</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="border-b border-slate-200 dark:border-slate-800 text-xs font-black text-slate-500 uppercase">
                                <th class="py-3 px-3">Tanggal & Jam</th>
                                <th class="py-3 px-3">No. Antrean</th>
                                <th class="py-3 px-3">Jenis Layanan</th>
                                <th class="py-3 px-3">Meja Loket</th>
                                <th class="py-3 px-3">Petugas Operator</th>
                                <th class="py-3 px-3">Status</th>
                                <th class="py-3 px-3 text-center">Waktu Tunggu</th>
                                <th class="py-3 px-3 text-center">Durasi Layanan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                            @forelse ($queues as $q)
                                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition">
                                    <td class="py-3 px-3 text-xs text-slate-500">
                                        {{ $q->created_at->format('d/m/Y H:i:s') }}
                                    </td>
                                    <td class="py-3 px-3 font-black text-base text-slate-900 dark:text-white">{{ $q->queue_number }}</td>
                                    <td class="py-3 px-3 text-xs font-bold text-blue-600 dark:text-blue-400">{{ $q->layanan?->nama_layanan ?? '-' }}</td>
                                    <td class="py-3 px-3 font-bold">{{ $q->meja?->nama_meja ?? '-' }}</td>
                                    <td class="py-3 px-3 text-xs font-semibold">{{ $q->operator?->name ?? '-' }}</td>
                                    <td class="py-3 px-3">
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
                                    <td class="py-3 px-3 text-center font-mono text-xs text-slate-500">
                                        {{ $q->formatted_wait_duration }}
                                    </td>
                                    <td class="py-3 px-3 text-center font-mono text-xs font-bold text-indigo-600 dark:text-indigo-400">
                                        {{ $q->formatted_serve_duration }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="py-8 text-center text-slate-500">Tidak ada riwayat antrean untuk filter ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 pt-4 border-t border-slate-200 dark:border-slate-800">
                    {{ $queues->links() }}
                </div>
            </div>
        </div>
    </section>
</x-layouts.terminal>

