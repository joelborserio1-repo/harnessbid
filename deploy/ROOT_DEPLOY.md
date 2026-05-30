# Deploying HarnessBid v2 to the site ROOT (same xCloud box)

This is the quick runbook for a **root** (`https://harnessbid.com/`) deploy via
`deploy/deploy.sh`, run from your machine. It assumes the live Supabase project
is already provisioned (it is) and that the root is free to use.

## One-time setup on the server

SSH to the box and create the server-side env file (NOT committed; `NEXT_PUBLIC_*`
are inlined at build time, so they must exist before the build runs):

```bash
ssh harnessbid@160.22.78.31
mkdir -p /home/harnessbid/harnessbid-v2/shared
cat > /home/harnessbid/harnessbid-v2/shared/.env.production <<'EOF'
NEXT_PUBLIC_SUPABASE_URL=https://ngscwqnvypxjwvpgqghg.supabase.co
NEXT_PUBLIC_SUPABASE_ANON_KEY=<anon/publishable key from Supabase → Settings → API>
SUPABASE_SERVICE_ROLE_KEY=<service_role/secret key — server only>
NEXT_PUBLIC_SITE_URL=https://harnessbid.com
CRON_SECRET=<generate: openssl rand -hex 32>
EOF
chmod 600 /home/harnessbid/harnessbid-v2/shared/.env.production
```

> `NEXT_PUBLIC_SITE_URL` has no `/v2` for a root deploy. Add Stripe keys later
> when you wire up payments.

## Deploy (from your machine, in the repo)

Root deploy = empty base path + web dir at the domain root:

```bash
DEPLOY_BASE_PATH="" \
DEPLOY_WEB_DIR="/var/www/harnessbid.com" \
DEPLOY_HEALTHCHECK_URL="https://harnessbid.com/" \
./deploy/deploy.sh
```

Tip: do a dry run first to see exactly what it will do without touching the box:

```bash
DEPLOY_BASE_PATH="" DEPLOY_WEB_DIR="/var/www/harnessbid.com" \
DEPLOY_HEALTHCHECK_URL="https://harnessbid.com/" ./deploy/deploy.sh --dry-run
```

What it does: archives committed HEAD → uploads → `npm/pnpm build` on the box →
atomic `current` symlink swap → `pm2 startOrReload` (port 3001) → copies static
assets + the PHP proxy (`index.php`) + `deploy/root.htaccess` (→ `.htaccess`)
into the web root → health-checks `https://harnessbid.com/`.

Rollback if needed:

```bash
DEPLOY_WEB_DIR="/var/www/harnessbid.com" ./deploy/deploy.sh --rollback
```

## Auction scheduler (cron) — set up once on the box

The auction sweep route is `/api/cron/auctions`, guarded by `CRON_SECRET`. Add a
system crontab entry (runs every minute; the RPC is idempotent and time-gated):

```bash
crontab -e
# add (substitute the same CRON_SECRET as in .env.production):
* * * * * curl -fsS "https://harnessbid.com/api/cron/auctions?secret=YOUR_CRON_SECRET" >/dev/null 2>&1
```

(Alternatively, pg_cron inside Supabase — see the commented schedule in
`supabase/migrations/202605290004_auction_bidding_engine.sql`.)

## First-run smoke test

- `https://harnessbid.com/` loads the marketplace home.
- `/auctions` shows the seeded live auctions; open one and confirm the bid panel
  updates in real time (Realtime is enabled on `auctions`/`bids`).
- Log in with `buyer@harnessbid.test` / `password123`; notifications bell works.
- Wait ~1–2 min and confirm a `scheduled` auction flips to `live` (cron working).
```
