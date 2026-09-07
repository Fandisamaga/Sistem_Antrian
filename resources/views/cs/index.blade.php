<x-layouts.terminal title="Loket CS">
    <section class="p-6">
        <div class="mb-8">
            <p class="font-bold text-blue-300">CUSTOMER SERVICE</p>
            <h1 class="text-4xl font-black">PILIH MEJA TUJUAN</h1>
            <p class="mt-2 text-slate-400">Klik meja untuk mencetak nomor antrean baru.</p>
        </div>

        <div class="grid grid-cols-2 gap-4 md:grid-cols-4 xl:grid-cols-5">
            @foreach ($mejas as $meja)
                <form method="POST" action="{{ route('cs.queues.store', $meja) }}" data-queue-form>
                    @csrf
                    <button class="h-36 w-full rounded bg-blue-600 p-4 text-2xl font-black hover:bg-blue-500">
                        {{ $meja->nama_meja }}
                    </button>
                </form>
            @endforeach
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
