# Soft Launch Plan (Phase 21 → 22)

Goal: a controlled, invite-only launch with a handful of trusted sellers and a
small bidder pool, monitored closely, before public marketing.

## Phasing

1. **Internal dry run** — staging loaded with `supabase/seed.sql`; run the
   E2E + load suites; complete `LAUNCH_CHECKLIST.md`.
2. **Invite-only sellers (week 1)** — 3–5 trusted individual sellers + 1
   enterprise consignor (APG/Nutrien). Manually verify each; seed their first
   real listings; keep auctions short and supervised.
3. **Invite-only bidders (week 2)** — small pool of known buyers; run 1–2 live
   auctions end-to-end (bid → outbid → close → winner).
4. **Open registration (week 3+)** — once auction integrity + moderation hold.

## Invite-only onboarding (placeholder mechanism)

- No code gate yet. Operate invite-only by **not publishing** the marketing
  domain and onboarding sellers manually. If a hard gate is needed:
  - simplest: an `INVITE_CODE` env checked in `signUpAction`, or
  - an `allowlist` table (email) checked at signup.
- Sellers start `unverified`; an admin verifies via `/admin/enterprise`
  (enterprise) or by setting `verification_status` (individual) before their
  listings surface publicly.

## Enterprise seller onboarding

1. Seller registers → completes enterprise onboarding wizard (creates
   `seller_accounts` + `enterprise_sellers` `in_review`).
2. Admin reviews in `/admin/enterprise` → **Approve** (verifies the seller,
   sets enterprise `active`) or set **invoiced** billing / **fee exempt**.
3. Create their sale event in `/admin/sale-events`, set **featured** + banner,
   then **assign lots** (listing IDs + lot numbers).
4. Confirm the public event page `/sales/<slug>` renders the catalogue.

## Moderation workflow

- Queue: `/admin/listings` (pending/draft). Approve → published; reject /
  unpublish / archive / feature as needed. Every action is written to
  `moderation_logs`.
- Reports: `/admin/reports` (buyer-submitted) → reviewing / actioned / dismiss.
- Roles: `admin` (full) and `moderator` (content) via `is_staff()`; moderators
  cannot change roles or access payments.

## Support workflow (placeholder)

- Buyer/seller comms run through the in-app conversation system
  (`/dashboard/messages`); enquiries auto-open threads.
- Support inbox: route `support@harnessbid.com` to the team; for account/auction
  issues, an admin can inspect via `/admin` + Supabase.
- SLA target during soft launch: < 4h business-hours response.

## Monitoring during soft launch

- Watch logs for `env_*`, `action_failed:*`, `cron_auctions_*` (see
  `lib/logger.ts`). Wire Sentry/PostHog before opening registration.
- Verify the auction cron runs every minute and closes auctions on time.

## Rollback / recovery

- App: `deploy/deploy.sh --rollback` (previous release) or Vercel "Promote"
  of the last good deployment.
- DB: migrations are forward-only; take a Supabase backup/snapshot before each
  migration apply. For a bad data state, restore from snapshot.
- Disable bidding fast: set affected auctions to `paused`/`cancelled` via
  `/admin` or SQL; the engine refuses bids on non-live auctions.

## Go / no-go gates

- `rls_check.sql` returns zero rows.
- E2E smoke + seeded journey green on staging.
- Load test p95 < 1.8s (browse) / < 2s (bidding), error rate < 2%.
- Cron verified closing a test auction with correct winner.
