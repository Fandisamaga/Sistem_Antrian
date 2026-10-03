// --- Live Digital Clock & Date Engine ---
const displayTimeZone = 'Asia/Makassar';

export function startLiveClock() {
    const clockEl = document.querySelector('#live-clock');
    const dateEl = document.querySelector('#live-date');
    if (!clockEl && !dateEl) return;

    function update() {
        const now = new Date();
        if (clockEl) {
            const time = new Intl.DateTimeFormat('id-ID', {
                timeZone: displayTimeZone,
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: false,
            }).format(now);
            clockEl.textContent = `${time} WITA`;
        }
        if (dateEl) {
            dateEl.textContent = new Intl.DateTimeFormat('id-ID', {
                timeZone: displayTimeZone,
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

