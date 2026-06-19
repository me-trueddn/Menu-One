import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/css/themes/adminlte4.css',
                'resources/js/themes/adminlte4.js',
                'resources/css/themes/login.css',
                'resources/js/themes/login.js',
            ],
            refresh: true,
        }),
    ],
});
