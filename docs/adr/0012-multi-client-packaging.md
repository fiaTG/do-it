# ADR-0012: Multi-Client-Strategie & Packaging (Web/PWA, Mobile, Desktop)

- **Status:** Vorgeschlagen
- **Datum:** 2026-06-12
- **Betrifft:** Auslieferung, Plattformen

## Kontext

Family Board soll **live als Web-App** gehen und perspektivisch **als App auf
Mobile (iOS/Android) und Desktop (Windows/macOS)** verfügbar sein. Die zentrale
Frage ist, wie viele Frontend-Codebasen dafür nötig sind. Mehrere native
UIs (z. B. React Native für Mobile + separates Desktop-UI) würden bedeuten,
dieselben Features mehrfach zu bauen und zu pflegen.

ADR-0001 legt ein **React-SPA** als Frontend fest, ADR-0009 macht es zur **PWA**.

## Entscheidung

**Eine** React-SPA-Codebasis ist die alleinige Quelle für alle Plattformen; die
Ziel-Pakete werden daraus abgeleitet:

| Plattform               | Weg                                  |
|-------------------------|--------------------------------------|
| Web (live)              | das SPA selbst                       |
| Installierbar (Basis)   | **PWA** (Manifest + Service Worker)  |
| Mobile iOS + Android    | **Capacitor** (wrappt das SPA in native Store-Apps) |
| Desktop Windows + macOS | **Tauri** (echtes Binary) oder PWA-Installation |

- **PWA zuerst** – deckt „installierbar / fühlt sich wie eine App an" auf allen
  Plattformen sofort und am günstigsten ab.
- **Capacitor/Tauri später** – nur falls echte Store-Präsenz oder tiefere
  Geräteintegration (Push, Kamera, Dateisystem) gebraucht wird. Da dasselbe SPA
  gewrappt wird, bleibt es **eine** Codebasis.
- Alle Clients sprechen über die versionierte API (ADR-0011) mit dem Backend.

## Konsequenzen

**Positiv**
- **Ein** Frontend für Web + Mobile + Desktop → minimaler Pflegeaufwand,
  konsistentes Verhalten überall.
- Stufenweiser Ausbau: erst Web/PWA live, native Pakete bei Bedarf nachziehen –
  ohne Rewrite.

**Negativ / Kosten**
- Gewrappte Web-Apps fühlen sich nicht 100 % „nativ" an wie echtes
  Swift/Kotlin/React-Native; für dieses Tool akzeptabel.
- Store-Veröffentlichung (Apple/Google) bringt eigene Hürden (Accounts,
  Review, Zertifikate) – erst relevant, wenn Mobile-Apps konkret werden.
- iOS-Builds (Capacitor) benötigen macOS/Xcode.

## Alternativen

- **React Native / Flutter für Mobile** – „nativer", aber **zweite** UI-Codebasis
  neben dem Web-SPA; widerspricht „einmal bauen, überall". Vorerst verworfen.
  *Hinweis:* Da das Frontend in **React** gebaut wird (ADR-0001), bleibt
  **React Native** eine realistische spätere Option für echte native Mobile-Apps
  – der Skill ist derselbe. Capacitor bleibt aber der Default, solange eine
  Codebasis genügt.
- **Nur Web, keine App-Pakete** – am einfachsten, verfehlt aber das erklärte
  Ziel. Verworfen (PWA ist der günstige Kompromiss).
