import { csrfToken, jsonHeaders } from './utils';

const actionButtons = {
    call: ['Panggil', 'bg-blue-600 hover:bg-blue-500 focus:ring-blue-400'],
    replay: ['Panggil Ulang', 'bg-sky-600 hover:bg-sky-500 focus:ring-sky-400'],
    skip: ['Lewati', 'bg-rose-600 hover:bg-rose-500 focus:ring-rose-400'],
    complete: ['Selesai', 'bg-emerald-600 hover:bg-emerald-500 focus:ring-emerald-400'],
};

function actionListForStatus(status) {
    return status === 'waiting' ? ['call'] : ['replay', 'skip', 'complete'];
}

function renderQueueActions(queueId, status) {
    return actionListForStatus(status).map((action) => {
        const [label, style] = actionButtons[action];

        return `
            <form method="POST" action="/operator/queues/${queueId}" data-queue-action>
                <input type="hidden" name="_token" value="${csrfToken}">
                <input type="hidden" name="_method" value="PATCH">
                <input type="hidden" name="action" value="${action}">
                <button type="submit" class="w-full rounded-2xl px-4 py-3 text-sm font-black text-white shadow-lg transition ${style}">${label}</button>
            </form>
        `;
    }).join('');
}

function updateActiveCount(total) {
    const activeCount = document.querySelector('#operator-active-count');
    if (activeCount) activeCount.textContent = total;
}

function updateEmptyState(list) {
    document.querySelector('#operator-empty-notice')?.classList.toggle('hidden', list.children.length > 0);
}

function upsertQueue(data) {
    const list = document.querySelector('#operator-queue-list');
    if (!list || !data?.id) return;

    const existing = list.querySelector(`[data-queue-id="${data.id}"]`);
    if (existing) {
        const statusText = existing.querySelector('.queue-status');
        if (statusText) statusText.textContent = data.status;

        const actions = existing.querySelector('.queue-actions');
        if (actions) {
            actions.innerHTML = renderQueueActions(data.id, data.status);
            bindQueueActions(existing);
        }
        return;
    }

    const row = document.createElement('article');
    row.dataset.queueId = data.id;
    row.className = 'queue-row flex flex-col gap-5 rounded-3xl border border-slate-200 bg-white/80 p-5 shadow-xl shadow-slate-300/30 backdrop-blur-xl transition hover:border-blue-400/50 dark:border-slate-700/80 dark:bg-slate-900/80 dark:shadow-slate-950/25 dark:hover:border-blue-400/40 sm:p-6 md:flex-row md:items-center md:justify-between';
    row.innerHTML = `
        <div>
            <p class="text-4xl font-black tracking-tight text-slate-900 dark:text-white">${data.queue_number}</p>
            <div class="mt-1 flex items-center gap-2">
                <span class="queue-status text-xs font-black tracking-widest uppercase text-blue-600 dark:text-blue-300">${data.status}</span>
                ${data.layanan ? `<span class="text-slate-400">&bull;</span><span class="text-xs font-semibold text-slate-500 dark:text-slate-400">${typeof data.layanan === 'object' ? (data.layanan.nama_layanan || '') : data.layanan}</span>` : ''}
                <span class="text-slate-400">&bull;</span>
                <span class="text-xs text-slate-400">${data.created_at || ''} WIB</span>
            </div>
        </div>
        <div class="queue-actions grid grid-cols-2 gap-3 md:flex">
            ${renderQueueActions(data.id, data.status)}
        </div>
    `;
    bindQueueActions(row);
    list.appendChild(row);
    updateEmptyState(list);
}

export async function refreshOperatorQueues() {
    try {
        const response = await fetch('/operator/queues/state', {
            headers: { Accept: 'application/json' },
        });
        if (!response.ok) return;

        const { queues } = await response.json();
        if (!Array.isArray(queues)) return;

        const list = document.querySelector('#operator-queue-list');
        if (!list) return;

        const activeQueueIds = new Set(queues.map((queue) => String(queue.id)));
        list.querySelectorAll('[data-queue-id]').forEach((row) => {
            if (!activeQueueIds.has(row.dataset.queueId)) row.remove();
        });

        queues.forEach(upsertQueue);
        updateActiveCount(queues.length);
        updateEmptyState(list);
    } catch {
        // WebSocket tetap menjadi jalur utama; polling akan mencoba lagi.
    }
}

export function bindQueueActions(container = document) {
    container.querySelectorAll('[data-queue-action]').forEach((form) => {
        if (form.dataset.bound) return;
        form.dataset.bound = 'true';

        form.addEventListener('submit', async (event) => {
            event.preventDefault();

            const btn = form.querySelector('button');
            if (btn) btn.disabled = true;

            try {
                const actionValue = form.querySelector('[name="action"]')?.value;
                const methodValue = form.querySelector('[name="_method"]')?.value || 'PATCH';
                const actionUrl = form.getAttribute('action');

                const response = await fetch(actionUrl, {
                    method: 'POST',
                    headers: {
                        ...jsonHeaders,
                        'X-HTTP-Method-Override': methodValue,
                    },
                    body: JSON.stringify({
                        _method: methodValue,
                        action: actionValue,
                    }),
                });

                if (!response.ok) {
                    const err = await response.json().catch(() => ({}));
                    alert(err.message || 'Aksi tidak dapat diproses.');
                    return;
                }

                if (actionValue === 'call') {
                    const row = form.closest('.queue-row');
                    const statusText = row?.querySelector('.queue-status');
                    if (statusText) statusText.textContent = 'called';

                    const actions = row?.querySelector('.queue-actions');
                    if (actions && row?.dataset.queueId) {
                        actions.innerHTML = renderQueueActions(row.dataset.queueId, 'called');
                        bindQueueActions(row);
                    }
                }

                if (['skip', 'complete'].includes(actionValue)) {
                    const row = form.closest('.queue-row');
                    if (row) {
                        row.style.transition = 'all 0.3s ease';
                        row.style.opacity = '0';
                        row.style.transform = 'translateX(20px)';
                        setTimeout(() => {
                            row.remove();
                            const list = document.querySelector('#operator-queue-list');
                            if (list && list.children.length === 0) {
                                document.querySelector('#operator-empty-notice')?.classList.remove('hidden');
                            }
                        }, 300);
                    }

                    const activeCount = document.querySelector('#operator-active-count');
                    if (activeCount) {
                        const current = parseInt(activeCount.textContent || '0', 10);
                        activeCount.textContent = Math.max(0, current - 1);
                    }
                }
            } catch (error) {
                console.error('Operator action failed:', error);
                alert('Terjadi kesalahan koneksi saat memproses antrean.');
            } finally {
                if (btn) btn.disabled = false;
            }
        });
    });
}

export function initOperator() {
    const operatorContainer = document.querySelector('#operator-panel');
    if (!operatorContainer) return;

    bindQueueActions(operatorContainer);
    refreshOperatorQueues();
    setInterval(refreshOperatorQueues, 5000);

    const mejaId = operatorContainer.dataset.mejaId;
    if (mejaId && window.Echo) {
        window.Echo.channel(`meja.${mejaId}`)
            .listen('.QueueCreated', (data) => {
                upsertQueue(data);
                const list = document.querySelector('#operator-queue-list');
                if (list) updateActiveCount(list.querySelectorAll('[data-queue-id]').length);
            })
            .listen('.QueueUpdated', (data) => {
                if (['skipped', 'completed'].includes(data.status)) {
                    const list = document.querySelector('#operator-queue-list');
                    if (list) {
                        const targetForm = list.querySelector(`[action*="/queues/${data.queue_id || data.id}"]`);
                        if (targetForm) {
                            const row = targetForm.closest('.queue-row');
                            if (row) {
                                row.remove();
                                if (list.children.length === 0) {
                                    document.querySelector('#operator-empty-notice')?.classList.remove('hidden');
                                }
                                const activeCount = document.querySelector('#operator-active-count');
                                if (activeCount) {
                                    const current = parseInt(activeCount.textContent || '0', 10);
                                    activeCount.textContent = Math.max(0, current - 1);
                                }
                            }
                        }
                    }
                }
            });
    }
}
