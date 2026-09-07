<x-layouts.terminal title="Operator {{ $meja->nama_meja }}">
    <section class="p-6">
        <p class="font-bold text-green-300">OPERATOR</p>
        <h1 class="mb-8 text-4xl font-black">{{ $meja->nama_meja }}</h1>

        <div class="space-y-4">
            @forelse ($queues as $queue)
                <article class="queue-row flex flex-col gap-4 rounded border-2 border-slate-700 p-5 md:flex-row md:items-center md:justify-between">
                    <div>
                        <p class="text-4xl font-black">{{ $queue->queue_number }}</p>
                        <p class="mt-1 font-bold uppercase text-slate-400">{{ $queue->status }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-3 md:flex">
                        @foreach (['call' => ['Panggil', 'blue'], 'replay' => ['Panggil Ulang', 'blue'], 'skip' => ['Lewati', 'red'], 'complete' => ['Selesai', 'green']] as $action => [$label, $color])
                            <form method="POST" action="{{ route('operator.queues.update', $queue) }}" data-queue-action>
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="action" value="{{ $action }}">
                                <button class="rounded bg-{{ $color }}-600 px-5 py-4 text-lg font-black">
                                    {{ $label }}
                                </button>
                            </form>
                        @endforeach
                    </div>
                </article>
            @empty
                <p class="rounded border-2 border-dashed border-slate-600 p-10 text-center text-xl font-bold text-slate-400">
                    Tidak ada antrean aktif.
                </p>
            @endforelse
        </div>
    </section>
</x-layouts.terminal>
