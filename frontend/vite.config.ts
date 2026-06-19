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
      includeAssets: ['icon.png'],
      manifest: {
        name: 'Nidula',
        short_name: 'Nidula',
        description:
          'Organisiert euer Familienleben: Einkaufsliste, Kalender, ToDos und Galerie an einem Ort.',
        lang: 'de',
        theme_color: '#3f5547',
        background_color: '#f2ece1',
        display: 'standalone',
        start_url: '/',
        icons: [
          {
            src: '/icon.png',
            sizes: '512x512',
            type: 'image/png',
            purpose: 'any maskable',
          },
        ],
      },
    }),
  ],
  // Genau eine React-Kopie erzwingen (Safeguard gegen doppelte React-Instanzen
  // durch hoisting, die sonst „Cannot read properties of null (useContext)" o.ä.
  // in Tests/3rd-Party-Komponenten auslösen).
  resolve: {
    dedupe: ['react', 'react-dom'],
  },
  test: {
    environment: 'jsdom',
    globals: true,
    setupFiles: './src/test/setup.ts',
    css: false,
  },
}))
