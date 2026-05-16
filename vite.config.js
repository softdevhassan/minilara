import { defineConfig } from 'vite';
import tailwindcss from '@tailwindcss/vite';
import FullReload from 'vite-plugin-full-reload';
import path from 'path';

export default defineConfig({
  base: './',
  plugins: [
    tailwindcss(),
    FullReload(['app/src/pages/**/*.php', 'app/core/**/*.php']),
  ],
  build: {
    outDir: 'app/public/build',
    manifest: true,
    chunkSizeWarningLimit: 1000,
    rollupOptions: {
      input: {
        app: path.resolve(__dirname, 'app/src/assets/js/app.js'),
        style: path.resolve(__dirname, 'app/src/assets/css/app.css'),
      },
      output: {
        manualChunks(id) {
          if (id.includes('node_modules')) {
            return 'vendor';
          }
        },
      },
    },
  },
  server: {
    strictPort: true,
    port: 5173,
    host: '127.0.0.1',
    hmr: {
      host: '127.0.0.1',
    },
    cors: true,
  },
  resolve: {
    alias: {
      '@': path.resolve(__dirname, 'app/src/assets'),
      'jquery': 'jquery',
    },
  },
});
