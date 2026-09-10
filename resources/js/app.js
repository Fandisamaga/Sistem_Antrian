import './echo';

const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

const jsonHeaders = {
    Accept: 'application/json',
    'Content-Type': 'application/json',
    'X-Requested-With': 'XMLHttpRequest',
    'X-CSRF-TOKEN': csrfToken,
};

// --- Theme Switcher (Gelap / Terang) ---
function initTheme() {
    const toggleBtn = document.querySelector('#btn-theme-toggle');
    const sunIcon = document.querySelector('#theme-icon-sun');
    const moonIcon = document.querySelector('#theme-icon-moon');

    function updateIcons() {
        const isDark = document.documentElement.classList.contains('dark');
        if (sunIcon && moonIcon) {
            if (isDark) {
                sunIcon.classList.remove('hidden');
                moonIcon.classList.add('hidden');
            } else {
                sunIcon.classList.add('hidden');
                moonIcon.classList.remove('hidden');
            }
        }
    }

    updateIcons();

    if (toggleBtn) {
        toggleBtn.addEventListener('click', () => {
            const isDark = document.documentElement.classList.toggle('dark');
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
            updateIcons();
        });
    }
}
initTheme();

// --- Web Audio API Chime & FIFO Announcement Engine ---
let audioCtxInstance = null;

function getAudioContext() {
    if (!audioCtxInstance) {
        const AudioCtx = window.AudioContext || window.webkitAudioContext;
        if (AudioCtx) {
            audioCtxInstance = new AudioCtx();
        }
    }
    if (audioCtxInstance && audioCtxInstance.state === 'suspended') {
        audioCtxInstance.resume();
    }
    return audioCtxInstance;
}

// Unlock audio on any click in window
window.addEventListener('click', () => {
    getAudioContext();
}, { once: false });

function playChime() {
    try {
        const ctx = getAudioContext();
        if (!ctx) return Promise.resolve();

        const now = ctx.currentTime;

        // Tone 1: High note (587.33Hz D5)
        const osc1 = ctx.createOscillator();
        const gain1 = ctx.createGain();
        osc1.type = 'sine';
        osc1.frequency.setValueAtTime(587.33, now);
        gain1.gain.setValueAtTime(0.3, now);
        gain1.gain.exponentialRampToValueAtTime(0.001, now + 0.45);
        osc1.connect(gain1);
        gain1.connect(ctx.destination);
        osc1.start(now);
        osc1.stop(now + 0.45);

        // Tone 2: Harmonic note (440.0Hz A4)
        const osc2 = ctx.createOscillator();
        const gain2 = ctx.createGain();
        osc2.type = 'sine';
        osc2.frequency.setValueAtTime(440.0, now + 0.28);
        gain2.gain.setValueAtTime(0.35, now + 0.28);
        gain2.gain.exponentialRampToValueAtTime(0.001, now + 0.85);
        osc2.connect(gain2);
        gain2.connect(ctx.destination);
        osc2.start(now + 0.28);
        osc2.stop(now + 0.85);

        return new Promise((resolve) => setTimeout(resolve, 850));
    } catch {
        return Promise.resolve();
    }
}

// Indonesian Text-to-Speech
function speakText(message) {
    return new Promise((resolve) => {
        if (!('speechSynthesis' in window)) {
            return resolve();
        }

        const utterance = new SpeechSynthesisUtterance(message);
        const voices = speechSynthesis.getVoices();
        const indonesianVoice = voices.find((v) => v.lang && v.lang.toLowerCase().startsWith('id'));

        utterance.lang = 'id-ID';
        utterance.rate = 0.85;
        utterance.pitch = 1.0;
        utterance.volume = 1;

        if (indonesianVoice) {
            utterance.voice = indonesianVoice;
        }

        utterance.onend = () => resolve();
        utterance.onerror = () => resolve();

        const timeoutId = setTimeout(() => resolve(), 9000);
        utterance.addEventListener('end', () => clearTimeout(timeoutId));
        utterance.addEventListener('error', () => clearTimeout(timeoutId));

        speechSynthesis.speak(utterance);
    });
}

// Announcement Queue to prevent speech cutoffs
const announcementQueue = [];
let isAnnouncing = false;

async function processAnnouncementQueue() {
    if (isAnnouncing || announcementQueue.length === 0) {
        return;
    }

    isAnnouncing = true;
    const current = announcementQueue.shift();

    try {
        await playChime();

        // Eja nomor secara ramah, misal 001 -> "nol nol satu"
const numberWords = [
    'nol',
    'satu',
    'dua',
    'tiga',
    'empat',
    'lima',
    'enam',
    'tujuh',
    'delapan',
    'sembilan',
    'sepuluh',
    'sebelas'
];

function numberToWords(number) {
    number = parseInt(number, 10);

    if (number < 12) {
        return numberWords[number];
    }

    if (number < 20) {
        return numberToWords(number - 10) + ' belas';
    }

    if (number < 100) {
        const tens = Math.floor(number / 10);
        const remainder = number % 10;

        if (remainder === 0) {
            return numberToWords(tens) + ' puluh';
        }

        return numberToWords(tens) + ' puluh ' + numberToWords(remainder);
    }

    return number.toString();
}

const queueNumber = parseInt(current.queue_number, 10);
const spelledNumber = numberToWords(queueNumber);

        await speakText(`Nomor antrean, ${spelledNumber}, silakan menuju, ${current.meja}`);
    } catch {
        // Fallback
    } finally {
        isAnnouncing = false;
        if (announcementQueue.length > 0) {
            setTimeout(processAnnouncementQueue, 400);
        }
    }
}

function queueAnnouncement(queueData) {
    announcementQueue.push(queueData);
    processAnnouncementQueue();
}

// --- Live Digital Clock & Date Engine ---
function startLiveClock() {
    const clockEl = document.querySelector('#live-clock');
    const dateEl = document.querySelector('#live-date');
    if (!clockEl && !dateEl) return;

    function update() {
        const now = new Date();
        if (clockEl) {
            const h = String(now.getHours()).padStart(2, '0');
            const m = String(now.getMinutes()).padStart(2, '0');
            const s = String(now.getSeconds()).padStart(2, '0');
            clockEl.textContent = `${h}:${m}:${s} WIB`;
        }
        if (dateEl) {
            dateEl.textContent = new Intl.DateTimeFormat('id-ID', {
                weekday: 'long',
                day: 'numeric',
                month: 'long',
                year: 'numeric',
            }).format(now);
        }
    }
    update();
    setInterval(update, 1000);
}
startLiveClock();

// --- CS: Pemilihan Layanan Dulu, Baru Pilih Meja ---
const layananButtons = document.querySelectorAll('[data-layanan-option]');
const labelLayananTerpilih = document.querySelector('#label-layanan-terpilih');

layananButtons.forEach(btn => {
    btn.addEventListener('click', () => {
        const id = btn.dataset.id;
        const nama = btn.dataset.nama;

        layananButtons.forEach(b => {
            b.classList.remove('border-blue-600', 'bg-blue-600', 'text-white', 'shadow-lg', 'shadow-blue-500/25', 'ring-2', 'ring-blue-400/30');
            b.classList.add('border-slate-200', 'bg-white/90', 'text-slate-700', 'dark:border-slate-700', 'dark:bg-slate-800/80', 'dark:text-slate-200');
        });

        btn.classList.add('border-blue-600', 'bg-blue-600', 'text-white', 'shadow-lg', 'shadow-blue-500/25', 'ring-2', 'ring-blue-400/30');
        btn.classList.remove('border-slate-200', 'bg-white/90', 'text-slate-700', 'dark:border-slate-700', 'dark:bg-slate-800/80', 'dark:text-slate-200');

        if (labelLayananTerpilih) {
            labelLayananTerpilih.textContent = `Dipilih: ${nama}`;
        }

        // Terapkan layanan terpilih ke semua hidden input form meja
        document.querySelectorAll('.input-layanan-id').forEach(input => {
            input.value = id;
        });
    });
});

// --- CS: Otomatis Sorting Tombol Meja (Meja Lengang / 0 Antrean Naik ke Atas) ---
function sortMejaButtons() {
    const grid = document.querySelector('#meja-buttons-grid');
    if (!grid) return;

    const wrappers = Array.from(grid.querySelectorAll('[data-meja-wrapper]'));

    wrappers.sort((a, b) => {
        const countA = parseInt(a.dataset.waitingCount || '0', 10);
        const countB = parseInt(b.dataset.waitingCount || '0', 10);

        if (countA !== countB) {
            return countA - countB; // Antrean paling sedikit di atas
        }

        const nomorA = parseInt(a.dataset.nomorMeja || '0', 10);
        const nomorB = parseInt(b.dataset.nomorMeja || '0', 10);
        return nomorA - nomorB;
    });

    wrappers.forEach(w => grid.appendChild(w));
}

function updateMejaBadge(mejaId, change) {
    const badge = document.querySelector(`[data-meja-counter="${mejaId}"]`);
    if (!badge) return;

    const wrapper = badge.closest('[data-meja-wrapper]');
    let current = parseInt(badge.textContent || '0', 10);
    let nextCount = Math.max(0, current + change);

    badge.textContent = `${nextCount} Menunggu`;
    if (wrapper) {
        wrapper.dataset.waitingCount = nextCount;
    }

    badge.className = 'counter-badge rounded-xl px-2.5 py-1 text-xs font-black shadow-sm transition-colors';
    if (nextCount === 0) {
        badge.classList.add('bg-emerald-500/15', 'text-emerald-600', 'dark:text-emerald-400', 'border', 'border-emerald-500/30');
    } else if (nextCount <= 2) {
        badge.classList.add('bg-amber-500/15', 'text-amber-600', 'dark:text-amber-400', 'border', 'border-amber-500/30');
    } else {
        badge.classList.add('bg-rose-500/15', 'text-rose-600', 'dark:text-rose-400', 'border', 'border-rose-500/30');
    }

    sortMejaButtons();
}

// Inisialisasi urutan meja saat load
sortMejaButtons();

// --- CS: Submit Form Buat Tiket ---
document.querySelectorAll('[data-queue-form]').forEach((form) => {
    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) submitBtn.disabled = true;

        try {
            const response = await fetch(form.getAttribute('action'), {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: new FormData(form),
            });

            if (!response.ok) {
                alert('Nomor antrean gagal dibuat. Silakan coba lagi.');
                return;
            }

            const data = await response.json();

            // Populate thermal paper
            const ticketNumberEl = document.querySelector('#ticket-number');
            const ticketMejaEl = document.querySelector('#ticket-meja');
            const ticketLayananEl = document.querySelector('#ticket-layanan');
            const ticketDateEl = document.querySelector('#ticket-date');

            if (ticketNumberEl) ticketNumberEl.textContent = data.queue.queue_number;
            if (ticketMejaEl) ticketMejaEl.textContent = data.meja;
            if (ticketLayananEl) ticketLayananEl.textContent = data.layanan || 'Pelayanan Umum';
            if (ticketDateEl) {
                ticketDateEl.textContent = new Intl.DateTimeFormat('id-ID', {
                    dateStyle: 'full',
                    timeStyle: 'medium',
                }).format(new Date());
            }

            // Update modal data
            const modal = document.querySelector('#ticket-modal');
            const modalNumber = document.querySelector('#modal-ticket-number');
            const modalMeja = document.querySelector('#modal-ticket-meja');
            const modalLayanan = document.querySelector('#modal-ticket-layanan');
            const modalClose = document.querySelector('#modal-ticket-close');

            if (modalNumber) modalNumber.textContent = data.queue.queue_number;
            if (modalMeja) modalMeja.textContent = data.meja;
            if (modalLayanan) modalLayanan.textContent = data.layanan || 'Pelayanan Umum';

            if (modal) {
                modal.classList.remove('hidden');
                modal.classList.add('flex');

                if (modalClose) {
                    modalClose.onclick = () => {
                        modal.classList.add('hidden');
                        modal.classList.remove('flex');
                    };
                }
            }

            // Cetak thermal fisik otomatis
            window.print();
        } finally {
            if (submitBtn) submitBtn.disabled = false;
        }
    });
});

// --- CS Realtime Sync via WebSocket ---
if (document.querySelector('#cs-panel') && window.Echo) {
    window.Echo.channel('queue-display')
        .listen('.QueueCreated', (event) => {
            updateMejaBadge(event.meja_id, 1);
        })
        .listen('.QueueCalled', (event) => {
            updateMejaBadge(event.meja_id, -1);
        });
}

// --- Operator Desk Actions & Realtime Sync ---
const operatorContainer = document.querySelector('#operator-panel');

function updateOperatorActiveQueueCount() {
    const list = document.querySelector('#operator-queue-list');
    const count = document.querySelector('#operator-active-queue-count');

    if (list && count) {
        count.textContent = list.querySelectorAll('.queue-row').length;
    }
}

if (operatorContainer && window.Echo) {
    const mejaId = operatorContainer.dataset.mejaId;

    if (mejaId) {
        window.Echo.channel(`meja.${mejaId}`).listen('.QueueCreated', (data) => {
            const list = document.querySelector('#operator-queue-list');
            const emptyNotice = document.querySelector('#operator-empty-notice');

            if (emptyNotice) {
                emptyNotice.classList.add('hidden');
            }

            if (list) {
                const row = document.createElement('article');
                row.className = 'queue-row flex flex-col gap-5 rounded-3xl border border-slate-200 bg-white/80 p-5 shadow-xl shadow-slate-300/30 backdrop-blur-xl transition hover:border-blue-400/50 dark:border-slate-700/80 dark:bg-slate-900/80 dark:shadow-slate-950/25 dark:hover:border-blue-400/40 sm:p-6 md:flex-row md:items-center md:justify-between';
                row.innerHTML = `
                    <div>
                        <p class="text-4xl font-black tracking-tight text-slate-900 dark:text-white">${data.queue_number}</p>
                        <div class="mt-1 flex items-center gap-2">
                            <span class="text-xs font-black tracking-widest uppercase text-blue-600 dark:text-blue-300">${data.status}</span>
                            ${data.layanan ? `<span class="text-slate-400">&bull;</span><span class="text-xs font-semibold text-slate-500 dark:text-slate-400">${data.layanan}</span>` : ''}
                            <span class="text-slate-400">&bull;</span>
                            <span class="text-xs text-slate-400">${data.created_at} WIB</span>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3 md:flex">
                        <form method="POST" action="/operator/queues/${data.id}" data-queue-action>
                            <input type="hidden" name="_token" value="${csrfToken}">
                            <input type="hidden" name="_method" value="PATCH">
                            <input type="hidden" name="action" value="call">
                            <button type="submit" class="w-full rounded-2xl px-4 py-3 text-sm font-black text-white shadow-lg transition bg-blue-600 hover:bg-blue-500 focus:ring-blue-400">Panggil</button>
                        </form>
                        <form method="POST" action="/operator/queues/${data.id}" data-queue-action>
                            <input type="hidden" name="_token" value="${csrfToken}">
                            <input type="hidden" name="_method" value="PATCH">
                            <input type="hidden" name="action" value="replay">
                            <button type="submit" class="w-full rounded-2xl px-4 py-3 text-sm font-black text-white shadow-lg transition bg-sky-600 hover:bg-sky-500 focus:ring-sky-400">Panggil Ulang</button>
                        </form>
                        <form method="POST" action="/operator/queues/${data.id}" data-queue-action>
                            <input type="hidden" name="_token" value="${csrfToken}">
                            <input type="hidden" name="_method" value="PATCH">
                            <input type="hidden" name="action" value="skip">
                            <button type="submit" class="w-full rounded-2xl px-4 py-3 text-sm font-black text-white shadow-lg transition bg-rose-600 hover:bg-rose-500 focus:ring-rose-400">Lewati</button>
                        </form>
                        <form method="POST" action="/operator/queues/${data.id}" data-queue-action>
                            <input type="hidden" name="_token" value="${csrfToken}">
                            <input type="hidden" name="_method" value="PATCH">
                            <input type="hidden" name="action" value="complete">
                            <button type="submit" class="w-full rounded-2xl px-4 py-3 text-sm font-black text-white shadow-lg transition bg-emerald-600 hover:bg-emerald-500 focus:ring-emerald-400">Selesai</button>
                        </form>
                    </div>
                `;
                bindQueueActions(row);
                list.appendChild(row);
                updateOperatorActiveQueueCount();
            }
        });
    }
}

function bindQueueActions(container = document) {
    container.querySelectorAll('[data-queue-action]').forEach((form) => {
        if (form.dataset.bound) return;
        form.dataset.bound = 'true';

        form.addEventListener('submit', async (event) => {
            event.preventDefault();

            const btn = form.querySelector('button');
            if (btn) btn.disabled = true;

            try {
                const response = await fetch(form.getAttribute('action'), {
                    method: 'PATCH',
                    headers: jsonHeaders,
                    body: JSON.stringify({
                        action: form.querySelector('[name="action"]').value,
                    }),
                });

                if (!response.ok) {
                    alert('Aksi tidak dapat diproses.');
                    return;
                }

                const action = form.querySelector('[name="action"]').value;

                if (action === 'call') {
                    const row = form.closest('.queue-row');
                    const statusText = row?.querySelector('.uppercase');
                    if (statusText) statusText.textContent = 'called';
                }

                if (['skip', 'complete'].includes(action)) {
                    const row = form.closest('.queue-row');
                    if (row) {
                        row.style.transition = 'all 0.3s ease';
                        row.style.opacity = '0';
                        row.style.transform = 'translateX(20px)';
                        setTimeout(() => {
                            row.remove();
                            updateOperatorActiveQueueCount();
                            const list = document.querySelector('#operator-queue-list');
                            if (list && list.children.length === 0) {
                                document.querySelector('#operator-empty-notice')?.classList.remove('hidden');
                            }
                        }, 300);
                    }
                }
            } finally {
                if (btn) btn.disabled = false;
            }
        });
    });
}
bindQueueActions();

// --- Display TV Engine (Daftar Seluruh Antrean Menunggu & Panggilan Utama) ---
function updateDisplay(queue, shouldAnnounce = false) {
    if (!queue) {
        return;
    }

    const numberEl = document.querySelector('#display-number');
    const mejaEl = document.querySelector('#display-meja');
    const layananEl = document.querySelector('#display-layanan');

    if (numberEl) {
        numberEl.textContent = queue.queue_number;
        numberEl.classList.remove('animate-call-pulse');
        void numberEl.offsetWidth;
        numberEl.classList.add('animate-call-pulse');
    }

    if (mejaEl) {
        mejaEl.textContent = queue.meja?.nama_meja || queue.meja || '---';
    }

    if (layananEl && queue.layanan) {
        layananEl.textContent = queue.layanan;
    }

    if (shouldAnnounce) {
        queueAnnouncement({
            queue_number: queue.queue_number,
            meja: queue.meja?.nama_meja || queue.meja,
        });
    }
}

// Tambah antrean baru ke daftar tunggu di panel samping display
function addWaitingQueue(queue) {
    const list = document.querySelector('#waiting-queues');
    const emptyState = document.querySelector('#waiting-queues-empty');
    const badgeNumber = document.querySelector('#waiting-count-number');

    if (!list || !queue) return;

    // Cek apakah sudah ada di list
    if (list.querySelector(`[data-waiting-queue="${queue.id}"]`)) {
        return;
    }

    const li = document.createElement('li');
    li.dataset.waitingQueue = queue.id;
    li.className = 'group flex items-center justify-between gap-4 rounded-2xl border border-slate-200 bg-gradient-to-r from-white via-slate-50 to-white p-4 shadow-sm transition-all hover:border-blue-400 dark:border-slate-700/70 dark:from-slate-950 dark:via-slate-900 dark:to-slate-950 dark:shadow-md';

    li.innerHTML = `
        <div class="flex items-center gap-3.5">
            <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-blue-500/15 border border-blue-500/30 text-blue-600 dark:text-blue-400 font-mono text-sm font-black">
                #
            </span>
            <div>
                <span data-queue-number class="text-2xl font-black tracking-tight text-slate-900 group-hover:text-blue-600 dark:text-white dark:group-hover:text-blue-300 block leading-tight">
                    ${queue.queue_number}
                </span>
                <div class="flex items-center gap-1.5 mt-0.5">
                    <span class="text-[10px] font-bold text-amber-600 dark:text-amber-400 uppercase tracking-wider">MENUNGGU</span>
                </div>
            </div>
        </div>
        <div class="flex flex-col items-end">
            <span data-queue-meja class="rounded-xl border border-emerald-400/30 bg-emerald-500/15 px-3 py-1.5 text-right text-xs font-black text-emerald-700 dark:text-emerald-300 shadow-sm">
                ${queue.meja?.nama_meja || queue.meja || '-'}
            </span>
        </div>
    `;

    list.appendChild(li);

    if (emptyState) emptyState.classList.add('hidden');
    if (badgeNumber) {
        badgeNumber.textContent = list.children.length;
    }
}

// Hapus antrean dari daftar tunggu saat dipanggil operator
function removeWaitingQueue(queueId) {
    const list = document.querySelector('#waiting-queues');
    const emptyState = document.querySelector('#waiting-queues-empty');
    const badgeNumber = document.querySelector('#waiting-count-number');

    if (!list) return;

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

function renderWaitingQueues(queues) {
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

async function refreshDisplay() {
    try {
        const response = await fetch('/display/state', {
            headers: { Accept: 'application/json' },
        });

        if (!response.ok) return;

        const state = await response.json();
        renderWaitingQueues(state.waiting);
        if (state.latest) {
            updateDisplay(state.latest, false);
        }
    } catch {
        // Fallback silently
    }
}

if (document.querySelector('#display-number')) {
    refreshDisplay();

    if (window.Echo) {
        window.Echo.channel('queue-display')
            .listen('.QueueCreated', (queue) => {
                // Tiket baru masuk daftar antrean menunggu di samping
                addWaitingQueue(queue);
            })
            .listen('.QueueCalled', (queue) => {
                // Antrean dipanggil: HILANG dari daftar antrean menunggu di samping...
                removeWaitingQueue(queue.queue_id);

                // ...lalu MASUK ke display utama tengah!
                const calledQueue = {
                    id: queue.queue_id,
                    queue_number: queue.queue_number,
                    meja: { nama_meja: queue.meja },
                    layanan: queue.layanan,
                };
                updateDisplay(calledQueue, true);
            });
    }

    setInterval(refreshDisplay, 30000);
}
