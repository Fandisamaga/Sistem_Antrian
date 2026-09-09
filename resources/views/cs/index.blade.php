<x-layouts.terminal title="Loket CS">
    <section class="flex-1 p-4 sm:p-6 lg:p-8">
        <div class="mx-auto max-w-6xl">
        <div class="mb-7 rounded-3xl border border-blue-200 bg-white/80 p-6 shadow-2xl shadow-blue-950/10 backdrop-blur-xl dark:border-blue-400/20 dark:bg-slate-900/75 dark:shadow-blue-950/20 sm:p-7">
            <p class="text-xs font-black tracking-[0.2em] text-blue-400">CUSTOMER SERVICE</p>
            <h1 class="mt-2 text-3xl font-black tracking-tight text-slate-900 dark:text-white sm:text-4xl">PILIH MEJA TUJUAN</h1>
            <p class="mt-2 text-slate-500 dark:text-slate-400">Klik meja untuk mencetak nomor antrean baru.</p>
        </div>

        <div class="grid grid-cols-2 gap-4 md:grid-cols-4 xl:grid-cols-5">
            @foreach ($mejas as $meja)
                <form method="POST" action="{{ route('cs.queues.store', $meja) }}" data-queue-form>
                    @csrf
                    <button class="group h-36 w-full rounded-3xl border border-blue-300 bg-gradient-to-br from-blue-500 to-blue-700 p-4 text-2xl font-black text-white shadow-xl shadow-blue-500/20 transition hover:-translate-y-1 hover:from-blue-400 hover:to-cyan-600 hover:shadow-blue-500/30 focus:outline-none focus:ring-2 focus:ring-cyan-400 focus:ring-offset-2 focus:ring-offset-slate-100 dark:border-blue-400/30 dark:from-blue-600 dark:to-blue-800 dark:shadow-blue-950/35 dark:hover:from-blue-500 dark:hover:to-cyan-700 dark:hover:shadow-blue-500/20 dark:focus:ring-cyan-300 dark:focus:ring-offset-slate-950">
                        {{ $meja->nama_meja }}
                    </button>
                </form>
            @endforeach
        </div>
        </div>
    </section>

    <section id="ticket" class="hidden">
        <div class="ticket-content text-center">
            <p class="ticket-label">NOMOR ANTRIAN</p>
            <strong id="ticket-number"></strong>
            <p id="ticket-meja"></p>
            <p id="ticket-date"></p>
        </div>
    </section>
</x-layouts.terminal>
