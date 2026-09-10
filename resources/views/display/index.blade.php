<x-layouts.terminal title="Display Antrian Publik - DUKCAPIL">
    <section class="flex flex-1 flex-col justify-between overflow-hidden bg-[radial-gradient(circle_at_15%_10%,rgba(59,130,246,0.10),transparent_35%),radial-gradient(circle_at_85%_90%,rgba(16,185,129,0.08),transparent_35%)] p-3 dark:bg-[radial-gradient(circle_at_15%_10%,rgba(59,130,246,0.22),transparent_35%),radial-gradient(circle_at_85%_90%,rgba(16,185,129,0.18),transparent_35%)] sm:p-5 lg:p-6">
        <!-- Main Grid Area -->
        <div class="grid flex-1 gap-4 lg:grid-cols-[minmax(0,1fr)_26rem] xl:gap-6 xl:grid-cols-[minmax(0,1fr)_30rem]">
            <!-- Hero Board: Panggilan Utama -->
            <div id="hero-display-card" class="relative flex flex-col justify-between overflow-hidden rounded-3xl border border-blue-200 bg-gradient-to-b from-white/95 to-slate-100/95 p-6 shadow-2xl shadow-blue-950/15 backdrop-blur-xl dark:border-blue-500/20 dark:from-slate-900/90 dark:to-slate-950/95 dark:shadow-blue-950/40 sm:p-8 lg:p-10">
                <!-- Ambient Glow Backgrounds -->
                <div class="pointer-events-none absolute -left-20 -top-20 h-80 w-80 rounded-full bg-blue-500/15 blur-3xl"></div>
                <div class="pointer-events-none absolute -bottom-24 -right-20 h-80 w-80 rounded-full bg-emerald-500/15 blur-3xl"></div>
                <div class="pointer-events-none absolute inset-0 opacity-[0.05] [background-image:linear-gradient(rgba(255,255,255,.5)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,.5)_1px,transparent_1px)] [background-size:32px_32px]"></div>

                <!-- Top Header inside Card -->
                <div class="relative flex items-center justify-between gap-4">
                    <div class="inline-flex items-center gap-2.5 rounded-full border border-blue-400/30 bg-blue-500/10 px-4 py-1.5 text-xs font-black tracking-widest text-blue-300 shadow-inner">
                        <span class="relative flex h-2.5 w-2.5">
                            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                        </span>
                        <span>PANGGILAN AKTIF</span>
                    </div>

                    <!-- Sound Wave Visualizer Indicator -->
                    <div id="sound-indicator" class="hidden items-center gap-1 rounded-full border border-slate-200 bg-white/80 px-3 py-1.5 sm:flex dark:border-slate-700/60 dark:bg-slate-800/80">
                        <span class="h-3 w-1 animate-pulse rounded-full bg-blue-400"></span>
                        <span class="h-4 w-1 animate-pulse rounded-full bg-cyan-400 [animation-delay:0.2s]"></span>
                        <span class="h-2 w-1 animate-pulse rounded-full bg-blue-400 [animation-delay:0.4s]"></span>
                        <span class="ml-1 text-[11px] font-bold text-slate-600 dark:text-slate-300">AUDIO AKTIF</span>
                    </div>
                </div>

                <!-- Center: Giant Number & Desk -->
                <div class="relative my-auto py-8 text-center">
                    <div class="inline-flex items-center gap-2 text-xs font-black tracking-[0.3em] uppercase text-blue-600 dark:text-blue-300/90 sm:text-sm">
                        <svg class="h-4 w-4 text-blue-400 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                        NOMOR ANTREAN SAAT INI
                    </div>

                    <!-- Giant Queue Number -->
                    <div id="display-number" class="mt-4 font-black leading-none tracking-tight text-slate-900 drop-shadow-[0_15px_35px_rgba(37,99,235,0.25)] transition-all duration-300 dark:text-white dark:drop-shadow-[0_15px_35px_rgba(37,99,235,0.45)] text-[clamp(5.5rem,14vw,14rem)]">
                        {{ $latestQueue?->queue_number ?? '---' }}
                    </div>

                    <div class="mx-auto mt-6 h-1 w-36 rounded-full bg-gradient-to-r from-transparent via-blue-400 to-transparent"></div>

                    <!-- Destination Counter Card -->
                    <div class="mt-7">
                        <p class="mb-2 text-xs font-extrabold uppercase tracking-widest text-slate-500 dark:text-slate-400">SILAKAN MENUJU KE</p>
                        <div class="inline-flex items-center gap-3 rounded-2xl border-2 border-emerald-400/40 bg-emerald-500/15 px-8 py-3.5 shadow-xl shadow-emerald-950/40 backdrop-blur">
                            <svg class="h-7 w-7 text-emerald-400 animate-bounce" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                            </svg>
                            <span id="display-meja" class="text-[clamp(1.5rem,2.8vw,2.8rem)] font-black tracking-wide text-emerald-700 dark:text-emerald-300">
                                {{ $latestQueue?->meja?->nama_meja ?? 'MENUNGGU PANGGILAN' }}
                            </span>
                        </div>
                        <p id="display-layanan" class="mt-2 text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                            {{ $latestQueue?->layanan?->nama_layanan ?? '' }}
                        </p>
                    </div>
                </div>

                <!-- Footer info banner -->
                <div class="relative flex items-center justify-center gap-3 text-center text-xs text-slate-500 dark:text-slate-400 sm:text-sm">
                    <span class="h-px flex-1 max-w-[4rem] bg-gradient-to-r from-transparent to-slate-300 dark:to-slate-700"></span>
                    <span class="flex items-center gap-2">
                        <svg class="h-4 w-4 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Harap persiapkan KTP, Kartu Keluarga, atau dokumen asli terkait saat menuju meja loket.
                    </span>
                    <span class="h-px flex-1 max-w-[4rem] bg-gradient-to-l from-transparent to-slate-300 dark:to-slate-700"></span>
                </div>
            </div>

            <!-- Side Panel: Seluruh Daftar Antrean Menunggu -->
            <aside class="flex flex-col overflow-hidden rounded-3xl border border-slate-200 bg-white/90 shadow-2xl shadow-slate-300/30 backdrop-blur-xl dark:border-slate-800 dark:bg-slate-900/90 dark:shadow-slate-950/50">
                <div class="border-b border-slate-200 bg-gradient-to-r from-white via-slate-100 to-white px-6 py-5 dark:border-slate-800 dark:from-slate-900 dark:via-slate-800/80 dark:to-slate-900">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-[11px] font-black tracking-widest text-blue-500 uppercase dark:text-blue-400">STATUS PELAYANAN</span>
                            <h2 class="text-xl font-black text-slate-900 dark:text-white">Daftar Antrean Menunggu</h2>
                        </div>
                        <span id="waiting-count-badge" class="flex h-8 items-center gap-1.5 rounded-full border border-blue-400/30 bg-blue-500/10 px-3 text-xs font-black text-blue-600 dark:text-blue-300">
                            <span class="h-2 w-2 rounded-full bg-blue-500 animate-ping"></span>
                            <span id="waiting-count-number">{{ $waitingQueues->count() }}</span> MENUNGGU
                        </span>
                    </div>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Daftar nomor antrean yang sedang menunggu panggilan operator loket</p>
                </div>

                <!-- Empty State -->
                <div id="waiting-queues-empty" @class(['m-6 flex flex-1 flex-col items-center justify-center rounded-2xl border-2 border-dashed border-slate-300 bg-slate-50/70 p-8 text-center text-slate-500 dark:border-slate-800 dark:bg-slate-950/40 dark:text-slate-400', 'hidden' => $waitingQueues->isNotEmpty()])>
                    <svg class="h-12 w-12 text-slate-400 dark:text-slate-600 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    <p class="font-bold text-slate-700 dark:text-slate-300">Belum Ada Antrean Menunggu</p>
                    <p class="mt-1 text-xs text-slate-500">Tiket yang baru dicetak dari loket CS akan otomatis muncul di sini.</p>
                </div>

                <!-- Waiting Queue Items -->
                <ol id="waiting-queues" class="flex-1 space-y-3 overflow-y-auto px-5 py-5 max-h-[calc(100vh-280px)]">
                    @foreach ($waitingQueues as $queue)
                        <li data-waiting-queue="{{ $queue->id }}" class="group flex items-center justify-between gap-4 rounded-2xl border border-slate-200 bg-gradient-to-r from-white via-slate-50 to-white p-4 shadow-sm transition-all hover:border-blue-400 dark:border-slate-700/70 dark:from-slate-950 dark:via-slate-900 dark:to-slate-950 dark:shadow-md">
                            <div class="flex items-center gap-3.5">
                                <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-blue-500/15 border border-blue-500/30 text-blue-600 dark:text-blue-400 font-mono text-sm font-black">
                                    #
                                </span>
                                <div>
                                    <span data-queue-number class="text-2xl font-black tracking-tight text-slate-900 group-hover:text-blue-600 dark:text-white dark:group-hover:text-blue-300 block leading-tight">{{ $queue->queue_number }}</span>
                                    <div class="flex items-center gap-1.5 mt-0.5">
                                        <span class="text-[10px] font-bold text-amber-600 dark:text-amber-400 uppercase tracking-wider">MENUNGGU</span>
                                        @if($queue->layanan)
                                            <span class="text-slate-400">&bull;</span>
                                            <span class="text-[10px] text-slate-500 dark:text-slate-400">{{ $queue->layanan->nama_layanan }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="flex flex-col items-end">
                                <span data-queue-meja class="rounded-xl border border-emerald-400/30 bg-emerald-500/15 px-3 py-1.5 text-right text-xs font-black text-emerald-700 dark:text-emerald-300 shadow-sm">
                                    {{ $queue->meja?->nama_meja ?? '-' }}
                                </span>
                                <span class="text-[10px] text-slate-400 mt-1 font-mono">
                                    {{ $queue->created_at->format('H:i') }} WIB
                                </span>
                            </div>
                        </li>
                    @endforeach
                </ol>
            </aside>
        </div>

        <!-- Running Text Ticker / Marquee Banner at Bottom -->
        <div class="no-print mt-4 flex items-center overflow-hidden rounded-2xl border border-slate-200 bg-white/90 px-4 py-2.5 shadow-lg shadow-slate-300/25 dark:border-slate-800 dark:bg-slate-900/95 dark:shadow-none">
            <div class="flex shrink-0 items-center gap-2 border-r border-slate-200 pr-4 text-xs font-extrabold tracking-wider text-blue-600 dark:border-slate-700/80 dark:text-blue-400">
                <span class="flex h-2 w-2 rounded-full bg-blue-400 animate-ping"></span>
                <span>INFORMASI</span>
            </div>
            <div class="overflow-hidden whitespace-nowrap pl-4 flex-1">
                <p class="animate-marquee text-xs font-semibold text-slate-600 dark:text-slate-300">
                    📢 SELAMAT DATANG DI DINAS KEPENDUDUKAN & PENCATATAN SIPIL KOTA PALU &nbsp;&bull;&nbsp; HARAP MENUNGGU DENGAN TERTIB HINGGA NOMOR ANTREAN ANDA DIPANGGIL &nbsp;&bull;&nbsp; PASTIKAN BERKAS PERSYARATAN ASLI DAN SALINAN (KTP, KK, AKTA) TELAH LENGKAP &nbsp;&bull;&nbsp; SELURUH PELAYANAN DI KANTOR INI GRATIS (TIDAK DIPUNGUT BIAYA) &nbsp;&bull;&nbsp; JAM PELAYANAN: SENIN - KAMIS 08.00 - 16.00 dan JUMAT 08.00 - 11.00 &nbsp;&bull;&nbsp; TERIMA KASIH ATAS KUNJUNGAN DAN KERJASAMA ANDA.
                </p>
            </div>
        </div>
    </section>
</x-layouts.terminal>
