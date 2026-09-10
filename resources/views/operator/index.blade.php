<x-layouts.terminal title="Operator {{ $meja->nama_meja }}">
    <section id="operator-panel" data-meja-id="{{ $meja->id }}" class="flex-1 p-4 sm:p-6 lg:p-8">
        <div class="mx-auto max-w-6xl">
            <!-- Header Panel Operator -->
            <div class="mb-7 rounded-3xl border border-blue-200 bg-white/80 p-6 shadow-2xl shadow-blue-950/10 backdrop-blur-xl dark:border-blue-400/20 dark:bg-slate-900/75 dark:shadow-blue-950/20 sm:p-7">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="rounded-lg bg-emerald-500/15 px-2.5 py-1 text-xs font-black tracking-widest text-emerald-600 dark:text-emerald-400 uppercase">
                                PANEL OPERATOR
                            </span>
                            <span class="text-xs font-bold text-slate-500 dark:text-slate-400">
                                Petugas: {{ auth()->user()->name }}
                            </span>
                        </div>
                        <h1 class="mt-2 text-3xl font-black tracking-tight text-slate-900 dark:text-white sm:text-4xl">
                            {{ $meja->nama_meja }}
                        </h1>
                        <p class="mt-1 text-sm font-semibold text-blue-600 dark:text-blue-400">
                            Layanan: {{ $meja->layanan?->nama_layanan ?? 'Pelayanan Umum' }}
                        </p>
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="flex flex-col items-end">
                            <span class="text-xs text-slate-500">Antrean Aktif Meja Ini</span>
                            <span id="operator-active-queue-count" class="text-2xl font-black text-slate-900 dark:text-white">{{ $queues->count() }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Daftar Antrean -->
            <div id="operator-queue-list" class="space-y-4">
                @forelse ($queues as $queue)
                    <article class="queue-row flex flex-col gap-5 rounded-3xl border border-slate-200 bg-white/80 p-5 shadow-xl shadow-slate-300/30 backdrop-blur-xl transition hover:border-blue-400/50 dark:border-slate-700/80 dark:bg-slate-900/80 dark:shadow-slate-950/25 dark:hover:border-blue-400/40 sm:p-6 md:flex-row md:items-center md:justify-between">
                        <div>
                            <p class="text-4xl font-black tracking-tight text-slate-900 dark:text-white">{{ $queue->queue_number }}</p>
                            <div class="mt-1 flex items-center gap-2">
                                <span class="text-xs font-black tracking-widest uppercase text-blue-600 dark:text-blue-300">{{ $queue->status }}</span>
                                @if($queue->layanan)
                                    <span class="text-slate-400">&bull;</span>
                                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">{{ $queue->layanan->nama_layanan }}</span>
                                @endif
                                <span class="text-slate-400">&bull;</span>
                                <span class="text-xs text-slate-400">{{ $queue->created_at->format('H:i') }} WIB</span>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3 md:flex">
                            @foreach (['call' => ['Panggil', 'bg-blue-600 hover:bg-blue-500 focus:ring-blue-400'], 'replay' => ['Panggil Ulang', 'bg-sky-600 hover:bg-sky-500 focus:ring-sky-400'], 'skip' => ['Lewati', 'bg-rose-600 hover:bg-rose-500 focus:ring-rose-400'], 'complete' => ['Selesai', 'bg-emerald-600 hover:bg-emerald-500 focus:ring-emerald-400']] as $action => [$label, $style])
                                <form method="POST" action="{{ route('operator.queues.update', $queue) }}" data-queue-action>
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="action" value="{{ $action }}">
                                    <button type="submit" class="w-full rounded-2xl px-4 py-3 text-sm font-black text-white shadow-lg transition focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-slate-900 {{ $style }}">
                                        {{ $label }}
                                    </button>
                                </form>
                            @endforeach
                        </div>
                    </article>
                @empty
                    <p id="operator-empty-notice" class="rounded-3xl border-2 border-dashed border-slate-300 bg-white/60 p-10 text-center text-xl font-bold text-slate-500 dark:border-slate-700 dark:bg-slate-900/60 dark:text-slate-400">
                        Tidak ada antrean aktif untuk meja ini saat ini.
                    </p>
                @endforelse
            </div>
        </div>
    </section>
</x-layouts.terminal>
