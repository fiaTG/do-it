# ADR-0018: Cross-Platform-Design-Strategie & Austauschbarkeit der Marke

- **Status:** Akzeptiert
- **Datum:** 2026-06-18
- **Betrifft:** Frontend, Design, Branding, Multi-Client

## Kontext

Dieselbe React-Codebasis läuft als Web/PWA, iOS und Android (ADR-0012). Damit
stellt sich die Frage: **ein** Design für alle Plattformen oder ein **eigenes
Design je Betriebssystem** (Material auf Android, Apples HIG auf iOS)?

Zweitens war der **Markenname zunächst offen**: Der frühere Arbeitstitel
„Heimathafen" wurde wegen eines Markenkonflikts (`heimathafen.com`, Kreuzfahrten)
verworfen. **Entschieden ist „Nidula"** (lateinisch *kleines Nest*) – passt zum
Familien-/„Nest"-Thema und zum Logo (Nest, das ein Herz hält). Das Design bleibt
dennoch bewusst **vom Namen entkoppelt**, damit ein künftiger Re-Brand billig
bleibt.

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
   Tokens/Logo/Texte tauschen**, kein Code-Umbau – wie der Wechsel von „Heimathafen"
   zu **Nidula** gezeigt hat.

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

## Umsetzung (2026-06-18, markenneutral)

Die brand-unabhängigen Teile sind umgesetzt (`frontend/src/lib/native.ts`):
- **`data-platform="ios|android|web"`** am `<html>` (in `main.tsx` vor dem ersten
  Paint gesetzt) – Mechanismus für plattform-bewusste Token-Overrides steht bereit,
  wird aber bewusst noch nicht für abweichende Optik genutzt.
- **Statusleiste** (`@capacitor/status-bar`) folgt dem Theme (helle Symbole im
  Dark Mode, dunkle im Light Mode; Android zusätzlich Hintergrundfarbe) – behebt
  die Lesbarkeit der Uhr/Statusleiste.
- **Android-Hardware-Zurück** (`@capacitor/app`): eine Ebene zurück, auf der
  Wurzel App schließen.

**Rebrand auf „Nidula" umgesetzt (2026-06-18):** warm-erdige Palette (Wald-Grün,
Terrakotta, Sand, Creme) in `index.css` (Light+Dark), neues Logo (Nest mit Herz,
`public/icon.svg` + `scripts/generate-icons.mjs` → native Icons/Splash),
Umbenennung in UI, `index.html`, PWA-Manifest, `APP_NAME`, README. Der frühere
maritime „Heimathafen"-Look ist damit abgelöst.

## Offene Punkte

- ggf. iOS-Swipe-Back-Feinschliff; weitere plattform-bewusste Feinheiten bei Bedarf.
