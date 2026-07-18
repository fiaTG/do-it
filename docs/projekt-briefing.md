# Nidula – Projekt-Briefing (zum Einfügen in ChatGPT & Co.)

> Zweck: Kompakter Kontext, damit externe KI-Tools mitreden können, ohne das
> Repo zu kennen. Enthält bewusst KEINE Zugangsdaten, Schlüssel, Adressen
> oder Servernamen. Stand: 2026-07-18 – bei großen Änderungen aktualisieren.

## Was ist Nidula?

Nidula (lat. „kleines Nest") ist eine Familien-Organisations-App:
Einkaufsliste, Kalender (mit Serienterminen, iCal-Import UND -Export),
ToDos, Bildergalerie mit Papierkorb, Adressbuch, Fun Area mit
Familien-Highscores, Spritpreise, Wetter. Jedes Familienmitglied stellt
sein Dashboard modular zusammen. Zielmarkt DACH, UI komplett Deutsch.

Entstanden aus einem Abschlussprojekt (Umschulung Fachinformatiker
Anwendungsentwicklung), inzwischen komplett modernisiert. Ein-Personen-
Projekt von Timo, entwickelt gemeinsam mit Claude (Anthropic) als
Pair-Programmer; das Repo dient zugleich als Portfolio.

## Geschäftsmodell

Werbefreies Freemium: Kernfunktionen für immer gratis. Premium
(2,99 €/Monat oder 24,99 €/Jahr, gilt für die GANZE Familie) schaltet
Komfort frei: unbegrenzte Galerie (Free: 30 Bilder), Kalender-Abos (iCal),
Kalender-Freigabe aufs Handy, Spritpreise. Später Stores via RevenueCat.
Leitsatz: „Lust statt Zwang, aber spürbar" – ehrliche UX, keine Dark
Patterns, Kommendes wird klar als „Bald" markiert.

## Technik (Kurzfassung)

- **Backend:** Laravel 12 (PHP 8.5), reine JSON-API unter /api/v1, MySQL 8.4,
  Redis-Queue + Worker (Bildverarbeitung), Sanctum-Auth (Cookie fürs Web-SPA,
  Bearer-Token für native Apps), Pest-Tests (~170), Policies je Familie.
- **Frontend:** React 19 + TypeScript, Vite, Tailwind v4 mit semantischen
  Design-Tokens (Light/Dark), Zustand, FullCalendar, PWA; Capacitor 8 für
  iOS/Android (native Runde steht noch aus).
- **Rollen:** Verwalter (guardian) / Kind (child). Einladungen sind
  E-Mail-gebunden; Registrierung ist per Schalter auf invite-only.
- **Medien:** privater Speicher, Zugriff nur über kurzlebige signierte URLs;
  EXIF/GPS wird beim Upload entfernt (fail-closed).
- **Betrieb:** 1 Cloud-Server (EU/DE) mit Docker Compose: Caddy (Auto-TLS,
  Security-Header, CSP) → php-fpm + Worker + Scheduler; DB/Redis ohne
  öffentliche Ports; ufw + Cloud-Firewall + fail2ban; tägliche Backups mit
  bestandener Restore-Probe. Web-Beta ist live (geschlossen: Bauzaun per
  Basic Auth + invite-only-Registrierung).
- Alle Architektur-Entscheidungen stehen als ADRs im Repo (aktuell 25).

## Stand & Fahrplan

- **Jetzt:** geschlossene Familien-Beta (eigene Familie), Feedback-Runden.
- **Stufe 2:** weitere Familien → vorher DSGVO-Paket (Datenschutzerklärung,
  AVV, Löschkonzept), SMTP-Mailversand, externes Backup-Ziel.
- **Stufe 3:** öffentliche Registrierung → vorher externer Security-Test.
- **Backlog (Auswahl):** ToDo-Belohnungssystem, Angebote in der
  Einkaufsliste, Feed-Katalog für Kalender-Abos, weitere Spiele
  (Ballon-Knallerei), globaler Styling-Run, native Apps, RevenueCat.

## Wie mit diesem Briefing arbeiten

Wichtig bei Vorschlägen von außen: Nidula ist ein Ein-Personen-Projekt in
der Familien-Beta – Lösungen bitte an dieser Größe ausrichten (kein
Enterprise-Overkill), Premium nie über gesperrte Kern-Apps, ehrliche UX,
Familie = Mandant (alles family-scoped). Konkrete Code-/Architektur-
entscheidungen trifft das Projekt selbst anhand der ADRs.
