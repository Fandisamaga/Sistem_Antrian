// --- Live Digital Clock & Date Engine ---
export function startLiveClock() {
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

