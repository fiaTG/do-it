# ADR-0001: Ziel-Stack ist Laravel (API) + eigenständiges SPA

- **Status:** Akzeptiert
- **Datum:** 2026-06-12
- **Betrifft:** Gesamtarchitektur

## Kontext

Family Board ist heute reines Vanilla-PHP: jede Seite mischt DB-Zugriff,
Auth-Prüfung, Geschäftslogik und HTML/CSS/JS (Befunde M1–M3). Sicherheits-
Basics wie CSRF, Session-Härtung und Rate-Limiting sind selbst gebaut und
lückenhaft (S2–S4).

Zusätzliche Zielvorgabe (geklärt am 2026-06-12): Die Anwendung soll **live als
Web-App** gehen **und** perspektivisch **als App auf Mobile (iOS/Android) und
Desktop (Windows/macOS)** verfügbar sein. Das verlangt eine **Trennung von
Backend und Client**: ein Server-gerendertes Blade-Frontend wäre im Kern
Web-only und damit ein Sackgassen-Ansatz für native Apps.

Der Autor kennt PHP bereits – ein Wechsel der *Backend*-Sprache (z. B. Python)
brächte für das App-Ziel nichts, da Backend-Sprachen nie im Client laufen.

## Entscheidung

**Backend:** **PHP 8.3 + Laravel 11 als reine JSON-API.**

- **Eloquent ORM** + Migrations statt rohem PDO und SQL-Dump (ADR-0005).
- **API Resources** + Form-Request-Validierung; das Backend rendert **keine**
  HTML-Seiten mehr.
- **Laravel Sanctum** für Authentifizierung (ADR-0004): Cookie-Auth fürs Web-SPA,
  Token-Auth für native Clients.
- Eingebaute **CSRF**-Middleware, **Hashing**, **Validierung**, **Rate-Limiting**.

**Frontend:** ein **eigenständiges Single-Page-App (SPA)** als alleinige
Client-Quelle für alle Plattformen.

- Framework: **React + TypeScript** (entschieden 2026-06-12 – wegen Job-Markt,
  größtem Ökosystem und dem React-Native-Pfad für spätere echte native Mobile-Apps).
- Build mit **Vite**, als **PWA** (ADR-0009).
- Multi-Client-Auslieferung (Web/PWA, Capacitor für Mobile, Tauri für Desktop)
  aus *einer* Codebasis (ADR-0012).
- API-Vertrag zwischen Backend und Frontend in ADR-0011.

## Konsequenzen

**Positiv**

- Eine Backend-API bedient Web, Mobile und Desktop gleichermaßen → das App-Ziel
  ist ohne späteren Architekturbruch erreichbar.
- Sicherheits-Grundlagen (CSRF, Hashing, Rate-Limit) kommen aus dem Framework →
  S2–S6 weitgehend erschlagen.
- Klare Trennung Backend/Frontend → behebt M1–M3; jede Seite testbar.
- PHP-Wissen bleibt nutzbar; modernes JS/TS-Frontend als Lern-/Portfolio-Gewinn.

**Negativ / Kosten**

- **Zwei Projekte** (API + SPA) statt einem → mehr Setup, ein API-Vertrag muss
  gepflegt werden. Das ist der bewusst akzeptierte Preis für Multi-Plattform.
- Zwei Ökosysteme (Composer + npm), zwei Lernkurven (Laravel + React).
- Mehr „Magie"/Abstraktion als beim heutigen Mini-Setup.

## Alternativen

- **Laravel + Blade (server-rendered)** – am einfachsten, aber Web-only; native
  Apps nur über Umwege nachrüstbar. Widerspricht dem App-Ziel → verworfen.
- **Laravel + Inertia (server-gekoppeltes SPA)** – ein Repo, gutes App-Feeling,
  PWA-fähig; guter Mittelweg, aber weniger sauber entkoppelt für echte native
  Clients. Verworfen zugunsten der klaren API/SPA-Trennung.
- **Vue statt React** – sanftere Lernkurve, reicht für Web + Capacitor, aber
  kleinerer Job-Markt und schwächerer Pfad zu echten nativen Mobile-Apps.
  Verworfen zugunsten React (siehe Frontend-Entscheidung).
- **Vanilla PHP / Slim / Symfony** – kein „Batteries included"; Auth/Security
  müssten selbst gebaut werden (heutige Hauptschwäche). Verworfen.
- **Wechsel der Backend-Sprache auf Python/Node** – ohne fachlichen Mehrwert für
  das App-Ziel; verwirft vorhandenes PHP-Wissen. Verworfen.
