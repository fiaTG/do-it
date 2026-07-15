# Native Apps (Capacitor) – Phase 6

> Status: **Android (Emulator) und iOS (Simulator + echtes iPhone) laufen.**
> Die React-SPA aus `frontend/` wird unverändert in eine native Hülle (Capacitor)
> gepackt. Hintergrund: [ADR-0012](adr/0012-multi-client-packaging.md).

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
| ------ | ---------------- |
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

Quelle ist das Nidula-Logo (Nest+Haus+Herz, ADR-0018). Die Roh-Assets
liegen in `frontend/assets/` (aus `scripts/generate-icons.mjs` per SVG erzeugt),
daraus baut `@capacitor/assets` die nativen Dichten:

```bash
cd frontend
npm run assets:icons      # = generate-icons.mjs + capacitor-assets generate --android
```

Die generierten nativen Dateien landen unter `android/.../res/` (gitignored).
Logo anpassen → Werte/SVG in `scripts/generate-icons.mjs` ändern und neu laufen
lassen. Das Web-/PWA-Icon ist `frontend/public/icon.svg` (gleiche Marke).

## iOS – aufs eigene iPhone (erprobtes Mac-Runbook)

iOS-Builds gehen **nur auf macOS + Xcode**. Am einfachsten den **ganzen Stack auf
dem Mac** fahren (Docker + Node + Xcode), dann ist nichts über zwei Rechner
verteilt. Dieses Runbook wurde am 2026-06-17 einmal komplett durchgespielt – die
markierten ⚠️-Stellen sind die Punkte, die wirklich Zeit gekostet haben.

### 0. Dev-Umgebung auf dem Mac (von Null)

1. **Xcode**: ⚠️ Der App Store bietet nur das **neueste** Xcode an, das ein
   neueres macOS verlangt (z. B. macOS 26 für Xcode 26). Auf **Sequoia 15.x** ein
   passendes **Xcode 16.x** über <https://developer.apple.com/download/all/>
   laden (`.xip`, Apple-ID-Login genügt, kein bezahlter Account). Entpacken →
   nach **/Programme** ziehen → einmal öffnen → iOS-Plattform-Komponente
   installieren lassen.
2. ⚠️ **Xcode mit den CLI-Tools verknüpfen** (sonst `xcrun simctl` =
   „unable to find utility / invalid developer directory"):

   ```bash
   sudo xcode-select -s /Applications/Xcode.app/Contents/Developer
   sudo xcodebuild -license accept
   ```

3. **Homebrew** installieren (<https://brew.sh>), die „Next steps"-Zeilen des
   Installers ausführen (Apple Silicon: `eval "$(/opt/homebrew/bin/brew shellenv)"`).
4. `brew install node cocoapods git`
5. `brew install --cask docker` → **Docker Desktop** starten und warten, bis der
   Wal **„Engine running"** zeigt.
6. Eine **Apple-ID** (kostenlos reicht; App läuft dann 7 Tage, danach neu signieren).

### 1. Code holen

```bash
cd ~
git clone https://github.com/fiaTG/do-it.git
cd do-it
git checkout modernize/phase-0-foundation
```

### 2. Backend starten

⚠️ **`vendor/` fehlt im frischen Klon** (gitignored). Sail baut sein Image aus
`vendor/laravel/sail/runtimes/8.5/Dockerfile` – ohne `vendor/` schlägt
`docker compose up` fehl („unable to prepare context … vendor/…"). Darum zuerst
per Composer-Container `vendor/` erzeugen (kein lokales PHP nötig):

```bash
cd ~/do-it/backend
cp .env.example .env
docker run --rm -u "$(id -u):$(id -g)" -v "$(pwd):/app" -w /app composer:latest install --ignore-platform-reqs --no-scripts
docker compose up -d --build
docker compose exec laravel.test php artisan key:generate
docker compose exec laravel.test php artisan migrate --seed
```

Check: `curl http://localhost:8080/api/v1/health` → `{"status":"ok",...}`.
⚠️ Docker Desktop muss **laufen** – „unable to get image / docker.sock no such
file" heißt: Docker ist aus/nicht bereit.

### 3. (Optional) Web im Browser testen

⚠️ Auch `frontend/.env` ist gitignored. Ohne sie ist `VITE_API_URL` leer → die
Web-Seite bleibt **weiß**. Darum:

```bash
cd ~/do-it/frontend
npm install
cp .env.example .env        # VITE_API_URL=http://localhost:8080/api/v1
npm run dev                 # http://localhost:5173
```

### 4. API-URL für die native App setzen

Native Builds nutzen Vite-Mode `capacitor` (`.env.capacitor` + `.env.capacitor.local`).

- **Simulator** erreicht den Mac unter `localhost`.
- **Echtes iPhone** braucht die **LAN-IP des Macs** (gleiches WLAN). IP:
  `ipconfig getifaddr en0`.
- `10.0.2.2` ist **nur** der Android-Emulator – auf iOS sinnlos.

```bash
cd ~/do-it/frontend
# Simulator:
printf 'VITE_API_URL=http://localhost:8080/api/v1\n' > .env.capacitor.local
# ODER echtes iPhone (IP einsetzen):
printf 'VITE_API_URL=http://192.168.0.246:8080/api/v1\n' > .env.capacitor.local
```

⚠️⚠️ **Die Variable MUSS exakt `VITE_API_URL` heißen (alles GROSS).** Vite liest
nur `VITE_`-Präfixe; ein Tippfehler wie `Vite_API_URL` wird **ignoriert** → es
greift die `.env.capacitor`-Standard-IP (`10.0.2.2`) → Login hängt ewig.
Verifizieren, welche IP wirklich im Build steckt:

```bash
npm run build:native
grep -ro "10.0.2.2\|192.168.0.246\|localhost" dist/assets | sort -u
```

### 5. iOS-Plattform anlegen + ATS

```bash
cd ~/do-it/frontend
npm run build:native
npx cap add ios               # erzeugt frontend/ios/ (+ pod install)
npx capacitor-assets generate --ios
```

⚠️ **Klartext-HTTP erlauben** (nur Dev) – iOS' App Transport Security blockt
sonst http. Per Terminal (sicher, ohne XML-Handarbeit):

```bash
/usr/libexec/PlistBuddy -c "Add :NSAppTransportSecurity dict" ios/App/App/Info.plist
/usr/libexec/PlistBuddy -c "Add :NSAppTransportSecurity:NSAllowsArbitraryLoads bool true" ios/App/App/Info.plist
```

(Die Origin `capacitor://localhost` ist in `config/cors.php` bereits erlaubt.
Der PWA-Service-Worker ist im capacitor-Build deaktiviert – sonst minutenlang
schwarzer Start, siehe `vite.config.ts`.)

### 6. Signieren & starten

```bash
npm run ios:open          # = build:native + cap sync ios + cap open ios
```

In Xcode:

1. Target **App** → **Signing & Capabilities** → „Automatically manage signing" →
   **Apple-ID-Team** wählen (Bundle-ID `app.nidula` ggf. eindeutig machen,
   z. B. `app.nidula.fia`).
2. **Echtes iPhone:** per USB anschließen, am iPhone „Diesem Computer vertrauen".
   ⚠️ **Entwicklermodus**: erscheint erst **nachdem** Xcode das Gerät einmal
   kontaktiert hat → dann am iPhone **Einstellungen → Datenschutz & Sicherheit →
   ganz unten → Entwicklermodus** an → Neustart. Erst danach taucht das iPhone in
   Xcodes Ziel-Liste auf (erstes Mal „Preparing device…", dauert ein paar Min).
3. **▶ Run**. Beim ersten Start am iPhone **Einstellungen → Allgemein → VPN &
   Geräteverwaltung** → Entwickler-Zertifikat **vertrauen**, dann App neu öffnen.

⚠️ **Nach jeder Frontend-/Env-Änderung:** `npm run cap:sync` **UND** in Xcode
nochmal **▶ Run** – `cap:sync` allein aktualisiert das Gerät **nicht**.

### Troubleshooting (alles real aufgetreten)

| Symptom | Ursache → Fix |
| --- | --- |
| Web-Seite **weiß**, Titel da | `frontend/.env` fehlt → `cp .env.example .env`, dev neu starten |
| App startet, **lange schwarz** | PWA-Service-Worker (in capacitor-Build via `vite.config.ts disable` aus); alte App vorm Re-Run löschen: `xcrun simctl uninstall booted app.nidula` (Installationen von vor dem Rebrand heißen noch `app.heimathafen`) |
| Login **hängt** ewig (Rädchen) | App ruft falschen Host (`10.0.2.2`/`localhost`) → `.env.capacitor.local` mit korrektem `VITE_API_URL` (GROSS!) + `cap:sync` + Xcode Run; im Web-Inspector die Request-URL prüfen |
| Login **sofort** fehlgeschlagen | Backend aus (Docker) oder falsche Daten; Health-Check + Container prüfen |
| `docker compose` „unable to get image / docker.sock" | Docker Desktop läuft nicht → starten, `docker info` muss ohne Fehler laufen |
| Build „unable to prepare context … vendor/…" | `vendor/` fehlt → Composer-Container (Schritt 2) |
| `xcrun simctl` „not found / invalid developer directory" | Xcode nach /Programme, `sudo xcode-select -s /Applications/Xcode.app/Contents/Developer` |
| iPhone **nicht** in Xcode-Liste | Kabel + „vertrauen" + **Entwicklermodus** (Schritt 6.2) |
| Simulator „failed to launch / No such process" | transienter Xcode-Glitch → Product → Clean Build Folder, `xcrun simctl shutdown all`, Run; ggf. anderes Gerät |

**Debugging-Werkzeuge:**

- **iOS WebView:** Safari → Einstellungen → Erweitert → „Funktionen für
  Webentwickler" an; am iPhone Einstellungen → Safari → Erweitert → Web-Inspector
  an. Dann Safari-Menü **Entwickler → [iPhone] → WebView** → Konsole/Netzwerk.
- **Android WebView:** `adb logcat` (adb unter
  `~/Library/Android/sdk/platform-tools` bzw. auf Windows
  `…/AppData/Local/Android/Sdk/platform-tools/adb.exe`), filtern auf `Capacitor/Console`.
