import type { CapacitorConfig } from '@capacitor/cli'

/**
 * Capacitor-Konfiguration für die nativen Hüllen (ADR-0012, Phase 6).
 * Die React-SPA aus `dist/` wird gebündelt; die API spricht der Client über
 * `VITE_API_URL` an (für den Android-Emulator i. d. R. http://10.0.2.2:8080).
 * Auth läuft nativ über Bearer-Token statt Cookie (siehe src/lib/api.ts).
 */
const config: CapacitorConfig = {
  appId: 'app.heimathafen',
  appName: 'Heimathafen',
  webDir: 'dist',
  server: {
    // Dev: App über http://localhost servieren, damit Aufrufe an die lokale
    // http-API/Bilder (10.0.2.2) NICHT als Mixed Content blockiert werden.
    // (Mit https-Schema blockt die WebView die http-Bilder; allowMixedContent
    // reicht dafür nicht zuverlässig.) Produktion: HTTPS-API -> https-Schema.
    androidScheme: 'http',
  },
}
// Hinweis: CapacitorHttp wurde bewusst NICHT aktiviert – es bricht
// multipart/FormData-Uploads. Stattdessen erlaubt die API die WebView-Origin
// per CORS (config/cors.php), Anfragen laufen über die normale WebView-Fetch.

export default config
