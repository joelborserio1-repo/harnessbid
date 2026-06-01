-- ===========================================================================
insert into public.marketplace_listings (id, seller_account_id, category_id, title, slug, status, sale_mode, description, condition, currency, price, accepts_offers, shipping_available, location_text, featured_until, published_at, sold_at)
select
  gen_random_uuid(),
  sa.id,
  cat.id,
  (array[
    'Carbon-fibre race bike - lightweight & track-ready',
    'Set of quality leather race harness, well maintained',
    'Composite training jogger, near new condition',
    'Full set of pacing hopples with spare straps',
    'Aluminium float - twin-horse angle load',
    'Stable safety helmet, certified and barely used',
    'Heavy-duty walking machine, six-horse',
    'Bulk supply of premium racing feed & supplements'
  ])[1 + (g % 8)] || ' #' || g,
  'xmkt-' || g,
  (array['published','published','published','under_offer','sold'])[1 + (g % 5)]::public.listing_status,
  'buy_now'::public.sale_mode,
  (array[
    'Well looked-after gear from a working stable. Stored undercover and ready to go straight to work. Genuine reason for sale - upgrading. Inspection welcome and freight can be arranged at buyer''s cost.',
    'Quality piece with plenty of life left in it. Maintained to a high standard and cleaned after every use. Pickup preferred but happy to organise transport for the right buyer.',
    'Reliable, race-proven equipment suited to professional and hobby trainers alike. No issues, sold as-is. Photos show actual item - what you see is what you get.'
  ])[1 + (g % 3)],
  (array['new','excellent','good','used','fair'])[1 + (g % 5)]::public.marketplace_condition,
  'USD',
  350 + g * 145,
  (g % 2 = 0),
  (g % 3 <> 0),
  (array['Menangle, NSW','Bendigo, VIC','Christchurch, NZ','Toowoomba, QLD','Cranbourne, VIC'])[1 + (g % 5)],
  case when g % 7 = 0 then now() + interval '14 days' else null end,
  now() - ((g || ' hours')::interval),
  case when (1 + (g % 5)) = 5 then now() - interval '1 day' else null end
from generate_series(1, 20) g
cross join lateral (
  select id from public.seller_accounts
  where slug like 'vendor-%' or slug like 'seed-seller-%'
  order by id offset (g % greatest(1,(select count(*) from public.seller_accounts where slug like 'vendor-%' or slug like 'seed-seller-%'))) limit 1
) sa
cross join lateral (
  select id from public.categories
  where category_type in ('marketplace','service') and is_active
  order by sort_order offset (g % greatest(1,(select count(*) from public.categories where category_type in ('marketplace','service') and is_active))) limit 1
) cat
on conflict (slug) do nothing;

-- ===========================================================================
-- Auctions for the new published auction-mode horses
