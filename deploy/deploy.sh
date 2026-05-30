#!/usr/bin/env bash
#
# HarnessBid v2 deploy script.
#
# Ships the committed git tree to the xCloud host, installs + builds it inside
# a timestamped release directory, atomically switches the `current` symlink,
# (re)starts the PM2 app, and syncs static assets + the PHP proxy into the web
# root. Safe to run locally or from CI.
#
# Usage:
#   deploy/deploy.sh                # full deploy of HEAD
#   deploy/deploy.sh --dry-run      # print what would happen, do nothing remote
#   deploy/deploy.sh --rollback     # switch `current` to the previous release
#
# Configuration is via environment variables (defaults match the project
# handoff). Secrets must NOT be committed — provide them via the shell/CI or a
# server-side env file (DEPLOY_ENV_FILE).
#
#   DEPLOY_SSH_USER       (default: harnessbid)
#   DEPLOY_SSH_HOST       (default: 160.22.78.31)
#   DEPLOY_SSH_PORT       (default: 22)
#   DEPLOY_SSH_KEY        (optional path to a private key, else ssh-agent/config)
#   DEPLOY_APP_DIR        (default: /home/harnessbid/harnessbid-v2)
#   DEPLOY_WEB_DIR        (default: /var/www/harnessbid.com/v2)
#   DEPLOY_PM2_APP        (default: harnessbid-v2)
#   DEPLOY_BASE_PATH      (default: /v2)
#   DEPLOY_PORT           (default: 3001)   # informational; set in ecosystem.config.cjs
#   DEPLOY_KEEP_RELEASES  (default: 5)
#   DEPLOY_ENV_FILE       (default: $DEPLOY_APP_DIR/shared/.env.production)
#   DEPLOY_HEALTHCHECK_URL(default: https://harnessbid.com/v2/)
#   DEPLOY_PKG_MANAGER    (default: auto -> pnpm if available, else npm)
#
set -euo pipefail

# --- configuration ---------------------------------------------------------
SSH_USER="${DEPLOY_SSH_USER:-harnessbid}"
SSH_HOST="${DEPLOY_SSH_HOST:-160.22.78.31}"
SSH_PORT="${DEPLOY_SSH_PORT:-22}"
SSH_KEY="${DEPLOY_SSH_KEY:-}"
APP_DIR="${DEPLOY_APP_DIR:-/home/harnessbid/harnessbid-v2}"
WEB_DIR="${DEPLOY_WEB_DIR:-/var/www/harnessbid.com/v2}"
PM2_APP="${DEPLOY_PM2_APP:-harnessbid-v2}"
BASE_PATH="${DEPLOY_BASE_PATH:-/v2}"
KEEP_RELEASES="${DEPLOY_KEEP_RELEASES:-5}"
ENV_FILE="${DEPLOY_ENV_FILE:-$APP_DIR/shared/.env.production}"
HEALTHCHECK_URL="${DEPLOY_HEALTHCHECK_URL:-https://harnessbid.com/v2/}"
PKG_MANAGER="${DEPLOY_PKG_MANAGER:-auto}"

DRY_RUN=0
ROLLBACK=0
for arg in "$@"; do
  case "$arg" in
    --dry-run) DRY_RUN=1 ;;
    --rollback) ROLLBACK=1 ;;
    -h|--help) grep '^#' "$0" | sed 's/^# \{0,1\}//'; exit 0 ;;
    *) echo "Unknown argument: $arg" >&2; exit 2 ;;
  esac
done

log() { printf '\033[0;34m[deploy]\033[0m %s\n' "$*"; }
err() { printf '\033[0;31m[deploy:error]\033[0m %s\n' "$*" >&2; }

SSH_OPTS=(-p "$SSH_PORT" -o StrictHostKeyChecking=accept-new)
[ -n "$SSH_KEY" ] && SSH_OPTS+=(-i "$SSH_KEY")
SSH_TARGET="$SSH_USER@$SSH_HOST"

run_remote() {
  if [ "$DRY_RUN" -eq 1 ]; then
    log "(dry-run) ssh $SSH_TARGET <<'remote script'"
    cat
    return 0
  fi
  ssh "${SSH_OPTS[@]}" "$SSH_TARGET" 'bash -s'
}

# --- rollback path ---------------------------------------------------------
if [ "$ROLLBACK" -eq 1 ]; then
  log "Rolling back to the previous release on $SSH_TARGET"
  run_remote <<REMOTE
set -euo pipefail
cd "$APP_DIR/releases"
PREV=\$(ls -1dt */ 2>/dev/null | sed -n '2p' | sed 's:/*\$::')
if [ -z "\$PREV" ]; then echo "No previous release to roll back to." >&2; exit 1; fi
ln -sfn "$APP_DIR/releases/\$PREV" "$APP_DIR/current"
cd "$APP_DIR/current"
NEXT_PUBLIC_BASE_PATH="$BASE_PATH" pm2 startOrReload ecosystem.config.cjs --update-env
pm2 save
echo "Rolled back to \$PREV"
REMOTE
  log "Rollback complete."
  exit 0
fi

# --- preflight -------------------------------------------------------------
command -v git >/dev/null || { err "git is required"; exit 1; }
command -v ssh >/dev/null || { err "ssh is required"; exit 1; }

REPO_ROOT="$(git rev-parse --show-toplevel)"
cd "$REPO_ROOT"

if ! git diff --quiet || ! git diff --cached --quiet; then
  log "WARNING: working tree has uncommitted changes — deploying committed HEAD only."
fi

SHA="$(git rev-parse --short HEAD)"
RELEASE="$(date +%Y%m%d%H%M%S)-$SHA"
REL_DIR="$APP_DIR/releases/$RELEASE"
TARBALL="/tmp/harnessbid-$RELEASE.tar.gz"

log "Release: $RELEASE"
log "Target : $SSH_TARGET:$REL_DIR"

# --- package committed tree (respects .gitattributes export-ignore) --------
log "Creating archive from git HEAD…"
git archive --format=tar.gz -o "$TARBALL" HEAD

if [ "$DRY_RUN" -eq 1 ]; then
  log "(dry-run) would scp $TARBALL -> $SSH_TARGET:/tmp/ and run the remote release script"
fi

# --- upload ----------------------------------------------------------------
if [ "$DRY_RUN" -eq 0 ]; then
  log "Uploading archive…"
  scp "${SSH_OPTS[@]/-p/-P}" "$TARBALL" "$SSH_TARGET:/tmp/" >/dev/null
fi
rm -f "$TARBALL"

# --- remote release --------------------------------------------------------
log "Running remote release…"
run_remote <<REMOTE
set -euo pipefail

REL_DIR="$REL_DIR"
APP_DIR="$APP_DIR"
WEB_DIR="$WEB_DIR"
BASE_PATH="$BASE_PATH"
ENV_FILE="$ENV_FILE"
KEEP_RELEASES="$KEEP_RELEASES"
TARBALL="/tmp/harnessbid-$RELEASE.tar.gz"

# 1. extract
mkdir -p "\$REL_DIR"
tar -xzf "\$TARBALL" -C "\$REL_DIR"
rm -f "\$TARBALL"
cd "\$REL_DIR"

# 2. load build/runtime env (NEXT_PUBLIC_* must exist at build time)
if [ -f "\$ENV_FILE" ]; then
  echo "[remote] sourcing \$ENV_FILE"
  set -a; . "\$ENV_FILE"; set +a
else
  echo "[remote] WARNING: \$ENV_FILE not found — NEXT_PUBLIC_SUPABASE_* may be missing at build."
fi
export NEXT_PUBLIC_BASE_PATH="\$BASE_PATH"

# 3. install + build (prefer pnpm with the committed lockfile)
PM="$PKG_MANAGER"
if [ "\$PM" = "auto" ]; then
  if command -v pnpm >/dev/null; then PM="pnpm"; else PM="npm"; fi
fi
echo "[remote] package manager: \$PM"
if [ "\$PM" = "pnpm" ]; then
  pnpm install --frozen-lockfile
  pnpm run build
else
  npm install --no-audit --no-fund
  npm run build
fi

# 4. atomic symlink switch
ln -sfn "\$REL_DIR" "\$APP_DIR/current"

# 5. (re)start PM2 from the current release
cd "\$APP_DIR/current"
NEXT_PUBLIC_BASE_PATH="\$BASE_PATH" pm2 startOrReload ecosystem.config.cjs --update-env
pm2 save

# 6. publish static assets + PHP proxy into the web root
mkdir -p "\$WEB_DIR/_next"
rm -rf "\$WEB_DIR/_next/static"
cp -a "\$REL_DIR/.next/static" "\$WEB_DIR/_next/static"
cp -a "\$REL_DIR/public/." "\$WEB_DIR/" 2>/dev/null || true
cp "\$REL_DIR/deploy/xcloud-v2-proxy.php" "\$WEB_DIR/index.php"
# Root deploys (empty base path) need an Apache rewrite so all non-static
# requests reach the proxy; under a sub-path the directory handler covers this.
if [ -z "\$BASE_PATH" ] && [ -f "\$REL_DIR/deploy/root.htaccess" ]; then
  cp "\$REL_DIR/deploy/root.htaccess" "\$WEB_DIR/.htaccess"
fi

# 7. prune old releases (keep the most recent \$KEEP_RELEASES)
cd "\$APP_DIR/releases"
ls -1dt */ 2>/dev/null | tail -n +\$((KEEP_RELEASES + 1)) | sed 's:/*\$::' | xargs -r rm -rf

echo "[remote] release \$REL_DIR is live"
REMOTE

# --- health check ----------------------------------------------------------
if [ "$DRY_RUN" -eq 0 ] && command -v curl >/dev/null; then
  log "Health check: $HEALTHCHECK_URL"
  CODE="$(curl -k -s -o /dev/null -w '%{http_code}' -L "$HEALTHCHECK_URL" || true)"
  if [ "$CODE" = "200" ]; then
    log "Health check OK (200)."
  else
    err "Health check returned HTTP $CODE — investigate (pm2 logs $PM2_APP)."
    exit 1
  fi
fi

log "Deploy complete: $RELEASE"
