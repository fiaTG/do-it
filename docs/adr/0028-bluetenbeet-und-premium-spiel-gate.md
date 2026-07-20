# ADR-0028: „Nidulas Blütenbeet" & serverseitiges Premium-Gate für Spiele

- **Status:** Akzeptiert (umgesetzt 2026-07-20)
- **Datum:** 2026-07-20
- **Betrifft:** Fun Area, Premium (ADR-0013), Spiele-Rechtelage

## Kontext

Als drittes Fun-Area-Spiel war „Block-Garten" (ein „Stapel-Puzzle") angeteasert.
Ein gestapeltes Klötzchen-Puzzle liegt aber gefährlich nah an **Tetris** –
dem aggressivsten Klage-Fall im Casual-Gaming (Tetris Holding gewinnt
Trade-Dress-/Look-and-Feel-Verfahren, im Fall *v. Xio* trotz eigener Grafik).
Das kollidiert mit unserer Fun-Area-Linie (eigene Themen, keine geschützten
Mechaniken/Optiken; vgl. Moorhuhn/Snake).

Der Entwurf durchlief die übliche Runde mit einem externen Sparringpartner
(ChatGPT). Zwischenschritte und ihre Ablehnung:

- **Tetris-artig (fallende Blöcke, Linien-Clear):** verworfen (Klage-Risiko).
- **2048-Reskin als Verschmelz-Garten:** verworfen. Zwei Gründe: (a) rechtlich
  *weniger* heikel als Tetris, aber „MIT-lizenziert" heißt nur, dass der *Code*
  frei ist – **kein** Schutz gegen Marken-/Design-Rechte, und 2048 ist selbst
  ein Ableger. (b) Produktseitig sofort als „2048 mit Pflanzen" erkennbar – für
  das Flaggschiff-Premium-Spiel zu wenig Eigenständigkeit.

## Entscheidung

### „Nidulas Blütenbeet" – ein eigenes, ruhiges Strategie-Puzzle

Rundenbasiertes 5×5-Garten-Puzzle (Genre: Nachbarschafts-Wertung / cozy
tile-placement, board-game-üblich und nicht auf ein geschütztes Werk kopiert):

- 20 Züge. Pro Zug **drei verschiedene** Pflanzen angeboten (seedbares
  „Auslassungs"-Prinzip: pro Zug fehlt genau eine der vier Arten; MVP ohne
  künstliche Seltenheit), eine wählen und aufs Beet setzen.
- Vier Arten mit einfachen 4-Wege-Nachbarschaftsregeln: **Blume** mag Vielfalt,
  **Kraut** mag Gesellschaft, **Strauch** mag den Rand, **Baum** braucht Abstand
  (Baumwert bei 0 gedeckelt – eine Pflanze wird nie negativ, der Zug-Delta
  schon, weil bestehende Bäume verlieren können).
- **Dynamische Gesamtwertung mit ehrlichem Delta:** der Punktestand ist immer
  aus dem *ganzen* Beet gerechnet (nie gecacht); die Vorschau eines Zugs ist der
  echte Zugewinn inkl. Wirkung auf Nachbarn. So bleibt es transparent UND
  strategisch (ein Feld, gut für die Blume jetzt, fehlt später dem Baum).

### Technik: reine Engine, test-first, Balance-geprüft

- **Spiellogik in `frontend/src/lib/bluetenbeet.ts`** – reine, immutable
  TypeScript-Engine, komplett von React getrennt. Seedbarer PRNG →
  reproduzierbar. `previewPlacement(state, offerIndex, cell)` kennt die gewählte
  Pflanze; Gesamtwert stets aus dem Brett.
- **UI = DOM-Grid aus echten Buttons** (kein Canvas): barrierearm (Emoji,
  Beschriftung und Form statt nur Farbe; Tastatur 1–3), `useReducer`,
  Animationen nur via `motion-safe` (respektiert `prefers-reduced-motion`).
- **Vitest** deckt jede Regel, ungültige Züge, Spielende, Determinismus und
  State-Immutabilität ab. Zusätzlich ein **Balance-Regressionstest**
  (Strategien × feste Seeds): aufmerksames Spiel schlägt Zufall deutlich
  (~2×), „immer Strauch" bleibt klar darunter → keine triviale Dominanz.
  1-Ply-Lookahead lokal geprüft (≈ greedy). Startzahlen konfigurierbar, nach
  dem Balance-Test justiert.

### Serverseitiges Premium-Gate für Premium-Spiele

Bisher lag die Premium-Sperre für Spiele **nur in der Oberfläche**. Für ein
kostenpflichtiges Spiel prüft der `GameScoreController` jetzt auch serverseitig:
`PREMIUM_GAMES = ['ballons', 'bluetenbeet']` → Bestenliste *und* Score-Speichern
liefern ohne aktives Familien-Abo **403**. Die Prüfung nutzt dieselbe zentrale
`Family::isPremium()`-Logik wie die `premium`-Middleware – **keine zweite
Definition**. Die Fun Area selbst und „Hungrige Raupe" bleiben frei (Timos
Linie: Fun Area gratis, neue Spiele Premium).

Ehrliche Abgrenzung: Das schützt die **Premium-Bestenliste**, nicht den lokal
ausgelieferten Spielcode. Manipulierbare Highscores bleiben auf Familien-Skala
akzeptiertes Restrisiko (serverseitiges Nachrechnen ganzer Spielverläufe wäre
überzogen). Nebeneffekt: Fuel/iCal nutzen die `premium`-Middleware schon
korrekt – Spiele ziehen konsistent nach.

## Konsequenzen

- Ein eigenständiges Denkspiel ergänzt Survival (Raupe) und Reflex (Ballon) um
  Strategie; rechtlich deutlich unauffälliger als ein Tetris-/2048-Reskin.
- Die getrennte, deterministische Engine macht Balancing per Simulation und
  gründliche Tests möglich – Blaupause für künftige Spiele.
- Vor Release: finaler Name in DPMAregister/TMview prüfen („Blütenbeet" ist
  generisch → geringes Risiko).
- **Vertagt:** „Tägliches Beet" (gemeinsamer Tages-Seed) – nicht gratis, da
  Datum/Zeitzone/Regelversion/Tages-Bestenliste sauber definiert werden müssten.
