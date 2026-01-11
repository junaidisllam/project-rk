import { fileURLToPath, URL } from 'node:url';
import { wayfinder } from '@laravel/vite-plugin-wayfinder';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import { defineConfig } from 'vite';
import viteCompression from 'vite-plugin-compression';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/js/app.ts'],
            ssr: 'resources/js/ssr.ts',
            refresh: true,
        }),
        tailwindcss(),
        wayfinder({
            formVariants: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        viteCompression(),
    ],
    resolve: {
        alias: {
            '@bangladeshi/bangladesh-address': fileURLToPath(new URL('./node_modules/@bangladeshi/bangladesh-address/build/src/index.js', import.meta.url)),
            'ziggy-js': fileURLToPath(new URL('./node_modules/ziggy-js', import.meta.url)),
        },
    },
    // server: {
    //     host: '0.0.0.0', // Explicitly listen on all IPv4 interfaces
    // },
});
