#!/usr/bin/env bash
# Nidula-Deploy (ADR-0025): SPA lokal bauen, Code auf den Server syncen,
# dort Image bauen und Container hochziehen, Migrationen ausführen.
# Aufruf:  NIDULA_DOMAIN=... [NIDULA_SSH=nidula@IP] ./deploy/deploy.sh
set -euo pipefail

REPO_DIR="$(cd "$(dirname "$0")/.." && pwd)"
NIDULA_SSH="${NIDULA_SSH:-nidula@167.233.64.98}"
NIDULA_SSH_KEY="${NIDULA_SSH_KEY:-$HOME/.ssh/nidula_hetzner}"
TARGET_DIR="/opt/nidula"

if [[ -z "${NIDULA_DOMAIN:-}" ]]; then
  echo "FEHLER: NIDULA_DOMAIN setzen (z. B. NIDULA_DOMAIN=nidula.example ./deploy/deploy.sh)" >&2
  exit 1
fi

SSH=(ssh -i "$NIDULA_SSH_KEY" "$NIDULA_SSH")

echo "==> 1/4 SPA bauen (VITE_API_URL=https://${NIDULA_DOMAIN}/api/v1)"
(cd "$REPO_DIR/frontend" && VITE_API_URL="https://${NIDULA_DOMAIN}/api/v1" npm run build)

echo "==> 2/4 Code + dist nach ${NIDULA_SSH}:${TARGET_DIR} syncen"
rsync -az --delete \
  --exclude '.git' \
  --exclude 'node_modules' \
  --exclude 'frontend/android' --exclude 'frontend/ios' \
  --exclude 'backend/vendor' \
  --exclude 'backend/.env' --exclude 'backend/.env.*' \
  --exclude 'backend/storage' \
  --exclude 'deploy/.env' --exclude 'deploy/.env.app' \
  -e "ssh -i $NIDULA_SSH_KEY" \
  "$REPO_DIR/" "$NIDULA_SSH:$TARGET_DIR/"

echo "==> 3/4 Image bauen + Container hochziehen"
# restart caddy: compose erkennt Änderungen an der bind-gemounteten Caddyfile
# nicht, und 'caddy reload' per exec griff real nicht zuverlässig (2026-07-18).
"${SSH[@]}" "cd $TARGET_DIR/deploy \
  && docker compose -f docker-compose.prod.yml build --pull app \
  && docker compose -f docker-compose.prod.yml up -d --remove-orphans \
  && docker compose -f docker-compose.prod.yml restart caddy"

echo "==> 4/4 Migrationen + Katalog-Seed + Caches"
# CatalogSeeder = App-Katalog + Läden (idempotent, KEINE Demo-Daten). Muss
# laufen, sonst gibt es keine auswählbaren Apps/Shops (Prod-Bug 2026-07-17).
"${SSH[@]}" "cd $TARGET_DIR/deploy \
  && docker compose -f docker-compose.prod.yml exec -T app php artisan migrate --force \
  && docker compose -f docker-compose.prod.yml exec -T app php artisan db:seed --class=CatalogSeeder --force \
  && docker compose -f docker-compose.prod.yml exec -T app php artisan config:cache \
  && docker compose -f docker-compose.prod.yml exec -T app php artisan route:cache"

echo "==> Fertig: https://${NIDULA_DOMAIN}"
