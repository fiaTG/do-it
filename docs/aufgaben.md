# Aufgaben-Board

> **Kurzüberblick, immer aktuell.** Eine Zeile pro Aufgabe, Details stehen in
> [roadmap.md](roadmap.md) und den [ADRs](adr/). Wird in jeder Arbeitsrunde
> gepflegt (Daueranweisung) – Erledigtes wandert mit Datum nach unten.

## 🔴 Jetzt: Web-Release Stufe 1 (ADR-0025)

- [ ] **Timo: auf <https://167-233-64-98.sslip.io> registrieren (hinter Bauzaun), dann Registrierung auf `invite` stellen** – Bootstrap, siehe deploy-hetzner.md
- [ ] Domain festlegen + DNS auf 167.233.64.98 zeigen – Timo
- [ ] MFA im Hetzner-Konto aktivieren – Timo
- [ ] Hetzner-Firewall (Cloud-Ebene) zusätzlich zur ufw anlegen (22/80/443)
- [ ] Hetzner Object Storage (privater Bucket) anlegen + anbinden – bis dahin Fotos im Server-Volume (privat via signierte URLs)
- [ ] Mail-Versand klären (SMTP-Anbieter) – bis dahin MAIL_MAILER=log, Invites per Link
- [x] 2026-07-17 Registrierungs-Schalter: `NIDULA_REGISTRATION=invite` sperrt Registrierung ohne Einladung (Timos Beta-Bedingung)
- [x] 2026-07-17 Server nidula-prod (CX23, Falkenstein) gehärtet: nur SSH-Key, kein Root-Login, ufw, fail2ban, Auto-Updates
- [x] 2026-07-17 Deploy-Kit + Runbook (deploy/, docs/deploy-hetzner.md); Scheduler-Container ersetzt cron
- [x] 2026-07-17 **ERSTER DEPLOY LIVE**: <https://167-233-64-98.sslip.io> (HTTPS, Security-Header, Bauzaun, Smoke-Test grün)

## 🟡 Demnächst: Stufe 2 (fremde Familien)

- [ ] Backups: verschlüsselter DB-Dump nach extern + **Restore einmal komplett proben**
- [ ] DSGVO-Basispaket: Datenschutzerklärung, Impressum, AVV Hetzner, Löschkonzept, DSFA-Prüfung dokumentieren
- [ ] Monitoring/Uptime-Alarm (einfach starten, z. B. Healthcheck-Ping)

## 🟢 Später (Backlog)

- [ ] Ballon-Knallerei (erstes Premium-Spiel) + Block-Garten
- [ ] Feed-Katalog/Abo-Assistent (Feiertage per Klick, Abfallkalender-Suche)
- [ ] Globaler Styling-Run über alle Seiten
- [ ] Native Runde: Keychain/Keystore für Tokens, PDF-Download nativ, iOS-Swipe-Back
- [ ] RevenueCat + Store-Release (ADR-0022), danach Webhook
- [ ] Externer Pentest vor öffentlicher Registrierung (Stufe 3, ADR-0025)
- [ ] Offene Produktentscheidung: Fun Area komplett Premium? (Timo, zurückgestellt 2026-07-17)

## ✅ Erledigt (Auszug)

- [x] 2026-07-17 Sicherheitsbasis Code: Rate Limits (Register/Invites/Uploads), CORS prod-sicher, guzzle-Update, `.env.production.example` (ADR-0025)
- [x] 2026-07-17 Kalender-Freigabe: Familienkalender als iCal-Abo fürs Handy (ADR-0024)
- [x] 2026-07-17 Kalender-Abos: externe iCal-Kalender importieren/abonnieren (ADR-0023)
- [x] 2026-07-17 Branch-Konsolidierung: nur noch `main`, Alt-Stand als Tag `legacy-schulprojekt`
- [x] 2026-07-17 PremiumPage-Redesign; echter Tankerkönig-Key live
- [x] 2026-07-16 Spritpreise (Premium), Fun Area mit „Hungrige Raupe", wiederkehrende Termine, Security-Härtung (externes Review)
- [x] 2026-07-15 Adressbuch, Wetter, Einladungs-Rollen + Owner-Schutz (ADR-0021), Zahlungsarchitektur (ADR-0022)
- [x] 2026-07-14 Galerie-Überholung inkl. Papierkorb (ADR-0020), Alt-App entfernt, CI grün
