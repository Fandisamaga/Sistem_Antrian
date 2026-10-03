import {
    queueAnnouncement,
    isAnnouncing,
    announcementQueue,
    removeAnnouncement,
    setOnAnnounce,
    setOnAudioStatus,
    prepareAudio,
} from './audio';

// --- Display TV Engine (Daftar Seluruh Antrean Menunggu & Panggilan Utama) ---
let lastAnnouncedCallSignature = null;

function announceCalledQueue(queue, force = false) {
    const queueId = queue?.queue_id || queue?.id;
    const callSignature = queue?.call_signature || String(queueId);

    if (!queueId || (!force && callSignature === lastAnnouncedCallSignature)) {
        return;
    }

    lastAnnouncedCallSignature = callSignature;
    queueAnnouncement(queue);
}

export function updateDisplayBoard(queue) {
    if (!queue) {
        return;
    }

    const numberEl = document.querySelector('#display-number');
    const mejaEl = document.querySelector('#display-meja');
    const layananEl = document.querySelector('#display-layanan');

    const deskName = queue.meja?.nama_meja || (typeof queue.meja === 'string' ? queue.meja : '---');
    const serviceName = queue.layanan?.nama_layanan || (typeof queue.layanan === 'string' ? queue.layanan : '');

    if (numberEl) {
        numberEl.textContent = queue.queue_number;
        numberEl.dataset.queueId = queue.id || queue.queue_id || '';
        numberEl.classList.remove('animate-call-pulse');
        void numberEl.offsetWidth;
        numberEl.classList.add('animate-call-pulse');
    }

    if (mejaEl) {
        mejaEl.textContent = deskName;
    }

    if (layananEl) {
        layananEl.textContent = serviceName;
    }
}

// Kosongkan panggilan utama setelah operator menyelesaikan nomor yang tampil.
export function clearDisplayBoard() {
    const numberEl = document.querySelector('#display-number');
    const mejaEl = document.querySelector('#display-meja');
    const layananEl = document.querySelector('#display-layanan');

    if (numberEl) {
        numberEl.textContent = '---';
        delete numberEl.dataset.queueId;
        numberEl.classList.remove('animate-call-pulse');
    }

    if (mejaEl) {
        mejaEl.textContent = 'MENUNGGU PANGGILAN';
    }

    if (layananEl) {
        layananEl.textContent = '';
    }
}

export function updateDisplay(queue, shouldAnnounce = false) {
    if (!queue) return;

    if (shouldAnnounce) {
        queueAnnouncement(queue);
    } else {
        updateDisplayBoard(queue);
    }
}

// Tambah antrean baru ke daftar tunggu di panel samping display
export function addWaitingQueue(queue) {
    const list = document.querySelector('#waiting-queues');
    const emptyState = document.querySelector('#waiting-queues-empty');
    const badgeNumber = document.querySelector('#waiting-count-number');

    if (!list || !queue) return;

    const queueId = queue.id || queue.queue_id;

    // Cek apakah sudah ada di list
    if (list.querySelector(`[data-waiting-queue="${queueId}"]`)) {
        return;
    }

    const deskName = queue.meja?.nama_meja || (typeof queue.meja === 'string' ? queue.meja : '-');

    const li = document.createElement('li');
    li.dataset.waitingQueue = queueId;
    li.className = 'group flex items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-gradient-to-r from-white via-slate-50 to-white p-3.5 shadow-sm transition-all hover:border-blue-400 dark:border-slate-700/70 dark:from-slate-950 dark:via-slate-900 dark:to-slate-950 dark:shadow-md';

    li.innerHTML = `
        <div class="flex items-center gap-3">
            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-500/15 border border-blue-500/30 text-blue-600 dark:text-blue-400 font-mono text-xs font-black">
                #
            </span>
            <div>
                <span data-queue-number class="text-2xl font-black tracking-tight text-slate-900 group-hover:text-blue-600 dark:text-white dark:group-hover:text-blue-300 block leading-tight">
                    ${queue.queue_number}
                </span>
                <span class="text-[10px] font-bold text-amber-600 dark:text-amber-400 uppercase tracking-wider">MENUNGGU</span>
            </div>
        </div>
        <div class="flex items-center">
            <span data-queue-meja class="rounded-xl border border-emerald-400/30 bg-emerald-500/15 px-3 py-1 text-xs font-black text-emerald-700 dark:text-emerald-300 shadow-sm">
                ${deskName}
            </span>
        </div>
    `;

    list.appendChild(li);

    if (emptyState) emptyState.classList.add('hidden');
    if (badgeNumber) {
        badgeNumber.textContent = list.children.length;
    }
}

// Hapus antrean dari daftar tunggu saat dipanggil operator atau diselesaikan
export function removeWaitingQueue(queueId) {
    const list = document.querySelector('#waiting-queues');
    const emptyState = document.querySelector('#waiting-queues-empty');
    const badgeNumber = document.querySelector('#waiting-count-number');

    if (!list || !queueId) return;

    const item = list.querySelector(`[data-waiting-queue="${queueId}"]`);
    if (item) {
        item.style.transition = 'all 0.3s ease';
        item.style.opacity = '0';
        item.style.transform = 'scale(0.95)';
        setTimeout(() => {
            item.remove();
            if (badgeNumber) {
                badgeNumber.textContent = list.children.length;
            }
            if (emptyState && list.children.length === 0) {
                emptyState.classList.remove('hidden');
            }
        }, 300);
    }
}

export function renderWaitingQueues(queues) {
    const list = document.querySelector('#waiting-queues');
    const emptyState = document.querySelector('#waiting-queues-empty');
    const badgeNumber = document.querySelector('#waiting-count-number');

    if (!list || !Array.isArray(queues)) return;

    list.replaceChildren();
    queues.forEach(addWaitingQueue);

    if (emptyState) {
        emptyState.classList.toggle('hidden', queues.length > 0);
    }
    if (badgeNumber) {
        badgeNumber.textContent = queues.length;
    }
}

export async function refreshDisplay() {
    try {
        const response = await fetch('/display/state', {
            headers: { Accept: 'application/json' },
        });

        if (!response.ok) return;

        const state = await response.json();
        renderWaitingQueues(state.waiting);

        if (state.latest) {
            // Cadangan apabila koneksi WebSocket display sedang terputus.
            // Panggilan baru tetap dibacakan pada siklus polling berikutnya.
            announceCalledQueue(state.latest);

            if (!isAnnouncing && announcementQueue.length === 0) {
                updateDisplayBoard(state.latest);
            }
        } else {
            clearDisplayBoard();
        }
    } catch {
        // Fallback silently
    }
}

export function initDisplay() {
    if (!document.querySelector('#display-number')) return;

    const displayPanel = document.querySelector('#display-panel');
    lastAnnouncedCallSignature = displayPanel?.dataset.latestCallSignature || null;

    const soundIndicator = document.querySelector('#sound-indicator');
    setOnAudioStatus((enabled) => {
        soundIndicator?.classList.toggle('hidden', !enabled);
    });

    // Tautkan sinkronisasi papan display dengan antrean audio FIFO
    setOnAnnounce((current) => {
        updateDisplayBoard(current);
    });

    refreshDisplay();

    if (window.Echo) {
        window.Echo.channel('queue-display')
            .listen('.QueueCreated', (queue) => {
                // Tiket baru masuk daftar antrean menunggu di samping
                addWaitingQueue(queue);
            })
            .listen('.QueueCalled', (queue) => {
                // Antrean dipanggil: HILANG dari daftar antrean menunggu di samping...
                removeWaitingQueue(queue.queue_id || queue.id);

                // ...lalu MASUK ke antrean audio-visual sinkron TV!
                const calledQueue = {
                    id: queue.queue_id || queue.id,
                    queue_number: queue.queue_number,
                    meja: queue.meja,
                    layanan: queue.layanan,
                };
                // `force` memastikan tombol Panggil Ulang untuk nomor yang
                // sama tetap selalu dibacakan.
                announceCalledQueue(calledQueue, true);
            })
            .listen('.QueueUpdated', (queue) => {
                // Jika antrean dilewati atau selesai, hapus dari antrean menunggu
                if (['skipped', 'completed'].includes(queue.status)) {
                    const queueId = queue.queue_id || queue.id;
                    removeWaitingQueue(queueId);
                    removeAnnouncement(queueId);

                    // Hanya kosongkan panel utama bila nomor yang selesai
                    // adalah nomor yang sedang ditampilkan.
                    const displayedQueueId = document.querySelector('#display-number')?.dataset.queueId;
                    if (String(displayedQueueId) === String(queueId)) {
                        clearDisplayBoard();
                    }
                }
            });
    }

    // Polling fallback setiap 5 detik agar selalu sinkron cepat tanpa lag
    setInterval(refreshDisplay, 5000);
}
