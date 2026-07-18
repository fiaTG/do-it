# ADR-0026: „Nest-Blätter" – Belohnungssystem für ToDos

- **Status:** Akzeptiert (MVP umgesetzt 2026-07-18)
- **Datum:** 2026-07-18
- **Betrifft:** ToDos, Dashboard, Profil; später Fun Area & Premium (ADR-0013)

## Kontext

Beta-Feedback Runde 1: „Die ToDo-App ist recht langweilig, bringt keinen
Mehrwert – vielleicht ein Belohnungssystem wie die Big Player." Analyse der
Vorbilder:

| Vorbild | Mechanik | Bewertung für Nidula |
| --------- | ---------- | ---------------------- |
| Todoist „Karma" | Punkte, Level | ✔ harmlos motivierend |
| Habitica | RPG-Avatar, ABER Lebenspunkte-Verlust als Strafe | Kosmetik ✔, Bestrafung ✘ |
| Duolingo | Streaks + Schuld-Pushes + Liga-FOMO | ✘ wirksam, aber manipulativ – kollidiert mit „ehrlich & fair" (ADR-0013/0022) |
| Fitness-Apps | Meilenstein-Abzeichen | ✔ Freude ohne Druck |
| Familien-Apps (OurHome u. a.) | Eltern bepreisen Aufgaben, Kinder lösen ECHTE Belohnungen ein | ✔✔ der Familien-Twist, den To-do-Apps nicht haben |

## Entscheidung (mit Timos Produktentscheidungen vom 2026-07-18)

**„Nest-Blätter" 🍃** – knüpft ans Marken-Bild (Nest, Raupe) an:

1. **1 Blatt pro erledigtem ToDo, alle gleich** (Timo: bewusst simpel, kein
   Gewichtungs-UI). Wer abhakt, bekommt das Blatt.
2. **Ledger statt Zählerspalte:** `todo_points`-Tabelle (family/user/todo,
   `todo_id` nullable). Punkte überleben so das Löschen erledigter ToDos;
   Zurücknehmen des Häkchens entfernt den Eintrag wieder. Kein Cache am User.
3. **Wochen-Champion sichtbar** (Timo: familieninterner Wettbewerb ok, wie
   die Spiele-Bestenliste): ToDo-Seite zeigt eigenen Stand + „Champion der
   Woche" (Woche ab Montag, `GET /todos/points` liefert week/totals je
   Mitglied). Erledigte Einträge zeigen den Avatar des Erledigers.
4. **Meilenstein-Abzeichen im Profil** (10/50/100/250/500: Erstes Grün,
   Fleißige Raupe, Blätterdach, Nest-Profi, Familien-Legende) – rein
   clientseitig aus den Gesamtwerten berechnet, kein Backend-Zustand.
5. **MVP ohne Belohnungs-Regal** (Timo): Das „Regal" – Verwalter legen echte
   Familien-Belohnungen mit Blatt-Preisen an (Eis, Kinoabend), Kinder lösen
   ein, Verwalter bestätigt – kommt als eigene Ausbaustufe und wird
   **Premium** (Timo: Basis frei, Regal spürbar; ADR-0013-konform, weil die
   Kern-Motivation frei bleibt).

**Bewusst ausgeschlossen (Anti-Dark-Pattern-Leitplanken):** keine Straf-
Mechaniken (Punktabzug, „Avatar leidet"), kein Streak-Verlust-Drama oder
Schuld-Pushes, keine kaufbaren Punkte, keine familienübergreifenden
Ranglisten. Diese Leitplanken gelten auch für alle Ausbaustufen.

## Konsequenzen

- ToDos bekommen einen Alltags-Anreiz, ohne dass die App nervt oder
  manipuliert – messbar am Familien-Feedback der Beta.
- Akzeptiertes Schlupfloch auf Familien-Skala: Man kann sich Blätter durch
  eigene Mini-ToDos erschummeln. Sozialkontrolle in der Familie regelt das
  besser als jede Technik; bei Bedarf später begrenzen.
- Champion-Reset ist implizit (Woche ab Montag im Query) – kein Cron nötig.
- Ausbaustufen später: Belohnungs-Regal (Premium), Blätter für Spiele-Erfolge
  (Fun-Area-Verzahnung), Wochenrückblick-Karte auf dem Dashboard.
