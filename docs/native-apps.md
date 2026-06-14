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
(`frontend/.env`) an. `localhost` zeigt auf dem Gerät auf sich selbst – darum:

| Ziel | `VITE_API_URL` |
|------|----------------|
| Android-Emulator → Host | `http://10.0.2.2:8080/api/v1` |
| Echtes Gerät im LAN | `http://<LAN-IP-des-PCs>:8080/api/v1` |
| Produktion | `https://api.<domain>/api/v1` |

Außerdem muss die API CORS/Sanctum für die native Origin erlauben bzw. den
Token-Pfad nutzen (Token-Auth braucht kein CORS-Cookie, aber die Domain muss
erreichbar sein). Für echte Geräte ggf. Klartext-HTTP in Android erlauben oder
HTTPS verwenden.

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

## iOS (später)

iOS braucht zwingend **macOS + Xcode**. Vorgehen analog:

```bash
npm install @capacitor/ios
npx cap add ios
npx cap open ios
```
