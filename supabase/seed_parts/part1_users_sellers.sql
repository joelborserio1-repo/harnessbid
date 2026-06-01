-- HarnessBid - EXTRA demo data (additive, idempotent). Run AFTER seed.sql.
-- Adds more login-able test users, sellers, realistic standardbred horse
-- listings + equipment, auctions, bids from several buyers, and images so the
-- front and back end can be exercised with a fuller catalogue.
--
-- All content is ORIGINAL (real sire/dam *lines* used the way the industry does,
-- but descriptions are written fresh - not copied from any platform).
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
-- Extra named sellers (studs / agents) - login-able
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
