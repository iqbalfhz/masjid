import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
            // Font sengaja tidak diunduh di sini. Plugin `bunny()` sebelumnya
            // mengambil Instrument Sans dari fonts.bunny.net pada setiap build,
            // dan build di server pernah gagal karena DNS container tidak bisa
            // menjangkaunya. Font kini disimpan di resources/fonts dan dimuat
            // lewat @font-face di app.css — build tidak butuh internet.
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
