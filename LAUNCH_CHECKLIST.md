# HarnessBid Launch Checklist (Phase 21)

Sign off each section before soft launch. See `PRODUCTION_HARDENING.md` for
detail and `deploy/README.md` for the deploy procedure.

## 1. Database & migrations
- [ ] `supabase link --project-ref <ref>`
- [ ] `supabase db push` (applies migrations `202605280001` → `202605290009` in order)
- [ ] `npm run supabase:types` → commit regenerated `src/types/supabase.ts`
- [ ] (staging only) load `supabase/seed.sql` via `supabase db reset`
- [ ] Run `supabase/audit/rls_check.sql` — all three checks return **0 rows**

## 2. Realtime
- [ ] Enable Realtime (Database → Replication) for: `notifications`, `messages`, `auctions`, `bids`

## 3. Storage
- [ ] Confirm `listing-images` bucket exists, public read, 10 MB + image MIME limit
- [ ] Verify upload RLS: a user can only write under their own `auth.uid()` prefix

## 4. Environment (Production + Preview separated)
- [ ] `NEXT_PUBLIC_SUPABASE_URL`, `NEXT_PUBLIC_SUPABASE_ANON_KEY`
- [ ] `SUPABASE_SERVICE_ROLE_KEY` (server-only; Production only)
- [ ] `NEXT_PUBLIC_SITE_URL`, `CRON_SECRET`
- [ ] Stripe (when going commercial): `NEXT_PUBLIC_STRIPE_PUBLISHABLE_KEY`, `STRIPE_SECRET_KEY`, `STRIPE_WEBHOOK_SECRET`
- [ ] Boot log shows `env_ok` (no `env_missing_required` / `env_secret_leak`)

## 5. Scheduled jobs
- [ ] Enable a scheduler: pg_cron `process_auction_transitions` every minute **or** Vercel Cron (`vercel.json` → `/api/cron/auctions`)
- [ ] Confirm one scheduler only (avoid double execution); verify a closed auction assigns `winner_profile_id`

## 6. Security
- [ ] No `NEXT_PUBLIC_*` secret leakage (env check)
- [ ] `/admin` blocked for non-staff; `/dashboard`, `/account`, `/onboarding` require auth
- [ ] Cross-user isolation spot-checks: bids, conversations/messages, notifications, watchlists
- [ ] Stripe webhook rejects unverified payloads (until verification wired)

## 7. Performance
- [ ] Public pages load < 1.5s p95 (run `tests/load/auctions.js`)
- [ ] Decide ISR/`revalidate` for `/`, `/marketplace`, `/auctions`, `/sales` (currently dynamic)
- [ ] Image strategy reviewed (optimizer currently off)

## 8. Monitoring
- [ ] Wire Sentry (errors) + PostHog/analytics in `lib/logger.ts` placeholders
- [ ] Vercel log drain / Logtail configured

## 9. Edge / DNS (Cloudflare)
- [ ] Proxy domain; SSL Full (strict); HSTS
- [ ] Cache static, bypass `/api/*` `/dashboard/*` `/admin/*`
- [ ] WAF managed rules; rate rules on `/login` `/register` `/api/*`; Bot Fight Mode

## 10. Testing
- [ ] `npm run lint` (0 errors) · `npm run typecheck` · `npm run build`
- [ ] Playwright smoke (`pnpm test:e2e`) against staging
- [ ] Seeded journey spec passes (enable `e2e/journey.spec.ts`)
- [ ] k6 bidding load (`tests/load/bidding.js`) — no errors, p95 < 2s

## 11. Manual QA (mobile + desktop)
- [ ] Register → onboarding (individual + enterprise) → dashboard
- [ ] Create listing (auction / buy-now / marketplace) with image upload → admin approve → public
- [ ] Bid → outbid notification → reserve/anti-snipe behaviour
- [ ] Watchlist toggle, enquiry → conversation reply, notifications bell
- [ ] Sale event landing + catalogue filters; seller storefront
- [ ] Admin: moderation, enterprise approval, reports, sale events, categories

## 12. Go-live
- [ ] Deploy (`deploy/deploy.sh` or Vercel) to production
- [ ] Post-deploy health check (`/`, `/sales`, `/api/cron/auctions` GET)
- [ ] Soft launch to invited sellers; monitor logs + cron for 24–48h
- [ ] Rollback ready: `deploy/deploy.sh --rollback`
