import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue2';
import inject from '@rollup/plugin-inject';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/js/supersonic.js',
                'resources/scss/supersonic.scss'
            ],
            publicDirectory: 'resources/dist',
        }),
        inject({
            Vue: 'vue',
            _: 'underscore',
            include: 'resources/js/components/**'
        }),
        vue()
    ],
});
