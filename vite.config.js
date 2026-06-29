import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';

export default defineConfig({
    server: {
        host: '127.0.0.1',
        port: 5173,
    },
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/navbar.jsx',
                'resources/js/landing.jsx',
                'resources/js/katalog.jsx',
                'resources/js/checkout.jsx',
                'resources/js/orders.jsx',
                'resources/js/order-detail.jsx',
                'resources/js/umkm-orders.jsx',
                'resources/js/umkm-dashboard-ui.jsx',
                'resources/js/admin-dashboard.jsx',
            ],
            refresh: true,
        }),
        react(),
    ],
});
