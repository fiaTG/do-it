# Native Apps (Capacitor) – Phase 6

> Status: Android-Gerüst steht. Die React-SPA aus `frontend/` wird unverändert
> in eine native Hülle (Capacitor) gepackt. Hintergrund: [ADR-0012](adr/0012-multi-client-packaging.md).

Die SPA ist die **einzige Quelle** – dieselbe Codebasis läuft als Web/PWA und
als native App. Capacitor bündelt den `dist/`-Build und stellt native APIs bereit.

## Auth: Web vs. nativ

Im Browser nutzt die App **Cookie-/CSRF-Auth** (Sanctum SPA). Native Apps haben
keine gemeinsame Origin/Cookies mit der API, daher schalten sie automatisch auf
**Bearer-Token-Auth** um:

- `frontend/src/lib/api.ts` erkennt über `Capacitor.isNativePlatform()` den
  nativen Modus (`isNative`).
- Login/Registrierung senden dann `device_name` → die API gibt einen API-Token
  zurück (`POST /auth/login` bzw. `/auth/register`).
- Der Token wird via `@capacitor/preferences` persistiert und als
  `Authorization: Bearer …` an jede Anfrage gehängt; beim Start lädt
  `loadStoredToken()` ihn wieder (Auto-Login). Logout löscht ihn lokal.

## API-URL (wichtig)

Die native App lädt gebündeltes HTML/JS und spricht die API über `VITE_API_URL`
an. `localhost` zeigt auf dem Gerät auf sich selbst – darum nutzen native Builds
einen **eigenen Vite-Mode** `capacitor` mit `frontend/.env.capacitor`, ohne den
Web-Build (`.env`, `localhost`) zu verändern:

| Ziel | `VITE_API_URL` (in `.env.capacitor` bzw. `.env.capacitor.local`) |
|------|----------------|
| Android-Emulator → Host | `http://10.0.2.2:8080/api/v1` (Standard) |
| Echtes Gerät im LAN | `http://<LAN-IP-des-PCs>:8080/api/v1` |
| Produktion | `https://api.<domain>/api/v1` |

Die Build-Skripte (`npm run cap:sync` / `android:open`) bauen mit
`vite build --mode capacitor`. Gerät-/maschinenspezifische Werte gehören in
`.env.capacitor.local` (gitignored).

**CORS:** In `capacitor.config.ts` ist `CapacitorHttp` aktiviert – API-Anfragen
laufen dadurch nativ und umgehen die CORS-Prüfung der WebView. Token-Auth braucht
ohnehin kein Cookie. Für echte Geräte ggf. Klartext-HTTP erlauben oder HTTPS.

## Voraussetzungen (lokal)

- **Android Studio** inkl. Android SDK + ein Emulator oder ein echtes Gerät.
- JDK (bringt Android Studio mit).

Der `frontend/android/`-Ordner ist **bewusst nicht eingecheckt** (siehe
`frontend/.gitignore`): er ist vollständig regenerierbar und enthält eine Kopie
des Builds. Sobald native Anpassungen (App-Icon, Splash, Permissions) dazukommen,
sollte er eingecheckt werden.

## Erstmaliges Einrichten

```bash
cd frontend
npm install
npm run build
npx cap add android        # erzeugt frontend/android/
```

## Bauen & Starten

```bash
cd frontend
# Build + Assets in die native Hülle kopieren und Android Studio öffnen:
npm run android:open
```

In Android Studio dann auf einem Emulator/Gerät starten (Run ▶) oder ein
APK/AAB bauen (Build → Build Bundle(s)/APK(s)).

Nach jeder Code-Änderung am Frontend:

```bash
npm run cap:sync          # = npm run build && cap sync
```

## App-Icon & Splash-Screen

Quelle ist ein selbst generiertes Anker-Logo (maritime Marke). Die Roh-Assets
liegen in `frontend/assets/` (aus `scripts/generate-icons.mjs` per SVG erzeugt),
daraus baut `@capacitor/assets` die nativen Dichten:

```bash
cd frontend
npm run assets:icons      # = generate-icons.mjs + capacitor-assets generate --android
```

Die generierten nativen Dateien landen unter `android/.../res/` (gitignored).
Logo anpassen → Werte/SVG in `scripts/generate-icons.mjs` ändern und neu laufen
lassen. Das Web-/PWA-Icon ist `frontend/public/icon.svg` (gleiche Marke).

## iOS (später)

iOS braucht zwingend **macOS + Xcode**. Vorgehen analog:

```bash
npm install @capacitor/ios
npx cap add ios
npx cap open ios
```
