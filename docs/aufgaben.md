# Aufgaben-Board

> **Kurzüberblick, immer aktuell.** Eine Zeile pro Aufgabe, Details stehen in
> [roadmap.md](roadmap.md) und den [ADRs](adr/). Wird in jeder Arbeitsrunde
> gepflegt (Daueranweisung) – Erledigtes wandert mit Datum nach unten.

## 🔴 Jetzt: Web-Release Stufe 1 (ADR-0025)

- [ ] Domain festlegen + DNS auf 167.233.64.98 zeigen – Timo
- [x] 2026-07-18 MFA im Hetzner-Konto aktiv (Authenticator-App)
- [x] 2026-07-18 Hetzner-Cloud-Firewall aktiv (22/80/443 + ICMP; von außen verifiziert: 3306/6379/8080 dicht)
- [ ] Hetzner Object Storage (privater Bucket) anlegen + anbinden – bis dahin Fotos im Server-Volume (privat via signierte URLs)
- [ ] Mail-Versand klären (SMTP-Anbieter) – bis dahin MAIL_MAILER=log, Invites per Link
- [x] 2026-07-17 Registrierungs-Schalter: `NIDULA_REGISTRATION=invite` sperrt Registrierung ohne Einladung (Timos Beta-Bedingung)
- [x] 2026-07-17 Server nidula-prod (CX23, Falkenstein) gehärtet: nur SSH-Key, kein Root-Login, ufw, fail2ban, Auto-Updates
- [x] 2026-07-17 Deploy-Kit + Runbook (deploy/, docs/deploy-hetzner.md); Scheduler-Container ersetzt cron
- [x] 2026-07-17 **ERSTER DEPLOY LIVE**: <https://167-233-64-98.sslip.io> (HTTPS, Security-Header, Bauzaun, Smoke-Test grün)
- [x] 2026-07-17 Registrierung nach Timos Bootstrap auf `invite` gestellt (live 403 verifiziert)
- [x] 2026-07-17 Beta-Bugfixes: App-Katalog geseedet (CatalogSeeder + in Deploy-Pipeline), Bauzaun nur vor Web-UI (kein Doppel-Dialog), Orts-UI als „ändern", Ortssuche count=10, Einladungs-Link kopierbar

## 🟡 Demnächst: Stufe 2 (fremde Familien)

- [ ] Backups extern: DB-Dump zusätzlich verschlüsselt nach außerhalb des Servers kopieren (3-2-1 komplett)
- [~] DSGVO-Basispaket: **Entwürfe fertig** in docs/legal/ (Datenschutzerklärung, Impressum, Löschkonzept) – OFFEN für Timo: Platzhalter füllen (Name/Adresse/E-Mail), juristisch prüfen (lassen), AVV bei Hetzner abschließen, DSFA-Erfordernis prüfen
- [x] 2026-07-22 **Datenexport** (DSGVO Art. 15/20): `/me/export` (JSON, nutzerbezogen) + Button „Meine Daten" im Profil, +4 Tests
- [x] 2026-07-22 **Legal-Seiten** gebaut: `/impressum` + `/datenschutz` (öffentlich, Entwurfs-Banner) – Footer-Links via `LEGAL_PUBLISHED`-Schalter noch AUS bis Platzhalter gefüllt
- [ ] Konto-/Familien-Löschung als Feature bauen (gemäß docs/legal/loeschkonzept.md; Produktentscheidung „Fotos beim Austritt behalten?" darin markiert) – Timo schaut später drauf
- [ ] Timo: Platzhalter in ImpressumPage/DatenschutzPage füllen + juristisch prüfen, dann `LEGAL_PUBLISHED=true` (schaltet Footer- + In-App-Links frei, Zwei-Klick-Regel; ADR-0029). Für AT ggf. Impressum-Spezifika (ECG/MedienG)
- [ ] Timo: Rechtstext-Quelle entscheiden (eRecht24 Premium / IT-Recht Kanzlei vs. einmalige Anwaltsprüfung; ADR-0029)
- [ ] **Monitoring WIRKSAM schalten (Timo):** Wächter-Dienst-Konto + Uptime-Monitor auf `/api/v1/health/ready` + Backup-Heartbeat-URL in `deploy/.env` + Test-Alarm auslösen (ADR-0027, Runbook)
- [ ] Platten-Alarm (Log-Rotation erkennt keine volle Platte durch DB/Bilder/Backups → nächste Monitoring-Aufgabe, ADR-0027 Stufe 2)
- [x] 2026-07-20 Monitoring Stufe-1-**Code** (ADR-0027): Readiness-Endpoint (DB+Redis, 503), Backup-Heartbeat (Totmann-Schalter), Docker-Log-Rotation, Incident-Runbook
- [x] 2026-07-18 DB-Dump täglich 03:30 (systemd-Timer, 14 Stände) + **Restore-Probe bestanden** (23/23 Tabellen)

## 🟢 Später (Backlog)

- [ ] Blütenbeet verständlicher machen (Timos Beta-Feedback 2026-07-20: Balance stimmt, aber Einstieg unklar) – Optionen: „So geht's"-Overlay beim 1. Start, dauerhaft sichtbare Regel-Legende, klare Zielzeile + Warum-Erklärung beim Anvisieren

- [ ] Belohnungs-Regal (Nest-Blätter-Ausbau, Premium): Verwalter legen echte Familien-Belohnungen mit Blatt-Preisen an, Kinder lösen ein (ADR-0026 Stufe 2)
- [ ] Einkaufsliste: eigene Läden je Familie anlegen
- [ ] Einkaufsliste: Angebote der Märkte – erst Rechte-/API-Recherche (Prospektdaten lizenzpflichtig!)
- [ ] Support-Formular in der App + E-Mail-Support (ab Stufe 2, braucht SMTP)
- [ ] Payment-Release-Rechtliches (ADR-0029 ⇄ ADR-0022): AGB + Widerrufsbelehrung für digitale Güter, Button-Lösung „Kostenpflichtig abonnieren" — erst wenn echtes Geld fließt
- [ ] Native-Release: Legal-Seiten per Remote-URL öffnen (kein Store-Resubmit bei Textänderung) + Datenschutz-URL in Play/App-Store-Console (ADR-0029)
- [ ] Feed-Katalog/Abo-Assistent (Feiertage per Klick, Abfallkalender-Suche)
- [ ] Globaler Styling-Run über alle Seiten
- [ ] Native Runde: Keychain/Keystore für Tokens, PDF-Download nativ, iOS-Swipe-Back
- [ ] RevenueCat + Store-Release (ADR-0022), danach Webhook
- [ ] Externer Pentest vor öffentlicher Registrierung (Stufe 3, ADR-0025)
- [ ] Offene Produktentscheidung: Fun Area komplett Premium? (Timo, zurückgestellt 2026-07-17)

## ✅ Erledigt (Auszug)

- [x] 2026-07-20 **Nidulas Blütenbeet** (ADR-0028): drittes Fun-Area-Spiel, eigenes Strategie-Puzzle (kein Tetris-/2048-Reskin), reine test-first-Engine + Balance-geprüft, barrierearmes DOM-Grid; **serverseitiges Premium-Gate** für Premium-Spiele (via zentraler `Family::isPremium()`)
- [x] 2026-07-18 **Ballon-Knallerei** live: erstes Premium-Spiel (60-s-Runden, goldene Laterne, Wespe), eigene Familien-Bestenliste, Fun Area bleibt frei

- [x] 2026-07-18 **„Nest-Blätter" MVP (ADR-0026)**: 1 🍃 je erledigtem ToDo (Ledger überlebt Löschen), Wochen-Champion auf der ToDo-Seite, Meilenstein-Abzeichen im Profil, Erlediger-Avatar in der Liste
- [x] 2026-07-18 Screenshot-Fixes: Kalender-Kopf mobil (kompakter Titel, Marken-Buttons, weniger Ansichten) + Galerie-Balken (verlorene onLoad-Events bei Cache-Bildern)

- [x] 2026-07-18 Beta-Feedback Runde 1 (Sofort-Teil): zeitabhängige Begrüßung, Owner-Auswahl deutlich markiert, Kalender mobil (Liste als Standard, Touch schneller, „+ x weitere"), Raupe ohne Steuerkreuz + größer, 17 Läden, Hilfe-Seite /help, ChatGPT-Briefing (docs/projekt-briefing.md) – Details roadmap.md Abschnitt 7

- [x] 2026-07-17 Sicherheitsbasis Code: Rate Limits (Register/Invites/Uploads), CORS prod-sicher, guzzle-Update, `.env.production.example` (ADR-0025)
- [x] 2026-07-17 Kalender-Freigabe: Familienkalender als iCal-Abo fürs Handy (ADR-0024)
- [x] 2026-07-17 Kalender-Abos: externe iCal-Kalender importieren/abonnieren (ADR-0023)
- [x] 2026-07-17 Branch-Konsolidierung: nur noch `main`, Alt-Stand als Tag `legacy-schulprojekt`
- [x] 2026-07-17 PremiumPage-Redesign; echter Tankerkönig-Key live
- [x] 2026-07-16 Spritpreise (Premium), Fun Area mit „Hungrige Raupe", wiederkehrende Termine, Security-Härtung (externes Review)
- [x] 2026-07-15 Adressbuch, Wetter, Einladungs-Rollen + Owner-Schutz (ADR-0021), Zahlungsarchitektur (ADR-0022)
- [x] 2026-07-14 Galerie-Überholung inkl. Papierkorb (ADR-0020), Alt-App entfernt, CI grün
