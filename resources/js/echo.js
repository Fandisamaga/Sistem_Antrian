import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

const configuredHost = import.meta.env.VITE_REVERB_HOST;
const host = (configuredHost && configuredHost !== 'localhost' && configuredHost !== '127.0.0.1')
    ? configuredHost
    : (window.location.hostname || '127.0.0.1');

const port = import.meta.env.VITE_REVERB_PORT ? Number(import.meta.env.VITE_REVERB_PORT) : 8081;
const scheme = import.meta.env.VITE_REVERB_SCHEME || window.location.protocol.replace(':', '');
const useTls = scheme === 'https' || window.location.protocol === 'https:';

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: host,
    wsPort: port,
    wssPort: port,
    forceTLS: useTls,
    enabledTransports: ['ws', 'wss'],
});

if (window.Echo?.connector?.pusher?.connection) {
    window.Echo.connector.pusher.connection.bind('connected', () => {
        console.info('[WebSocket] Terhubung ke Reverb:', `${useTls ? 'https' : 'http'}://${host}:${port}`);
    });
    window.Echo.connector.pusher.connection.bind('disconnected', () => {
        console.warn('[WebSocket] Koneksi Reverb terputus; mencoba menyambung kembali.');
    });
    window.Echo.connector.pusher.connection.bind('error', (err) => {
        console.warn('[WebSocket] Kendala koneksi Reverb; Echo akan mencoba menyambung kembali:', err);
    });
}
