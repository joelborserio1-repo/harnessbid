# Testing (Phase 20 scaffold → Phase 21 build-out)

## E2E (Playwright)

```bash
pnpm add -D @playwright/test
npx playwright install --with-deps
E2E_BASE_URL=http://localhost:3000 pnpm test:e2e
```

- Config: `playwright.config.ts` (chromium + mobile iPhone project).
- Specs: `e2e/*.spec.ts` (`smoke.spec.ts` covers public routes + admin guard).
- Phase 21 adds: register → onboarding → create listing → publish (admin
  approve) → bid → outbid notification → message thread.

## Load (k6)

```bash
K6_BASE_URL=https://staging.harnessbid.com k6 run tests/load/auctions.js
```

- Read-heavy browse scenario now; Phase 21 adds concurrent authenticated
  bidding against a seeded live auction to validate `place_bid` + anti-sniping.

## Seed data

Use the Supabase SQL editor (or a future `supabase/seed.sql`) to insert a
verified seller, a published horse listing, and a live auction. A scripted
seed helper is a Phase 21 deliverable.
