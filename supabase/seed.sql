-- HarnessBid seed data (Phase 21).
--
-- For LOCAL / STAGING only. Runs automatically with `supabase db reset`
-- (after all migrations). Idempotent via ON CONFLICT DO NOTHING.
--
-- Test accounts (password for all: "password123"):
--   admin@harnessbid.test       (admin)
--   seller@harnessbid.test      (individual seller, verified)
--   enterprise@harnessbid.test  (enterprise seller, verified)
--   buyer@harnessbid.test       (buyer)
--
-- profiles are auto-created by the on_auth_user_created trigger when the
-- auth.users rows are inserted; we then set roles/display names below.

-- ---------------------------------------------------------------------------
-- Auth users
-- ---------------------------------------------------------------------------
insert into auth.users (id, instance_id, aud, role, email, encrypted_password,
  email_confirmed_at, raw_app_meta_data, raw_user_meta_data, created_at, updated_at)
values
  ('a0000000-0000-0000-0000-000000000001','00000000-0000-0000-0000-000000000000','authenticated','authenticated','admin@harnessbid.test', crypt('password123', gen_salt('bf')), now(), '{"provider":"email","providers":["email"]}', '{"full_name":"HarnessBid Admin"}', now(), now()),
  ('a0000000-0000-0000-0000-000000000002','00000000-0000-0000-0000-000000000000','authenticated','authenticated','seller@harnessbid.test', crypt('password123', gen_salt('bf')), now(), '{"provider":"email","providers":["email"]}', '{"full_name":"Riverside Stables"}', now(), now()),
  ('a0000000-0000-0000-0000-000000000003','00000000-0000-0000-0000-000000000000','authenticated','authenticated','enterprise@harnessbid.test', crypt('password123', gen_salt('bf')), now(), '{"provider":"email","providers":["email"]}', '{"full_name":"APG Sales"}', now(), now()),
  ('a0000000-0000-0000-0000-000000000004','00000000-0000-0000-0000-000000000000','authenticated','authenticated','buyer@harnessbid.test', crypt('password123', gen_salt('bf')), now(), '{"provider":"email","providers":["email"]}', '{"full_name":"Jordan Buyer"}', now(), now())
on conflict (id) do nothing;

insert into auth.identities (id, user_id, identity_data, provider, provider_id, last_sign_in_at, created_at, updated_at)
values
  (gen_random_uuid(),'a0000000-0000-0000-0000-000000000001','{"sub":"a0000000-0000-0000-0000-000000000001","email":"admin@harnessbid.test"}','email','a0000000-0000-0000-0000-000000000001', now(), now(), now()),
  (gen_random_uuid(),'a0000000-0000-0000-0000-000000000002','{"sub":"a0000000-0000-0000-0000-000000000002","email":"seller@harnessbid.test"}','email','a0000000-0000-0000-0000-000000000002', now(), now(), now()),
  (gen_random_uuid(),'a0000000-0000-0000-0000-000000000003','{"sub":"a0000000-0000-0000-0000-000000000003","email":"enterprise@harnessbid.test"}','email','a0000000-0000-0000-0000-000000000003', now(), now(), now()),
  (gen_random_uuid(),'a0000000-0000-0000-0000-000000000004','{"sub":"a0000000-0000-0000-0000-000000000004","email":"buyer@harnessbid.test"}','email','a0000000-0000-0000-0000-000000000004', now(), now(), now())
on conflict do nothing;

-- Roles / display names (profiles already created by trigger).
update public.profiles set role = 'admin', display_name = 'HarnessBid Admin', full_name = 'HarnessBid Admin' where id = 'a0000000-0000-0000-0000-000000000001';
update public.profiles set role = 'seller', display_name = 'Riverside Stables', full_name = 'Riverside Stables' where id = 'a0000000-0000-0000-0000-000000000002';
update public.profiles set role = 'seller', display_name = 'APG Sales', full_name = 'APG Sales' where id = 'a0000000-0000-0000-0000-000000000003';
update public.profiles set role = 'buyer', display_name = 'Jordan Buyer', full_name = 'Jordan Buyer' where id = 'a0000000-0000-0000-0000-000000000004';

-- ---------------------------------------------------------------------------
-- Seller accounts (verified) + enterprise seller
-- ---------------------------------------------------------------------------
insert into public.seller_accounts (id, owner_profile_id, account_type, display_name, slug, bio, location_text, verification_status, is_active, verified_at)
values
  ('b0000000-0000-0000-0000-000000000001','a0000000-0000-0000-0000-000000000002','individual','Riverside Stables','riverside-stables','Standardbred breaking and pre-training.', 'Menangle, NSW', 'verified', true, now()),
  ('b0000000-0000-0000-0000-000000000002','a0000000-0000-0000-0000-000000000003','enterprise','APG Sales','apg-sales','Australian Pacing Gold premier yearling sales.', 'Melbourne, VIC', 'verified', true, now())
on conflict (id) do nothing;

insert into public.enterprise_sellers (id, seller_account_id, legal_name, trading_name, tier, onboarding_status)
values
  ('c0000000-0000-0000-0000-000000000001','b0000000-0000-0000-0000-000000000002','Australian Pacing Gold Pty Ltd','APG Sales','premier','active')
on conflict (id) do nothing;

-- ---------------------------------------------------------------------------
-- Sale event (APG) + lots
-- ---------------------------------------------------------------------------
insert into public.sale_events (id, enterprise_seller_id, name, slug, event_type, status, description, timezone, starts_at, ends_at, featured, sort_order)
values
  ('10000000-0000-0000-0000-000000000001','c0000000-0000-0000-0000-000000000001','APG Yearling Sale 2026','apg-yearling-sale-2026','online_auction','live','Premier Australian Pacing Gold yearling catalogue.', 'Australia/Melbourne', now() - interval '1 day', now() + interval '6 days', true, 1)
on conflict (id) do nothing;

-- ---------------------------------------------------------------------------
-- Horse listings (published): one auction lot, one buy-now
-- ---------------------------------------------------------------------------
insert into public.horse_listings (id, seller_account_id, sale_event_id, title, slug, status, sale_mode, currency, asking_price, short_description, description, location_text, breed, sex, gait, age_years, color, sire, dam, best_mile, lot_number, lot_order, published_at)
values
  ('d0000000-0000-0000-0000-000000000001','b0000000-0000-0000-0000-000000000002','10000000-0000-0000-0000-000000000001','Bettor Dream (Lot 1)','bettor-dream-lot-1','published','auction','USD', null, 'Outstanding colt by Bettors Delight.', 'A beautifully bred yearling colt with elite pedigree.', 'Melbourne, VIC', 'Standardbred','colt','pacer', 1, 'Bay', 'Bettors Delight', 'Dream Of Glory', null, '1', 1, now()),
  ('d0000000-0000-0000-0000-000000000002','b0000000-0000-0000-0000-000000000001',null,'Sundons Gift','sundons-gift','published','buy_now','USD', 18000, 'Ready-to-race trotting filly.', 'Honest trotting filly, qualified and ready.', 'Menangle, NSW', 'Standardbred','filly','trotter', 3, 'Brown', 'Sundon', 'Gift Of Gab', '1:58.2', null, null, now())
on conflict (id) do nothing;

-- ---------------------------------------------------------------------------
-- Marketplace listing (published)
-- ---------------------------------------------------------------------------
insert into public.marketplace_listings (id, seller_account_id, title, slug, status, sale_mode, description, condition, currency, price, shipping_available, location_text, published_at)
values
  ('e0000000-0000-0000-0000-000000000001','b0000000-0000-0000-0000-000000000001','Finntack Pro Race Bike','finntack-pro-race-bike','published','buy_now','Carbon race bike in excellent condition.', 'excellent','USD', 4200, true, 'Menangle, NSW', now())
on conflict (id) do nothing;

-- ---------------------------------------------------------------------------
-- Live auction on the APG lot, with a leading bid from the buyer
-- ---------------------------------------------------------------------------
insert into public.auctions (id, horse_listing_id, sale_event_id, status, currency, starts_at, ends_at, soft_close_seconds, starting_bid, reserve_price, bid_increment, current_bid, bid_count, reserve_met)
values
  ('f0000000-0000-0000-0000-000000000001','d0000000-0000-0000-0000-000000000001','10000000-0000-0000-0000-000000000001','live','USD', now() - interval '1 hour', now() + interval '2 days', 300, 10000, 25000, 1000, 12000, 2, false)
on conflict (id) do nothing;

insert into public.bids (id, auction_id, bidder_profile_id, amount, max_proxy_amount, status)
values
  ('f1000000-0000-0000-0000-000000000001','f0000000-0000-0000-0000-000000000001','a0000000-0000-0000-0000-000000000004', 12000, 15000, 'winning')
on conflict (id) do nothing;

-- ---------------------------------------------------------------------------
-- Buyer watchlist + an enquiry (exercises the conversation trigger)
-- ---------------------------------------------------------------------------
insert into public.watchlists (profile_id, horse_listing_id)
values ('a0000000-0000-0000-0000-000000000004','d0000000-0000-0000-0000-000000000002')
on conflict do nothing;

insert into public.enquiries (id, sender_profile_id, seller_account_id, horse_listing_id, subject, message)
values ('e1000000-0000-0000-0000-000000000001','a0000000-0000-0000-0000-000000000004','b0000000-0000-0000-0000-000000000001','d0000000-0000-0000-0000-000000000002','Enquiry: Sundons Gift','Hi — is Sundons Gift still available and can you ship to QLD?')
on conflict (id) do nothing;
