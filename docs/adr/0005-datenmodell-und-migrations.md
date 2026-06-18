# ADR-0005: Datenmodell, Eloquent & Migrations

- **Status:** Akzeptiert
- **Datum:** 2026-06-12
- **Betrifft:** Datenbank, Domänenmodell

## Kontext

Das heutige Schema (Tabellen `user`, `family`, `invites`, `app`, `userapps`,
`events`, `bilder`, `shop`, `shopitems`, `todo`) wird nur als statischer
SQL-Dump gepflegt. Probleme:

- Schema-Änderungen sind nicht versioniert/nachvollziehbar.
- **S7:** `invites.email` ist UNIQUE → eine E-Mail kann systemweit nur einmal
  eingeladen werden.
- **B1:** `shoppingList` nutzt `ON DUPLICATE KEY UPDATE` auf `itemName`, ohne
  dass darauf ein UNIQUE-Key liegt → Mengenlogik defekt.
- **B3:** Spalte `app.appPfad` enthält hartkodierte absolute Localhost-URLs.
- **M4:** Sprachmix (Deutsch/Englisch), Tippfehler („calender"), uneinheitliche
  Spaltennamen.
- `todo`/`shopitems` haben **keine** `famID`/`userID`-Bindung im Schema – die
  Zugehörigkeit hängt teils an Zwischen-/Join-Tabellen, teils gar nicht.

## Entscheidung

Das Datenmodell wird über **Laravel-Migrations** versioniert neu aufgebaut und
über **Eloquent-Modelle** mit expliziten Beziehungen abgebildet.

Konkrete Änderungen gegenüber heute:

- **Migrations** als Single Source of Truth; Seeder für Demo-/Testdaten
  (ersetzt den SQL-Dump und das `test_db.php`-Skript).
- **Konventionen** (siehe ADR-0008): Tabellen Plural/`snake_case`
  (`users`, `families`, `invites`, `apps`, `events`, `images`, `shops`,
  `shopping_items`, `todos`), `id`-Primärschlüssel, `created_at/updated_at`,
  Fremdschlüssel mit `ON DELETE CASCADE`. „calender" → „calendar".
- **Beziehungen:** `User belongsTo Family`; `Family hasMany Users/Events/Images`;
  `User belongsToMany App` (Pivot `app_user`); konsequente `family_id`-Bindung
  für alle gemeinsam genutzten Inhalte.
- **Fixes:** `invites` ohne UNIQUE auf `email`, dafür `token`-Index +
  `expires_at`/`accepted_at` (S7). Einkaufslisten-Mengenlogik sauber über
  Pivot/Constraint statt kaputtem `ON DUPLICATE KEY` (B1).
- **`app.appPfad` entfällt** als URL-Spalte – die App-Verlinkung läuft über
  benannte Routen, nicht über DB-URLs (B3).

## Konsequenzen

**Positiv**
- Schema wird versioniert, reproduzierbar (`migrate --seed`) und review-bar.
- Beziehungen explizit → weniger handgeschriebene JOINs, weniger Bugs.
- Lange schwelende Schemafehler (S7, B1, B3) werden strukturell gelöst.

**Negativ / Kosten**
- Einmaliger Aufwand, Schema + Datenimport sauber zu übersetzen.
- Umbenennungen erfordern ein sorgfältiges Migrationsskript für Altdaten.

## Alternativen

- **Alten Dump 1:1 importieren** – schnell, aber zementiert S7/B1/B3 und den
  Sprachmix; verworfen.
- **Schema unverändert lassen, nur Eloquent drüberlegen** – spart Umbenennungen,
  behält aber die Designfehler. Teilkompromiss, verworfen zugunsten Klarheit.
