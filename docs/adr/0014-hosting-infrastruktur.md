# ADR-0014: Hosting-Infrastruktur – entkoppelte Compute-/Objektspeicher-Architektur

- **Status:** Akzeptiert
- **Datum:** 2026-06-12
- **Betrifft:** Betrieb, Skalierung, Datenschutz, Medien-Speicherung

## Kontext

Heimathafen ([ADR-0013](0013-monetarisierung-freemium.md)) ist eine
Multiuser-Plattform mit mehreren integrierten Apps – darunter das **Fotoalbum**,
das potenziell große Datenmengen erzeugt. Die Infrastruktur muss:

- mit den Nutzerzahlen **flexibel skalieren**,
- große Datenmengen (Bilder u. a.) **kostengünstig** speichern,
- **performante Bildverarbeitung** (Thumbnails) ermöglichen,
- strikte **deutsche Datenschutzvorgaben (DSGVO)** erfüllen.

**Entscheidungsrelevante Faktoren (Drivers):**

- **Skalierbarkeit:** unkompliziertes Wachstum von Compute und Speicher.
- **Kosteneffizienz:** geringe Einstiegskosten in Entwicklung/Test, kalkulierbare
  Kosten für Massenspeicher.
- **Datenschutz & Latenz:** Serverstandort zwingend in Deutschland, niedrige
  Ping-Zeiten für deutsche Nutzer.
- **Architektonische Entkopplung:** keine Serverabstürze durch volle Festplatten.

## Entscheidung

**Trennung von Anwendung und Daten (Separation of Concerns):** Anwendungslogik
(Backend-API) und statische Mediendateien (Bilder usw.) werden strikt getrennt
betrieben.

- **Compute:** virtueller Cloud-Server für API und Worker. Start mit einer
  **Hetzner Cloud CX22** (2 vCPU, 4 GB RAM, 40 GB NVMe-SSD).
- **Storage:** **Hetzner Object Storage** (S3-kompatibel) für das Fotoalbum →
  praktisch unbegrenzt skalierbarer Medienspeicher.
- **Caching/CDN:** vorgeschaltetes **Cloudflare-CDN** zur Entlastung des Speichers
  und für schnelle Bildladezeiten.
- **Bildverarbeitung:** Thumbnails/Komprimierung asynchron über eine
  **Hintergrund-Warteschlange (Redis + Worker)**.
- **Provider:** **Hetzner Online** (Standort Deutschland, DSGVO-konform),
  Alternative **IONOS**.

Diese Entscheidung baut direkt auf [ADR-0006](0006-bild-und-dateispeicherung.md)
auf: Bilder liegen bereits hinter Laravels **Storage-Abstraktion**, daher ist der
Umstieg von der lokalen `public`-Disk auf S3 weitgehend **reine Konfiguration**.
Compute/Worker passen zum Docker-Ansatz aus [ADR-0002](0002-containerisierung-docker-sail.md).

## Konsequenzen

**Positiv**
- **Unabhängige Skalierung:** Compute und Speicher wachsen getrennt; teure
  Server-Upgrades nur, wenn wirklich Rechenlast nötig ist.
- **Keine „Disk-full"-Abstürze:** Medien liegen außerhalb des App-Servers.
- **Günstiger Massenspeicher** mit kalkulierbaren Kosten.
- **DSGVO-konform** durch deutschen Standort; **schnelle Ladezeiten** via CDN.
- Dank Storage-Abstraktion (ADR-0006) geringer Migrationsaufwand vom Dev-Setup.

**Negativ / Kosten**
- **Erhöhter Entwicklungsaufwand:** Uploads müssen direkt an die S3-API gehen
  oder im Hintergrund dorthin verschoben werden (signierte URLs / Worker).
- **Zusätzliche Komplexität:** Compute + S3 + optionale Redis-Warteschlange sind
  mehr bewegliche Teile als ein simpler All-in-one-Server (mehr Monitoring/Betrieb).

## Alternativen

- **All-in-one-Server** (App + Medien auf einer Maschine) – am einfachsten, aber
  Risiko voller Festplatten, schlechte unabhängige Skalierung. Verworfen.
- **Hyperscaler (AWS/GCP/Azure)** – sehr mächtig/skalierbar, aber teurer, komplexer
  und mit DSGVO-/Souveränitätsfragen (US-Anbieter). Für ein deutsches Familien-
  Produkt verworfen; Hetzner deckt die Anforderungen günstiger und DE-lokal ab.
- **Mediendaten lokal auf dem VPS** (aktuelles Dev-Setup, ADR-0006 `public`-Disk)
  – für Entwicklung ausreichend, aber nicht für Produktions-Skalierung. Wird
  durch S3 abgelöst, sobald produktiv.
