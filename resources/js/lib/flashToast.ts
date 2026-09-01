/**
 * @file flashToast.ts
 * @description Global Flash Message & CRUD Notification Interceptor menggunakan vue3-toastify.
 * Menangani respon flash backend Laravel (success, error, warning, info) secara otomatis
 * di semua dashboard (Admin, Staff, Dokter, Pasien) serta mengekspor fungsi utilitas toast.
 */
import { router } from '@inertiajs/vue3';
import { toast, type ToastOptions, type ToastType } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';

export interface FlashPayload {
    success?: string | null;
    error?: string | null;
    warning?: string | null;
    info?: string | null;
    message?: string | null;
    toast?: {
        type: 'success' | 'error' | 'warning' | 'info';
        message: string;
    };
}

const defaultOptions: ToastOptions = {
    autoClose: 3500,
    position: 'top-right',
    theme: 'colored',
    pauseOnHover: true,
    closeOnClick: true,
    dangerouslyHTMLString: false,
};

let lastShownMessage = '';
let lastShownTime = 0;

/**
 * Mencegah pesan duplikat dalam jeda waktu yang sangat singkat (< 600ms)
 */
function shouldShowToast(msg: string): boolean {
    if (!msg || typeof msg !== 'string') return false;
    const now = Date.now();
    if (lastShownMessage === msg && now - lastShownTime < 600) {
        return false;
    }
    lastShownMessage = msg;
    lastShownTime = now;
    return true;
}

/**
 * Menampilkan pesan toast berdasarkan flash data
 */
export function handleFlashMessage(flash?: FlashPayload): void {
    if (!flash) return;

    if (flash.success && typeof flash.success === 'string' && shouldShowToast(flash.success)) {
        toast.success(flash.success, defaultOptions);
    } else if (flash.error && typeof flash.error === 'string' && shouldShowToast(flash.error)) {
        toast.error(flash.error, defaultOptions);
    } else if (flash.warning && typeof flash.warning === 'string' && shouldShowToast(flash.warning)) {
        toast.warning(flash.warning, defaultOptions);
    } else if (flash.info && typeof flash.info === 'string' && shouldShowToast(flash.info)) {
        toast.info(flash.info, defaultOptions);
    } else if (flash.message && typeof flash.message === 'string' && shouldShowToast(flash.message)) {
        toast.info(flash.message, defaultOptions);
    } else if (flash.toast && flash.toast.message && shouldShowToast(flash.toast.message)) {
        const type = flash.toast.type || 'info';
        toast(flash.toast.message, { ...defaultOptions, type: type as ToastType });
    }
}

/**
 * Inisialisasi listener Inertia untuk semua request navigasi & CRUD
 */
export function initializeFlashToast(): void {
    // 1. Dengarkan setiap kali request Inertia selesai dengan sukses
    router.on('success', (event) => {
        const page = event.detail.page;
        const flash = page.props.flash as FlashPayload | undefined;
        handleFlashMessage(flash);
    });

    // 2. Dengarkan jika terjadi error validasi / server error dari request Inertia
    router.on('error', (errors) => {
        const detail = (errors as any).detail?.errors;
        if (detail && typeof detail === 'object') {
            const firstError = Object.values(detail)[0];
            if (typeof firstError === 'string' && shouldShowToast(firstError)) {
                toast.error(firstError, defaultOptions);
            }
        }
    });

    // 3. Dengarkan event custom flash jika dipanggil manual
    router.on('flash' as any, (event: any) => {
        const flash = event.detail?.flash;
        handleFlashMessage(flash);
    });
}

/**
 * Helper notifikasi manual yang dapat dipanggil langsung dari komponen Vue apa pun
 */
export const notify = {
    success: (message: string, options?: ToastOptions) => {
        if (shouldShowToast(message)) {
            toast.success(message, { ...defaultOptions, ...options });
        }
    },
    error: (message: string, options?: ToastOptions) => {
        if (shouldShowToast(message)) {
            toast.error(message, { ...defaultOptions, ...options });
        }
    },
    warning: (message: string, options?: ToastOptions) => {
        if (shouldShowToast(message)) {
            toast.warning(message, { ...defaultOptions, ...options });
        }
    },
    info: (message: string, options?: ToastOptions) => {
        if (shouldShowToast(message)) {
            toast.info(message, { ...defaultOptions, ...options });
        }
    },
};

export { toast };
