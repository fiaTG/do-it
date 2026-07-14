# ADR-0002: Containerisierung mit Docker (Laravel Sail)

- **Status:** Akzeptiert
- **Datum:** 2026-06-12
- **Betrifft:** Laufzeitumgebung, Deployment

## Kontext

Heute läuft alles über XAMPP: Apache + MySQL werden manuell gestartet, die
Konfiguration (PHP-Version, Extensions, DB) ist an die lokale Maschine gebunden
und nicht reproduzierbar. Die App-Pfade sind sogar fest auf
`http://localhost/files/Do-IT/...` verdrahtet (B3). Das Setup ist nicht
portabel und für andere schwer aufzusetzen.

Vorgabe des Autors: künftig über Container statt XAMPP.

## Entscheidung

Die Entwicklungsumgebung wird über **Docker mit Laravel Sail** bereitgestellt.

- Services: **PHP-FPM (App)**, **MySQL**, **Mailpit** (lokaler Mail-Catcher,
  ersetzt Mailtrap für die Entwicklung).
- Start per `./vendor/bin/sail up`; kein XAMPP, kein manuelles Apache/MySQL mehr.
- Konfiguration ausschließlich über `.env` (ADR-0007), keine maschinen-
  spezifischen Pfade mehr im Code.
- Der Web-Einstieg ist Laravels `public/`-Verzeichnis – nicht mehr die
  Projektwurzel (behebt S8).

## Konsequenzen

**Positiv**

- Reproduzierbare, identische Umgebung auf jeder Maschine („läuft bei mir" → läuft überall).
- Saubere Trennung App/DB/Mail; einfaches Onboarding (ein Befehl).
- Direkter Pfad Richtung echtem Deployment (gleiches Image-Prinzip).
- Mailpit ersetzt die hartkodierten Mailtrap-Creds (S1) lokal komplett.

**Negativ / Kosten**

- Docker Desktop muss installiert sein; etwas Ressourcen-Overhead.
- Sail ist auf Entwicklung ausgelegt – für echte Produktion braucht es später
  ein eigenes, schlankeres Dockerfile/Compose (in Phase 5, falls Betrieb kommt).

## Alternativen

- **Bei XAMPP bleiben** – widerspricht der Vorgabe; bleibt nicht reproduzierbar.
- **Eigenes Dockerfile/Compose von Hand** – mehr Kontrolle, aber mehr Aufwand;
  Sail liefert für den Anfang dasselbe schneller. Ein Wechsel auf ein eigenes
  Setup für Produktion bleibt jederzeit möglich.
