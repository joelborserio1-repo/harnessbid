-- HarnessBid — EXTRA demo data (additive, idempotent). Run AFTER seed.sql.
-- Adds more login-able test users, sellers, realistic standardbred horse
-- listings + equipment, auctions, bids from several buyers, and images so the
-- front and back end can be exercised with a fuller catalogue.
--
-- All content is ORIGINAL (real sire/dam *lines* used the way the industry does,
-- but descriptions are written fresh — not copied from any platform).
-- LOCAL / STAGING / DEMO ONLY. Token columns set to '' (GoTrue requires non-null).

-- ===========================================================================
-- Extra login-able buyers (password "password123")
-- ===========================================================================
insert into auth.users (id, instance_id, aud, role, email, encrypted_password,
  email_confirmed_at, raw_app_meta_data, raw_user_meta_data, created_at, updated_at,
  confirmation_token, recovery_token, email_change_token_new, email_change,
  email_change_token_current, phone_change, phone_change_token, reauthentication_token)
values
  ('a0000000-0000-0000-0000-000000000010','00000000-0000-0000-0000-000000000000','authenticated','authenticated','bidder1@harnessbid.test', crypt('password123', gen_salt('bf')), now(), '{"provider":"email","providers":["email"]}', '{"full_name":"Casey Fontaine"}', now(), now(), '', '', '', '', '', '', '', ''),
  ('a0000000-0000-0000-0000-000000000011','00000000-0000-0000-0000-000000000000','authenticated','authenticated','bidder2@harnessbid.test', crypt('password123', gen_salt('bf')), now(), '{"provider":"email","providers":["email"]}', '{"full_name":"Morgan Pryce"}', now(), now(), '', '', '', '', '', '', '', ''),
  ('a0000000-0000-0000-0000-000000000012','00000000-0000-0000-0000-000000000000','authenticated','authenticated','bidder3@harnessbid.test', crypt('password123', gen_salt('bf')), now(), '{"provider":"email","providers":["email"]}', '{"full_name":"Reese Calloway"}', now(), now(), '', '', '', '', '', '', '', '')
on conflict (id) do nothing;

insert into auth.identities (id, user_id, identity_data, provider, provider_id, last_sign_in_at, created_at, updated_at)
select gen_random_uuid(), u.id, json_build_object('sub', u.id::text, 'email', u.email)::jsonb, 'email', u.id::text, now(), now(), now()
from auth.users u
where u.email in ('bidder1@harnessbid.test','bidder2@harnessbid.test','bidder3@harnessbid.test')
on conflict do nothing;

update public.profiles set role='buyer' where id in (
  select id from auth.users where email like 'bidder%@harnessbid.test'
);

-- ===========================================================================
-- Extra named sellers (studs / agents) — login-able
-- ===========================================================================
insert into auth.users (id, instance_id, aud, role, email, encrypted_password,
  email_confirmed_at, raw_app_meta_data, raw_user_meta_data, created_at, updated_at,
  confirmation_token, recovery_token, email_change_token_new, email_change,
  email_change_token_current, phone_change, phone_change_token, reauthentication_token)
select gen_random_uuid(), '00000000-0000-0000-0000-000000000000','authenticated','authenticated',
  'vendor'||g||'@harnessbid.test', crypt('password123', gen_salt('bf')), now(),
  '{"provider":"email","providers":["email"]}',
  json_build_object('full_name',(array['Aldebaran Park','Burwood Stud','Cobbitty Equine','Devon Meadows Racing','Empire Stallions','Fernleigh Lodge'])[g])::jsonb,
  now(), now(), '', '', '', '', '', '', '', ''
from generate_series(1,6) g
on conflict do nothing;

update public.profiles set role='seller' where id in (
  select id from auth.users where email like 'vendor%@harnessbid.test'
);

insert into public.seller_accounts (id, owner_profile_id, account_type, display_name, slug, bio, location_text, verification_status, is_active, verified_at)
select gen_random_uuid(), p.id, 'business', p.display_name,
  'vendor-' || lower(regexp_replace(p.display_name, '[^a-zA-Z0-9]+', '-', 'g')),
  (array[
    'Boutique standardbred nursery focused on staying pacers and trotters.',
    'Commercial broodmare band and yearling preparation since the 1990s.',
    'Performance-focused racing stable offering tried and proven racehorses.',
    'Family operation breeding and selling quality colonial-bred stock.',
    'Stallion station and bloodstock advisory for the southern hemisphere.',
    'Agistment, breaking and pre-training with a select sales draft each year.'
  ])[1 + (row_number() over (order by p.email)::int % 6)],
  (array['Menangle, NSW','Bendigo, VIC','Christchurch, NZ','Toowoomba, QLD','Cranbourne, VIC','Ballarat, VIC'])[1 + (row_number() over (order by p.email)::int % 6)],
  'verified', true, now()
from public.profiles p
where p.id in (select id from auth.users where email like 'vendor%@harnessbid.test')
on conflict (slug) do nothing;

-- ===========================================================================
-- Richer horse listings (original descriptions, real sire/dam lines)
-- ===========================================================================
insert into public.horse_listings (id, seller_account_id, sale_event_id, title, slug, status, sale_mode, currency, asking_price, short_description, description, location_text, breed, sex, gait, age_years, color, sire, dam, best_mile, featured_until, published_at, sold_at, lot_number, lot_order)
select
  gen_random_uuid(),
  sa.id,
  null,
  (array[
    'Bettors Reign','Lou''s Legacy','Captain''s Pride','Sundons Gift','Ideal Tribute',
    'Majestic Art','Rocknroll Heaven Bay','Always Be Sweet','Somebeach Star','American Dream Girl'
  ])[1 + (g % 10)] || ' (Lot ' || (100 + g) || ')',
  'xhorse-' || g,
  (array['published','published','published','published','sold','under_offer'])[1 + (g % 6)]::public.listing_status,
  (array['auction','buy_now'])[1 + (g % 2)]::public.sale_mode,
  'USD',
  case when g % 2 = 1 then 12000 + g * 850 else null end,
  (array[
    'Strong-gaited youngster with an excellent attitude and natural early speed.',
    'Tried winner ready to step up in grade — sound and racing in great order.',
    'Beautifully bred filly from a prolific producing family. Future broodmare prospect.',
    'Honest trotting type with good manners and a genuine will to win.'
  ])[1 + (g % 4)],
  (array[
    'Presented in outstanding order and working a treat at the trials. This one shows above-average gate speed and a high cruising tempo, with the temperament to match. Vet records and recent x-rays available to genuine buyers. An exciting prospect for a progressive stable.',
    'A racetrack-proven performer with multiple wins and consistent place form against quality fields. Eats well, travels well, and has never missed a beat. Suit an owner looking to step straight into the winners'' circle.',
    'From one of the breed''s most influential maternal families, this individual has the page to back up the looks. Correct, mobile and forward — exactly the kind of type that develops into a stakes-class performer or foundation broodmare.',
    'A genuine, no-fuss racehorse with a great constitution. Strong through the line and improving with every run. Ready to continue racing immediately or carry on as a breeding proposition down the track.'
  ])[1 + (g % 4)],
  (array['Menangle, NSW','Bendigo, VIC','Christchurch, NZ','Toowoomba, QLD','Cranbourne, VIC'])[1 + (g % 5)],
  'Standardbred',
  (array['colt','filly','gelding','mare','stallion'])[1 + (g % 5)]::public.horse_sex,
  (array['pacer','pacer','trotter','dual_gaited'])[1 + (g % 4)]::public.horse_gait,
  1 + (g % 9),
  (array['Bay','Brown','Black','Chestnut','Roan'])[1 + (g % 5)],
  (array['Bettors Delight','Sweet Lou','Captaintreacherous','Sundon','American Ideal','Art Major','Always B Miki','Somebeachsomewhere'])[1 + (g % 8)],
  (array['Reign Of Fire','Legacy Lady','Pride Of Place','Gift Horse','Tribute Belle','Artistic Flair','Bay Of Plenty','Sweet Surrender'])[1 + (g % 8)],
  case when g % 3 = 0 then '1:5' || (1 + g % 8) || '.' || (g % 9) else null end,
  case when g % 9 = 0 then now() + interval '21 days' else null end,
  case when (1 + (g % 6)) in (1,2,3,4,6) then now() - ((g * 2 || ' hours')::interval) else null end,
  case when (1 + (g % 6)) = 5 then now() - interval '3 days' else null end,
  (100 + g)::text,
  100 + g
from generate_series(1, 30) g
cross join lateral (
  select id from public.seller_accounts
  where slug like 'vendor-%' or slug like 'seed-seller-%'
  order by id offset (g % greatest(1,(select count(*) from public.seller_accounts where slug like 'vendor-%' or slug like 'seed-seller-%'))) limit 1
) sa
on conflict (slug) do nothing;

-- ===========================================================================
-- Richer equipment / marketplace listings (original copy)
-- ===========================================================================
insert into public.marketplace_listings (id, seller_account_id, category_id, title, slug, status, sale_mode, description, condition, currency, price, accepts_offers, shipping_available, location_text, featured_until, published_at, sold_at)
select
  gen_random_uuid(),
  sa.id,
  cat.id,
  (array[
    'Carbon-fibre race bike — lightweight & track-ready',
    'Set of quality leather race harness, well maintained',
    'Composite training jogger, near new condition',
    'Full set of pacing hopples with spare straps',
    'Aluminium float — twin-horse angle load',
    'Stable safety helmet, certified and barely used',
    'Heavy-duty walking machine, six-horse',
    'Bulk supply of premium racing feed & supplements'
  ])[1 + (g % 8)] || ' #' || g,
  'xmkt-' || g,
  (array['published','published','published','under_offer','sold'])[1 + (g % 5)]::public.listing_status,
  'buy_now'::public.sale_mode,
  (array[
    'Well looked-after gear from a working stable. Stored undercover and ready to go straight to work. Genuine reason for sale — upgrading. Inspection welcome and freight can be arranged at buyer''s cost.',
    'Quality piece with plenty of life left in it. Maintained to a high standard and cleaned after every use. Pickup preferred but happy to organise transport for the right buyer.',
    'Reliable, race-proven equipment suited to professional and hobby trainers alike. No issues, sold as-is. Photos show actual item — what you see is what you get.'
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
