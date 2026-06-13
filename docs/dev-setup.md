# Entwicklungs-Setup (neuer Stack)

> Gilt für den modernisierten Stack (Phase 0+). Die **alte** XAMPP-App bleibt
> davon unberührt und läuft weiter wie bisher. Hintergrund: [roadmap.md](roadmap.md).

Das Projekt besteht ab jetzt aus zwei Teilen:

| Ordner      | Inhalt                          | Läuft auf            |
|-------------|----------------------------------|----------------------|
| `backend/`  | Laravel-API (Docker/Sail, MySQL, Mailpit) | http://localhost:8080 |
| `frontend/` | React-SPA (Vite + TypeScript)    | http://localhost:5173 |

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
