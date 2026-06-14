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

**CORS & Schema (Mixed Content):** Die WebView blockiert `http`-Inhalte auf einer
`https`-Seite (Mixed Content). Da die lokale API plain `http` ist, servieren wir
die App im Dev über **`http://localhost`** (`server.androidScheme: 'http'` in
`capacitor.config.ts`) – dann sind App, API und Bilder same-scheme. Die API
erlaubt die WebView-Origin per CORS (`config/cors.php`: `http://localhost`,
`https://localhost`, `capacitor://localhost`). API-Anfragen/Uploads laufen über
die normale WebView-Fetch (CapacitorHttp ist bewusst aus – es bricht
multipart-Uploads).

> **Wichtig fürs Produktions-Release:** Dann läuft die API über **HTTPS** →
> `androidScheme` zurück auf `https` (Default) stellen (am besten per Env-Variable
> umschaltbar) und `VITE_API_URL=https://…`. `http` ist ausschließlich eine
> Dev-Erleichterung für die lokale Plain-HTTP-API.

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

## iOS – aufs eigene iPhone (Runbook für den Mac)

iOS-Builds gehen **nur auf macOS + Xcode**. Da Backend/Code hier auf dem Windows-
Rechner liegen, läuft der iOS-Teil auf dem MacBook. Am einfachsten ist es, den
**ganzen Stack auf dem Mac** zu fahren (Docker + Node + Xcode), dann ist nichts
über zwei Rechner verteilt.

### 0. Voraussetzungen (Mac)
- **Xcode** (App Store) + einmal öffnen, Command Line Tools installieren lassen.
- **CocoaPods** (`sudo gem install cocoapods` oder via Homebrew).
- **Node** + **Docker Desktop für Mac**.
- Eine **Apple-ID** (kostenlos reicht für eigenes Gerät; App läuft dann 7 Tage,
  danach neu signieren).

### 1. Code auf den Mac holen
Auf **Windows** den Branch pushen, dann auf dem **Mac** klonen:
```bash
# Mac
git clone https://github.com/fiaTG/do-it.git
cd do-it
git checkout modernize/phase-0-foundation
```

### 2. Backend auf dem Mac starten
```bash
cd backend
cp .env.example .env && php artisan key:generate   # falls nötig
docker compose up -d
docker compose exec laravel.test php artisan migrate --seed
```

### 3. API-URL setzen (iOS)
Anders als beim Android-Emulator (`10.0.2.2`) gilt für iOS:
- **iOS-Simulator** erreicht den Mac unter `localhost`.
- **Echtes iPhone** braucht die **LAN-IP des Macs** (gleiches WLAN).

In `frontend/.env.capacitor.local` (gitignored) überschreiben:
```
# Simulator:
VITE_API_URL=http://localhost:8080/api/v1
# ODER echtes iPhone (IP per `ipconfig getifaddr en0`):
# VITE_API_URL=http://192.168.x.y:8080/api/v1
```

### 4. iOS-Plattform anlegen
```bash
cd frontend
npm install
npm run build:native
npx cap add ios               # erzeugt frontend/ios/ (+ pod install)
npx capacitor-assets generate --ios   # App-Icon & Splash
```

### 5. Klartext-HTTP erlauben (nur Dev)
iOS' App Transport Security blockt `http`. Für die lokale http-API in
`frontend/ios/App/App/Info.plist` ergänzen (Produktion mit HTTPS: weglassen):
```xml
<key>NSAppTransportSecurity</key>
<dict>
  <key>NSAllowsArbitraryLoads</key>
  <true/>
</dict>
```
(Die WebView-Origin `capacitor://localhost` ist in `config/cors.php` bereits
erlaubt.)

### 6. In Xcode öffnen, signieren, starten
```bash
npm run ios:open          # = build:native + cap sync ios + cap open ios
```
In Xcode:
1. Target **App** → **Signing & Capabilities** → dein **Apple-ID-Team** wählen
   (Xcode signiert automatisch; Bundle-ID `app.heimathafen` ggf. eindeutig machen).
2. Oben das **iPhone** als Ziel wählen (per USB verbunden, „Diesem Computer
   vertrauen" bestätigen) – oder einen **Simulator**.
3. **▶ Run**.
4. Echtes Gerät: am iPhone **Einstellungen → Allgemein → VPN & Geräteverwaltung**
   → Entwickler-Zertifikat **vertrauen**.

Nach Frontend-Änderungen wie bei Android: `npm run cap:sync` (bzw. `ios:open`).
