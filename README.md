# Nidula

Eine Web-App, mit der eine Familie ihren Alltag gemeinsam organisiert:
Einkaufsliste, Kalender (mit Serienterminen, iCal-Abos für Schule/Verein/
Abfallkalender und iCal-Freigabe in den Handy-Kalender), ToDos, Bildergalerie
mit Papierkorb, Adressbuch, Fun Area mit Familien-Highscores und Spritpreisen
(Premium) – modular auswählbar pro Nutzer, dazu das Zuhause-Wetter auf dem
Dashboard.
(„Nidula" = lat. *kleines Nest*; zuvor Arbeitstitel „Heimathafen", davor „Family
Board". Hintergrund: [`docs/adr/0018-cross-platform-design-und-brand.md`](docs/adr/0018-cross-platform-design-und-brand.md).)

> Ursprünglich ein Vanilla-PHP/XAMPP-Schulprojekt, inzwischen modernisiert zu
> einer **Laravel-API + React-SPA in Docker**. Hintergrund, Entscheidungen und
> Fahrplan: [`docs/roadmap.md`](docs/roadmap.md) und die ADRs unter
> [`docs/adr/`](docs/adr/).

![CI](https://github.com/fiaTG/do-it/actions/workflows/ci.yml/badge.svg?branch=main)

## Architektur

| Teil | Stack | Läuft auf |
|------|-------|-----------|
| `backend/` | PHP 8.5 · **Laravel 12** (JSON-API, Sanctum) · MySQL | <http://localhost:8080> |
| `frontend/` | **React 19 + TypeScript** (Vite, Tailwind, PWA, Capacitor) | <http://localhost:5173> |

Auth läuft über Sanctum (Cookie fürs Web-SPA, Token für spätere native Apps).
Die Ports liegen bewusst neben einer evtl. laufenden XAMPP-Installation.

## Schnellstart

Voraussetzungen: **Docker Desktop** und **Node.js**.

```bash
# 1) Backend (API + MySQL + Mailpit) via Docker
cd backend
cp .env.example .env
php artisan key:generate          # bzw. im Container
docker compose up -d --build
docker compose exec laravel.test php artisan migrate --seed

# 2) Frontend (React-SPA)
cd ../frontend
npm install
npm run dev
```

Dann **<http://localhost:5173>** öffnen. Demo-Login:

- **E-Mail:** `dozent@example.com`
- **Passwort:** `test123!`

Weitere Dienste: **Mailpit** (abgefangene E-Mails) unter <http://localhost:8025>.
Ausführliche Anleitung: [`docs/dev-setup.md`](docs/dev-setup.md).

## Tests

```bash
# Backend (Pest) – im Container
cd backend && docker compose exec laravel.test php artisan test

# Frontend (Vitest)
cd frontend && npm test
```

Bei jedem Push/PR laufen Backend- und Frontend-Checks automatisch über
GitHub Actions ([`.github/workflows/ci.yml`](.github/workflows/ci.yml)).

## Dokumentation

- [`docs/roadmap.md`](docs/roadmap.md) – Modernisierungs-Fahrplan & Befunde
- [`docs/adr/`](docs/adr/) – Architecture Decision Records
