# ADR-0029: Rechtstexte – Beschaffung & Einbindung

- **Status:** Akzeptiert (2026-07-22)
- **Datum:** 2026-07-22
- **Betrifft:** Recht/DSGVO, Premium (verknüpft ADR-0022), Native-Release, Betrieb
- **Ergänzt:** ADR-0025 (Stufenmodell), die Legal-Entwürfe unter `docs/legal/`

## Kontext

Für den rechtssicheren Betrieb braucht Nidula verpflichtende Rechtstexte
(Impressum, Datenschutzerklärung; bei echten Zahlungen zusätzlich AGB +
Widerrufsbelehrung). Ausgangspunkt war ein externer ADR-Entwurf, der gegen den
tatsächlichen Stand abgeglichen wurde. Zentrale Erkenntnis: Der Entwurf ist für
die **native App mit echten Zahlungen** geschrieben – Nidula ist aber
**Web-Beta mit simuliertem Kauf**. Das meiste ist damit „richtig, aber für eine
spätere Phase". Diese Phasen-Zuordnung ist der Kern dieses ADRs.

## Entscheidung

### Beschaffung

- **Professioneller Anbieter statt Gratis-Generator**, mit juristischer Prüfung
  vor dem Scharfschalten. Optionen: eRecht24 Premium oder IT-Recht Kanzlei
  (Abo, hält Texte aktuell + liefert AGB-/Widerruf-Generator) vs. einmalige
  Anwaltsprüfung. **Kosten-Entscheidung liegt bei Timo** (Solo-Betreiber mit
  Abo-Produkt → laufender Dienst spricht für sich, ist aber kein Technik-Thema).
- **Marktscope DE + AT, Schweiz ausgeschlossen** (spart Schweizer DSG). Unsere
  Entwürfe sind derzeit **rein DE** (zitieren § 5 DDG, § 18 MStV); für AT sind
  bei der Finalisierung Impressum-Spezifika (ECG/MedienG) zu ergänzen. AT ist
  ebenfalls DSGVO → die Datenschutzerklärung bleibt weitgehend gültig.

### Einbindung (server-gehostet, zentral änderbar)

- Rechtstexte sind **mobiloptimierte Seiten in der SPA** (`/impressum`,
  `/datenschutz`), auf dem Server gehostet → Änderungen sofort wirksam, ohne
  App-Update. Für die Web-App ist dieser Vorteil **bereits realisiert**.
- **Sichtbarkeit über `LEGAL_PUBLISHED`** (`frontend/src/lib/legal.ts`): solange
  `false`, sind die Seiten Entwurf (Banner) und **nicht verlinkt**. Timo füllt
  die Platzhalter in `ImpressumPage.tsx`/`DatenschutzPage.tsx`, lässt prüfen,
  setzt dann `true` → Footer- und In-App-Links erscheinen, Banner verschwindet.
- **Zwei-Klick-Regel:** Links sind in ≤ 2 Klicks erreichbar – im Landing-Footer
  und in-app unter Hilfe → „Rechtliches" (beide an `LEGAL_PUBLISHED` gekoppelt).

### Phasen-Zuordnung (was wann)

- **Jetzt (Web-Beta):** Impressum + Datenschutz finalisieren + prüfen lassen,
  dann `LEGAL_PUBLISHED=true`. AVV mit Hetzner abschließen (ADR-0025 Stufe 2).
- **Beim echten Zahlungsstart (verknüpft ADR-0022, Store-/Web-Payment-Release):**
  - **AGB + Widerrufsbelehrung für digitale Güter** (Verbraucher-Abo).
  - **Button-Lösung § 312j BGB:** der Kauf-Button muss rechtssicher heißen
    („Kostenpflichtig abonnieren"). Heute unkritisch, da simuliert („Premium
    aktivieren" + Hinweis „keine echte Zahlung"); bei Web/Stripe umbenennen,
    bei Store-IAP übernimmt Apple/Google die Kaufbestätigung.
- **Native-Release-Runde:** Datenschutz-URL in Play Console / App Store Connect
  hinterlegen. **Wichtig:** Damit Legal-Änderungen KEIN Store-Resubmission
  auslösen, muss der native Build die Texte **per Remote-URL** öffnen
  (Custom Tab / Systembrowser), nicht die im Build gebündelte SPA-Route rendern.

## Konsequenzen

- Haftungsrisiko sinkt (Abmahnschutz), Texte bleiben zentral pflegbar.
- Store-Review braucht valide Datenschutzerklärung → im Native-Release gesetzt.
- Laufende Kosten bei einem gepflegten Rechtstext-Dienst (Timos Abwägung).
- Web-App braucht Internet für die Anzeige (unkritisch, es IST eine Web-App);
  im Native-Build ggf. Caching bedenken.

## Bewusst NICHT jetzt

AGB/Widerruf/Button-Lösung (erst echte Zahlungen), Store-Console-Einträge und
das Remote-Laden der Legal-Seiten (erst Native-Runde), AT-Spezifika (bei der
Finalisierung), Konto-/Familien-Löschung als Feature (eigener Backlog-Punkt).
