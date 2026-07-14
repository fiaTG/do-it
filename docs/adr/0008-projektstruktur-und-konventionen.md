# ADR-0008: Projektstruktur & Konventionen

- **Status:** Akzeptiert
- **Datum:** 2026-06-12
- **Betrifft:** Code-Organisation, Lesbarkeit

## Kontext

Die heutige Struktur ist die Hauptursache für schlechte Wartbarkeit: jede `.php`
mischt DB, Auth, Logik, HTML, Inline-CSS und Inline-JS (**M1**); die
Sidebar-Navigation und die Erfolgs-Animation sind über viele Dateien dupliziert
(**M2**); es gibt keinen Router/kein Autoloading des eigenen Codes (**M3**);
Bezeichner mischen Deutsch/Englisch, „calender" ist falsch geschrieben (**M4**).

Mit der Architekturentscheidung aus ADR-0001 (Laravel-**API** + getrenntes
**React-SPA**) gibt es ab jetzt **zwei** klar getrennte Projektteile mit je eigenen
Konventionen.

## Entscheidung

### Backend (Laravel-API)

- **Schichtung:** `routes/api.php` → Controller (dünn) → Form-Request
  (Validierung) → Service/Action (Geschäftslogik) → Eloquent-Modelle →
  **API Resource** (JSON-Ausgabe). Keine HTML-Ausgabe, kein Blade fürs App-UI.
- **Autorisierung** über **Policies** pro Modell (ADR-0004).
- **PSR-12**, durchgesetzt mit **Laravel Pint**.

### Frontend (React-SPA)

- Eigenes Verzeichnis/Repo (z. B. `frontend/`), **React + TypeScript + Vite**.
- **React Router** (Seiten), **Zustand** (oder Redux Toolkit) für State
  (u. a. Auth), **TanStack Query** für Server-State/Caching, zentraler
  **API-Client** (axios/fetch-Wrapper) statt verstreuter `fetch`-Aufrufe mit
  hartkodierten URLs (behebt B3/M2 im Frontend).
- Komponenten-Struktur: wiederverwendbare Bausteine (z. B. *eine*
  Sidebar-Komponente, *eine* Flash-Komponente) → behebt M1/M2 endgültig.
- Linting/Format über **ESLint + Prettier**.

### Projektweit

- **Sprache der Bezeichner: Englisch** (Klassen, Methoden, DB-Spalten, Routen,
  Komponenten). Nutzersichtbare UI-Texte bleiben **Deutsch** → beendet M4.
- **Benennung:** Models `PascalCase` Singular (`User`, `ShoppingItem`), Tabellen
  `snake_case` Plural, API-Routen `kebab-case`, React-Komponenten `PascalCase`.
  „calender" → „calendar".
- Die vier Apps (ShoppingList, Calendar, Todo, Gallery) je als eigener
  API-Controller **und** eigener Frontend-Bereich, statt loser Skripte in
  `private/apps/`.

## Konsequenzen

**Positiv**

- Backend und Frontend sind unabhängig entwickel- und testbar.
- Duplizierung verschwindet (eine Sidebar/Flash-Komponente; ein API-Client).
- Einheitlicher, automatisch geprüfter Stil (Pint + ESLint in CI, ADR-0010).

**Negativ / Kosten**

- Zwei Konventions-Sets, zwei Toolchains (Composer/Pint + npm/ESLint).
- Mehr Dateien/Indirektion – dafür jede Datei klein und fokussiert.
- Englische Bezeichner sind gewöhnungsbedürftig, wenn man bisher deutsch benannt
  hat.

## Alternativen

- **Deutsch als Code-Sprache** – vertraut, aber unüblich und erschwert
  internationale Lesbarkeit; verworfen (Englisch im Code, Deutsch in der UI).
- **Frei strukturieren statt Framework-Konventionen** – verschenkt den
  Hauptvorteil (jeder findet sich sofort zurecht); verworfen.
