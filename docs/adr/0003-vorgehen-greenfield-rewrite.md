# ADR-0003: Vorgehen – Greenfield-Neuaufbau mit DB-Übernahme

- **Status:** Akzeptiert
- **Datum:** 2026-06-12
- **Betrifft:** Migrationsstrategie

## Kontext

Die bestehende App ist klein, aber durchgängig verschachtelt (Logik+HTML+CSS+JS
in einer Datei, Duplizierung der Navigation/Bootstraps). Zwei Wege stehen zur
Wahl: den vorhandenen Code Stück für Stück refactoren (Strangler) oder sauber
neu aufbauen und das funktionale Verhalten nachziehen.

Bei dieser Codegröße und dem Grad der Vermischung ist ein sauberer Neuaufbau auf
Laravel meist schneller und ergibt einen klareren Stand als das Entwirren von
Spaghetti-Code. Das **Datenmodell** dagegen ist im Kern brauchbar und soll
erhalten bleiben.

## Entscheidung

**Greenfield-Neuaufbau** in einem frischen Laravel-Projekt, dabei:

- Das bestehende DB-Schema wird als **Eloquent-Migrations nachgebaut**
  (nicht der alte SQL-Dump importiert), inkl. der dabei zu fixenden Schemafehler
  (S7 `invites.email`-UNIQUE, B1 fehlender UNIQUE-Key).
- Bestehende **Daten** werden über ein einmaliges Import-/Seed-Skript aus dem
  alten Schema übernommen (Bilder dabei von BLOB auf Storage migrieren, ADR-0006).
- Die **alte App bleibt lauffähig**, bis das neue Projekt funktional gleichzieht.
  Erst dann wird umgeschaltet und der Alt-Code archiviert.
- Reihenfolge der Portierung folgt dem Phasenplan der Roadmap: Auth → Datenmodell
  → Dashboard/Apps.

## Konsequenzen

**Positiv**

- Sauberster möglicher Ausgangszustand, keine Altlasten im neuen Code.
- Klare „Definition of Done" pro Feature (Verhalten der alten App = Referenz).
- Daten gehen nicht verloren.

**Negativ / Kosten**

- Während der Übergangszeit existieren zwei Codebasen parallel.
- Jedes Feature muss bewusst nachgebaut werden – nichts wird „einfach übernommen".
- Risiko, kleine undokumentierte Verhaltensweisen der Alt-App zu übersehen
  (Gegenmittel: die Bedienungsanleitung als Feature-Checkliste nutzen).

## Alternativen

- **Strangler-Fig-Refactor** – risikoärmer, App durchgehend lauffähig, aber
  langsamer und man schleppt die Vermischung länger mit. Für diese Codegröße
  nicht lohnend → verworfen.
- **Big-Bang ohne Datenübernahme** – am schnellsten, aber Verlust der Testdaten
  und unnötig; das Schema ist es wert, übernommen zu werden.
