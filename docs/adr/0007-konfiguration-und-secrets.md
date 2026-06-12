# ADR-0007: Konfiguration & Secrets (.env)

- **Status:** Vorgeschlagen
- **Datum:** 2026-06-12
- **Betrifft:** Konfiguration, Sicherheit

## Kontext

Konfiguration ist heute hartkodiert verstreut:

- **S1:** echte Mailtrap-SMTP-Zugangsdaten stehen im Klartext in
  `dashboard.php` und sind ins Git eingecheckt.
- DB-Zugang in `config/db.php` (immerhin via `.gitignore` ausgenommen, mit
  `db.example.php` als Vorlage – das ist gut und bleibt im Prinzip erhalten).
- **B3:** Basis-URLs (`http://localhost/files/Do-IT/...`) hartkodiert im Code.
- **B2:** `composer.lock` ist fälschlich in `.gitignore` – reproduzierbare
  Builds sind so nicht garantiert.

## Entscheidung

Alle umgebungsabhängigen Werte und Secrets laufen über **`.env`** (Laravel-Standard).

- `.env` ist **nie** im Git; eine gepflegte **`.env.example`** dokumentiert alle
  benötigten Schlüssel (DB, Mail, `APP_URL`, `APP_KEY`, `APP_DEBUG`).
- Mail-Zugangsdaten kommen aus `.env`; lokal zeigt Mail auf **Mailpit**
  (ADR-0002), nicht auf hartkodiertes Mailtrap → S1 gelöst.
- Basis-URLs über `APP_URL` / benannte Routen statt Localhost-Literale → B3.
- **`.gitignore` korrigieren:** `composer.lock` **einchecken** (B2);
  Secrets/`.env` weiter ignorieren.
- Die alten, exponierten Mailtrap-Credentials gelten als kompromittiert und
  werden **rotiert** (neu generiert), nicht nur entfernt.

## Konsequenzen

**Positiv**
- Keine Secrets mehr im Code/Git; pro Umgebung eigene Konfiguration.
- Reproduzierbare Builds durch eingecheckte `composer.lock`.
- Portabel über Maschinen/Container hinweg.

**Negativ / Kosten**
- Jeder, der das Projekt aufsetzt, muss `.env` aus `.env.example` befüllen
  (in der README zu dokumentieren).

## Alternativen

- **Bei hartkodierter PHP-Config bleiben** – widerspricht Sicherheits- und
  Portabilitätszielen; verworfen.
- **Secret-Manager (Vault o. ä.)** – sinnvoll erst bei echtem, größerem Betrieb;
  für jetzt Overkill. `.env` ist der pragmatische Standard.
