import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';

const appVersion = process.env.npm_package_version ?? 'dev';
const buildDate = new Date().toISOString();

// https://vitejs.dev/config/
export default defineConfig({
  base: './',
  plugins: [react()],
  optimizeDeps: {
    exclude: ['lucide-react'],
  },
  define: {
    __APP_VERSION__: JSON.stringify(appVersion),
    __BUILD_DATE__: JSON.stringify(buildDate),
  },
});
