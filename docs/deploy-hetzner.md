# Deploy-Runbook: Nidula auf Hetzner (ADR-0025)

Stand 2026-07-17. Server: **nidula-prod** (Hetzner CX23, Falkenstein,
Ubuntu 24.04 LTS, IPv4 `167.233.64.98`). Übergangs-Domain bis zur echten:
`https://167-233-64-98.sslip.io` (sslip.io löst automatisch auf die IP auf,
dadurch funktioniert Let's Encrypt schon vor der Domain-Entscheidung).

## Architektur auf dem Server

```text
Internet ──443──> Caddy (Auto-TLS, Security-Header, Basic-Auth-Bauzaun)
                   ├─ /api/*, /sanctum/*  ->  app (php-fpm, Laravel)
                   └─ alles andere        ->  React-SPA (statisch)
        app / worker / scheduler = EIN Image (deploy/Dockerfile)
        MySQL 8.4 + Redis: NUR internes Docker-Netz, keine offenen Ports
        Volumes: dbdata, media (Fotos), caddy_data (Zertifikate)
```

- Code liegt unter `/opt/nidula` (Besitzer `nidula`), Deploy = Image neu bauen.
- Secrets NUR auf dem Server: `/opt/nidula/deploy/.env` (Compose: Domain,
  Bauzaun-Hash, DB-Passwörter) und `.env.app` (Laravel), beide `chmod 600`,
  beide gitignored. Vorlage: `backend/.env.production.example`.
- Scheduler-Container ersetzt cron (`schedule:work` → Papierkorb-Purge).

## Server-Härtung (durchgeführt 2026-07-17)

- Nutzer `nidula` (sudo + docker), SSH nur mit Key `~/.ssh/nidula_hetzner`.
- `PermitRootLogin no`, `PasswordAuthentication no`
  (`/etc/ssh/sshd_config.d/90-nidula.conf`) – Root-Login verifiziert tot.
- ufw: deny incoming, erlaubt nur OpenSSH/80/443. fail2ban aktiv (sshd-Jail;
  bannt real – 6 IPs am ersten Tag).
- unattended-upgrades installiert (automatische Security-Updates).

## Backups (Stand 2026-07-18)

1. **Hetzner-Vollbackup** (täglich, 7 Stände, inkl. media-Volume/Fotos).
2. **DB-Dump täglich 03:30** via systemd-Timer `nidula-backup.timer` →
   `deploy/backup-db.sh` → `/opt/nidula-backups/` (14 Stände, chmod 600,
   konsistent per --single-transaction). Status: `systemctl list-timers
   nidula-backup.timer`, Log: `journalctl -u nidula-backup`.
3. **Restore-Probe am 2026-07-18 bestanden** (Dump → Wegwerf-DB, 23/23
   Tabellen). Probe-Ablauf: Dump in `restore_probe`-DB einspielen, Tabellen
   zählen, DB droppen. Vor Stufe 2 wiederholen + Dump zusätzlich NACH EXTERN
   kopieren (3-2-1 komplett; steht in docs/aufgaben.md).

## Deployen (vom Entwicklungsrechner)

```bash
NIDULA_DOMAIN=167-233-64-98.sslip.io ./deploy/deploy.sh
# baut SPA lokal, rsynct nach /opt/nidula, baut Image, migriert, cached
```

Bei Domain-Wechsel zusätzlich einmalig auf dem Server `deploy/.env`
(`NIDULA_DOMAIN`) und `deploy/.env.app` (`APP_URL`, `FRONTEND_URL`,
`SESSION_DOMAIN`, `SANCTUM_STATEFUL_DOMAINS`) anpassen, dann
`docker compose -f docker-compose.prod.yml up -d --force-recreate caddy app worker scheduler`
und neu deployen (SPA muss mit neuer `VITE_API_URL` gebaut werden).

## Katalog-Daten (Pflicht!)

`deploy.sh` führt nach der Migration `db:seed --class=CatalogSeeder --force`
aus – das ist der **App-Katalog + die Läden** (idempotent, KEINE Demo-Daten).
Ohne diesen Schritt gibt es keine auswählbaren Apps/Shops (real passiert beim
ersten Deploy 2026-07-17). Der `DemoSeeder` (Testfamilie) läuft in Prod NIE.

## Beta-Zugangsschutz (ADR-0025 Stufe 1)

1. **Bauzaun:** Basic-Auth vor der Web-Oberfläche (Benutzer `familie`,
   Passwort hat Timo) – bewusst NICHT vor `/api` und `/sanctum` (sonst
   Doppel-Dialog + Passwort-Manager-Verwirrung; die API ist per Sanctum +
   invite-only ohnehin geschützt). Entfernen: `@fenced`/`basic_auth`-Block im
   `deploy/Caddyfile` löschen + deployen. Vor Native-Apps zwingend entfernen.
2. **Registrierung:** `NIDULA_REGISTRATION` in `.env.app` – steht seit dem
   Bootstrap (Timos Erst-Registrierung, 2026-07-17) auf `invite`: niemand ohne
   persönliche, E-Mail-gebundene Einladung (live verifiziert: 403).
   Einladungs-Links stehen zum Kopieren in der Familien-Seite (Mail-Versand
   ist noch aus).

**WICHTIG bei .env-Änderungen:** `docker compose restart` lädt env_file
NICHT neu (real passiert beim Bootstrap)! Immer so:

```bash
docker compose -f docker-compose.prod.yml up -d --force-recreate app worker scheduler
docker compose -f docker-compose.prod.yml exec -T app php artisan config:cache
```

## Selbst nachschauen: Wer klopft an? (Timos Frage 2026-07-18)

Alles vom Entwicklungsrechner (WSL) aus – der SSH-Schlüssel liegt dort:

```bash
# Gebannte Angreifer-IPs (SSH-Bruteforce; Grundrauschen ist NORMAL):
ssh -i ~/.ssh/nidula_hetzner nidula@167.233.64.98 "sudo fail2ban-client status sshd"

# Wer versucht sich gerade einzuloggen (letzte abgewiesene Versuche):
ssh -i ~/.ssh/nidula_hetzner nidula@167.233.64.98 "sudo journalctl -u ssh --since -1h | grep -i 'invalid\|failed' | tail -20"

# Platz/Last im Blick (wichtig wegen 40-GB-Platte + Foto-Uploads):
ssh -i ~/.ssh/nidula_hetzner nidula@167.233.64.98 "df -h / && uptime"
```

Einordnung: Bots scannen rund um die Uhr das gesamte Internet. Gebannte IPs
heißen „die Abwehr arbeitet", nicht „wir werden gezielt angegriffen". Kritisch
wäre: Login-Versuche mit ECHTEN Nutzernamen, volle Platte, Dauerlast.

## Monitoring & Alerting (ADR-0027)

**Health-Endpunkte** (beide öffentlich unter `/api`, kein Bauzaun):

- `GET /api/v1/health` – Liveness (Prozess antwortet). Trivial.
- `GET /api/v1/health/ready` – Readiness: prüft DB + Cache/Redis, `200
  {"status":"ready"}` bzw. `503 {"status":"degraded"}`. **Der externe Monitor
  zeigt hierauf**, damit auch DB-/Redis-Ausfälle Alarm auslösen.

**Backup-Heartbeat verdrahten** (wenn der Wächter-Dienst steht):

1. Sicherstellen, dass `curl` da ist: `ssh … "command -v curl"` (Ubuntu-Server
   hat es i. d. R.; sonst `sudo apt-get install -y curl`).
2. Beim Dienst einen Heartbeat-/Cron-Monitor mit Fenster „täglich + Karenz"
   anlegen, dessen Ping-URL kopieren.
3. In `deploy/.env` auf dem Server als `NIDULA_BACKUP_HEARTBEAT_URL=…`
   eintragen (server-only, NIE ins Repo). Ohne Eintrag ist der Heartbeat ein
   No-op.
4. Testen: `ssh … "/opt/nidula/deploy/backup-db.sh"` → beim Dienst muss ein
   Ping ankommen. Danach einen ausbleibenden Heartbeat testen (Alarm prüfen).

**Wichtig:** Nach dem Deploy ist das Monitoring nur *vorbereitet*. Wirksam erst,
wenn Uptime-Monitor + Heartbeat verbunden und ein Test-Alarm ausgelöst wurde.
Offen bleibt der Platten-Alarm (Log-Rotation begrenzt nur Logs, s. ADR-0027).

## Incident-Runbook (kurz, für Einzelbetrieb)

Bei einem Alarm – ruhig, der Reihe nach:

1. **Verifizieren:** Ist es echt? `curl -sS https://…/api/v1/health/ready` und
   `curl -sSI https://…/` (Bauzaun-401 ist normal). Kurzer Ausfall kann ein
   Deploy oder Neustart sein.
2. **Lage prüfen:** `docker compose … ps` (Container up/healthy?),
   `docker compose … logs --since 15m app worker` (Fehler?), `df -h /` (Platte
   voll?), `journalctl -u nidula-backup --since -1d` (Backup ok?).
3. **Container-Neustart** (kleinste Maßnahme), wenn ein einzelner Dienst hängt:
   `docker compose … restart <service>` bzw. `up -d --force-recreate <service>`.
4. **Rollback**, wenn ein frischer Deploy die Ursache ist: auf dem Server
   `git -C /opt/nidula log --oneline -5`, gewünschten Stand auschecken und
   `./deploy/deploy.sh` vom Entwicklungsrechner mit dem vorherigen Commit – oder
   das `nidula-app`-Image auf den letzten funktionierenden Stand zurücksetzen.
5. **Platte voll:** alte Backups/Logs prüfen (`/opt/nidula-backups`,
   `docker system df`), `docker system prune` (Vorsicht), Log-Rotation greift
   künftig automatisch.
6. **Isolieren** bei Verdacht auf Kompromittierung: Registrierung/Uploads über
   den Kill-Switch stoppen (`NIDULA_REGISTRATION=invite` steht schon; Uploads
   notfalls per Route/Firewall), ggf. Server aus dem Netz nehmen
   (Hetzner-Firewall auf nur eigene Admin-IP).
7. **Secrets rotieren** nach einem Vorfall: DB-Passwörter, `APP_KEY`,
   Bauzaun-Passwort, Kalender-Freigabe-Tokens (in der App rotierbar),
   ggf. SSH-Key. Reihenfolge + Auswirkungen vorher überlegen.
8. **Restore** aus Backup: siehe Backup-Abschnitt oben (Dump → DB), Medien aus
   Hetzner-Vollbackup. Restore vorher IMMER in einer Wegwerf-DB proben.
9. **Meldepflicht** (ab Stufe 2, echte Fremd-Daten): Datenschutzverletzung nach
   Art. 33 DSGVO ggf. binnen 72 h melden – im Zweifel dokumentieren + prüfen.

## Nützliche Kommandos (auf dem Server, in /opt/nidula/deploy)

```bash
docker compose -f docker-compose.prod.yml ps          # Status
docker compose -f docker-compose.prod.yml logs -f app # Logs (stderr)
docker compose -f docker-compose.prod.yml exec -T app php artisan about
docker compose -f docker-compose.prod.yml exec -T mysql \
  mysqldump -u nidula -p"$DB_PASSWORD" nidula > backup.sql   # Hand-Backup
```

## Bekannte Punkte / offen

- Mail läuft auf `MAIL_MAILER=log` (kein Versand) – Einladungen in der Beta
  über Link/Log; SMTP-Anbieter steht in docs/aufgaben.md.
- Medien liegen im `media`-Volume (lokal, über signierte URLs privat) –
  Umzug auf Hetzner Object Storage steht in docs/aufgaben.md.
- Demo-Daten/Seeds gibt es in Produktion NICHT (ADR-0025).
