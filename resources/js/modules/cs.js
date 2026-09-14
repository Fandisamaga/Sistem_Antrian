import { csrfToken } from './utils';

// --- CS: Otomatis Sorting Tombol Meja (Meja Lengang / 0 Antrean Naik ke Atas) ---
export function sortMejaButtons() {
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

export function updateMejaBadge(mejaId, change) {
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

export function initCustomerService() {
    const csPanel = document.querySelector('#cs-panel');
    if (!csPanel) return;

    // 1. Pemilihan Layanan Dulu, Baru Pilih Meja
    const layananButtons = csPanel.querySelectorAll('[data-layanan-option]');
    const labelLayananTerpilih = csPanel.querySelector('#label-layanan-terpilih');

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
            csPanel.querySelectorAll('.input-layanan-id').forEach(input => {
                input.value = id;
            });
        });
    });

    // 2. Inisialisasi urutan meja awal
    sortMejaButtons();

    // 3. Submit Form Buat Tiket
    csPanel.querySelectorAll('[data-queue-form]').forEach((form) => {
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

                // Isi data tiket thermal fisik
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

                // Tampilkan modal tiket
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

                // Cetak thermal otomatis
                window.print();
            } finally {
                if (submitBtn) submitBtn.disabled = false;
            }
        });
    });

    // 4. CS Realtime Sync via WebSocket
    if (window.Echo) {
        window.Echo.channel('queue-display')
            .listen('.QueueCreated', (event) => {
                updateMejaBadge(event.meja_id, 1);
            })
            .listen('.QueueCalled', (event) => {
                updateMejaBadge(event.meja_id, -1);
            })
            .listen('.QueueUpdated', (event) => {
                if (event.previous_status === 'waiting' && ['skipped', 'completed'].includes(event.status)) {
                    updateMejaBadge(event.meja_id, -1);
                }
            });
    }
}

