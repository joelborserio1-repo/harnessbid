#!/usr/bin/env bash
#
# HarnessBid one-command redeploy — run ON THE BOX.
#
# Pulls the latest committed code, rebuilds, and restarts the PM2 app, using the
# server env file for NEXT_PUBLIC_* (inlined at build time). Replaces the manual
# pull/build/restart ritual.
#
#   cd /home/harnessbid/harnessbid-v2/src
#   ./deploy/redeploy.sh
#
# Branch defaults to claude/pensive-dirac-frJvE; override: BRANCH=main ./deploy/redeploy.sh
set -euo pipefail

SRC_DIR="${SRC_DIR:-/home/harnessbid/harnessbid-v2/src}"
ENV_FILE="${ENV_FILE:-/home/harnessbid/harnessbid-v2/shared/.env.production}"
BRANCH="${BRANCH:-claude/pensive-dirac-frJvE}"
PM2_APP="${PM2_APP:-harnessbid-v2}"

cd "$SRC_DIR"

echo "[redeploy] pulling $BRANCH (git will prompt for credentials if needed)…"
git pull origin "$BRANCH"

echo "[redeploy] now at: $(git log --oneline -1)"

echo "[redeploy] loading env + building…"
set -a; . "$ENV_FILE"; set +a
export NEXT_PUBLIC_BASE_PATH=""
npm install --no-audit --no-fund >/dev/null 2>&1 || true
npm run build

echo "[redeploy] restarting PM2 app $PM2_APP…"
pm2 restart "$PM2_APP" --update-env
pm2 status | grep "$PM2_APP" || pm2 status

echo "[redeploy] health check…"
sleep 2
CODE=$(curl -s -o /dev/null -w '%{http_code}' https://harnessbid.com/ || true)
echo "[redeploy] homepage HTTP $CODE"
[ "$CODE" = "200" ] && echo "[redeploy] ✓ done" || echo "[redeploy] ⚠ homepage not 200 — check pm2 logs $PM2_APP"
