# ADR-0004: Authentifizierung & Session-Sicherheit (Sanctum)

- **Status:** Akzeptiert
- **Datum:** 2026-06-12
- **Betrifft:** Auth, Sicherheit

## Kontext

Der heutige Auth-Kern ist selbst gebaut und hat mehrere konkrete Schwächen
(siehe Roadmap, Abschnitt 2.1): kein CSRF-Schutz (**S2**), keine
Session-Regeneration nach Login (**S3**, Session-Fixation), kein Rate-Limiting
(**S4**), aktive Debug-Ausgabe (**S5**), Passwortänderung ohne Stärke-Prüfung
(**S6**), kaputtes Einladungssystem (**S7**), getrimmte Passwörter (**S9**),
E-Mail-Enumeration (**S10**), ungehärtete Session-Cookies / kein HTTPS (**S11**).

Da Backend (API) und Frontend (SPA) getrennt sind (ADR-0001) und es **mehrere
Client-Typen** gibt – Web-SPA, Mobile, Desktop – braucht Auth zwei Modi:
Cookie-/Session-basiert für das Web-SPA (gleiche Top-Level-Domain) und
Token-basiert für native Apps.

Positiv heute: Prepared Statements und `password_hash()`/`password_verify()`
werden bereits genutzt – das Prinzip bleibt erhalten.

## Entscheidung

Authentifizierung wird mit **Laravel Sanctum** umgesetzt.

- **Web-SPA:** Sanctum **SPA-Authentifizierung** (Cookie/Session, `XSRF-TOKEN`).
  Damit gilt der **CSRF**-Schutz und **Session-Regeneration** des Frameworks → S2/S3.
- **Native Apps (Capacitor/Tauri):** Sanctum **Personal Access Tokens**
  (Bearer-Token), da Cookies dort nicht zuverlässig greifen.
- **Rate-Limiting** der Login-/Auth-Routen über Laravels `RateLimiter`
  (Throttle pro IP/E-Mail) → S4.
- **Hashing** zentral (`bcrypt`/`argon2`); Passwörter werden **nicht** getrimmt → S9.
- **Validierung** mit zentraler **Passwort-Regel** (`Password::min(8)...`),
  einheitlich für Registrierung **und** Passwortänderung → S6.
- **Generische** Auth-Fehlermeldungen; Registrierung vermeidet E-Mail-Enumeration → S10.
- **Debug aus** in Produktion via `APP_DEBUG=false` (nicht im Code) → S5.
- **Cookies/Transport:** `HttpOnly`, `SameSite`, `Secure` (Prod), HTTPS-Zwang → S11.
- **Einladungen** neu modelliert: kein UNIQUE auf `email`, stattdessen
  `token`-Index + `expires_at`/`accepted_at`; Tokens laufen ab und sind einmal
  verwendbar → S7.
- **Autorisierung** über **Policies/Gates** auf API-Ebene: ein Nutzer darf nur
  Ressourcen **seiner eigenen Familie** lesen/ändern (heute teils inkonsistent).

## Konsequenzen

**Positiv**

- Ein Auth-Mechanismus deckt Web *und* native Clients ab.
- Der Großteil der Sicherheitsbefunde ist mit gewarteten Framework-Mitteln gelöst.
- Einheitliche Passwort-Policy an genau einer Stelle; robuste Einladungen.

**Negativ / Kosten**

- Zwei Auth-Pfade (Cookie für Web, Token für native) erhöhen die Komplexität
  leicht und müssen beide getestet werden.
- Sanctum-SPA-Auth verlangt korrektes CORS-/Domain-/Cookie-Setup (Stolperfalle).

## Alternativen

- **Auth weiter selbst bauen** – die heutige Fehlerquelle; verworfen.
- **Laravel Breeze (Blade/Session)** – nur fürs Web sinnvoll; passt nicht zur
  API/SPA-Trennung und zu nativen Clients. Verworfen.
- **JWT (z. B. tymon/jwt-auth)** – verbreitet, aber Sanctum ist der
  Laravel-eigene, einfachere Standard für genau diesen Misch-Fall (SPA + Token).
  Verworfen zugunsten Sanctum.
