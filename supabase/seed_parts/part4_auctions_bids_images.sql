-- ===========================================================================
with new_auction_horses as (
  select h.id, row_number() over (order by h.created_at, h.id) as rn
  from public.horse_listings h
  where h.slug like 'xhorse-%' and h.sale_mode = 'auction' and h.status = 'published'
)
insert into public.auctions (id, horse_listing_id, sale_event_id, status, currency, starts_at, ends_at, soft_close_seconds, starting_bid, reserve_price, bid_increment, current_bid, bid_count, reserve_met)
select
  gen_random_uuid(), ah.id, null,
  (array['live','live','live','scheduled','extended'])[1 + (ah.rn % 5)]::public.auction_status,
  'USD',
  now() - interval '2 hours',
  now() + (((4 + ah.rn) || ' hours')::interval),
  300,
  9000 + ah.rn * 750,
  case when ah.rn % 3 = 0 then 30000 + ah.rn * 800 else null end,
  1000,
  9000 + ah.rn * 750 + (ah.rn % 5) * 1500,
  (ah.rn % 5),
  case when ah.rn % 3 = 0 then (9000 + ah.rn * 750 + (ah.rn % 5) * 1500) >= (30000 + ah.rn * 800) else false end
from new_auction_horses ah
on conflict (horse_listing_id) do nothing;

-- ===========================================================================
-- Bids from several buyers on the new live auctions
-- ===========================================================================
insert into public.bids (id, auction_id, bidder_profile_id, amount, max_proxy_amount, status)
select gen_random_uuid(), a.id, b.profile_id, a.current_bid, a.current_bid + 4000, 'winning'
from public.auctions a
join public.horse_listings h on h.id = a.horse_listing_id
cross join lateral (
  select id as profile_id from public.profiles
  where id in (
    'a0000000-0000-0000-0000-000000000004',
    'a0000000-0000-0000-0000-000000000010',
    'a0000000-0000-0000-0000-000000000011',
    'a0000000-0000-0000-0000-000000000012'
  )
  order by md5(a.id::text || id::text) limit 1
) b
where h.slug like 'xhorse-%' and a.status = 'live' and a.bid_count > 0
on conflict do nothing;

-- ===========================================================================
-- Images for the new listings (stock photos; next.config images.unoptimized)
-- ===========================================================================
delete from public.listing_images
where storage_path is null and (
  horse_listing_id in (select id from public.horse_listings where slug like 'xhorse-%')
  or marketplace_listing_id in (select id from public.marketplace_listings where slug like 'xmkt-%')
);

with horse_pics(idx, url) as (
  values
    (0, 'https://images.unsplash.com/photo-1553284965-83fd3e82fa5a?w=1200&q=80'),
    (1, 'https://images.unsplash.com/photo-1534773728080-33d31da27ae5?w=1200&q=80'),
    (2, 'https://images.unsplash.com/photo-1598974357801-cbca100e65d3?w=1200&q=80'),
    (3, 'https://images.unsplash.com/photo-1511994714008-b6d68a8b32a2?w=1200&q=80')
),
nh as (
  select id, row_number() over (order by created_at, id) - 1 as rn
  from public.horse_listings where slug like 'xhorse-%'
)
insert into public.listing_images (horse_listing_id, image_url, alt_text, position, is_primary)
select nh.id, p.url, 'Standardbred racing horse', 0, true
from nh join horse_pics p on p.idx = (nh.rn % 4);

with mkt_pics(idx, url) as (
  values
    (0, 'https://images.unsplash.com/photo-1605479808382-2d2d2c0a8f3f?w=1200&q=80'),
    (1, 'https://images.unsplash.com/photo-1568605117036-5fe5e7bab0b7?w=1200&q=80'),
    (2, 'https://images.unsplash.com/photo-1583511655857-d19b40a7a54e?w=1200&q=80')
),
nm as (
  select id, row_number() over (order by created_at, id) - 1 as rn
  from public.marketplace_listings where slug like 'xmkt-%'
)
insert into public.listing_images (marketplace_listing_id, image_url, alt_text, position, is_primary)
select nm.id, p.url, 'Harness racing equipment', 0, true
from nm join mkt_pics p on p.idx = (nm.rn % 3);

-- Summary
select
  (select count(*) from public.horse_listings where slug like 'xhorse-%') as extra_horses,
  (select count(*) from public.marketplace_listings where slug like 'xmkt-%') as extra_equipment,
  (select count(*) from public.auctions a join public.horse_listings h on h.id=a.horse_listing_id where h.slug like 'xhorse-%') as extra_auctions,
  (select count(*) from auth.users where email like 'bidder%@harnessbid.test' or email like 'vendor%@harnessbid.test') as extra_users;
