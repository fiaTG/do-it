# ADR-0009: Frontend – SPA-Build mit Vite & PWA

- **Status:** Vorgeschlagen
- **Datum:** 2026-06-12
- **Betrifft:** Frontend, Build-Prozess, Design

## Kontext

Heute wird SCSS manuell zu CSS kompiliert, kompiliertes CSS ist teils im Git
eingecheckt, JS liegt als lose Dateien mit hartkodierten Fetch-URLs vor; es gibt
keinen definierten Build, kein Cache-Busting, keine Minifizierung.

Mit ADR-0001 wird das Frontend ein eigenständiges **React-SPA**, das laut
ADR-0012 zugleich die Basis für Mobile (Capacitor) und Desktop (Tauri/PWA) ist.
Außerdem ist die heutige **Landing Scene gestalterisch überholt** (die Bilder
passen nicht in die Dreiecks-/Triangle-Form, das Layout wirkt veraltet) und soll
neu gebaut werden.

## Entscheidung

- **Build mit Vite** (React-Standard): TypeScript, Hot-Reload, Minifizierung,
  Cache-Busting (gehashte Assets).
- **PWA** über `vite-plugin-pwa` (Manifest + Service Worker) → installierbar auf
  Desktop und Mobile, Grundlage für ADR-0012.
- **Styling: Tailwind CSS** (Default) statt des bestehenden SCSS. Tailwind ist
  der aktuelle State-of-the-art-Ansatz, passt nahtlos zu Vite/React und vermeidet
  die heutige CSS-Duplizierung. *(Alternative s. u.: SCSS migrieren.)*
- **Kompilierte Assets gehören nicht ins Git** (`dist/` wird ignoriert); gebaut
  wird lokal bzw. in CI.
- **Landing Scene Neugestaltung** als erste sichtbare SPA-Aufgabe: modernes,
  responsives Layout, Bildbehandlung (Aspect-Ratio/`object-fit`, definierte
  Bildbereiche) statt erzwungener Formen. Wird als **eigenes Design-Thema** nach
  dem Architektur-Setup angegangen.

## Konsequenzen

**Positiv**
- Reproduzierbarer Build, Cache-Busting, Minifizierung ohne Handarbeit.
- Kein generiertes CSS mehr im Git → keine Merge-Konflikte.
- Mit Tailwind konsistentes, schnell iterierbares Design; PWA ebnet den Weg zu
  den nativen Clients.

**Negativ / Kosten**
- Node/npm als Build-Abhängigkeit (im Container abgedeckt).
- Tailwind hat eine eigene Lernkurve; das bestehende Design muss neu umgesetzt
  (nicht 1:1 portiert) werden.

## Alternativen

- **Bestehendes SCSS nach `resources/` migrieren statt Tailwind** – erhält das
  vorhandene Design 1:1 und spart die Tailwind-Kurve, ist aber weniger
  „state of the art" und behält den Hang zur CSS-Duplizierung. **Fallback, falls
  der Design-Neubau zu groß wird.**
- **Weiter manuell SCSS kompilieren** – kein Cache-Busting, generierte Dateien
  im Git; verworfen.
- **CSS-in-JS / andere Frameworks** – für dieses Projekt unnötiger Overhead.
