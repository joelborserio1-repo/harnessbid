# HarnessBid v2 Schema Review

Reviewed against the current Next.js/v0 frontend integration.

## Good Fit For Initial Reads

- Horse listings and marketplace listings are separate tables, matching the product split.
- Auctions can target horse listings or marketplace listings with a one-target constraint.
- Listing images support both horse and marketplace records.
- Categories are seeded and can drive marketplace navigation.
- Public RLS allows reads for published listings, visible auctions, active categories, and verified sellers.
- Sale events attach to exactly one seller target: either a seller account or an enterprise seller extension.
- Enquiries attach to exactly one public target: horse listing, marketplace listing, or sale event.

## Integration Risks

- Public listing reads require `status in ('published', 'under_offer', 'sold')` and `published_at is not null`. Seed data currently only creates categories, so the app will show branded empty states until listing rows are inserted.
- Public seller account reads require `verification_status = 'verified'` and `is_active = true`. If a listing references an unverified seller, the seller may not be visible through anon reads depending on query shape.
- Auction reads are public by auction status, but a useful auction card also needs a visible `horse_listing_id`, listing image, and seller record. Missing related records will be skipped in the current UI adapter.
- Listing `specs` is flexible `jsonb`; the frontend supports either `{ "Label": "Value" }` objects or `[{ "label": "...", "value": "..." }]` arrays.
- Payment tables are placeholders only and should remain disconnected until checkout, escrow, fees, and webhooks are designed.
- `payment_intents` currently allows more than one target because final payment context has not been designed; keep writes disconnected until that model is tightened.

## Current Integration Scope

- Read-only public Supabase queries.
- No bids, payments, checkout, enquiries, watchlists, seller writes, or auth mutations are connected.
- Empty, loading, and error surfaces remain frontend-only UI states.
