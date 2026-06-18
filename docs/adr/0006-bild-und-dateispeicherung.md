# ADR-0006: Bild- & Dateispeicherung (Storage statt BLOB)

- **Status:** Akzeptiert
- **Datum:** 2026-06-12
- **Betrifft:** Datei-Uploads, Performance

## Kontext

Profilbilder (`user.profilbild` als `longblob`) und Galeriebilder
(`bilder.bild` als `mediumblob`) werden heute **als Binärdaten direkt in der
Datenbank** gespeichert und beim Anzeigen Base64-kodiert ins HTML eingebettet
(z. B. `family_members.php`, `profile.php`). Folgen:

- DB wird unnötig groß und langsam; jedes Listing lädt komplette Bilddaten.
- Kein Browser-Caching, kein CDN, keine einfache Größen-/Format-Optimierung.
- Backups der DB werden schwergewichtig.

## Entscheidung

Dateien werden über Laravels **`Storage`/Filesystem-Abstraktion** im Dateisystem
abgelegt, in der DB steht nur noch der **Pfad/Key**.

- Disk `public` (bzw. später S3-kompatibel) für Galerie- und Profilbilder.
- Upload-Validierung (Mime-Typ, Maximalgröße) über Laravel-Validation.
- Beim Daten-Import (ADR-0003) werden bestehende BLOBs einmalig in Dateien
  ausgelagert und die Pfade gesetzt.
- Auslieferung über normale URLs (Caching durch den Browser), nicht mehr als
  Base64-Inline.

## Konsequenzen

**Positiv**
- Schlankere, schnellere DB; günstigere Backups.
- Browser-Caching und spätere Optimierung (Thumbnails, CDN) möglich.
- Sauberere Templates (kein Base64-Blob im HTML).

**Negativ / Kosten**
- Datei-Lebenszyklus muss verwaltet werden (Löschen verwaister Dateien).
- In Containern muss der Storage-Pfad als Volume persistiert werden, sonst sind
  Uploads bei jedem Neustart weg.

## Alternativen

- **BLOB in der DB belassen** – einfach „alles an einem Ort", aber genau die
  beschriebenen Nachteile; verworfen.
- **Direkt S3/Objektspeicher von Anfang an** – sauber für echten Betrieb, aber
  für lokale Entwicklung Overkill. Lokal `public`-Disk, S3 bleibt durch die
  Storage-Abstraktion jederzeit nachrüstbar.
