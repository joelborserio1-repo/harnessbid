-- HarnessBid — reset a partially/previously provisioned project so that
-- supabase/full_setup.sql can run cleanly from scratch.
--
-- DESTRUCTIVE: drops everything the migrations created (all public-schema
-- tables/types/functions/triggers, the listing-images storage bucket and its
-- policies, and the auth.users signup trigger). Only run this on a project you
-- intend to (re)provision — never on one with data you care about.
--
-- Usage: paste this into the SQL Editor and Run, then paste full_setup.sql and Run.

-- 1. The signup trigger lives on auth.users (outside the public schema), and it
--    depends on public.handle_new_user(); drop it before nuking public.
drop trigger if exists on_auth_user_created on auth.users;

-- 2. Storage: drop the access policies. We intentionally do NOT delete the
--    bucket or its objects here -- newer Supabase projects block direct DELETE
--    on storage.* via a protect_delete() trigger ("Direct deletion from storage
--    tables is not allowed"). It is also unnecessary: full_setup.sql re-creates
--    the bucket with `on conflict (id) do update`, so leaving it in place is
--    harmless and idempotent. To remove a bucket entirely, use the dashboard
--    Storage UI or the Storage API rather than SQL.
drop policy if exists "Public read listing images" on storage.objects;
drop policy if exists "Sellers upload own listing images" on storage.objects;
drop policy if exists "Sellers update own listing images" on storage.objects;
drop policy if exists "Sellers delete own listing images" on storage.objects;

-- 3. Everything else (tables, the app_role/listing_status/etc. enums, functions,
--    triggers) lives in the public schema — drop and recreate it, then restore
--    the standard Supabase grants.
drop schema if exists public cascade;
create schema public;
grant usage on schema public to anon, authenticated, service_role;
grant all on schema public to postgres, service_role;
comment on schema public is 'standard public schema';
