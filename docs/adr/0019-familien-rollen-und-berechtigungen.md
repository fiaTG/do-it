# ADR-0019: Familien-Rollen & Berechtigungen (Verwalter/Kind)

- **Status:** Akzeptiert (umgesetzt 2026-06-18)
- **Datum:** 2026-06-18
- **Betrifft:** Auth/Berechtigungen, Familie, Kalender

## Kontext

Bisher waren alle Familienmitglieder **gleichberechtigt**: familiengebundene
Policies ([ADR-0004](0004-auth-und-session-sicherheit.md),
[ADR-0008](0008-projektstruktur-und-konventionen.md)) erlauben jedem Mitglied,
gemeinsame Ressourcen zu verwalten. In einer Familie mit **Kindern** ist das zu
grob – ein Kind konnte die Termine der Eltern bearbeiten/löschen. Es braucht
eine Unterscheidung **Eltern/Verwalter ↔ Kind**.

## Entscheidung

**Eine Rolle je Mitglied** auf dem Nutzer (`users.role` ∈ `guardian` | `child`,
Default `guardian`):

- **Verwalter (`guardian`):** dürfen **alle** Familientermine verwalten **und**
  die Rolle **anderer** Mitglieder setzen.
- **Kind (`child`):** darf **nur eigene** Termine (Owner = es selbst) anlegen,
  bearbeiten, löschen; fremde Termine nur **ansehen**.

**Owner-Modell (Kalender):** Jeder Termin hat einen **Owner** = das Mitglied,
*für das* er ist (Standard = Ersteller). Verwalter dürfen den Owner frei wählen
(z. B. Termin für ein Kind anlegen), Kinder nur sich selbst.

**Durchsetzung serverseitig** (Frontend spiegelt nur, Sicherheit liegt im Backend):
- `EventPolicy::canManage` – Verwalter alle, Kind nur `owner_id == user`.
- `EventController` – beim Anlegen Owner für Kinder auf sich erzwungen, Owner-ID
  per `Rule::exists(... family_id)` validiert; Kinder dürfen Owner nicht umhängen.
- `FamilyController::updateRole` – nur Verwalter, **nicht die eigene** Rolle
  (kein Selbst-Lockout), Mitglied muss derselben Familie angehören.

**Bewusst minimal:** nur zwei Rollen, kein feingranulares RBAC pro Modul.

## Konsequenzen

**Positiv**
- Kinder können fremde Familientermine nicht mehr verändern; Eltern behalten die
  Kontrolle. Klares, einfaches, erweiterbares Modell.

**Negativ / Kosten**
- Grobe Granularität (alles vs. nur eigenes). Rollen-UI nötig (Familien-Seite).
- Default `guardian`: neu eingeladene Mitglieder sind zunächst Verwalter, bis ein
  Verwalter sie zum Kind macht.

## Alternativen

- **Keine Rollen** (Status quo) – verworfen, Kind editiert Eltern-Termine.
- **Feingranulares RBAC / Berechtigungen pro Modul** – Overkill für eine Familie.
- **Read-only-Kind** (Kind darf gar nichts) – verworfen; Kinder sollen **eigene**
  Termine eintragen können.

## Zugehörige Produktentscheidung (Kalender)

Ergänzend zum Owner-Modell: Der Familienkalender denkt in **„WER"** statt
„Lebensbereich" – Termine werden **nach Person (Owner) gefärbt**, die Legende
zeigt Mitglieder statt Kategorien. Standardansicht ist **Woche**; zusätzlich gibt
es eine **selbstgebaute „Tag nach Person"-Spaltenansicht** (Spalte je Mitglied),
um ohne kostenpflichtiges FullCalendar-Premium die FamilyWall-typische Übersicht
zu erreichen.

## Offene Punkte

- Default-Rolle **eingeladener** Mitglieder ggf. schon bei der Einladung wählbar.
- „Owner-Schutz": Darf ein Kind einen vom Elternteil **für es** angelegten Termin
  (owner=Kind) löschen? Aktuell ja – bei Bedarf verschärfen (z. B. nach
  `created_by`).
