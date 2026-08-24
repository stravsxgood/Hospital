/**
 * @file echo.ts
 * @description Inisialisasi Laravel Echo Client untuk Laravel Reverb WebSockets.
 * Memungkinkan sinkronisasi real-time instan ke Layar Display Antrean,
 * Pembaruan Resep Farmasi, dan Notifikasi Pembayaran Kasir POS.
 */
import Echo from 'laravel-echo'
import Pusher from 'pusher-js'

declare global {
    interface Window {
        Pusher: typeof Pusher
        Echo: Echo<'reverb'>
    }
}

window.Pusher = Pusher

const reverbKey = import.meta.env.VITE_REVERB_APP_KEY || 'ehgs1mjbjxtjxdgnwpqe'
const reverbHost = import.meta.env.VITE_REVERB_HOST || window.location.hostname
const reverbPort = Number(import.meta.env.VITE_REVERB_PORT || 8080)
const reverbScheme = import.meta.env.VITE_REVERB_SCHEME || 'http'
const isTls = reverbScheme === 'https'

export const echo = new Echo({
    broadcaster: 'reverb',
    key: reverbKey,
    wsHost: reverbHost,
    wsPort: reverbPort,
    wssPort: reverbPort,
    forceTLS: isTls,
    enabledTransports: ['ws', 'wss'],
    disableStats: true,
})

window.Echo = echo

export default echo
