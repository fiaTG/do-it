import { defineConfig } from 'vitest/config'
import react from '@vitejs/plugin-react'
import tailwindcss from '@tailwindcss/vite'
import { VitePWA } from 'vite-plugin-pwa'

// https://vite.dev/config/
export default defineConfig(({ mode }) => ({
  plugins: [
    react(),
    tailwindcss(),
    VitePWA({
      // In nativen Builds (Capacitor) keinen Service-Worker: in der WKWebView
      // verzögert dessen Precache den Start massiv (langer schwarzer Screen).
      // PWA bleibt fürs Web aktiv.
      disable: mode === 'capacitor',
      registerType: 'autoUpdate',
      includeAssets: ['icon.svg'],
      manifest: {
        name: 'Heimathafen',
        short_name: 'Heimathafen',
        description:
          'Organisiert euer Familienleben: Einkaufsliste, Kalender, ToDos und Galerie an einem Ort.',
        lang: 'de',
        theme_color: '#1f3347',
        background_color: '#f5fafc',
        display: 'standalone',
        start_url: '/',
        icons: [
          {
            src: '/icon.svg',
            sizes: 'any',
            type: 'image/svg+xml',
            purpose: 'any maskable',
          },
        ],
      },
    }),
  ],
  test: {
    environment: 'jsdom',
    globals: true,
    setupFiles: './src/test/setup.ts',
    css: false,
  },
}))
