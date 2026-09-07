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

let lastCallKey = null;

async function refreshDisplay() {
    const numberElement = document.querySelector('#display-number');

    if (!numberElement) {
        return;
    }

    const response = await fetch('/display/latest', {
        headers: { Accept: 'application/json' },
    });
    const queue = await response.json();

    const callKey = queue ? `${queue.id}:${queue.call_count}` : null;

    if (!queue || callKey === lastCallKey) {
        return;
    }

    lastCallKey = callKey;
    numberElement.textContent = queue.queue_number;
    document.querySelector('#display-meja').textContent = queue.meja.nama_meja;

    if ('speechSynthesis' in window) {
        speechSynthesis.cancel();
        const utterance = new SpeechSynthesisUtterance(
            `Nomor antrean ${queue.queue_number}, silakan menuju ${queue.meja.nama_meja}`,
        );

        utterance.lang = 'id-ID';
        utterance.rate = 0.9;
        utterance.pitch = 1;

        speechSynthesis.speak(utterance);
    }
}

if (document.querySelector('#display-number')) {
    refreshDisplay();
    setInterval(refreshDisplay, 2000);
}
