import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

const token = localStorage.getItem('sdf_token');
console.log("Echo token:", token);

// Guard: Only instantiate Echo if the Reverb key actually exists
if (import.meta.env.VITE_REVERB_APP_KEY) {
    window.Echo = new Echo({
        broadcaster: 'reverb',
        key: import.meta.env.VITE_REVERB_APP_KEY,
        wsHost: import.meta.env.VITE_REVERB_HOST,
        wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
        wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
        forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
        enabledTransports: ['ws', 'wss'],
        authEndpoint: '/api/broadcasting/auth',
        auth: {
            headers: {
                // 2. Tell Laravel not to redirect (no more 302s!)
                'Accept': 'application/json',
                // 3. Pass your Sanctum token
                get Authorization() {
                    const currentToken = localStorage.getItem('sdf_token');
                    return currentToken ? `Bearer ${currentToken}` : '';
                }
            }
        }
    });
} else {
    console.warn("VITE_REVERB_APP_KEY is missing or undefined. Echo initialization skipped.");
}
