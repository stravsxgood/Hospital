import type { DefineComponent } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { initializeTheme } from '@/composables/useAppearance';
import AppLayout from '@/layouts/AppLayout.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { initializeFlashToast } from '@/lib/flashToast';
import { configureEcho } from '@laravel/echo-vue';

configureEcho({
    broadcaster: 'reverb',
});

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) =>
        resolvePageComponent(
            `./pages/${name}.vue`,
            import.meta.glob<DefineComponent>('./pages/**/*.vue'),
        ),
    layout: (name) => {
        switch (true) {
            // Bebaskan halaman publik, TV display, dan admin layout terdedikasi dari default AppLayout
            case name === 'Welcome' ||
                 name === 'DisplayBoard' ||
                 name === 'Display/QueueTv' ||
                 name === 'MyAppointments' ||
                 name === 'PatientStory' ||
                 name === 'Patient/Story' ||
                 name === 'Specializations/Index' ||
                 name === 'Specializations/Show' ||
                 name === 'Specialization' ||
                 name === 'teams/Index' ||
                 name === 'Teams/Index' ||
                 name === 'Clinic/Location' ||
                 name === 'clinic/Location' ||
                 name === 'doctor/Schedule' ||
                 name === 'doctor/Schedules' ||
                 name === 'doctor/QueueBoard':
                return null;
            case name.startsWith('admin/'):
                return null; // Menggunakan AdminLayout terdedikasi
            case name.startsWith('auth/'):
                return AuthLayout;
            case name.startsWith('settings/'):
                return [AppLayout, SettingsLayout];
            default:
                return AppLayout;
        }
    },
    progress: {
        color: '#4B5563',
    },
});

// This will set light / dark mode on page load...
initializeTheme();

// This will listen for flash toast data from the server...
initializeFlashToast();