# Löschkonzept & Aufbewahrung (Entwurf)

> **Status: interner Entwurf, vor Stufe 2 juristisch prüfen lassen.** Legt fest,
> welche Daten wie lange gespeichert werden und was beim Löschen passiert.
> Grundlage für das noch zu bauende „Konto löschen"-Feature (DSGVO Art. 17).
> Bezug: ADR-0025 (Stufenmodell), ADR-0015 (Medien), ADR-0020 (Papierkorb).

## 1. Welche personenbezogenen Daten gibt es?

| Kategorie | Felder / Inhalt | Tabelle |
| ----------- | ----------------- | --------- |
| Konto | Vor-/Nachname, E-Mail, Passwort (gehasht), Rolle | `users` |
| Profil | Avatar (Bild), Geburtsdatum, Geschlecht, Social-Links, Farbe | `users` |
| Familie | Familienname, Heimatort (Name + Koordinaten) | `families` |
| Geteilte Inhalte | Termine, Einkaufsliste, ToDos, Kontakte, Fotos | je eigene Tabelle |
| Nutzung | Spiel-Highscores, ToDo-Punkte („Nest-Blätter") | `game_scores`, `todo_points` |
| Abo | Plan/Status/Ablauf (kein Zahlungsmittel – Kauf simuliert) | `subscriptions` |
| Einladungen | eingeladene E-Mail-Adresse, Rolle, Token | `invites` |
| Auth | API-Tokens (nativ), Sessions (Web), Passwort-Reset-Token | diverse |

Fotos werden beim Upload von Standort-/EXIF-Daten befreit (ADR-0015); der
Zugriff läuft ausschließlich über kurzlebige signierte URLs.

## 2. Aufbewahrungsfristen

- **Galerie-Papierkorb:** gelöschte Bilder 30 Tage wiederherstellbar, danach
  endgültig entfernt (Datei + Datenbank, `trash_retention_days`, ADR-0020).
- **Native API-Tokens:** 90 Tage gültig, dann ungültig; bei Passwortwechsel
  sofort widerrufen.
- **Passwort-Reset-Token:** kurzlebig (Minuten).
- **Backups:** DB-Dumps 14 Tage (täglich rotierend) + Hetzner-Server-Backups
  7 Tage. **Wichtig:** Gelöschte Daten bleiben bis zum Rotieren in Backups
  erhalten – eine rückwirkende Entfernung aus Backups erfolgt nicht (Stand der
  Technik). Nach spätestens ~14 Tagen sind sie auch dort weg.
- **Server-Logs (Docker):** max. 3×10 MB je Dienst (rotierend, ADR-0027);
  enthalten keine Passwörter/Tokens/Cookies.
- **Aktive Konten/Familiendaten:** bis zur Löschung durch die Nutzer.

## 3. Löschvorgänge

### 3.1 Automatisch

- Papierkorb-Bilder nach 30 Tagen (`model:prune`, Scheduler-Container).
- Abgelaufene Tokens werden ungültig.

### 3.2 Konto löschen (Selbstbedienung – Feature noch zu bauen)

**Ein Mitglied verlässt eine Familie mit weiteren Mitgliedern:**

- Gelöscht: Konto + persönliche Daten (Name, E-Mail, Passwort, Avatar,
  Geburtsdatum, Geschlecht, Social-Links), alle Tokens/Sessions, persönliche
  Nutzungsdaten (Highscores, Nest-Blätter).
- Anonymisiert, NICHT gelöscht: die von der Person angelegten **geteilten**
  Familieninhalte (Termine, Einkaufsliste, ToDos, Kontakte, Fotos) – der
  `created_by`-Verweis wird auf „null" gesetzt (Schema unterstützt das bereits),
  damit die Familie ihre gemeinsamen Inhalte behält.

> **Produktentscheidung für Timo (Default vorgeschlagen):** Von der Person
> **hochgeladene Fotos** bleiben als Familien-Galerie-Inhalt erhalten
> (anonymisiert). Alternative: mitlöschen. Vorschlag = behalten, weil bewusst
> in die geteilte Familien-Galerie geladen; wer einzelne Fotos entfernt haben
> will, löscht sie vor dem Konto-Löschen selbst. Bitte bestätigen/ändern.

**Die letzte verwaltende Person (Guardian) mit verbleibenden Mitgliedern:**

- Blockiert mit klarer Meldung: „Bitte zuerst ein anderes Mitglied zum
  Verwalter machen – oder die ganze Familie löschen." (Konsistent mit dem
  bestehenden Schutz gegen das Herabstufen des letzten Verwalters.)

**Das letzte Mitglied einer Familie:**

- Konto **und** die ganze Familie samt aller familiengebundenen Daten werden
  gelöscht (Kaskade: Termine, Einkaufsliste, ToDos, Kontakte, Fotos inkl.
  Dateien, Abo, offene Einladungen).

### 3.3 Ganze Familie löschen (durch einen Verwalter)

- Entfernt alle Mitglieder-Konten, alle geteilten Inhalte und Dateien
  vollständig. Für den „alles löschen"-Wunsch (DSGVO Art. 17).

### 3.4 Auf Anfrage (bis Self-Service steht)

- Löschung/Auskunft/Export per E-Mail an die im Impressum genannte Adresse;
  Umsetzung durch den Betreiber, dokumentiert.

## 4. Betroffenenrechte (Umsetzung)

- **Auskunft/Export (Art. 15/20):** geplant als Selbstbedienungs-Export (JSON
  der eigenen + Familiendaten); bis dahin auf Anfrage.
- **Berichtigung (Art. 16):** über Profil-/Familienseite selbst möglich.
- **Löschung (Art. 17):** siehe 3.2/3.3.
- **Widerspruch/Einschränkung:** per Anfrage.

## 5. Offene Punkte vor Stufe 2

- „Konto löschen" + „Familie löschen" + Datenexport als Feature bauen
  (Backend + UI + Tests) gemäß den Semantiken oben.
- Fristen/Prozess final mit Datenschutz-Beratung bestätigen.
- Produktentscheidung Fotos beim Mitglied-Austritt (siehe Kasten) klären.
