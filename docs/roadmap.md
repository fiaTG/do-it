# Family Board – Modernisierungs-Fahrplan

> Stand: 2026-06-12 · Autor des Projekts: Timo Giese · erstellt mit Claude Code

Dieses Dokument ist der Gesamt-Fahrplan, um Family Board von der aktuellen
XAMPP/Vanilla-PHP-Lösung auf einen sauberen, wartbaren und modernen Stand zu
bringen: eine **Laravel-API** plus ein **eigenständiges React-SPA**, das als Web-App
live geht und perspektivisch als App auf **Mobile (iOS/Android)** und **Desktop
(Windows/macOS)** läuft. Die Grundsatzentscheidungen sind als **ADRs** unter
[`adr/`](adr/) dokumentiert.

---

## 1. Zielbild

| Aspekt            | Heute                                   | Ziel                                            |
|-------------------|-----------------------------------------|-------------------------------------------------|
| Architektur       | alles in einer PHP-Seite                 | **Backend-API + getrenntes Frontend-SPA**       |
| Backend           | Vanilla PHP, rohes PDO                    | **PHP 8.5 + Laravel 12** als JSON-API           |
| Frontend          | HTML in `echo`, Inline-CSS/JS           | **React + TypeScript** SPA (Vite, Tailwind)     |
| Clients           | nur Browser                              | **Web/PWA + Mobile (Capacitor) + Desktop (Tauri/PWA)** |
| Laufzeit          | XAMPP (Apache lokal)                     | **Docker** (Laravel Sail)                       |
| DB-Zugriff        | rohes PDO, SQL im View                   | Eloquent ORM + **Migrations**                   |
| Auth/Security     | selbstgebaut, lückenhaft                 | **Laravel Sanctum** (Cookie fürs SPA, Token für native) |
| Bilder            | BLOB in der DB                           | Dateisystem über `Storage`                      |
| Konfiguration     | hartkodiert im Code                      | `.env`                                          |
| Tests             | keine                                    | **Pest** (Backend) + Vitest (Frontend) + CI     |
| Build             | manuell, kompiliertes CSS im Git         | **Vite** (SPA), kein generiertes Asset im Git   |

**Strategie:** Greenfield-Neuaufbau als **zwei** saubere Projektteile
(Laravel-API + React-SPA). Das bestehende DB-Schema wird per Migration nachgebaut
und die Daten importiert. Die alte App bleibt lauffähig, bis das Neue Feature für
Feature gleichzieht. Siehe [ADR-0001](adr/0001-ziel-stack-laravel.md) und
[ADR-0003](adr/0003-vorgehen-greenfield-rewrite.md).

---

## 2. Ist-Zustand – konkrete Befunde

Diese Befunde stammen aus der Code-Durchsicht (Juni 2026) und begründen die ADRs.
Sie sind die Checkliste, was beim Neuaufbau gelöst sein muss.

### 2.1 Sicherheit (Login, Registrierung & allgemein)

| ID | Befund | Ort | Schwere |
| ----- | -------- | ----- | --------- |
| S1 | Echte Mailtrap-SMTP-Zugangsdaten hartkodiert und im Git eingecheckt | `private/dashboard/dashboard.php:135-136` | hoch |
| S2 | **Kein CSRF-Schutz** auf irgendeinem Formular | überall | hoch |
| S3 | Kein `session_regenerate_id()` nach Login → **Session-Fixation** | `private/auth/login.php` | hoch |
| S4 | Kein Brute-Force-Schutz / Rate-Limiting am Login | `private/auth/login.php` | hoch |
| S5 | `display_errors=1` + `E_ALL` → Information Disclosure | `private/auth/register.php:2-4` | mittel |
| S6 | Passwortänderung prüft **keine** Passwort-Stärke (Registrierung schon) | `private/dashboard/setup.php` | mittel |
| S7 | `invites.email` UNIQUE → E-Mail systemweit nur einmal einladbar; Tokens ohne Ablauf | DB-Schema / `dashboard.php` | mittel |
| S8 | Sicherheit hängt komplett an Apache-`.htaccess` (deny-all + Whitelist); greift unter nginx/Container nicht | `private/.htaccess` | mittel |
| S9 | Passwörter werden vor dem Hashen `trim()`t | `login.php:7`, `register.php:21` | niedrig |
| S10 | E-Mail-Enumeration bei Registrierung | `register.php:57` | niedrig |
| S11 | Session-Cookies ungehärtet; kein HTTPS-Zwang | global | niedrig |

### 2.2 Korrektheit / Bugs

| ID  | Befund | Ort |
|-----|--------|-----|
| B1  | `ON DUPLICATE KEY UPDATE menge` auf `itemName` ohne UNIQUE-Key → Mengenlogik greift nie | `private/apps/shoppingList.php:53-56` |
| B2  | `composer.lock` in `.gitignore` (Zeile 11) – sollte **eingecheckt** werden | `.gitignore` |
| B3  | Hartkodierte absolute URLs (`http://localhost/files/Do-IT/...`) in iframes, Redirects, DB-Spalte `app.appPfad` | `dashboard.php`, DB `app` |
| B4  | GET-Parameter `famID`/`userID` werden mitgeschleppt, aber serverseitig ignoriert | `dashboard/*.php` |
| B5  | Tippfehler „calender" statt „calendar" in Datei-/Spaltennamen | `apps/calender*.php` |

### 2.3 Wartbarkeit

- **M1** Keine Trennung von Logik & Darstellung (DB+Auth+Logik+HTML+CSS+JS pro Datei).
- **M2** Massive Duplizierung (Sidebar in jeder Seite, Erfolgs-Animation doppelt, DB-Bootstrap überall).
- **M3** Kein Router, kein Autoloading des eigenen Codes (URL = Dateipfad).
- **M4** Sprachmix Deutsch/Englisch, uneinheitliche/falsche Benennung.
- **M5** Keine Tests, manueller Build (kompiliertes CSS teils im Git).

---

## 3. Phasenplan

Jede Phase ist eigenständig abschließbar. Reihenfolge = empfohlene Bearbeitung.

### Phase 0 – Fundament & Container *(ADR-0001, 0002)*

- Neues **Laravel-API-Projekt** (inzwischen Laravel 12) + neues **React-SPA-Projekt** im Repo (Greenfield).
- Docker via Laravel Sail (PHP, MySQL, Mailpit). Kein XAMPP mehr.
- `.env`-Konfiguration, Secrets raus aus dem Code *(ADR-0007)*.
- **Ergebnis:** `sail up` startet die API, das SPA lädt und erreicht die API.

### Phase 1 – Datenmodell *(ADR-0005, 0006)*

- Schema als Eloquent-Migrations nachbauen, Befunde S7/B1 fixen.
- Modelle + Beziehungen; Bilder von BLOB auf `Storage` migrieren (Import-Skript).
- **Ergebnis:** Schema + Seed-Daten reproduzierbar per `migrate --seed`.

### Phase 2 – API-Fundament & Auth *(ADR-0011, 0004)*

- API-Konventionen festlegen: `/api/v1`, API Resources, Fehlerformat, CORS.
- **Sanctum-Auth**: Registrierung, Login, Logout, Passwort ändern, Einladung –
  mit CSRF (Web), Token (native), Rate-Limiting, Policies. Behebt S1–S11.
- **Ergebnis:** sichere, dokumentierte Auth-API + erste geschützte Endpunkte.

### Phase 3 – Features: API + SPA *(ADR-0008)*

- Pro App (Einkaufsliste, Kalender, ToDo, Galerie): API-Endpunkte **und**
  React-Views; geteiltes Layout mit *einer* Sidebar-Komponente (löst M1/M2).
- Dashboard + App-/Widget-Auswahl (`userapps`) über die API.
- **Ergebnis:** funktionsgleiche, aber sauber getrennte App.

### Phase 4 – Frontend-Politur & PWA *(ADR-0009, 0012)*

- Tailwind-Design, **Landing Scene neu gestalten** (Bilder/Layout).
- **PWA** aktivieren (installierbar auf Desktop & Mobile).
- **Ergebnis:** modernes, responsives, installierbares Web-Frontend, live-fähig.

### Phase 5 – Qualität & Auslieferung *(ADR-0010)*

- Pest- (Backend) + Vitest/Playwright- (Frontend) Tests; GitHub-Actions-CI.
- README auf Docker umstellen; alte XAMPP-Anleitung archivieren.
- Optional bei echtem Betrieb: HTTPS, Backups, echtes SMTP, Monitoring, DSGVO.

### Phase 6 – Native Pakete (optional, bei Bedarf) *(ADR-0012)*

- **Capacitor** für iOS/Android, **Tauri** für Windows/macOS – dasselbe SPA gewrappt.
- **Ergebnis:** Store-/Desktop-Apps aus einer Codebasis.

---

## 4. ADR-Index

| ADR | Titel | Status |
| ----- | ------- | -------- |
| [0001](adr/0001-ziel-stack-laravel.md) | Ziel-Stack: Laravel-API + React-SPA | Akzeptiert |
| [0002](adr/0002-containerisierung-docker-sail.md) | Containerisierung mit Docker (Laravel Sail) | Akzeptiert |
| [0003](adr/0003-vorgehen-greenfield-rewrite.md) | Vorgehen: Greenfield-Neuaufbau mit DB-Übernahme | Akzeptiert |
| [0004](adr/0004-auth-und-session-sicherheit.md) | Authentifizierung & Session-Sicherheit (Sanctum) | Akzeptiert |
| [0005](adr/0005-datenmodell-und-migrations.md) | Datenmodell, Eloquent & Migrations | Akzeptiert |
| [0006](adr/0006-bild-und-dateispeicherung.md) | Bild- & Dateispeicherung (Storage statt BLOB) | Akzeptiert |
| [0007](adr/0007-konfiguration-und-secrets.md) | Konfiguration & Secrets (.env) | Akzeptiert |
| [0008](adr/0008-projektstruktur-und-konventionen.md) | Projektstruktur & Konventionen | Akzeptiert |
| [0009](adr/0009-frontend-build-vite.md) | Frontend – SPA-Build mit Vite & PWA | Akzeptiert |
| [0010](adr/0010-tests-und-ci-cd.md) | Tests & CI/CD | Akzeptiert |
| [0011](adr/0011-api-design.md) | API-Design & Vertrag Backend/Frontend | Akzeptiert |
| [0012](adr/0012-multi-client-packaging.md) | Multi-Client-Strategie & Packaging | Akzeptiert (iOS + Android live) |
| [0013](adr/0013-monetarisierung-freemium.md) | Monetarisierung – werbefreies Freemium | Akzeptiert |
| [0014](adr/0014-hosting-infrastruktur.md) | Hosting – entkoppelte Compute-/Objektspeicher-Architektur | Akzeptiert |
| [0015](adr/0015-medien-prinzipien.md) | Medien-Prinzipien (Immutability, Privacy/EXIF, responsive) | Akzeptiert |
| [0016](adr/0016-skalierung-modularer-monolith.md) | Skalierung – modularer Monolith (kein verfrühtes Microservices/CQRS) | Akzeptiert |
| [0017](adr/0017-design-system-tokens.md) | Design-System mit Tokens & „Heimathafen"-Theming | Akzeptiert |
| [0018](adr/0018-cross-platform-design-und-brand.md) | Cross-Platform-Design-Strategie & Austauschbarkeit der Marke | Akzeptiert |
| [0019](adr/0019-familien-rollen-und-berechtigungen.md) | Familien-Rollen & Berechtigungen (Verwalter/Kind) | Akzeptiert |
| [0020](adr/0020-galerie-papierkorb.md) | Galerie-Papierkorb (Soft-Delete mit Aufbewahrungsfrist) | Akzeptiert |
| [0021](adr/0021-einladungs-rollen-und-owner-schutz.md) | Einladungs-Rollen & Owner-Schutz im Kalender | Akzeptiert |
| [0022](adr/0022-zahlungsabwicklung-und-premium-erlebnis.md) | Zahlungsabwicklung & Premium-Erlebnis (RevenueCat, Jahresabo) | Akzeptiert |
