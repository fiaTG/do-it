# ADR-0017: Design-System mit Design-Tokens & „Heimathafen"-Theming

- **Status:** Akzeptiert (umgesetzt 2026-06-13)
- **Datum:** 2026-06-13
- **Betrifft:** Frontend, Design, Branding, Barrierefreiheit

## Kontext

Die App wird in **„Heimathafen"** umbenannt (Markenkonflikt mit *FamilyWall* u. a.,
vgl. [ADR-0013](0013-monetarisierung-freemium.md)). Damit verbunden ist eine klare
visuelle Identität: ruhig, modern, freundlich, zuverlässig – mit **dezent**
maritimem Thema (Hafen/Meer), nicht verspielt.

Heute nutzt das Frontend Tailwind v4 mit nur wenigen Marken-Farben im `@theme`
([ADR-0009](0009-frontend-build-vite.md)). Das reicht für ein konsistentes,
erweiterbares Design und einen späteren **Dark Mode** nicht aus.

## Entscheidung

Ein **semantisches Design-Token-System** als einzige Quelle der Wahrheit:

- **Tokens als CSS-Custom-Properties**, gruppiert: Farben, Typografie, Abstände,
  Border-Radius, Schatten, Linien + **Komponenten-Tokens** (Cards, Buttons, Forms,
  Navigation).
- **Zwei Ebenen:** Roh-Paletten (`--color-deep-ocean` …) → **semantische** Tokens
  (`--color-primary`, `--color-surface`, `--color-text` …). **Komponenten nutzen
  nur semantische Tokens, nie Rohfarben.**
- **Integration mit Tailwind v4:** Tokens im `@theme` registrieren, sodass sie
  Tailwind-Utilities speisen – kein paralleles System.
- **Marken-Palette „Heimathafen"** (maritim, dezent):
  - Hafen/Meer: `--color-harbor-navy #1F3347`, `--color-deep-ocean #274C63`,
    `--color-sea-blue #3E7C9B`, `--color-light-sea #D8EEF5`
  - Warm/Familie: `--color-sand #F3E7D3`, `--color-warm-sand #E6D1B3`,
    `--color-driftwood #A9825A`, `--color-harbor-wood #7A5A3A`
  - Akzente: `--color-coral #E58A72`, `--color-seafoam #8FCBB8`,
    `--color-lighthouse #F4C95D` (nur für Hinweise/aktive Navigation/Marker)
- **Dark Mode vorbereiten:** Umschaltung über `[data-theme="dark"]`, das nur die
  **semantischen** Tokens überschreibt (Komponenten bleiben unverändert).
- **Maritimes Thema dezent**: weiche Schatten, runde Formen, feine Linien; keine
  überladenen Schiff-/Anker-Motive.

## Konsequenzen

**Positiv**

- Konsistentes, hochwertiges Erscheinungsbild; ein zentraler Ort für Anpassungen.
- **Dark Mode** und Re-Theming werden trivial (nur semantische Tokens tauschen).
- Klare Markenidentität „Heimathafen"; gute Grundlage für Barrierefreiheit
  (Kontraste zentral steuerbar).

**Negativ / Kosten**

- Einmaliger Aufwand: Tokens definieren (`variables.css` bzw. `@theme`) und
  bestehende Komponenten von Ad-hoc-Klassen/Brand-Farben auf semantische Tokens
  umstellen.
- Disziplin nötig: keine Rohfarben/Magic-Numbers mehr in Komponenten.

## Migration (Skizze)

1. Tokens zentral definieren (ersetzt die kleinen `@theme`-Brand-Farben aus ADR-0009).
2. Semantische Utilities/Klassen einführen (`bg-surface`, `text-muted`, `rounded-card` …).
3. Komponenten schrittweise umstellen; parallel den **„Heimathafen"-Rename**
   sichtbar machen (Titel, Logo, Landing).
4. Dark-Mode-Override-Layer ergänzen + Umschalter.

## Umsetzung (2026-06-13)

- `frontend/src/index.css`: zwei Ebenen – Roh-Palette + semantische Tokens in
  `:root`, Dark-Mode-Override unter `[data-theme="dark"]`. Tokens via
  `@theme inline` als Tailwind-Utilities registriert (`bg-surface`, `text-muted`,
  `bg-primary`, `border-border`, `bg-sidebar` …), sodass Dark Mode live greift.
  `color-scheme` + FullCalendar-CSS-Variablen an die Tokens gebunden.
- Theme-Umschalter: `frontend/src/store/theme.ts` (Zustand, Persistenz in
  `localStorage` `heimathafen-theme`, System-Präferenz als Default) +
  `components/ThemeToggle.tsx`; No-Flash-Inline-Script in `index.html`.
- Komponenten/Seiten von Ad-hoc-Farben (`bg-brand`, `text-slate-*`, `bg-white`)
  auf semantische Utilities umgestellt.
- **„Heimathafen"-Rebrand** sichtbar: UI-Texte, `index.html`, PWA-Manifest
  (`vite.config.ts`), Backend `APP_NAME`/Einladungs-Mail, README.
- Verifiziert: `npm run lint`, 7 Vitest-Tests, `npm run build` grün.

## Alternativen

- **Weiter Ad-hoc-Tailwind-Farben** – schnell, aber inkonsistent, Dark Mode/
  Re-Theming mühsam, keine klare Marke. Verworfen.
- **Komponentenbibliothek (z. B. Material UI)** – viel fertiges UI, aber generischer
  Look und schwer an die maritime Marke anpassbar; widerspricht dem eigenen,
  ruhigen Stil. Verworfen zugunsten eigener Tokens auf Tailwind.
