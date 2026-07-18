#!/usr/bin/env bash
# Nächtlicher MySQL-Dump (ADR-0025): konsistent (--single-transaction),
# komprimiert, 14 Stände, nur root/nidula lesbar. Läuft per systemd-Timer
# (nidula-backup.timer, 03:30) – Einrichtung siehe docs/deploy-hetzner.md.
# Ergänzt die täglichen Hetzner-Vollbackups um punktgenaue DB-Wiederherstellung;
# die Fotos (media-Volume) deckt das Hetzner-Vollbackup ab.
set -euo pipefail

cd /opt/nidula/deploy
# NICHT `source .env`: der Bauzaun-Hash enthält $$, das würde die Shell expandieren.
DB_PASSWORD=$(grep '^DB_PASSWORD=' .env | cut -d= -f2)

BACKUP_DIR=/opt/nidula-backups
mkdir -p "$BACKUP_DIR"
FILE="$BACKUP_DIR/nidula-$(date +%F-%H%M).sql.gz"

docker compose -f docker-compose.prod.yml exec -T mysql \
  mysqldump --single-transaction --routines --no-tablespaces \
  -u nidula -p"$DB_PASSWORD" nidula | gzip > "$FILE"
chmod 600 "$FILE"

# Sanity: ein leerer/abgebrochener Dump soll auffallen (Timer meldet Fehler).
[ "$(stat -c%s "$FILE")" -gt 1000 ] || { echo "Dump verdächtig klein: $FILE" >&2; exit 1; }

# Die 14 neuesten behalten
ls -1t "$BACKUP_DIR"/nidula-*.sql.gz | tail -n +15 | xargs -r rm --

echo "Backup ok: $FILE ($(du -h "$FILE" | cut -f1))"
