import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/js/app.js'],
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    build: {
        // Especifica el directorio de salida para la producción
        outDir: 'public/build',
        // Limpiar el directorio de salida al construir el proyecto
        emptyOutDir: true,
        // Generar manifest.json en el build
        manifest: true,
        rollupOptions: {
            // Especificar la entrada para facilitar la división de código si es necesario
            input: 'resources/js/app.js',
        },
    },
});
