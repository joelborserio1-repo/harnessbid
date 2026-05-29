# Production Hardening (Phase 20)

Operational reference for launching HarnessBid. Code-level hardening is
implemented; infrastructure items below are configuration steps.

## Environment

- Validation: `lib/env.ts` + `instrumentation.ts` log missing/leaked vars at
  boot (non-fatal). Required: `NEXT_PUBLIC_SUPABASE_URL`,
  `NEXT_PUBLIC_SUPABASE_ANON_KEY`. Optional/feature: `SUPABASE_SERVICE_ROLE_KEY`,
  `STRIPE_*`, `CRON_SECRET`, `NEXT_PUBLIC_SITE_URL`.
- Only `NEXT_PUBLIC_*` reach the browser. Service role / Stripe secret / cron
  secret are server-only; `lib/supabase/admin.ts` and `lib/payments/service.ts`
  use `import "server-only"` so a client import fails the build.
- Vercel: set Production + Preview env separately; never expose service role in
  Preview to untrusted PRs.

## Security review summary

- **Auth/routes:** `proxy.ts` guards `/dashboard`, `/account`, `/onboarding`,
  `/admin`. Admin additionally guarded server-side via `requireStaff()` in the
  admin layout — never client-only.
- **Server actions:** all re-check auth + ownership; mutations run through RLS
  (no service role on the client). Listing/auction/message/enquiry/report/admin
  actions verified.
- **RPCs:** bidding + auction lifecycle are `SECURITY DEFINER` with internals
  revoked from `public`; only `place_bid`, `close_auction_if_ended`,
  `get_auction_bid_history` are granted to `authenticated`/`anon`.
  `process_auction_transitions` is granted only to `service_role` (cron).
- **Storage:** `listing-images` bucket — public read; writes restricted to the
  uploader's own `auth.uid()` path prefix; 10 MB + image MIME allowlist.
- **Webhooks:** `/api/stripe/webhook` never trusts unverified payloads
  (503/501 until signature verification is wired).
- **Realtime:** subscriptions are auth-scoped by filter and cleaned up on
  unmount; degrade gracefully if Realtime is disabled.

### RLS audit (tables → policy intent)

| Table | Public read | Owner/participant write | Staff |
|---|---|---|---|
| profiles | authenticated | self | — |
| seller_accounts | verified+active (public) | owner | staff read |
| enterprise_sellers | active (public) | owner | staff manage |
| horse/marketplace listings | published only | owner | staff manage |
| auctions | visible statuses | owner (via listing) | staff manage |
| bids | visible auctions | own (insert via RPC) | admin |
| listing_images | published listings | owner | — |
| watchlists | — | own | — |
| enquiries | participants | sender insert | — |
| conversations / messages | participants | participants | staff read |
| notifications | own | own | — |
| saved_searches | own | own | — |
| listing_reports | reporter/staff | reporter insert | staff manage |
| moderation_logs | staff | staff insert | staff |
| invoices / payouts / auction_deposits | owner | (admin/bidder) | staff manage |

> Action item: run an automated RLS-enabled check (every `public` table has
> `rowsecurity = true`) before launch.

## Rate limiting

- `lib/rate-limit.ts` — fixed-window per-user limiter applied to message sends
  and bidding; enquiries also have a DB-window check (Phase 14A.5).
- **Per-instance** (serverless) — first line only. For strict limits back with
  Upstash/Redis or a Postgres counter, and add Cloudflare rate rules (below).

## Cron / scheduled jobs

- `process_auction_transitions()` flips scheduled→live and closes ended
  auctions (winner + notifications); idempotent + time-gated → safe to retry.
- Trigger options (pick one):
  - **pg_cron** (recommended): `select cron.schedule('auction-transitions','* * * * *', $$select public.process_auction_transitions();$$);`
  - **Vercel Cron**: `vercel.json` schedules `/api/cron/auctions` every minute
    (Vercel sends `Authorization: Bearer $CRON_SECRET`).
  - Any external scheduler with `x-cron-secret` / `?secret=`.
- Lazy close also runs on auction view (`close_auction_if_ended`).

## Cloudflare / DNS (config steps — not applied here)

- Proxy `harnessbid.com` + `www` through Cloudflare (orange cloud).
- SSL/TLS: Full (strict). HSTS enabled (also set by app headers).
- Cache: bypass for `/api/*`, `/dashboard/*`, `/admin/*`; cache static assets
  (`/_next/static/*`, images) aggressively.
- WAF: enable managed ruleset; rate rules on `/login`, `/register`,
  `/api/*`, and bid endpoints (e.g. 30 req/min/IP).
- Bot Fight Mode on; allow known good crawlers (sitemap/robots provided).

## Performance

- Detail queries (`getHorseAuction`, `getMarketplaceListing`,
  `getSellerStorefront`) wrapped at the page level in React `cache()` so
  `generateMetadata` + render share one fetch (no double DB hit).
- Security headers + `poweredByHeader:false` + `reactStrictMode` in
  `next.config.mjs`.
- Most authed pages are `force-dynamic` (correct). **Candidate for ISR/caching:**
  public `/`, `/marketplace`, `/auctions`, `/sales`, listing/event detail —
  add `revalidate` once data volume warrants (currently dynamic for freshness).
- Images: `images.unoptimized:true` for now (Supabase public URLs). Enable the
  optimizer with a remote pattern when a CDN/loader is chosen.

## Deployment

- `deploy/deploy.sh` (xCloud/PM2) or Vercel. Build is green (`next build`).
- Apply migrations in order (`supabase db push`) then `npm run supabase:types`.
- Enable Realtime on `notifications`, `messages`, `auctions`, `bids`.

## Remaining production risks

1. Migrations + types not yet applied/generated against the live project.
2. Stripe webhook verification + real checkout not implemented (placeholders).
3. Rate limiting is per-instance until backed by a shared store + Cloudflare.
4. No external error monitoring wired (Sentry/PostHog placeholders in
   `lib/logger.ts`).
5. Image optimization disabled; large uploads served unoptimized.
