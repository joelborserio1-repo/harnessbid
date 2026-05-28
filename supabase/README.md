# HarnessBid v2 Supabase Schema

Migrations live in `supabase/migrations`.

## Files

- `202605280001_harnessbid_v2_schema.sql` creates the core tables, enums, indexes, triggers, constraints, and RLS policies.
- `202605280002_seed_categories.sql` seeds the first-pass horse and marketplace categories from the v0 UI.

## Design Notes

- `profiles` maps one-to-one with `auth.users`.
- `seller_accounts` is the reusable seller identity for individual, business, and enterprise sellers.
- `enterprise_sellers` extends seller accounts for larger vendors, sale operators, and managed accounts.
- `horse_listings` and `marketplace_listings` stay separate because horse auctions need pedigree, race, and veterinary fields while equipment/services need condition, shipping, and product fields.
- `auctions` can target either a horse listing or a marketplace listing, enforced with `num_nonnulls(...) = 1`.
- `watchlists`, `listing_images`, and `enquiries` use one-target constraints so future UI surfaces can support horse, marketplace, and sale-event assets without ambiguous records.
- `payment_intents` is intentionally looser while payments remain placeholders, so later checkout design can decide whether an auction payment should also carry listing context.
- Payment tables are placeholders only. They are deliberately provider-neutral and do not configure Stripe, webhooks, checkout, escrow, or payouts yet.

## RLS Shape

- Public users can read published listings, visible auctions, active categories, verified seller accounts, and public listing images.
- Authenticated users manage their own profile, watchlist, enquiries, and bids.
- Seller owners manage their own seller accounts, listings, images, sale events, auctions, and payment placeholders.
- Admin checks are centralized in `public.is_admin()`.
