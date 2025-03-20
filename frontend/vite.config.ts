import { fileURLToPath, URL } from 'node:url'

import { defineConfig, loadEnv } from 'vite'
import vue from '@vitejs/plugin-vue'
import vueJsx from '@vitejs/plugin-vue-jsx'
import VueDevTools from 'vite-plugin-vue-devtools'
import fs from 'fs';

// https://vitejs.dev/config/
export default defineConfig(({ mode }) => {
  const env = loadEnv(mode, process.cwd(), '')

  return {
    plugins: [
      vue(),
      vueJsx(),
      VueDevTools(),
    ],
    resolve: {
      alias: {
        '@': fileURLToPath(new URL('./src', import.meta.url))
      }
    },
    optimizeDeps: {
      exclude: ['jwt-decode'] // Fixes 504 (Outdated Optimize Dep) error.
    },
    server: mode === 'development' ? {
      host: true, // Allow external access
      https: {
        key: fs.readFileSync('/certificates/'+env.VITE_DOMAIN+'.key'),
        cert: fs.readFileSync('/certificates/'+env.VITE_DOMAIN+'.crt'),
      },
    } : {},
  }
})
