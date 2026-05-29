# Deployment

`deploy/deploy.sh` deploys the committed `HEAD` to the xCloud host using a
release-directory + symlink strategy and PM2.

## What it does

1. `git archive HEAD` → tarball (heavy/non-deploy paths excluded via `.gitattributes`).
2. Upload + extract into `releases/<timestamp>-<sha>/`.
3. Source the server env file, then install deps and `next build`
   (`NEXT_PUBLIC_BASE_PATH=/v2`).
4. Atomically switch the `current` symlink.
5. `pm2 startOrReload ecosystem.config.cjs --update-env` + `pm2 save`.
6. Copy `.next/static` → `web/_next/static`, `public/.` → web root, and the
   PHP proxy → `web/index.php`.
7. Prune old releases (keep the newest `DEPLOY_KEEP_RELEASES`).
8. HTTP health check.

## Prerequisites

- **Local:** `git`, `ssh`, `scp`, `curl`. SSH access to the host (key in
  `ssh-agent`, `~/.ssh/config`, or via `DEPLOY_SSH_KEY`).
- **Server:** Node + PM2, and either `pnpm` (preferred, uses the committed
  `pnpm-lock.yaml`) or `npm`.
- **Server env file** at `$DEPLOY_APP_DIR/shared/.env.production` containing:
  ```
  NEXT_PUBLIC_SUPABASE_URL=...
  NEXT_PUBLIC_SUPABASE_ANON_KEY=...
  SUPABASE_SERVICE_ROLE_KEY=...   # server-only
  ```
  `NEXT_PUBLIC_*` are inlined at build time, so they must be present here.
  This file is **not** committed.

## Usage

```bash
# Full deploy of the current committed HEAD
./deploy/deploy.sh

# Preview the steps without touching the server
./deploy/deploy.sh --dry-run

# Roll back to the previous release
./deploy/deploy.sh --rollback
```

Override any default via environment variables (see the header of
`deploy.sh`), e.g.:

```bash
DEPLOY_SSH_HOST=1.2.3.4 DEPLOY_KEEP_RELEASES=10 ./deploy/deploy.sh
```

## Supabase migrations (run before deploying schema-dependent changes)

The app deploy does **not** touch the database. Apply migrations separately
with the Supabase CLI, pointed at the project:

```bash
supabase link --project-ref <ref>
supabase db push
npm run supabase:types   # regenerates src/types/supabase.ts
```

## CI (GitHub Actions)

A manual-dispatch workflow is provided at `.github/workflows/deploy.yml`
(it never runs automatically). It requires these repository secrets:

- `DEPLOY_SSH_KEY` — private key with access to the host
- `DEPLOY_SSH_HOST`, `DEPLOY_SSH_USER` (optional; defaults apply)

Trigger it from the Actions tab → “Deploy (manual)” → Run workflow.
