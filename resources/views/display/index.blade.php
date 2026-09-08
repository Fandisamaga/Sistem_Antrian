<x-layouts.terminal title="Display Antrian">
    <section class="min-h-[calc(100vh-73px)] bg-[radial-gradient(circle_at_12%_2%,rgba(59,130,246,.25),transparent_28%),radial-gradient(circle_at_88%_96%,rgba(16,185,129,.14),transparent_30%)] p-4 sm:p-6 lg:h-[calc(100vh-73px)] lg:p-7">
        <div class="grid h-full gap-5 lg:grid-cols-[minmax(0,1fr)_24rem] xl:gap-7 xl:grid-cols-[minmax(0,1fr)_27rem]">
            <div class="relative flex min-h-[31rem] flex-col justify-between overflow-hidden rounded-[2rem] border border-blue-400/15 bg-slate-900/75 px-6 py-6 shadow-2xl shadow-blue-950/30 backdrop-blur sm:px-10 lg:min-h-0 lg:py-9">
                <div class="pointer-events-none absolute -left-24 -top-24 h-72 w-72 rounded-full bg-blue-500/15 blur-3xl"></div>
                <div class="pointer-events-none absolute -bottom-32 -right-20 h-72 w-72 rounded-full bg-emerald-400/10 blur-3xl"></div>
                <div class="pointer-events-none absolute inset-0 opacity-[0.07] [background-image:linear-gradient(rgba(148,163,184,.4)_1px,transparent_1px),linear-gradient(90deg,rgba(148,163,184,.4)_1px,transparent_1px)] [background-size:32px_32px]"></div>

                <div class="relative flex items-center justify-between gap-4">
                    <div class="inline-flex items-center gap-2 rounded-full border border-blue-300/20 bg-blue-400/10 px-4 py-2 text-xs font-black tracking-[.18em] text-blue-200">
                        <span class="h-2 w-2 rounded-full bg-blue-300 shadow-[0_0_14px_rgb(147,197,253)]"></span>
                        DISPLAY PUBLIK
                    </div>
                    <p class="hidden text-right text-xs font-bold tracking-[.16em] text-slate-500 sm:block">DUKCAPIL · ANTRIAN</p>
                </div>

                <div class="relative my-8 text-center lg:my-4">
                    <p class="text-sm font-black tracking-[.26em] text-blue-300 sm:text-base">NOMOR ANTREAN</p>
                    <div id="display-number" class="mt-5 whitespace-nowrap font-black leading-none tracking-tight text-white drop-shadow-[0_12px_30px_rgba(15,23,42,.85)] text-[clamp(5rem,13vw,14rem)]">
                        {{ $latestQueue?->queue_number ?? '---' }}
                    </div>
                    <div class="mx-auto mt-7 h-px w-28 bg-gradient-to-r from-transparent via-blue-300 to-transparent"></div>
                    <p id="display-meja" class="mt-7 inline-flex rounded-full border border-emerald-300/30 bg-emerald-400/10 px-6 py-3 text-[clamp(1.35rem,2.4vw,2.5rem)] font-black text-emerald-300 shadow-lg shadow-emerald-950/20">
                        {{ $latestQueue?->meja?->nama_meja ?? 'MENUNGGU PANGGILAN' }}
                    </p>
                </div>

                <div class="relative flex items-center justify-center gap-3 text-center text-sm text-slate-400 sm:text-base">
                    <span class="h-px w-8 bg-slate-700"></span>
                    Silakan menuju meja pelayanan saat nomor Anda dipanggil.
                    <span class="h-px w-8 bg-slate-700"></span>
                </div>
            </div>

            <aside class="flex min-h-[24rem] flex-col overflow-hidden rounded-[2rem] border border-slate-800 bg-slate-900/90 shadow-2xl shadow-slate-950/40 backdrop-blur lg:min-h-0">
                <div class="border-b border-slate-700/80 bg-gradient-to-br from-blue-500/15 to-transparent px-6 py-6">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-black tracking-[.22em] text-blue-300">STATUS TERKINI</p>
                            <h2 class="mt-1 text-2xl font-black text-white">Antrean Dipanggil</h2>
                        </div>
                        <span class="rounded-full border border-emerald-300/20 bg-emerald-400/10 px-3 py-1 text-xs font-black tracking-wide text-emerald-300">AKTIF</span>
                    </div>
                    <p class="mt-3 text-sm text-slate-400">Nomor yang sedang menunggu pelayanan.</p>
                </div>

                <p id="called-queues-empty" @class(['m-5 rounded-2xl border border-dashed border-slate-700 bg-slate-950/30 px-5 py-10 text-center text-slate-400', 'hidden' => $calledQueues->isNotEmpty()])>
                    Belum ada antrean yang dipanggil.
                </p>

                <ol id="called-queues" class="min-h-0 flex-1 space-y-3 overflow-y-auto px-5 py-5">
                    @foreach ($calledQueues as $queue)
                        <li data-called-queue="{{ $queue->id }}" class="flex items-center justify-between gap-4 rounded-2xl border border-slate-700/80 bg-gradient-to-r from-slate-950 to-slate-900 px-5 py-4 shadow-lg shadow-slate-950/20">
                            <div class="flex items-center gap-3">
                                <span class="h-9 w-1 rounded-full bg-blue-400"></span>
                                <span data-called-number class="text-2xl font-black tracking-tight text-white">{{ $queue->queue_number }}</span>
                            </div>
                            <span data-called-meja class="rounded-lg bg-emerald-400/10 px-3 py-1.5 text-right text-sm font-black text-emerald-300">{{ $queue->meja->nama_meja }}</span>
                        </li>
                    @endforeach
                </ol>
            </aside>
        </div>
    </section>
</x-layouts.terminal>
