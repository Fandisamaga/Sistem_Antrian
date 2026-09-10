<x-layouts.terminal title="Loket CS - Cetak Tiket">
    <section id="cs-panel" class="flex-1 p-4 sm:p-6 lg:p-8">
        <div class="mx-auto max-w-7xl">
            <!-- Header CS -->
            <div class="mb-6 rounded-3xl border border-blue-200 bg-white/80 p-6 shadow-2xl shadow-blue-950/10 backdrop-blur-xl dark:border-blue-400/20 dark:bg-slate-900/75 dark:shadow-blue-950/20 sm:p-7">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-xs font-black tracking-[0.2em] text-blue-500 dark:text-blue-400 uppercase">LOKET CUSTOMER SERVICE</p>
                        <h1 class="mt-1 text-2xl font-black tracking-tight text-slate-900 dark:text-white sm:text-3xl">PENERBITAN TIKET ANTREAN</h1>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Pilih jenis layanan terlebih dahulu, kemudian tentukan meja tujuan untuk warga.</p>
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 rounded-full border border-emerald-400/30 bg-emerald-500/10 px-3.5 py-1.5 text-xs font-black text-emerald-600 dark:text-emerald-300">
                            <span class="h-2 w-2 rounded-full bg-emerald-500 animate-ping"></span>
                            SISTEM TIKET AKTIF
                        </span>
                    </div>
                </div>
            </div>

            <!-- LANGKAH 1: PILIH LAYANAN -->
            <div class="mb-6 rounded-3xl border border-slate-200 bg-white/80 p-5 shadow-xl dark:border-slate-800 dark:bg-slate-900/80 backdrop-blur-xl">
                <div class="flex items-center justify-between mb-3.5">
                    <div class="flex items-center gap-2">
                        <span class="flex h-6 w-6 items-center justify-center rounded-full bg-blue-600 text-xs font-black text-white">1</span>
                        <h2 class="text-sm font-black uppercase tracking-wider text-slate-900 dark:text-white">Langkah 1: Pilih Jenis Pelayanan</h2>
                    </div>
                    <span id="label-layanan-terpilih" class="text-xs font-black text-blue-600 dark:text-blue-400">
                        Dipilih: {{ $layanans->first()?->nama_layanan ?? 'Umum' }}
                    </span>
                </div>

                <div id="layanan-selector-grid" class="grid grid-cols-2 gap-2.5 sm:grid-cols-4 md:grid-cols-4 lg:grid-cols-4">
                    @foreach ($layanans as $index => $layanan)
                        <button type="button"
                                data-layanan-option
                                data-id="{{ $layanan->id }}"
                                data-nama="{{ $layanan->nama_layanan }}"
                                @class([
                                    'layanan-btn flex flex-col items-start justify-between rounded-2xl border p-3.5 text-left transition focus:outline-none',
                                    'border-blue-600 bg-blue-600 text-white shadow-lg shadow-blue-500/25 ring-2 ring-blue-400/30' => $index === 0,
                                    'border-slate-200 bg-white/90 text-slate-700 hover:border-blue-400 hover:bg-blue-50/50 dark:border-slate-700 dark:bg-slate-800/80 dark:text-slate-200 dark:hover:border-blue-500/50' => $index !== 0,
                                ])>
                            <span class="text-[10px] font-black uppercase tracking-wider opacity-75">{{ $layanan->kode_layanan }}</span>
                            <span class="mt-1 font-black text-xs leading-tight line-clamp-2">{{ $layanan->nama_layanan }}</span>
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- LANGKAH 2: PILIH MEJA TUJUAN (SORTING OTOMATIS: MEJA KOSONG / PALING LENGANG DI ATAS) -->
            <div class="rounded-3xl border border-slate-200 bg-white/80 p-5 shadow-xl dark:border-slate-800 dark:bg-slate-900/80 backdrop-blur-xl">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2">
                        <span class="flex h-6 w-6 items-center justify-center rounded-full bg-emerald-600 text-xs font-black text-white">2</span>
                        <div>
                            <h2 class="text-sm font-black uppercase tracking-wider text-slate-900 dark:text-white">Langkah 2: Pilih Meja Tujuan</h2>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400">Meja yang belum memiliki antrean (0 antrean) otomatis berada di urutan teratas agar beban pelayanan merata.</p>
                        </div>
                    </div>
                </div>

                <div id="meja-buttons-grid" class="grid grid-cols-2 gap-3.5 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5">
                    @foreach ($mejas as $meja)
                        <div data-meja-wrapper data-waiting-count="{{ $meja->waiting_count ?? 0 }}" data-nomor-meja="{{ $meja->nomor_meja }}">
                            <form method="POST" action="{{ route('cs.queues.store', $meja) }}" data-queue-form>
                                @csrf
                                <input type="hidden" name="layanan_id" class="input-layanan-id" value="{{ $layanans->first()?->id }}">
                                <button type="submit" class="group relative flex h-36 w-full flex-col justify-between rounded-3xl border border-slate-200 bg-white p-4 text-left shadow-md transition hover:-translate-y-1 hover:border-blue-500 hover:shadow-xl dark:border-slate-700/80 dark:bg-slate-800/90 focus:outline-none focus:ring-2 focus:ring-blue-400">
                                    <!-- Bagian Atas: Nama Meja & Nomor -->
                                    <div class="flex items-start justify-between w-full">
                                        <div>
                                            <span class="text-lg font-black text-slate-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                                                {{ $meja->nama_meja }}
                                            </span>
                                        </div>
                                        <span class="text-2xl font-black text-slate-300 dark:text-slate-600 group-hover:text-blue-500 dark:group-hover:text-blue-400 transition-colors">
                                            {{ $meja->formatted_nomor }}
                                        </span>
                                    </div>

                                    <!-- Bagian Bawah: Indikator Jumlah Antrean Menunggu -->
                                    <div class="w-full pt-2 border-t border-slate-100 dark:border-slate-700/60">
                                        <div class="flex items-center justify-between">
                                            <span class="text-[11px] font-extrabold uppercase tracking-wide text-slate-400 group-hover:text-blue-500">
                                                KLIK AMBIL TIKET
                                            </span>
                                            <span data-meja-counter="{{ $meja->id }}" @class([
                                                'counter-badge rounded-xl px-2.5 py-1 text-xs font-black shadow-sm transition-colors',
                                                'bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 border border-emerald-500/30' => ($meja->waiting_count ?? 0) === 0,
                                                'bg-amber-500/15 text-amber-600 dark:text-amber-400 border border-amber-500/30' => ($meja->waiting_count ?? 0) > 0 && ($meja->waiting_count ?? 0) <= 2,
                                                'bg-rose-500/15 text-rose-600 dark:text-rose-400 border border-rose-500/30' => ($meja->waiting_count ?? 0) > 2,
                                            ])>
                                                {{ $meja->waiting_count ?? 0 }} Menunggu
                                            </span>
                                        </div>
                                    </div>
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <!-- Modal Konfirmasi Tiket & Opsi Kirim WhatsApp -->
    <div id="ticket-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/70 p-4 backdrop-blur-sm">
        <div class="relative w-full max-w-md rounded-3xl border border-slate-200 bg-white p-6 shadow-2xl dark:border-slate-800 dark:bg-slate-900 sm:p-8">
            <div class="text-center">
                <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-500/15 text-emerald-600 dark:text-emerald-400">
                    <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                </div>

                <p class="text-xs font-extrabold tracking-widest text-slate-500 uppercase dark:text-slate-400">TIKET BERHASIL DITERBITKAN</p>
                <div id="modal-ticket-number" class="my-2 text-6xl font-black text-slate-900 dark:text-white">---</div>
                <div class="inline-flex items-center gap-2 rounded-xl bg-blue-50 px-4 py-1.5 text-base font-black text-blue-700 dark:bg-blue-950/50 dark:text-blue-300">
                    <span id="modal-ticket-meja">Meja</span>
                </div>
                <p id="modal-ticket-layanan" class="mt-2 text-xs font-bold text-slate-600 dark:text-slate-300">Pelayanan Umum</p>
            </div>

            <!-- Pilihan Aksi: Cetak Thermal & Tutup -->
            <div class="mt-6 flex gap-3">
                <button type="button" onclick="window.print()" class="flex-1 rounded-2xl border border-slate-300 bg-slate-100 px-4 py-3.5 text-sm font-black text-slate-700 transition hover:bg-slate-200 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700">
                    Cetak Ulang Thermal
                </button>
                <button id="modal-ticket-close" type="button" class="flex-1 rounded-2xl border border-blue-600 bg-blue-600 px-4 py-3.5 text-sm font-black text-white transition hover:bg-blue-500 shadow-lg shadow-blue-500/25">
                    Selesai
                </button>
            </div>
        </div>
    </div>

    <!-- Layout Cetak Tiket Thermal: 001 Meja 1 -->
    <section id="ticket" class="hidden">
        <div class="ticket-content text-center font-mono py-2">
            <h2 class="text-sm font-black uppercase">DINAS DUKCAPIL</h2>
            <p class="text-[10px] font-bold">KOTA PALU</p>
            <div class="my-2 border-y border-dashed border-black py-2">
                <p class="text-[9px] font-bold tracking-widest uppercase">NOMOR ANTREAN</p>
                <strong id="ticket-number" class="block text-5xl font-black">---</strong>
                <p id="ticket-meja" class="text-xl font-black uppercase mt-1">Meja</p>
            </div>
            <p id="ticket-layanan" class="text-[10px] font-bold uppercase"></p>
            <p id="ticket-date" class="text-[8px] mt-1 text-slate-700"></p>
        </div>
    </section>
</x-layouts.terminal>
