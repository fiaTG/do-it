# Aufgaben-Board

> **Kurzüberblick, immer aktuell.** Eine Zeile pro Aufgabe, Details stehen in
> [roadmap.md](roadmap.md) und den [ADRs](adr/). Wird in jeder Arbeitsrunde
> gepflegt (Daueranweisung) – Erledigtes wandert mit Datum nach unten.

## 🔴 Jetzt: Web-Release Stufe 1 (ADR-0025)

- [ ] Hetzner-Server anlegen (CX22, DE, Ubuntu 24.04, nur SSH-Key) – Timo, Anleitung siehe Session-Chat
- [ ] Domain festlegen + DNS auf den Server zeigen – Timo
- [ ] Deploy-Kit bauen: docker-compose.prod + Caddy (Auto-TLS, Security-Header) + Deploy-Runbook
- [ ] Server härten: ufw, SSH-Config, MFA im Hetzner-Konto, Hetzner-Firewall
- [ ] Hetzner Object Storage (privater Bucket) anlegen + anbinden
- [ ] Mail-Versand klären (SMTP-Anbieter) – bis dahin bleiben Invite-Mails Beta-intern
- [ ] Scheduler (cron `schedule:run`) fürs Papierkorb-Purge einrichten
- [ ] Erster Deploy + Smoke-Test, dann Familien-Beta

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
