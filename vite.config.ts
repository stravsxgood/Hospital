import inertia from '@inertiajs/vite';
import { wayfinder } from '@laravel/vite-plugin-wayfinder';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import { defineConfig } from 'vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.ts'],
            refresh: true,
            fonts: [
                bunny('Instrument Sans', {
                    weights: [400, 500, 600],
                }),
            ],
        }),
        inertia(),
        tailwindcss(),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        wayfinder({
            formVariants: true,
        }),
    ],
    build: {
        cssCodeSplit: true,
        chunkSizeWarningLimit: 1000,
        rollupOptions: {
            output: {
                manualChunks(id) {
                    if (id.includes('node_modules')) {
                        if (id.includes('vue') || id.includes('@inertiajs') || id.includes('@vueuse')) {
                            return 'vendor-vue';
                        }
                        if (id.includes('@lucide/vue') || id.includes('lucide-vue-next')) {
                            return 'vendor-icons';
                        }
                        if (id.includes('laravel-echo') || id.includes('pusher-js')) {
                            return 'vendor-realtime';
                        }
                        if (
                            id.includes('motion-v') ||
                            id.includes('reka-ui') ||
                            id.includes('clsx') ||
                            id.includes('tailwind-merge') ||
                            id.includes('class-variance-authority') ||
                            id.includes('vue-sonner')
                        ) {
                            return 'vendor-ui';
                        }
                        if (id.includes('@rive-app')) {
                            return 'vendor-rive';
                        }
                        if (id.includes('axios')) {
                            return 'vendor-axios';
                        }
                    }
                },
            },
        },
    },
});
