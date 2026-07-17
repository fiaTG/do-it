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
- ufw: deny incoming, erlaubt nur OpenSSH/80/443. fail2ban aktiv (sshd-Jail).
- unattended-upgrades installiert (automatische Security-Updates).
- Hetzner-Backups aktiv (täglich, 7 Stände). TODO Stufe 2: zusätzlicher
  verschlüsselter DB-Dump nach extern + Restore-Probe (docs/aufgaben.md).

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

## Beta-Zugangsschutz (ADR-0025 Stufe 1)

1. **Bauzaun:** Basic-Auth vor der ganzen Seite (Benutzer `familie`,
   Passwort hat Timo). Entfernen: `basic_auth`-Block im `deploy/Caddyfile`
   löschen + deployen. Vor Native-Apps zwingend entfernen.
2. **Registrierung:** `NIDULA_REGISTRATION` in `.env.app` – stand nach dem
   Bootstrap (Timos Erst-Registrierung) auf `invite`: niemand ohne
   persönliche, E-Mail-gebundene Einladung. Nach Änderung:
   `docker compose -f docker-compose.prod.yml exec -T app php artisan config:cache`
   und `restart app worker scheduler`.

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
