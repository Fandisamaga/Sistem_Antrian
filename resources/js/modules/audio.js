// --- Web Audio API Chime & FIFO Announcement Engine ---
let audioCtxInstance = null;
let audioUnlocked = false;
let onAudioStatusHandler = null;

function getAudioContext() {
    if (!audioCtxInstance) {
        const AudioCtx = window.AudioContext || window.webkitAudioContext;
        if (AudioCtx) {
            audioCtxInstance = new AudioCtx();
            // A context can be suspended again after the display has been in
            // the background. Keep observing it, rather than only its first
            // transition, so the enable-audio prompt remains accurate.
            audioCtxInstance.addEventListener('statechange', () => {
                audioUnlocked = audioCtxInstance.state === 'running';
                notifyAudioStatus();
            });
        }
    }
    return audioCtxInstance;
}

function notifyAudioStatus() {
    onAudioStatusHandler?.(audioUnlocked);
}

export function setOnAudioStatus(callback) {
    onAudioStatusHandler = callback;
    notifyAudioStatus();
}

// This is deliberately safe to call from a WebSocket event. A number call
// must never wait for a visitor/operator to click the display first.
export async function prepareAudio() {
    const ctx = getAudioContext();

    try {
        // Resume TTS first. This API is independent from Web Audio and must
        // still run when the browser blocks the optional chime.
        window.speechSynthesis?.resume();

        if (ctx && ctx.state !== 'running') {
            // Browsers can leave this promise pending while autoplay is
            // restricted. Do not let it hold the spoken queue announcement.
            const resumeContext = Promise.resolve(ctx.resume()).catch(() => null);
            const continueWithoutChime = new Promise((resolve) => {
                setTimeout(resolve, 250);
            });

            await Promise.race([resumeContext, continueWithoutChime]);
        }

        // Safari/Chrome can keep the speech engine paused until a user gesture.
        window.speechSynthesis?.resume();
        audioUnlocked = !ctx || ctx.state === 'running';
    } catch {
        audioUnlocked = false;
    }

    notifyAudioStatus();
    return audioUnlocked;
}

export function playChime() {
    try {
        const ctx = getAudioContext();
        if (!ctx || ctx.state !== 'running') return Promise.resolve();

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

function numberToIndonesian(number) {
    const value = Number.parseInt(number, 10);
    if (!Number.isFinite(value) || value < 0) return String(number);

    const words = ['', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan'];

    if (value < 10) return words[value] || 'nol';
    if (value === 10) return 'sepuluh';
    if (value === 11) return 'sebelas';
    if (value < 20) return `${words[value - 10]} belas`;
    if (value < 100) {
        return `${numberToIndonesian(Math.floor(value / 10))} puluh${value % 10 ? ` ${numberToIndonesian(value % 10)}` : ''}`;
    }
    if (value < 200) return `seratus${value % 100 ? ` ${numberToIndonesian(value % 100)}` : ''}`;
    if (value < 1000) {
        return `${numberToIndonesian(Math.floor(value / 100))} ratus${value % 100 ? ` ${numberToIndonesian(value % 100)}` : ''}`;
    }
    if (value < 2000) return `seribu${value % 1000 ? ` ${numberToIndonesian(value % 1000)}` : ''}`;
    if (value < 1000000) {
        return `${numberToIndonesian(Math.floor(value / 1000))} ribu${value % 1000 ? ` ${numberToIndonesian(value % 1000)}` : ''}`;
    }

    return String(value);
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
        const indonesianVoice = voices.find((v) => v.lang?.toLowerCase() === 'id-id')
            || voices.find((v) => v.lang?.toLowerCase().startsWith('id'));

        utterance.lang = 'id-ID';
        utterance.rate = 0.78;
        utterance.pitch = 0.92;
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
        // TTS must be attempted immediately for every call. Some browsers may
        // reject the chime before a trusted interaction, but Web Speech can
        // still announce the number and the FIFO queue must continue.
        await prepareAudio();
        await playChime();

        const queueNumber = numberToIndonesian(current.queue_number);

        const deskName = current.meja?.nama_meja || (typeof current.meja === 'string' ? current.meja : 'loket');
        await speakText(`Perhatian. Nomor antrean ${queueNumber}. Silakan menuju ke. ${deskName}.`);
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
