# ADR-0018: Cross-Platform-Design-Strategie & Austauschbarkeit der Marke

- **Status:** Akzeptiert
- **Datum:** 2026-06-18
- **Betrifft:** Frontend, Design, Branding, Multi-Client

## Kontext

Dieselbe React-Codebasis läuft als Web/PWA, iOS und Android (ADR-0012). Damit
stellt sich die Frage: **ein** Design für alle Plattformen oder ein **eigenes
Design je Betriebssystem** (Material auf Android, Apples HIG auf iOS)?

Zweitens ist der Arbeitsname **„Heimathafen" provisorisch**: Es existiert bereits
`heimathafen.com` (Kreuzfahrten), die Marke ist also rechtlich **nicht gesichert**
und kann sich noch ändern. Das Design darf deshalb nicht hart an den konkreten
Namen/das Logo gekoppelt sein.

Basis ist das semantische Design-Token-System aus [ADR-0017](0017-design-system-tokens.md),
das Re-Theming (z. B. Dark Mode über `[data-theme="dark"]`) bereits trivial macht.

## Entscheidung

1. **Ein konsistentes Marken-Design über alle Plattformen** (Single Source: die
   Design-Tokens, ADR-0017). **Keine** zwei vollständig getrennten OS-Designs.
   Begründung: Es ist eine **eigene Marke**, kein system-konformes Utility –
   eine durchgängige Identität (wie bei Spotify/Airbnb) ist hier wertvoller als
   das Nachbauen der jeweiligen Plattform-Optik, und spart Pflege/Testfläche.

2. **Plattform-bewusste Feinheiten statt getrennter Designs.** Angepasst wird nur,
   wo Nutzererwartung/UX es verlangt:
   - Safe-Areas/Notch (bereits umgesetzt),
   - Statusleisten-Stil (iOS/Android),
   - Zurück-Navigation (Android-Hardware-Back vs. iOS-Swipe),
   - vereinzelt native-anmutende Detail-Komponenten.

3. **Mechanismus vorbereitet (sparsam nutzen):** Analog zum Dark Mode kann ein
   `data-platform="ios|android|web"` am Wurzelelement gesetzt werden
   (`Capacitor.getPlatform()`), das **nur semantische Tokens** überschreibt –
   Komponenten bleiben unverändert. Wird bewusst minimal eingesetzt.

4. **Marke austauschbar halten.** Name, Logo und Palette sind zentralisiert:
   Logo-SVG (`frontend/public/icon.svg` + `scripts/generate-icons.mjs`), semantische
   Farb-Tokens (`index.css`), `APP_NAME`, PWA-Manifest, UI-Texte. Ein **Rebrand =
   Tokens/Logo/Texte tauschen**, kein Code-Umbau. „Heimathafen" bleibt
   **Arbeitstitel**, bis Name/Marke geklärt sind.

## Konsequenzen

**Positiv**
- Konsistente Identität auf Web/iOS/Android; geringe Pflege, keine Design-Divergenz.
- Plattform-Anpassungen bleiben klein und gezielt.
- **Billiger Rebrand** möglich (wichtig wegen des offenen Namens).

**Negativ / Kosten**
- Fühlt sich nicht zu 100 % „nativ" je OS an – bewusst akzeptiert.
- Disziplin nötig, Plattform-Overrides wirklich minimal zu halten (sonst schleichend
  doch zwei Designs).

## Alternativen

- **Zwei vollständige OS-Designs** (Material vs. HIG) – doppelte Pflege/Tests,
  Marken-Verwässerung. Verworfen.
- **Reines System-/Framework-UI je Plattform** (z. B. Ionic-Komponenten im
  iOS/Android-Mode) – verliert die eigene Marke, koppelt an Framework-Optik.
  Verworfen zugunsten eigener Tokens (ADR-0017).

## Offene Punkte

- **Markenname + Logo final klären** (rechtlich; Konflikt mit `heimathafen.com`).
  Bis dahin „Heimathafen" als Platzhalter.
- Plattform-Feinheiten konkret umsetzen: Statusleisten-Plugin
  (`@capacitor/status-bar`), Android-Back-Handling, ggf. iOS-Swipe-Back.
