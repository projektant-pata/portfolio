import {
    defineConfig
} from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from "@tailwindcss/vite";

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/pages/about-me.css',
                'resources/css/pages/blog.css',
                'resources/css/pages/dashboard.css',
                'resources/css/pages/experience.css',
                'resources/css/pages/index.css',
                'resources/css/pages/projects.css',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        cors: true,
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
