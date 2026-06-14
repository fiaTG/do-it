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
  plugins: {
    // API-Anfragen nativ ausführen → umgeht CORS der WebView komplett.
    CapacitorHttp: { enabled: true },
  },
}

export default config
