import './echo';

const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

const jsonHeaders = {
    Accept: 'application/json',
    'X-Requested-With': 'XMLHttpRequest',
    'X-CSRF-TOKEN': csrfToken,
};

document.querySelectorAll('[data-queue-form]').forEach((form) => {
    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        const response = await fetch(form.getAttribute('action'), {
            method: 'POST',
            headers: jsonHeaders,
            body: new FormData(form),
        });

        if (!response.ok) {
            alert('Nomor antrean gagal dibuat.');
            return;
        }

        const data = await response.json();

        document.querySelector('#ticket-number').textContent = data.queue.queue_number.replace(/^A-/, '');
        document.querySelector('#ticket-meja').textContent = data.meja;
        document.querySelector('#ticket-date').textContent = new Intl.DateTimeFormat('id-ID', {
            dateStyle: 'long',
        }).format(new Date());

        window.print();
    });
});

document.querySelectorAll('[data-queue-action]').forEach((form) => {
    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        const response = await fetch(form.getAttribute('action'), {
            method: 'POST',
            headers: jsonHeaders,
            body: new FormData(form),
        });

        if (!response.ok) {
            alert('Aksi tidak dapat diproses.');
            return;
        }

        const action = form.querySelector('[name="action"]').value;

        if (['skip', 'complete'].includes(action)) {
            form.closest('.queue-row').remove();
        }
    });
});

function speakInIndonesian(message) {
    if (!('speechSynthesis' in window)) {
        return;
    }

    const utterance = new SpeechSynthesisUtterance(message);
    const indonesianVoice = speechSynthesis
        .getVoices()
        .find((voice) => voice.lang.toLowerCase().startsWith('id'));

    utterance.lang = 'id-ID';
    utterance.rate = 0.75;
    utterance.pitch = 0.90;
    utterance.volume = 1;

    if (indonesianVoice) {
        utterance.voice = indonesianVoice;
    }

    speechSynthesis.cancel();
    speechSynthesis.speak(utterance);
}

function updateDisplay(queue, shouldAnnounce = false) {
    if (!queue) {
        return;
    }

    document.querySelector('#display-number').textContent = queue.queue_number;
    document.querySelector('#display-meja').textContent = queue.meja.nama_meja;

    if (shouldAnnounce) {
        speakInIndonesian(
            `Nomor antrean ${queue.queue_number.replace('-', ' ')}, silakan menuju ${queue.meja.nama_meja}`,
        );
    }
}

function upsertCalledQueue(queue) {
    const list = document.querySelector('#called-queues');
    const emptyState = document.querySelector('#called-queues-empty');

    if (!list || !queue) {
        return;
    }

    let item = list.querySelector(`[data-called-queue="${queue.id}"]`);

    if (!item) {
        item = document.createElement('li');
        item.dataset.calledQueue = queue.id;
        item.className = 'flex items-center justify-between gap-4 rounded-2xl border border-slate-700/80 bg-gradient-to-r from-slate-950 to-slate-900 px-5 py-4 shadow-lg shadow-slate-950/20';

        const detail = document.createElement('div');
        detail.className = 'flex items-center gap-3';

        const marker = document.createElement('span');
        marker.className = 'h-9 w-1 rounded-full bg-blue-400';

        const number = document.createElement('span');
        number.dataset.calledNumber = '';
        number.className = 'text-2xl font-black tracking-tight text-white';

        const meja = document.createElement('span');
        meja.dataset.calledMeja = '';
        meja.className = 'rounded-lg bg-emerald-400/10 px-3 py-1.5 text-right text-sm font-black text-emerald-300';

        detail.append(marker, number);
        item.append(detail, meja);
    }

    item.querySelector('[data-called-number]').textContent = queue.queue_number;
    item.querySelector('[data-called-meja]').textContent = queue.meja.nama_meja;
    list.prepend(item);
    emptyState?.classList.add('hidden');
}

function renderCalledQueues(queues) {
    const list = document.querySelector('#called-queues');
    const emptyState = document.querySelector('#called-queues-empty');

    if (!list || !Array.isArray(queues)) {
        return;
    }

    list.replaceChildren();
    queues.slice().reverse().forEach(upsertCalledQueue);
    emptyState?.classList.toggle('hidden', queues.length > 0);
}

async function refreshDisplay() {
    try {
        const response = await fetch('/display/state', {
            headers: { Accept: 'application/json' },
        });

        if (!response.ok) {
            return;
        }

        const state = await response.json();
        renderCalledQueues(state.called);
        updateDisplay(state.latest);
    } catch {
        // Reverb will keep attempting to reconnect; this is only a silent fallback.
    }
}

if (document.querySelector('#display-number')) {
    refreshDisplay();
    window.Echo.channel('queue-display').listen('.QueueCalled', (queue) => {
        const calledQueue = {
            id: queue.queue_id,
            queue_number: queue.queue_number,
            meja: { nama_meja: queue.meja },
        };

        upsertCalledQueue(calledQueue);
        updateDisplay(calledQueue, true);
    });

    // Keeps the display correct if it was offline while an event was sent.
    setInterval(refreshDisplay, 30000);
}


