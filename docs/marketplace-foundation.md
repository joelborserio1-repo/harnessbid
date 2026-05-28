# Marketplace Foundation

Marketplace inventory is intentionally modelled as normal `product` rows with normal `product_listing` rows. The differentiator is `product_meta.field = 'listing_kind'` with `value = 'marketplace'`.

This avoids changing the existing `product.type` enum and lets marketplace listings reuse auctions, classified listings, watchlists, member accounts, checkout and payment gateways.

## Public URLs

- `/marketplace/` shows the marketplace browse page.
- `/marketplace/{category}/` filters by marketplace category.
- `/marketplace/feed.json` returns latest live marketplace listings for HarnessLink widgets.

## Product Meta

Use these fields on marketplace products:

- `listing_kind`: `marketplace`
- `marketplace_category`: `sulkies`, `harness`, `carts`, `gear`, `transport`, `stable`
- `marketplace_condition`: `new`, `excellent`, `good`, `fair`, `parts`
- `marketplace_payment_mode`: `contact`, `deposit`, `full_payment`
- `marketplace_deposit_amount`: decimal amount
- `marketplace_fulfilment`: `pickup`, `freight`, `buyer_to_arrange`
- `marketplace_make`, `marketplace_model`, `marketplace_year`, `marketplace_dimensions`: free-form listing details

## Member Listing Flow

The member listing wizard now asks whether the seller is listing a horse or marketplace gear. When marketplace gear is selected, it saves `listing_kind = marketplace`, shows marketplace category/detail fields, and keeps `listing_type` as the existing `auction` or `classified` choice.
