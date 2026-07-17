# ADR-0023: Kalender-Abos (iCal-Import)

- **Status:** Akzeptiert (umgesetzt 2026-07-17)
- **Datum:** 2026-07-17
- **Betrifft:** Kalender, Premium (ADR-0013/0022), Sicherheit

## Kontext

Familien haben viele fremdverwaltete Termine: Schul- und Vereinskalender,
gemeindliche Abfuhrkalender, Feiertage. Fast alle Anbieter veröffentlichen sie
als iCal (`.ics` – Datei zum Download oder Abo-URL/webcal). Die PremiumPage
versprach „Synchronisation mit externen Kalendern" bisher als „Bald" – dieses
ADR löst das Versprechen ein. Es ist nach den Spritpreisen (ADR-0022) das
zweite Feature hinter der `premium`-Middleware.

## Entscheidung

**Externe Kalender werden als eigene, schreibgeschützte Ebene geführt –
nicht in die `events`-Tabelle importiert.**

- Tabelle `calendar_feeds` (family-scoped): Name, Layer-Farbe, optionale URL,
  zuletzt geholter ICS-Rohtext (`ics_data`), `last_synced_at`, `last_error`.
- Zwei Arten: **URL-Abo** (wird aktualisiert) und **einmaliger Datei-Import**
  (`url = null`, z. B. heruntergeladener Abfallkalender).
- Der Server speichert nur den ICS-Rohtext und **expandiert on-demand** ins
  angefragte Zeitfenster (`GET /calendar-feeds/events?from&to`, Fenster
  ≤ 1300 Tage, max. 2000 Vorkommen je Feed). Kein Duplikat-Abgleich, keine
  verwaisten Termine: Abo löschen = Ebene weg.
- **Parsing/RRULE: `sabre/vobject`** (Nextcloud-Unterbau). RRULE-Expansion
  selbst zu bauen wäre fahrlässig (Zeitzonen, EXDATE, RECURRENCE-ID, BYDAY …).
  Zeiten kommen normalisiert in UTC, Ganztagstermine bleiben reine Datumswerte
  mit exklusivem Ende (RFC 5545 = FullCalendar-Konvention).
- **Refresh-Politik wie Tankerkönig (ADR-0022): nur on-demand.** Beim Lesen
  werden URL-Abos aktualisiert, die älter als 6 h sind; dazu ein manueller
  „Jetzt aktualisieren"-Button. Kein Cron, kein Polling. Schlägt ein Refresh
  fehl, bleiben die alten Termine nutzbar und der Fehler steht als
  `last_error` im Verwalter-UI; `last_synced_at` wird auch im Fehlerfall
  gesetzt, damit ein toter Anbieter nicht jeden Kalender-Aufruf um die volle
  Timeout-Wartezeit verzögert.
- **Rechte wie ADR-0021:** Verwalter verwalten Abos (max. 5 je Familie), alle
  Familienmitglieder sehen die Termine. Kinder-Konten können nichts kaputt
  machen – die Ebene ist ohnehin schreibgeschützt.

**Sicherheit: Server-seitiger Abruf fremder URLs = SSRF-Vektor.** Der
`IcsFetcher` erzwingt deshalb:

- nur `http(s)`/`webcal` (webcal → https) auf Standard-Ports,
- **alle per DNS aufgelösten IPs (A + AAAA) müssen öffentlich sein** – private
  und reservierte Bereiche (10/8, 172.16/12, 192.168/16, 127/8,
  169.254.169.254-Metadata, ::1, fc00::/7 …) werden abgelehnt,
- Redirects werden manuell verfolgt (max. 3) und **jede Station erneut
  geprüft**,
- 6 s Timeout, max. 2 MB, Antwort muss `BEGIN:VCALENDAR` enthalten.

Restrisiko DNS-Rebinding (TTL 0, zweite Auflösung zeigt intern) ist bewusst
akzeptiert: Der Angreifer bräuchte ein Verwalter-Konto einer Premium-Familie,
und der Fetch folgt unmittelbar auf die Prüfung. Bei Produktionsbetrieb hinter
eigener Infrastruktur ggf. per Egress-Firewall zusätzlich absichern.

## Alternativen (verworfen)

- **Import in die `events`-Tabelle:** Duplikate bei Re-Import, Löschen des Abos
  müsste Termine wiederfinden, RRULE→eigenes Recurrence-Modell verlustbehaftet
  (unsere Recurrences sind bewusst simpel, ICS-RRULEs beliebig komplex).
- **Client-seitiges Abrufen der ICS-URL:** scheitert an CORS, würde den
  API-Key … äh, die Abo-URL jedem Client geben und ließe kein Server-Caching zu.
- **CalDAV-Zwei-Wege-Sync:** deutlich größerer Scope (Auth je Anbieter,
  Konfliktauflösung); iCal-Lesen deckt die realen Familien-Anwendungsfälle ab.

## Folgen

- PremiumPage: „Kalender-Abos" ist jetzt als verfügbar gelistet (ehrlich,
  ADR-0022); Frontend zeigt Abo-Termine mit Feed-Farbe + Globus-Icon im
  Kalender, in der Legende (ein-/ausblendbar) und im Dashboard-Widget.
- Free-Familien sehen im Kalender einen dezenten „Kalender-Abos (Premium)"-
  Hinweis → PremiumPage („Lust statt Zwang, aber spürbar").
- Worst Case beim Kalender-Aufruf: 5 Abos × 6 s Timeout, wenn alle Anbieter
  gleichzeitig tot sind – akzeptiert, da selten und die Termine trotzdem
  (aus `ics_data`) erscheinen.
