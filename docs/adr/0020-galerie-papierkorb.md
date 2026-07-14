# ADR-0020: Galerie-Papierkorb (Soft-Delete mit Aufbewahrungsfrist)

- **Status:** Akzeptiert (umgesetzt 2026-07-14)
- **Datum:** 2026-07-14
- **Betrifft:** Galerie, Medien-Speicher, Freemium (ADR-0013), Medien-Prinzipien (ADR-0015)

## Kontext

Löschen in der Galerie war bisher **sofort endgültig**: Row und alle Dateien
(Original, Thumbnail, Varianten) weg, kein Zurück. Ein Fehlklick – gerade auf
dem Handy und seit es Mehrfachauswahl gibt – vernichtet unwiederbringlich
Familienfotos. Große Plattformen (Google/Apple/Amazon Photos) lösen das mit
einem Papierkorb samt Aufbewahrungsfrist.

## Entscheidung

**Löschen = Soft-Delete in den Papierkorb, endgültig erst nach 30 Tagen**
(`features.trash_retention_days`, zentral konfigurierbar):

- `deleted_at` auf `images` (Laravel SoftDeletes). Galerie-Listen, Quota-Zählung
  und Widgets blenden Papierkorb-Bilder automatisch aus.
- **Dateien bleiben** beim Soft-Delete vollständig erhalten – nur so ist
  verlustfreies Wiederherstellen möglich.
- Endpunkte: `GET images/trash` (Liste mit `deleted_at`/`expires_at`),
  `POST images/restore` und `POST images/purge` (je `ids[]`, max. 100,
  idempotent, Alles-oder-nichts-Autorisierung wie `batch-delete`).
- **Auto-Purge** über Eloquent `Prunable`: `model:prune` läuft täglich
  (routes/console.php) und entfernt abgelaufene Papierkorb-Bilder inklusive
  aller Dateien (`pruning()`-Hook). ⚠️ Braucht in Produktion einen laufenden
  Scheduler (cron `schedule:run` oder `schedule:work`) – lokal läuft keiner.
- **Quota-Regeln (ADR-0013):** Papierkorb zählt NICHT ins Free-Limit (er leert
  sich von selbst), aber **Wiederherstellen prüft das Limit** – sonst wäre das
  Limit über Löschen/Hochladen/Wiederherstellen umgehbar.
- Die signierten Medien-Routen lösen Bilder `withTrashed()` auf, damit der
  Papierkorb Vorschaubilder zeigen kann; signierte URLs dafür entstehen
  weiterhin nur in den Resources für berechtigte Familienmitglieder (ADR-0015).
- UI: Löschen ohne Bestätigungsdialog, dafür Toast mit „Rückgängig";
  Bestätigung nur noch für endgültiges Löschen/Papierkorb leeren.

## Konsequenzen

**Positiv**

- Fehlklicks sind 30 Tage lang folgenlos; Bestätigungsdialoge entfallen.
- Verhalten entspricht dem, was Nutzer von Foto-Plattformen kennen.

**Negativ / Kosten**

- Gelöschte Bilder belegen bis zu 30 Tage weiter Speicher (Kostenfaktor S3).
- Privacy-Abwägung: „Gelöscht" heißt erst nach Fristablauf wirklich gelöscht –
  dafür gibt es „Endgültig löschen" für den Sofort-Fall.
- Produktions-Deploy braucht zusätzlich einen Scheduler-Prozess (Hetzner-Plan
  ADR-0014 entsprechend ergänzen).

## Alternativen

- **Status quo (endgültig löschen)** – verworfen: Datenverlust durch Fehlklick.
- **Nur Undo-Toast ohne Papierkorb** (Löschen erst nach Toast-Timeout
  ausführen) – verworfen: rettet nur die ersten Sekunden, nicht den Fall
  „gestern versehentlich gelöscht".
- **Papierkorb ohne Datei-Erhalt** (nur Row soft-deleten, Dateien sofort weg) –
  sinnlos, Wiederherstellen wäre nur ein leerer Eintrag.
