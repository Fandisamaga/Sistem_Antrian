import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

const host = (import.meta.env.VITE_REVERB_HOST && import.meta.env.VITE_REVERB_HOST !== 'localhost')
    ? import.meta.env.VITE_REVERB_HOST
    : (window.location.hostname || '127.0.0.1');

const port = import.meta.env.VITE_REVERB_PORT ? Number(import.meta.env.VITE_REVERB_PORT) : 8081;
const scheme = import.meta.env.VITE_REVERB_SCHEME || 'http';

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: host,
    wsPort: port,
    wssPort: port,
    forceTLS: scheme === 'https',
    enabledTransports: ['ws', 'wss'],
});
