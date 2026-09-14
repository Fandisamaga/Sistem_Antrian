// --- Theme Switcher (Gelap / Terang) ---
export function initTheme() {
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

