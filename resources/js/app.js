import './echo';

const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

const jsonHeaders = {
    Accept: 'application/json',
    'X-Requested-With': 'XMLHttpRequest',
    'X-CSRF-TOKEN': csrfToken,
};

// Theme Switcher (Gelap / Terang)
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

// Web Audio API Ding-Dong Chime (587.33Hz D5 -> 440Hz A4)
function playChime() {
    try {
        const AudioCtx = window.AudioContext || window.webkitAudioContext;
        if (!AudioCtx) return Promise.resolve();
        const ctx = new AudioCtx();
        if (ctx.state === 'suspended') {
            ctx.resume();
        }

        const now = ctx.currentTime;

        // Tone 1: High note
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

        // Tone 2: Lower harmonic note
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

        return new Promise((resolve) => setTimeout(resolve, 800));
    } catch {
        return Promise.resolve();
    }
}

// Indonesian Text-to-Speech
function speakInIndonesian(message) {
    if (!('speechSynthesis' in window)) {
        return;
    }

    const utterance = new SpeechSynthesisUtterance(message);
    const indonesianVoice = speechSynthesis
        .getVoices()
        .find((voice) => voice.lang.toLowerCase().startsWith('id'));

    utterance.lang = 'id-ID';
    utterance.rate = 0.85;
    utterance.pitch = 1.0;
    utterance.volume = 1;

    if (indonesianVoice) {
        utterance.voice = indonesianVoice;
    }

    speechSynthesis.cancel();
    speechSynthesis.speak(utterance);
}

// Live Digital Clock & Date Engine
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

// Kiosk Customer Service Ticket Form
document.querySelectorAll('[data-queue-form]').forEach((form) => {
    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) submitBtn.disabled = true;

        try {
            const response = await fetch(form.getAttribute('action'), {
                method: 'POST',
                headers: jsonHeaders,
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
            const ticketDateEl = document.querySelector('#ticket-date');

            if (ticketNumberEl) ticketNumberEl.textContent = data.queue.queue_number;
            if (ticketMejaEl) ticketMejaEl.textContent = data.meja;
            if (ticketDateEl) {
                ticketDateEl.textContent = new Intl.DateTimeFormat('id-ID', {
                    dateStyle: 'full',
                    timeStyle: 'medium',
                }).format(new Date());
            }

            // Show visual feedback modal if present
            const modal = document.querySelector('#ticket-modal');
            const modalNumber = document.querySelector('#modal-ticket-number');
            const modalMeja = document.querySelector('#modal-ticket-meja');
            const modalClose = document.querySelector('#modal-ticket-close');

            if (modal && modalNumber && modalMeja) {
                modalNumber.textContent = data.queue.queue_number;
                modalMeja.textContent = data.meja;
                modal.classList.remove('hidden');
                modal.classList.add('flex');

                if (modalClose) {
                    modalClose.onclick = () => {
                        modal.classList.add('hidden');
                        modal.classList.remove('flex');
                    };
                }

                setTimeout(() => {
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                }, 4000);
            }

            // Trigger physical ticket print
            window.print();
        } finally {
            if (submitBtn) submitBtn.disabled = false;
        }
    });
});

// Operator Desk Actions
document.querySelectorAll('[data-queue-action]').forEach((form) => {
    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        const btn = form.querySelector('button');
        if (btn) btn.disabled = true;

        try {
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
                const row = form.closest('.queue-row');
                if (row) {
                    row.style.transition = 'all 0.3s ease';
                    row.style.opacity = '0';
                    row.style.transform = 'translateX(20px)';
                    setTimeout(() => row.remove(), 300);
                }
            }
        } finally {
            if (btn) btn.disabled = false;
        }
    });
});

// Public Display update
function updateDisplay(queue, shouldAnnounce = false) {
    if (!queue) {
        return;
    }

    const numberEl = document.querySelector('#display-number');
    const mejaEl = document.querySelector('#display-meja');

    if (numberEl) {
        numberEl.textContent = queue.queue_number;
        numberEl.classList.remove('animate-call-pulse');
        void numberEl.offsetWidth;
        numberEl.classList.add('animate-call-pulse');
    }

    if (mejaEl) {
        mejaEl.textContent = queue.meja.nama_meja;
    }

    if (shouldAnnounce) {
        playChime().then(() => {
            speakInIndonesian(
                `Nomor antrean, ${queue.queue_number.replace('-', ' ')}, silakan menuju, ${queue.meja.nama_meja}`,
            );
        });
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
        item.className =
            'group flex items-center justify-between gap-4 rounded-2xl border border-slate-200 bg-slate-50/90 shadow-sm p-4 hover:border-blue-400 hover:bg-white dark:border-slate-700/70 dark:bg-gradient-to-r dark:from-slate-950 dark:via-slate-900 dark:to-slate-950 dark:shadow-lg dark:shadow-slate-950/30 dark:hover:border-blue-500/50 dark:hover:bg-slate-850 transition-all';

        const detail = document.createElement('div');
        detail.className = 'flex items-center gap-3.5';

        const iconContainer = document.createElement('span');
        iconContainer.className =
            'flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600 border border-blue-200 dark:bg-blue-500/15 dark:border-blue-500/30 dark:text-blue-400';
        iconContainer.innerHTML =
            '<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>';

        const textWrapper = document.createElement('div');

        const number = document.createElement('span');
        number.dataset.calledNumber = '';
        number.className = 'text-2xl font-black tracking-tight text-slate-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-200 block';

        const statusSub = document.createElement('p');
        statusSub.className = 'text-[10px] font-bold text-slate-500 dark:text-slate-400';
        statusSub.textContent = 'STATUS: DIPANGGIL';

        textWrapper.append(number, statusSub);
        detail.append(iconContainer, textWrapper);

        const meja = document.createElement('span');
        meja.dataset.calledMeja = '';
        meja.className =
            'rounded-xl border border-emerald-500/30 bg-emerald-50 px-3.5 py-2 text-right text-xs font-black text-emerald-700 dark:border-emerald-400/30 dark:bg-emerald-500/15 dark:text-emerald-300 shadow-sm';

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
        // Silent fallback
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

    setInterval(refreshDisplay, 30000);
}



