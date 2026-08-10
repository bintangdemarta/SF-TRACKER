import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { VitePWA } from 'vite-plugin-pwa';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        VitePWA({
            strategies: 'injectManifest',
            srcDir: 'resources/js',
            filename: 'sw.js',
            injectManifest: {
                // Only precache the actual build assets + the explicit
                // offline-shell entries added below — not every file Vite
                // touches.
                globPatterns: ['assets/*.{js,css}'],
                additionalManifestEntries: [
                    { url: '/offline.html', revision: 'v1' },
                    { url: '/icon-192.png', revision: 'v1' },
                    { url: '/icon-512.png', revision: 'v1' },
                ],
            },
            registerType: 'autoUpdate',
            injectRegister: false,
            includeAssets: ['favicon.ico'],
            // sw.js physically lives under /build/ (Vite's outDir), but it
            // must control the whole app, not just its own asset directory —
            // paired with the `Service-Worker-Allowed` header in
            // public/.htaccess and docker/nginx/app.conf.
            scope: '/',
            manifest: {
                name: 'SF-Tracker - Keuangan Driver ShopeeFood',
                short_name: 'SF-Tracker',
                description: 'Catat keuangan dan performa driver ShopeeFood — profit bersih real-time.',
                theme_color: '#0f172a',
                background_color: '#0f172a',
                display: 'standalone',
                start_url: '/dashboard',
                scope: '/',
                icons: [
                    { src: '/icon-192.png', sizes: '192x192', type: 'image/png', purpose: 'any' },
                    { src: '/icon-192.png', sizes: '192x192', type: 'image/png', purpose: 'maskable' },
                    { src: '/icon-512.png', sizes: '512x512', type: 'image/png', purpose: 'any' },
                    { src: '/icon-512.png', sizes: '512x512', type: 'image/png', purpose: 'maskable' },
                ],
            },
            devOptions: {
                enabled: false,
            },
        }),
    ],
});
