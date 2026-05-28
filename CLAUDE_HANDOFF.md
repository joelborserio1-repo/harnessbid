# HarnessBid v2 Claude Handoff

Workspace packaged from:

`/Users/joel/Documents/Codex/2026-05-28/harnessbid-v2-ux-ui-basic-sheets`

## Current Status

- Next.js HarnessBid v2 frontend prototype is connected to Supabase read queries in phases.
- xCloud live preview is served at `https://harnessbid.com/v2/`.
- PM2 app name on xCloud: `harnessbid-v2`.
- Server user/path from earlier successful deploys:
  - SSH user: `harnessbid`
  - Host: `160.22.78.31`
  - Site path: `/var/www/harnessbid.com/v2`
  - App releases: `/home/harnessbid/harnessbid-v2/releases`
  - Current symlink: `/home/harnessbid/harnessbid-v2/current`

## Latest Local Source Changes

- Homepage hero image now uses `public/thekingman.jpg`.
- Hero overlay was changed to `bg-primary opacity-30`.
- Hero headline/supporting text was returned to white foreground text for readability over the overlay.
- Header/footer HarnessBid wordmark links were changed to base-path-aware `/v2/` anchors for xCloud.
- Homepage no longer returns a full-page Supabase preview error state when preview reads fail.

## Important Live Deploy Note

The latest 30% overlay source change is in this zip and the remote build already passed in:

`/home/harnessbid/harnessbid-v2/releases/202605290_hero_overlay`

However, SSH became unavailable before the final release switch/PM2 restart could be run. The live site may still be on:

`/home/harnessbid/harnessbid-v2/releases/202605290_hero_contrast`

## Final Server Commands To Finish Overlay Deploy

Once SSH works, run:

```bash
set -e
RELEASE=/home/harnessbid/harnessbid-v2/releases/202605290_hero_overlay
ln -sfn "$RELEASE" /home/harnessbid/harnessbid-v2/current
cd "$RELEASE"

NEXT_PUBLIC_BASE_PATH=/v2 pm2 delete harnessbid-v2 || true
NEXT_PUBLIC_BASE_PATH=/v2 pm2 start ecosystem.config.cjs
pm2 save

mkdir -p /var/www/harnessbid.com/v2/_next
rm -rf /var/www/harnessbid.com/v2/_next/static
cp -a "$RELEASE/.next/static" /var/www/harnessbid.com/v2/_next/static
cp -a "$RELEASE/public/." /var/www/harnessbid.com/v2/
cp "$RELEASE/deploy/xcloud-v2-proxy.php" /var/www/harnessbid.com/v2/index.php
```

Verify:

```bash
curl -k -L https://harnessbid.com/v2/
curl -k -L https://harnessbid.com/v2/thekingman.jpg
pm2 describe harnessbid-v2
```

## Supabase Notes

- Migrations live in `supabase/migrations/`.
- Supabase CLI and local package tooling were unavailable in the Codex local environment, so generated Supabase types were not produced.
- `src/types/README.md` documents the generation command.
- Existing manual types remain under `lib/supabase/database.types.ts`.

## Required Env Vars

```bash
NEXT_PUBLIC_SUPABASE_URL=
NEXT_PUBLIC_SUPABASE_ANON_KEY=
SUPABASE_SERVICE_ROLE_KEY=
```

`SUPABASE_SERVICE_ROLE_KEY` must only be used server-side and never exposed client-side.
