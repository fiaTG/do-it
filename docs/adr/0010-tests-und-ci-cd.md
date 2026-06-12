# ADR-0010: Tests & CI/CD

- **Status:** Vorgeschlagen
- **Datum:** 2026-06-12
- **Betrifft:** Qualitätssicherung, Auslieferung

## Kontext

Das Projekt hat heute **keine automatisierten Tests** (M5) und keine
CI-Pipeline. Jede Änderung wird nur manuell im Browser geprüft. Für ein
Portfolio-/Lernprojekt sind Tests + grüne CI zugleich Qualitätsnetz **und**
Aushängeschild; für einen späteren echten Betrieb sind sie Pflicht.

## Entscheidung

- **Backend-Tests: Pest** (auf PHPUnit aufbauend, Laravel-Standard).
- **Frontend-Tests: Vitest** (Unit/Komponenten) + optional **Playwright** für
  End-to-End-Flows durch das SPA.
- **Mindestabdeckung** beim Neuaufbau (Backend, gegen die API):
  - Feature-Tests für die kritischen Auth-Flows (Registrierung, Login, Logout,
    Passwort ändern, Einladung annehmen) – das sind die heutigen Schwachstellen.
  - Feature-Tests pro App-Kernfunktion (Item hinzufügen/löschen, Event anlegen,
    Todo abhaken, Bild hochladen).
  - Autorisierungs-Tests: ein Nutzer kommt **nicht** an Ressourcen fremder
    Familien.
- **CI über GitHub Actions:** bei jedem Push/PR laufen für das **Backend** `pint`
  + Pest + `composer validate`, für das **Frontend** ESLint + Vitest + Build.
  Merge nur bei grüner Pipeline.
- **Setup-Doku:** README auf Docker/Sail umstellen; alte XAMPP-Anleitung als
  historisch markieren/archivieren.
- **Optional bei echtem Betrieb (Phase 5):** Deploy-Job, HTTPS, DB-Backups,
  Fehler-/Uptime-Monitoring, echtes SMTP, DSGVO-Betrachtung der Bild-/Personendaten.

## Konsequenzen

**Positiv**
- Regressionen werden automatisch gefangen; Refactoring wird gefahrlos.
- Grüne CI als sichtbares Qualitätssignal im Portfolio.
- Die heutigen Sicherheitslücken bekommen Tests, die ihr Wiederauftreten verhindern.

**Negativ / Kosten**
- Tests schreiben kostet zusätzliche Zeit pro Feature.
- CI-Konfiguration muss gepflegt werden.

## Alternativen

- **Keine/erst später Tests** – spart kurzfristig Zeit, verschenkt aber genau
  den Vorteil, der den Neuaufbau absichert; verworfen.
- **Nur Unit-Tests** – zu wenig für eine web-lastige App; der Wert liegt hier in
  Feature-/HTTP-Tests, daher Fokus darauf.
