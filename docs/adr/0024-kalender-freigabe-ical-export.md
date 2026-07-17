# ADR-0024: Kalender-Freigabe (iCal-Export)

- **Status:** Akzeptiert (umgesetzt 2026-07-17)
- **Datum:** 2026-07-17
- **Betrifft:** Kalender, Premium (ADR-0013/0022), Gegenstück zu ADR-0023

## Kontext

ADR-0023 holt fremde Kalender **in** Nidula. Die Gegenrichtung fehlte: Die
Familie will Nidula-Termine dort sehen, wo sie ohnehin hinschaut – im
Google-/Apple-/Outlook-Kalender des Handys, inklusive System-Erinnerungen.
Der Standardweg dafür ist eine abonnierbare iCal-URL („secret address", wie
sie auch Google Calendar für private Kalender ausgibt).

## Entscheidung

**Jede Familie kann eine geheime .ics-Abo-URL aktivieren
(`GET /api/v1/calendar-export/{token}`), die den kompletten Familienkalender
liefert.** Premium-Feature wie alles unter „Kalender-Sync".

- **Token statt Login:** Kalender-Apps können sich nicht einloggen – ein
  64-Hex-Token (`random_bytes(32)`) in `families.calendar_token` ist die
  einzige Zugangskontrolle. Route mit Regex-Constraint + Throttle (30/min).
- **Klartext-Speicherung des Tokens – bewusster Trade-off:** gehasht (wie
  Sanctum-Tokens) wäre die URL nur einmal anzeigbar; die Familie muss sie
  aber auf jedem Gerät neu kopieren können. Das Token gewährt nur LESE-Zugriff
  auf Termine, nicht aufs Konto. Gleiche Abwägung wie Googles „geheime
  Adresse". `$hidden` am Family-Model verhindert Leaks über JSON-Resources
  (per Test abgesichert).
- **Rechte:** Alle Mitglieder sehen/kopieren die URL (jedes Handy soll
  abonnieren), nur Verwalter aktivieren, rotieren („neue Adresse erzeugen",
  macht die alte sofort ungültig) und beenden.
- **Serien als echte RRULEs:** Unser Recurrence-Modell mappt 1:1 auf RFC 5545
  (`biweekly` → `FREQ=WEEKLY;INTERVAL=2`, `recurrence_until` → `UNTIL=…Z`
  inklusiv). Die Kalender-App expandiert selbst – kein Server-Aufwand, und
  Änderungen an der Serie brauchen nur einen Feed-Abruf. Generierung über das
  bereits vorhandene **sabre/vobject** (keine neue Abhängigkeit); Zeiten in
  UTC, Owner-Vorname im Titel („Zahnarzt (Max)"), `X-WR-CALNAME` +
  `REFRESH-INTERVAL PT6H` als Hinweise.
- **10-min-Server-Cache** je Familie (Muster ADR-0022/0023) gegen aggressive
  Poller; Kalender-Apps aktualisieren ohnehin nur alle paar Stunden – die UI
  sagt das ehrlich.
- **Premium-Pause statt Löschung:** Läuft das Abo aus, antwortet der Feed 404,
  Token und Einstellung bleiben erhalten – Reaktivierung genügt.

## Alternativen (verworfen)

- **CalDAV-Server:** Zwei-Wege-Sync wäre schöner, ist aber ein eigenes
  Protokoll-Projekt (Auth, ETags, Konflikte). Lesen deckt den Alltag ab.
- **Signierte URLs mit Ablauf (wie Medien, ADR-0015):** Kalender-Apps
  speichern die URL dauerhaft – ablaufende Signaturen würden das Abo stumm
  brechen. Rotation auf Wunsch ist das passendere Modell.
- **Pro-Mitglied-Feeds:** mehr Tokens, mehr UI, wenig Mehrwert – der
  Familienkalender IST das gemeinsame Artefakt. Bei Bedarf später ergänzbar.

## Folgen

- „Teilen"-Button im Kalender (alle Mitglieder, Premium) mit Kopier-Dialog,
  Anleitung für Google/Apple und ehrlichen Hinweisen (Aktualisierungs-Latenz,
  „wer die Adresse kennt, liest mit → rotieren").
- Free-Verwalter sehen im Kalender den kombinierten Teaser
  „Kalender-Abos & Teilen (Premium)".
- PremiumPage listet die Freigabe als verfügbaren Vorteil (ADR-0022: ehrlich).
- In Produktion MUSS `APP_URL` öffentlich erreichbar sein (die Feed-URL wird
  daraus gebaut); lokal funktioniert der Test nur im Browser auf demselben
  Rechner.
