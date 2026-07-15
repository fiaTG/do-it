# ADR-0021: Einladungs-Rollen & Owner-Schutz im Kalender

- **Status:** Akzeptiert (umgesetzt 2026-07-15)
- **Datum:** 2026-07-15
- **Betrifft:** Einladungen, Rollen (ADR-0019), Kalender

## Kontext

ADR-0019 hat zwei Punkte bewusst offen gelassen:

1. Eingeladene Mitglieder wurden immer **Verwalter** (Default) – ein Kind
   einzuladen erforderte nachträgliches Umstellen. Zudem durfte **jedes**
   Mitglied einladen, auch Kinder.
2. Ein Kind durfte jeden Termin mit sich als Owner löschen – auch den vom
   Elternteil angelegten Zahnarzttermin.

Außerdem verschwanden verschickte Einladungen im Nichts: keine Übersicht,
kein Zurückziehen.

## Entscheidung

**Einladungen sind Verwalter-Sache mit Rollen-Wahl:**

- **Nur Verwalter** dürfen einladen (`InviteRequest::authorize`); Kinder nicht
  mehr. Die Rolle des Eingeladenen (`invites.role` ∈ `guardian` | `child`,
  Default `guardian`) wird **beim Einladen** festgelegt und bei der
  Registrierung über den Token übernommen.
- **Offene Einladungen** (nicht eingelöst, nicht abgelaufen) sind für alle
  Mitglieder sichtbar (`GET /invites`), **zurückziehen** dürfen nur Verwalter
  (`DELETE /invites/{invite}`; 409, wenn bereits angenommen).

**Owner-Schutz (verschärft ADR-0019):**

- Kinder dürfen nur Termine bearbeiten/löschen, die sie **selbst angelegt**
  haben (`owner_id == user && user_id == user` in `EventPolicy::canManage`).
- Vom Verwalter *für* das Kind angelegte Termine (Owner = Kind, Ersteller =
  Verwalter) sind für das Kind **nur lesbar**.

## Konsequenzen

**Positiv**

- Eltern behalten die Kontrolle über Pflichttermine der Kinder; Einladungen
  sind nachvollziehbar und korrigierbar (falsche Adresse → zurückziehen).
- Kind-Konten entstehen direkt mit der richtigen Rolle – kein Zeitfenster
  mehr, in dem ein frisch eingeladenes Kind Verwalterrechte hat.

**Negativ / Kosten**

- Kinder können keine Mitglieder mehr einladen (bewusste Einschränkung).
- Ein Kind kann einen selbst angelegten Termin nicht mehr löschen, wenn ein
  Verwalter ihn nachträglich übernehmen möchte – Workaround: Verwalter
  bearbeitet/löscht.

## Alternativen

- **Rollen-Wahl für alle Mitglieder** – verworfen: ein Kind könnte
  Verwalter-Konten anlegen und so die Rollentrennung aushebeln.
- **Owner-Schutz über ein Flag je Termin** („gesperrt") – flexibler, aber
  mehr UI/Modell-Komplexität; das `created_by`-Kriterium ist einfach und
  deckt den Alltagsfall (Eltern legen Pflichttermine an).
- **Status quo** – verworfen, siehe Kontext.
