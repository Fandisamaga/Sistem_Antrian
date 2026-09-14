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
if (typeof window !== 'undefined') {
    window.addEventListener('click', () => {
        getAudioContext();
    }, { once: false });
}

export function playChime() {
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
export function speakText(message) {
    return new Promise((resolve) => {
        if (!('speechSynthesis' in window)) {
            return resolve();
        }

        try {
            window.speechSynthesis.cancel();
            window.speechSynthesis.resume();
        } catch {
            // Ignore resume errors
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

// Announcement Queue to prevent speech cutoffs & synchronize visual display
export const announcementQueue = [];
export let isAnnouncing = false;

let onAnnounceHandler = null;

export function setOnAnnounce(callback) {
    onAnnounceHandler = callback;
}

export function updateNextCallBadge() {
    const banner = document.querySelector('#next-call-banner');
    const textEl = document.querySelector('#next-call-text');
    if (!banner || !textEl) return;

    if (announcementQueue.length > 0) {
        const next = announcementQueue[0];
        const desk = next.meja?.nama_meja || (typeof next.meja === 'string' ? next.meja : '');
        textEl.textContent = `${next.queue_number} (${desk})`;
        banner.classList.remove('hidden');
        banner.classList.add('inline-flex');
    } else {
        banner.classList.add('hidden');
        banner.classList.remove('inline-flex');
    }
}

export async function processAnnouncementQueue() {
    if (isAnnouncing || announcementQueue.length === 0) {
        return;
    }

    isAnnouncing = true;
    const current = announcementQueue.shift();

    // Notifikasi sinkronisasi tampilan layar TV
    if (onAnnounceHandler) {
        onAnnounceHandler(current);
    }
    updateNextCallBadge();

    try {
        await playChime();

        // Eja nomor secara ramah, misal 001 -> "nol nol satu"
        const spelledNumber = current.queue_number.split('').map(d => {
            const map = {'0': 'nol', '1': 'satu', '2': 'dua', '3': 'tiga', '4': 'empat', '5': 'lima', '6': 'enam', '7': 'tujuh', '8': 'delapan', '9': 'sembilan'};
            return map[d] || d;
        }).join(' ');

        const deskName = current.meja?.nama_meja || (typeof current.meja === 'string' ? current.meja : 'loket');
        await speakText(`Nomor antrean, ${spelledNumber}, silakan menuju, ${deskName}`);
    } catch {
        // Fallback
    } finally {
        isAnnouncing = false;
        updateNextCallBadge();
        if (announcementQueue.length > 0) {
            setTimeout(processAnnouncementQueue, 400);
        }
    }
}

export function queueAnnouncement(queueData) {
    announcementQueue.push(queueData);
    updateNextCallBadge();
    processAnnouncementQueue();
}

