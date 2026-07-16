# Entwicklungs-Setup

> Hintergrund zum Neuaufbau: [roadmap.md](roadmap.md). Die alte XAMPP-App wurde
> inzwischen vollständig aus dem Repo entfernt.

Das Projekt besteht aus zwei Teilen:

| Ordner      | Inhalt                          | Läuft auf            |
|-------------|----------------------------------|----------------------|
| `backend/`  | Laravel-API (Docker/Sail, MySQL, Mailpit) | <http://localhost:8080> |
| `frontend/` | React-SPA (Vite + TypeScript)    | <http://localhost:5173> |

Die Ports sind bewusst neben XAMPP gelegt (XAMPP belegt 80 und 3306), damit beides
parallel laufen kann.

## Voraussetzungen

- Docker Desktop (läuft)
- Node.js + npm (fürs Frontend)

## Backend starten (Laravel-API)

```bash
cd backend
cp .env.example .env          # beim ersten Mal
php artisan key:generate      # falls APP_KEY leer ist
docker compose up -d --build  # Container bauen & starten (erster Build dauert)
docker compose exec laravel.test php artisan migrate
```

Health-Check: <http://localhost:8080/api/v1/health> → JSON `{"status":"ok",...}`

Weitere Dienste:

- **Mailpit** (abgefangene E-Mails): <http://localhost:8025>
- **MySQL**: Host `127.0.0.1`, Port **3307**, DB `familyboard`, User `sail`, PW `password`
- **MinIO** (S3-kompatibler Medienspeicher, ADR-0014): API `:9000`, Web-Konsole
  <http://localhost:9001> (User `sail` / PW `password`), Bucket `media` (**privat** –
  Zugriff nur über signierte Proxy-URLs der API, ADR-0015)
- **Redis** + **worker** (`queue:work`): asynchrone Bildverarbeitung (Thumbnails)

> Speicher-/Queue-Modus steht in `.env`: `MEDIA_DISK=s3` + `QUEUE_CONNECTION=redis`
> nutzt MinIO + Worker (Produktions-nah). Einfacher: `MEDIA_DISK=public` +
> `QUEUE_CONNECTION=sync` (kein MinIO/Redis nötig, Thumbnails inline).

Stoppen: `docker compose down` (Daten bleiben in den Volumes erhalten).

> Hinweis: Statt `docker compose ...` geht unter WSL/Git Bash auch das Sail-Wrapper-
> Skript `./vendor/bin/sail up` bzw. unter Windows `vendor\bin\sail.bat up`.

## Frontend starten (React-SPA)

```bash
cd frontend
npm install        # beim ersten Mal
npm run dev        # Dev-Server auf http://localhost:5173
```

Die API-Basis-URL steht in `frontend/.env` (`VITE_API_URL`). Beim Laden zeigt die
Startseite einen Konnektivitäts-Check gegen `/health` – grün = API erreichbar.

## Build (Frontend, Produktion)

```bash
cd frontend
npm run build      # erzeugt dist/
```

## Stolpersteine (real aufgetreten)

- **Neue Laravel-Routen greifen erst nach Container-Neustart:** Der laufende
  Serverprozess hält den Routen-Stand im Speicher. `php artisan route:list`
  zeigt die neue Route, der Server antwortet trotzdem 405 – bis
  `docker compose restart laravel.test` lief.
- **Geänderte Queue-Jobs brauchen einen Worker-Neustart:** Der `worker`-Container
  führt sonst weiter den alten Job-Code aus (Job „läuft durch", neue Felder
  fehlen aber). Lösung: `docker compose restart worker`.
- In beiden Fällen sind die **Pest-Tests nicht betroffen** (eigener Prozess) –
  grüne Tests bei gleichzeitig falschem Laufzeitverhalten sind das typische
  Erkennungszeichen.
- **Scheduler:** Der Galerie-Papierkorb (ADR-0020) räumt abgelaufene Bilder über
  `model:prune` auf (täglich via `routes/console.php` eingeplant). Lokal läuft
  kein Scheduler – bei Bedarf manuell `docker compose exec laravel.test php
  artisan model:prune`. In Produktion braucht es cron (`schedule:run`) oder
  einen `schedule:work`-Prozess.
- **Vitest ohne `.env`:** Tests laden `frontend/.env.test` (eingecheckt) –
  eine lokale `.env` ist dafür nicht nötig.
- **Spritpreise (Premium):** braucht `TANKERKOENIG_API_KEY` in `backend/.env`
  (kostenlos registrieren: <https://creativecommons.tankerkoenig.de>). Der
  Demo-Key liefert nur Fake-Preise. Kein Key gehört jemals ins Repo! Nach
  `.env`-Änderung: `docker compose restart laravel.test`.
