import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/css/dashboard.css',
                'resources/css/dashboard-admin.css',
                'resources/css/dashboard-portal.css',
                'resources/js/dashboard.js',
                'resources/js/dashboard-admin.js',
            ],
            refresh: true,
        }),
    ],
});
