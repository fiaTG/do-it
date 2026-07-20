# ADR-0027: Verhältnismäßiges Monitoring & Alerting

- **Status:** Akzeptiert (Stufe-1-Code umgesetzt 2026-07-20; externe Einrichtung offen)
- **Datum:** 2026-07-20
- **Betrifft:** Betrieb, Verfügbarkeit, Logging, Incident Response (ergänzt ADR-0025)

## Kontext

Nidula läuft als geschlossene Familien-Beta auf einem Hetzner-Server (ADR-0025).
Die Sicherheits- und Backup-Basis steht, aber es gibt **keine automatische
Benachrichtigung**, wenn die Seite ausfällt, ein Backup still scheitert oder die
Platte vollläuft – man müsste zufällig selbst per SSH nachschauen.

Ausgangspunkt war ein externer Entwurf (ChatGPT). Triage gegen den realen Code
bestätigte drei echte Lücken und einen wichtigen Vorbehalt:

1. **Health-Check ist nur Liveness:** `/api/v1/health` meldete `status: ok`,
   ohne DB oder Redis zu prüfen – ein DB-Ausfall bliebe unsichtbar.
2. **Kein Backup-Heartbeat:** `deploy/backup-db.sh` meldete Fehlschläge nur
   lokal; ein still gescheiterter Dump fällt niemandem auf.
3. **Docker-Logs unbegrenzt:** keine Rotation → Logs können langsam die Platte
   füllen.
4. **Access-Logging bewusst AUS:** Nidula hat Geheimnisse in URLs (Einladungs-,
   Kalender-Freigabe-, signierte Medien-Tokens). Zugriffslogs dürfen diese
   nicht mitschreiben.

Nidula ist ein Ein-Personen-Projekt. Die Lösung muss verhältnismäßig, bezahlbar
und mit geringem Wartungsaufwand betreibbar sein – kein SOC, kein SIEM.

## Entscheidung

Schlankes, stufenweise ausbaubares Monitoring. Das primäre Alarmierungssystem
läuft **außerhalb des Servers** (kann sonst nicht alarmieren, wenn der Server
selbst weg ist). Der konkrete Wächter-Dienst bleibt **tool-neutral** (Timo
wählt beim Einrichten; der Heartbeat ist nur eine URL aus der Server-`.env`).
Hetzner bleibt zuständig für physische Infrastruktur, DDoS-Schutz, Cloud-
Firewall, Server-Backups und Statusmeldungen.

### Stufe 1 – für die geschlossene Beta (Code umgesetzt 2026-07-20)

- **Readiness-Endpoint** `GET /api/v1/health/ready` (`HealthController`): prüft
  DB (`select 1`) und den Cache-Store per Round-Trip (eindeutiger Key je
  Request gegen Races, String-Token wegen Redis-Serialisierung). 200
  `{"status":"ready"}` bzw. **503 `{"status":"degraded"}`** ohne Detail-Leak;
  Ursache nur ins Server-Log. `throttle:30,1`, da jeder Aufruf echte
  Abhängigkeiten anfasst. `/api/v1/health` bleibt als reine Liveness bestehen.
  Der externe Monitor zeigt auf `/health/ready`.
- **Backup-Heartbeat** (Totmann-Schalter): `backup-db.sh` sendet NUR nach
  Erfolg ein Lebenszeichen an eine `NIDULA_BACKUP_HEARTBEAT_URL` aus
  `deploy/.env`. Bleibt es aus → Alarm. Mit Retries; ein Sende-Fehler lässt
  das Backup trotzdem als erfolgreich gelten.
- **Docker-Log-Rotation:** je Container 3×10 MB (Compose `x-logging`).
- **Extern (Timo, später):** Uptime-/TLS-Monitor auf `/health/ready`,
  Heartbeat-Monitor mit Karenz, Hetzner-Statusmeldungen abonnieren,
  Test-Alarm auslösen.

### Stufe 2 – vor der Beta mit fremden Familien

Readiness um Medienspeicher-Check erweitern; **Platten-Alarm** (80 % Warnung /
90 % kritisch – Log-Rotation allein erkennt keine volle Platte durch
DB/Bilder/Backups); Heartbeats/Checks für Worker, Scheduler und gestoppte oder
ständig neustartende Container; fehlgeschlagene Backups.

### Stufe 3 – vor öffentlicher Registrierung

Security-Event-Monitoring (auffällige Mengen 401/403/429/5xx, erfolgreiche
SSH-Logins, Passwort-/Rollen-/Einladungs-/Freigabe-Änderungen); externes
Error-Tracking (Laravel/React); externer Security-Test; zentrale Ablage
ausgewählter Sicherheitslogs außerhalb des Servers.

## Logging

- **Laravel:** bereits produktiv auf `stderr` (`LOG_CHANNEL=stderr`) → über
  Docker erfasst; keine Dateien, die bei Container-Neubau verloren gehen.
- **Docker:** Größen-/Aufbewahrungsbegrenzung gesetzt (s. o.).
- **Caddy-Access-Logging:** bleibt **AUS**, bis eine datenschutz- und
  sicherheitsgerechte Filterung steht, die Tokens in Pfaden/Query-Strings
  (Einladungen, Kalender-Freigabe, signierte Medien) sowie Auth-Header, Cookies
  und Request-Bodies zuverlässig maskiert. (Stufe 3.)

## Alarmierungsprinzipien

Kein Alarm bei jedem Bot-Scan oder jeder fail2ban-Sperre – das ist normales
Internet-Grundrauschen. Alarmiert wird nur bei Ereignissen, die Eingreifen
erfordern: bestätigter Ausfall, fehlendes/fehlgeschlagenes Backup, kritischer
Speichermangel, ausgefallener Worker/Scheduler/Container, anhaltende
Serverfehler. Jeder Alarm verweist aufs Incident-Runbook (docs/deploy-hetzner.md).

## Abgrenzung zu ADR-0025

**Cloudflare/WAF bleibt vertagt** – die ADR-0025-Entscheidung (kein US-CDN vor
privaten Familienmedien in der Beta) wird hier NICHT neu aufgemacht; Caddy
liefert TLS + Security-Header direkt.

## Bewusst nicht vorgesehen

24/7-SOC, Kubernetes/HA-Cluster, eigener Prometheus/Grafana- oder
SIEM-/Wazuh-Server, jahrelange Voll-Zugriffslogs, Alarm bei jedem einzelnen Bot,
selbstgehostetes Uptime-Monitoring auf demselben Produktionsserver. Diese
Maßnahmen entsprechen nicht dem aktuellen Risiko und Betriebsaufwand.

## Konsequenzen

**Positiv:** Ausfälle und tote Backups werden erkannt, bevor Nutzer sie melden;
die Lösung bleibt für eine Einzelperson beherrschbar und wächst mit der
Nutzerzahl. **Negativ:** ein zusätzlicher externer Dienst; Schwellenwerte
brauchen Feinabstimmung; Uptime-Monitoring erkennt nicht jede erfolgreiche
Kompromittierung. **Wichtig:** Nach dem Code-Deploy ist das Monitoring nur
*vorbereitet* – wirksam erst, wenn der externe Monitor + Heartbeat verbunden und
ein Test-Alarm ausgelöst wurden.
