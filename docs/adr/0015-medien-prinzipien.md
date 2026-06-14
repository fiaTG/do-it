# ADR-0015: Medien-Prinzipien – Unveränderlichkeit, Privatsphäre & responsive Verarbeitung

- **Status:** Akzeptiert (umgesetzt 2026-06-14)
- **Datum:** 2026-06-13
- **Betrifft:** Medien (Galerie/Avatare), Datenschutz, Sicherheit, Performance

> **Umsetzung:** EXIF/GPS-Strip (`App\Support\ImageUpload`), private Medien +
> signierter Proxy (`MediaController`, signierte Routen) und **responsive
> WebP-Varianten** (`App\Support\ImageVariants`, async in `GenerateThumbnail`,
> `media.variant`-Proxy, `srcset` in `ImageResource` + Galerie-Lightbox) sind
> vollständig umgesetzt und getestet.

## Kontext

Heimathafen ist medienlastig (Familienfotos). [ADR-0006](0006-bild-und-dateispeicherung.md)
legt Dateien ins Storage (Pfad in DB), [ADR-0014](0014-hosting-infrastruktur.md)
trennt Compute/Objektspeicher und lagert Thumbnails async aus (lokal via MinIO +
Redis-Worker bereits umgesetzt). Es fehlen aber noch **Grundsätze für den
korrekten und sicheren Umgang mit Nutzer-Fotos** – insbesondere Datenschutz.

Akut relevant: Der aktuelle (lokale) MinIO-Bucket ist **public-read** – praktisch
für die Entwicklung, aber für **private Familienfotos** ungeeignet.

## Entscheidung

1. **Unveränderlichkeit (Immutability):** Hochgeladene Originale werden nie
   überschrieben oder in-place verändert. Bearbeitungen (Zuschnitt, Filter) und
   Thumbnails sind **eigene Derivat-Dateien**. (Das Thumbnail-Derivat existiert
   bereits.)

2. **Privacy-by-Design – EXIF/Geo strippen:** Beim Upload werden sensible
   Metadaten (v. a. **GPS/EXIF**) serverseitig entfernt, bevor das Bild für andere
   sichtbar ist. Verhindert ungewolltes Teilen von Standortdaten.

3. **Resource-Level-Schutz / private Medien:** Familienfotos sind **privat**.
   Zugriff nur für Mitglieder der Familie – geprüft auf Ressourcen-Ebene (nicht
   nur „eingeloggt"). In Produktion: **privater Bucket + signierte (temporäre)
   URLs** oder ein auth-geschützter Medien-Proxy statt public-read. (Dev nutzt
   weiterhin public für Einfachheit.)

4. **Responsive / On-Demand-Varianten:** Mehrere Auflösungen (mind. Thumbnail +
   Vorschau) async erzeugen; **nie das Original** in Listen/Übersichten laden.
   Auslieferung über ein **CDN** (ADR-0014). Optional später ein Image-Proxy
   (z. B. imgproxy) für „just-in-time"-Größen.

5. **Stateless App-Layer:** App-Server speichern keine Medien/Sessions lokal –
   jedes Bild geht sofort in den Objektspeicher (bereits via S3-Disk; Sessions in
   DB/Redis). Ermöglicht horizontale Skalierung.

## Konsequenzen

**Positiv**
- Echter Schutz der Privatsphäre (kein Standort-Leak, keine offenen Foto-URLs).
- Skalierbar und performant (CDN, responsive Größen, stateless).
- Originale bleiben unangetastet (Datensicherheit, Wiederherstellbarkeit).

**Negativ / Kosten**
- Mehr Implementierung: EXIF-Strip-Bibliothek, signierte URLs / Proxy, mehrere
  Größen pro Bild (mehr Storage, mehr Worker-Last).
- Private URLs sind nicht ewig cachebar (Signatur-Ablauf) → CDN-Strategie nötig.

## Umsetzungsstand & offene Punkte

- ✅ Async-Thumbnail (Worker), S3-Disk, stateless (Sessions in DB).
- ⬜ **EXIF/GPS-Stripping beim Upload** (neu).
- ⬜ **Private Buckets + signierte URLs** statt public-read (Prod).
- ⬜ Weitere responsive Größen + CDN-Anbindung.

## Alternativen

- **Fotos öffentlich lassen** – einfach, aber inakzeptabel für private Familien-
  bilder. Verworfen.
- **Alles synchron/just-in-time skalieren** – ohne async/CDN bei vielen Bildern
  langsam und teuer. Verworfen zugunsten async + CDN.
