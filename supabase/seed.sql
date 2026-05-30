-- HarnessBid realistic seed data (Phase 21).
--
-- LOCAL / STAGING ONLY. Runs with `supabase db reset` (after all migrations).
-- Produces ~40 horse listings + ~24 marketplace listings across many sellers,
-- 2 enterprise sale events, live/scheduled/closed auctions (incl. reserve-not-
-- met + completed sales), bids, enquiries (-> conversations), saved searches
-- and notifications. Idempotent on unique keys.
--
-- Test logins (password "password123"):
--   admin@harnessbid.test | seller@harnessbid.test |
--   enterprise@harnessbid.test | buyer@harnessbid.test

-- ===========================================================================
-- Named auth users (login-able) + identities
-- ===========================================================================
insert into auth.users (id, instance_id, aud, role, email, encrypted_password,
  email_confirmed_at, raw_app_meta_data, raw_user_meta_data, created_at, updated_at)
values
  ('a0000000-0000-0000-0000-000000000001','00000000-0000-0000-0000-000000000000','authenticated','authenticated','admin@harnessbid.test', crypt('password123', gen_salt('bf')), now(), '{"provider":"email","providers":["email"]}', '{"full_name":"HarnessBid Admin"}', now(), now()),
  ('a0000000-0000-0000-0000-000000000002','00000000-0000-0000-0000-000000000000','authenticated','authenticated','seller@harnessbid.test', crypt('password123', gen_salt('bf')), now(), '{"provider":"email","providers":["email"]}', '{"full_name":"Riverside Stables"}', now(), now()),
  ('a0000000-0000-0000-0000-000000000003','00000000-0000-0000-0000-000000000000','authenticated','authenticated','enterprise@harnessbid.test', crypt('password123', gen_salt('bf')), now(), '{"provider":"email","providers":["email"]}', '{"full_name":"APG Sales"}', now(), now()),
  ('a0000000-0000-0000-0000-000000000004','00000000-0000-0000-0000-000000000000','authenticated','authenticated','buyer@harnessbid.test', crypt('password123', gen_salt('bf')), now(), '{"provider":"email","providers":["email"]}', '{"full_name":"Jordan Buyer"}', now(), now())
on conflict (id) do nothing;

insert into auth.identities (id, user_id, identity_data, provider, provider_id, last_sign_in_at, created_at, updated_at)
select gen_random_uuid(), u.id, json_build_object('sub', u.id::text, 'email', u.email)::jsonb, 'email', u.id::text, now(), now(), now()
from auth.users u
where u.email in ('admin@harnessbid.test','seller@harnessbid.test','enterprise@harnessbid.test','buyer@harnessbid.test')
on conflict do nothing;

-- Bulk seller users (owners only; not used for login).
insert into auth.users (id, instance_id, aud, role, email, encrypted_password,
  email_confirmed_at, raw_app_meta_data, raw_user_meta_data, created_at, updated_at)
select gen_random_uuid(), '00000000-0000-0000-0000-000000000000','authenticated','authenticated',
  'bulkseller'||g||'@harnessbid.test', crypt('password123', gen_salt('bf')), now(),
  '{"provider":"email","providers":["email"]}',
  json_build_object('full_name',(array['Hunter Valley Bloodstock','Southern Cross Stud','Lincoln Farms','Yirribee Pacing','Woodlands Stud','Alabar Australia','Emu Park Stud','Tara Lodge'])[g])::jsonb,
  now(), now()
from generate_series(1,8) g
on conflict do nothing;

-- Roles / display names (profiles auto-created by trigger).
update public.profiles set role='admin', display_name='HarnessBid Admin', full_name='HarnessBid Admin' where id='a0000000-0000-0000-0000-000000000001';
update public.profiles set role='seller', display_name='Riverside Stables', full_name='Riverside Stables' where id='a0000000-0000-0000-0000-000000000002';
update public.profiles set role='seller', display_name='APG Sales', full_name='APG Sales' where id='a0000000-0000-0000-0000-000000000003';
update public.profiles set role='buyer', display_name='Jordan Buyer', full_name='Jordan Buyer' where id='a0000000-0000-0000-0000-000000000004';
update public.profiles set role='seller' where id in (select id from auth.users where email like 'bulkseller%@harnessbid.test');

-- ===========================================================================
-- Seller accounts
-- ===========================================================================
insert into public.seller_accounts (id, owner_profile_id, account_type, display_name, slug, bio, location_text, verification_status, is_active, verified_at)
values
  ('b0000000-0000-0000-0000-000000000001','a0000000-0000-0000-0000-000000000002','individual','Riverside Stables','riverside-stables','Standardbred breaking and pre-training.','Menangle, NSW','verified', true, now()),
  ('b0000000-0000-0000-0000-000000000002','a0000000-0000-0000-0000-000000000003','enterprise','APG Sales','apg-sales','Australian Pacing Gold premier yearling sales.','Melbourne, VIC','verified', true, now())
on conflict (id) do nothing;

insert into public.seller_accounts (id, owner_profile_id, account_type, display_name, slug, bio, location_text, verification_status, is_active, verified_at)
select gen_random_uuid(), p.id, 'individual', p.display_name,
  'seed-seller-' || row_number() over (order by p.email),
  'Standardbred sales, agistment and pre-training.',
  (array['Menangle, NSW','Melbourne, VIC','Auckland, NZ','Brisbane, QLD'])[1 + (row_number() over (order by p.email)::int % 4)],
  'verified', true, now()
from public.profiles p
where p.id in (select id from auth.users where email like 'bulkseller%@harnessbid.test')
on conflict (slug) do nothing;

insert into public.enterprise_sellers (id, seller_account_id, legal_name, trading_name, tier, onboarding_status)
values ('c0000000-0000-0000-0000-000000000001','b0000000-0000-0000-0000-000000000002','Australian Pacing Gold Pty Ltd','APG Sales','premier','active')
on conflict (id) do nothing;

-- ===========================================================================
-- Sale events (APG + Nutrien)
-- ===========================================================================
insert into public.sale_events (id, enterprise_seller_id, seller_account_id, name, slug, event_type, status, description, timezone, starts_at, ends_at, featured, sort_order)
values
  ('10000000-0000-0000-0000-000000000001','c0000000-0000-0000-0000-000000000001',null,'APG Yearling Sale 2026','apg-yearling-sale-2026','online_auction','live','Premier Australian Pacing Gold yearling catalogue.','Australia/Melbourne', now() - interval '1 day', now() + interval '6 days', true, 1),
  ('10000000-0000-0000-0000-000000000002', null,'b0000000-0000-0000-0000-000000000001','Nutrien Harness Mixed Sale','nutrien-harness-mixed-sale','timed_auction','scheduled','Mixed-age dispersal and racing stock sale.','Australia/Sydney', now() + interval '10 days', now() + interval '17 days', true, 2)
on conflict (id) do nothing;

-- ===========================================================================
-- Horse listings (bulk, varied)
-- ===========================================================================
insert into public.horse_listings (id, seller_account_id, sale_event_id, title, slug, status, sale_mode, currency, asking_price, short_description, description, location_text, breed, sex, gait, age_years, color, sire, dam, best_mile, featured_until, published_at, sold_at, lot_number, lot_order)
select
  gen_random_uuid(),
  sa.id,
  case when g % 4 = 0 then '10000000-0000-0000-0000-000000000001'::uuid else null end,
  (array['Bettor Dream','Classic Pace','Northern Light','Sweet Victory','Major Custom','Art Major Magic'])[1 + (g % 6)] || ' (' || g || ')',
  'seed-horse-' || g,
  (array['published','published','published','sold','draft'])[1 + (g % 5)]::public.listing_status,
  (array['auction','buy_now'])[1 + (g % 2)]::public.sale_mode,
  'USD',
  case when g % 2 = 1 then 9000 + g * 300 else null end,
  'Quality standardbred prospect with strong residual value.',
  repeat('Exceptional conformation and an elite maternal family. ', 6),
  (array['Menangle, NSW','Melbourne, VIC','Auckland, NZ','Brisbane, QLD'])[1 + (g % 4)],
  'Standardbred',
  (array['colt','filly','gelding','mare','stallion'])[1 + (g % 5)]::public.horse_sex,
  (array['pacer','trotter'])[1 + (g % 2)]::public.horse_gait,
  1 + (g % 8),
  (array['Bay','Brown','Black','Chestnut'])[1 + (g % 4)],
  (array['Bettors Delight','Sweet Lou','Captaintreacherous','Sundon','American Ideal'])[1 + (g % 5)],
  (array['Dream Of Glory','Gift Of Gab','Classic Lady','Northern Star','Velocity'])[1 + (g % 5)],
  case when g % 3 = 0 then '1:5' || (2 + g % 7) || '.' || (g % 9) else null end,
  case when g % 7 = 0 then now() + interval '20 days' else null end,
  case when (1 + (g % 5)) in (1,2,3,4) then now() - ((g || ' hours')::interval) else null end,
  case when (1 + (g % 5)) = 4 then now() - interval '2 days' else null end,
  case when g % 4 = 0 then g::text else null end,
  case when g % 4 = 0 then g else null end
from generate_series(1, 40) g
cross join lateral (
  select id from public.seller_accounts
  order by id offset (g % (select count(*) from public.seller_accounts)) limit 1
) sa
on conflict (slug) do nothing;

-- ===========================================================================
-- Marketplace listings (bulk, across categories)
-- ===========================================================================
insert into public.marketplace_listings (id, seller_account_id, category_id, title, slug, status, sale_mode, description, condition, currency, price, accepts_offers, shipping_available, location_text, featured_until, published_at, sold_at)
select
  gen_random_uuid(),
  sa.id,
  cat.id,
  cat.name || ' — ' || (array['Pro','Elite','Classic','Premium','Standard'])[1 + (g % 5)] || ' #' || g,
  'seed-mkt-' || g,
  (array['published','published','published','sold'])[1 + (g % 4)]::public.listing_status,
  'buy_now'::public.sale_mode,
  'Well-maintained ' || cat.name || '. ' || repeat('Ready for the season. ', 4),
  (array['new','excellent','good','used','fair'])[1 + (g % 5)]::public.marketplace_condition,
  'USD',
  200 + g * 120,
  (g % 2 = 0),
  (g % 3 <> 0),
  (array['Menangle, NSW','Melbourne, VIC','Auckland, NZ','Brisbane, QLD'])[1 + (g % 4)],
  case when g % 6 = 0 then now() + interval '15 days' else null end,
  now() - ((g || ' hours')::interval),
  case when (1 + (g % 4)) = 4 then now() - interval '1 day' else null end
from generate_series(1, 24) g
cross join lateral (
  select id from public.seller_accounts
  order by id offset (g % (select count(*) from public.seller_accounts)) limit 1
) sa
cross join lateral (
  select id, name from public.categories
  where category_type in ('marketplace','service') and is_active
  order by sort_order offset (g % (select count(*) from public.categories where category_type in ('marketplace','service') and is_active)) limit 1
) cat
on conflict (slug) do nothing;

-- ===========================================================================
-- Auctions for published auction-mode horses (live / scheduled / extended)
-- ===========================================================================
with auction_horses as (
  select h.id, h.sale_event_id, row_number() over (order by h.created_at, h.id) as rn
  from public.horse_listings h
  where h.slug like 'seed-horse-%' and h.sale_mode = 'auction' and h.status = 'published'
)
insert into public.auctions (id, horse_listing_id, sale_event_id, status, currency, starts_at, ends_at, soft_close_seconds, starting_bid, reserve_price, bid_increment, current_bid, bid_count, reserve_met)
select
  gen_random_uuid(), ah.id, ah.sale_event_id,
  (array['live','live','scheduled','extended'])[1 + (ah.rn % 4)]::public.auction_status,
  'USD',
  now() - interval '3 hours',
  now() + (((10 + ah.rn) || ' hours')::interval),
  300,
  10000 + ah.rn * 500,
  case when ah.rn % 3 = 0 then 35000 + ah.rn * 500 else null end,
  1000,
  10000 + ah.rn * 500 + (ah.rn % 4) * 1000,
  (ah.rn % 4),
  case when ah.rn % 3 = 0 then (10000 + ah.rn * 500 + (ah.rn % 4) * 1000) >= (35000 + ah.rn * 500) else false end
from auction_horses ah
on conflict (horse_listing_id) do nothing;

-- A completed auction with a winner (reserve met) + a closed reserve-not-met,
-- driven off two existing published auction horses.
update public.auctions a
set status = 'closed', current_bid = 42000, reserve_price = 30000, reserve_met = true,
    bid_count = 6, winner_profile_id = 'a0000000-0000-0000-0000-000000000004',
    ends_at = now() - interval '1 hour'
where a.id = (select id from public.auctions order by created_at limit 1);

update public.auctions a
set status = 'closed', current_bid = 18000, reserve_price = 40000, reserve_met = false,
    bid_count = 3, ends_at = now() - interval '2 hours'
where a.id = (select id from public.auctions order by created_at offset 1 limit 1);

-- ===========================================================================
-- Bids on live auctions (buyer leads several)
-- ===========================================================================
insert into public.bids (id, auction_id, bidder_profile_id, amount, max_proxy_amount, status)
select gen_random_uuid(), a.id, 'a0000000-0000-0000-0000-000000000004', a.current_bid, a.current_bid + 5000, 'winning'
from public.auctions a
join public.horse_listings h on h.id = a.horse_listing_id
where h.slug like 'seed-horse-%' and a.status = 'live' and a.bid_count > 0
on conflict do nothing;

-- ===========================================================================
-- Enquiries (buyer -> sellers) — triggers conversations + notifications
-- ===========================================================================
insert into public.enquiries (id, sender_profile_id, seller_account_id, horse_listing_id, subject, message)
select gen_random_uuid(), 'a0000000-0000-0000-0000-000000000004', h.seller_account_id, h.id,
  'Enquiry: ' || h.title, 'Interested in ' || h.title || '. Is it still available, and can you ship interstate?'
from public.horse_listings h
where h.slug like 'seed-horse-%' and h.status = 'published'
order by h.created_at
limit 5;

-- ===========================================================================
-- Saved searches + a welcome notification for the buyer
-- ===========================================================================
insert into public.saved_searches (profile_id, name, search_type, filters, alert_enabled)
values
  ('a0000000-0000-0000-0000-000000000004','Pacing colts','horse_auction','{"gait":"pacer","sex":"colt"}'::jsonb, true),
  ('a0000000-0000-0000-0000-000000000004','Race bikes & sulkies','marketplace','{"category":"bikes-sulkies"}'::jsonb, false)
on conflict do nothing;

insert into public.notifications (profile_id, type, title, body, link_url)
values ('a0000000-0000-0000-0000-000000000004','system','Welcome to HarnessBid','Your account is ready. Explore live auctions and the APG yearling catalogue.','/auctions');
