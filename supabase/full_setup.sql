-- HarnessBid — consolidated database setup (all migrations, in order).
-- Generated from supabase/migrations/*. Paste into the Supabase SQL Editor
-- on a FRESH project and Run once. (CLI users: use 'supabase db push' with
-- the individual migration files instead — do not mix the two approaches.)
-- Then optionally run supabase/seed.sql (staging) and supabase/audit/rls_check.sql.


-- ============================================================================
-- supabase/migrations/202605280001_harnessbid_v2_schema.sql
-- ============================================================================
-- HarnessBid v2 core schema
-- Supabase/Postgres migration for marketplace, horse auctions, sellers, and future payments.

create extension if not exists pgcrypto;
create extension if not exists citext;

create type public.app_role as enum ('buyer', 'seller', 'enterprise_seller', 'admin');
create type public.verification_status as enum ('unverified', 'pending', 'verified', 'rejected', 'suspended');
create type public.seller_account_type as enum ('individual', 'business', 'enterprise');
create type public.enterprise_tier as enum ('standard', 'preferred', 'premier', 'strategic');
create type public.onboarding_status as enum ('draft', 'invited', 'in_review', 'active', 'paused', 'offboarded');
create type public.category_type as enum ('horse', 'marketplace', 'service', 'content');
create type public.listing_status as enum ('draft', 'pending_review', 'published', 'paused', 'under_offer', 'sold', 'expired', 'rejected', 'archived');
create type public.marketplace_condition as enum ('new', 'excellent', 'good', 'fair', 'used', 'for_parts', 'not_applicable');
create type public.sale_mode as enum ('classified', 'buy_now', 'auction', 'private_treaty');
create type public.horse_sex as enum ('colt', 'filly', 'gelding', 'mare', 'stallion', 'ridgling', 'unknown');
create type public.horse_gait as enum ('pacer', 'trotter', 'dual_gaited', 'unknown');
create type public.sale_event_type as enum ('online_auction', 'timed_auction', 'live_sale', 'private_sale', 'clearance_sale');
create type public.sale_event_status as enum ('draft', 'scheduled', 'live', 'closed', 'settled', 'cancelled', 'archived');
create type public.auction_status as enum ('draft', 'scheduled', 'live', 'extended', 'closed', 'settled', 'cancelled');
create type public.bid_status as enum ('active', 'outbid', 'winning', 'won', 'retracted', 'rejected');
create type public.enquiry_status as enum ('open', 'replied', 'closed', 'spam', 'archived');
create type public.payment_status as enum ('placeholder', 'pending', 'requires_action', 'authorized', 'captured', 'failed', 'cancelled', 'refunded');

create or replace function public.set_updated_at()
returns trigger
language plpgsql
as $$
begin
  new.updated_at = now();
  return new;
end;
$$;

create table public.profiles (
  id uuid primary key references auth.users(id) on delete cascade,
  email citext unique,
  full_name text,
  display_name text,
  phone text,
  avatar_url text,
  role public.app_role not null default 'buyer',
  country_code char(2),
  timezone text,
  is_active boolean not null default true,
  last_seen_at timestamptz,
  metadata jsonb not null default '{}'::jsonb,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);

create or replace function public.current_profile_role()
returns public.app_role
language sql
stable
security definer
set search_path = public
as $$
  select role from public.profiles where id = auth.uid()
$$;

create or replace function public.is_admin()
returns boolean
language sql
stable
security definer
set search_path = public
as $$
  select coalesce(public.current_profile_role() = 'admin'::public.app_role, false)
$$;

create table public.seller_accounts (
  id uuid primary key default gen_random_uuid(),
  owner_profile_id uuid not null references public.profiles(id) on delete restrict,
  account_type public.seller_account_type not null default 'individual',
  display_name text not null,
  slug text not null unique,
  bio text,
  logo_url text,
  website_url text,
  contact_email citext,
  contact_phone text,
  location_text text,
  city text,
  region text,
  country_code char(2),
  response_time_label text,
  verification_status public.verification_status not null default 'unverified',
  rating numeric(3,2) not null default 0 check (rating >= 0 and rating <= 5),
  review_count integer not null default 0 check (review_count >= 0),
  total_sales integer not null default 0 check (total_sales >= 0),
  total_listings integer not null default 0 check (total_listings >= 0),
  is_active boolean not null default true,
  verified_at timestamptz,
  metadata jsonb not null default '{}'::jsonb,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now(),
  constraint seller_accounts_slug_format check (slug ~ '^[a-z0-9]+(?:-[a-z0-9]+)*$')
);

create table public.enterprise_sellers (
  id uuid primary key default gen_random_uuid(),
  seller_account_id uuid not null unique references public.seller_accounts(id) on delete cascade,
  legal_name text,
  trading_name text,
  tier public.enterprise_tier not null default 'standard',
  onboarding_status public.onboarding_status not null default 'draft',
  account_manager_profile_id uuid references public.profiles(id) on delete set null,
  external_reference text,
  contract_starts_at date,
  contract_ends_at date,
  featured_until timestamptz,
  brand_settings jsonb not null default '{}'::jsonb,
  billing_settings jsonb not null default '{}'::jsonb,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);

create table public.categories (
  id uuid primary key default gen_random_uuid(),
  parent_id uuid references public.categories(id) on delete set null,
  category_type public.category_type not null,
  name text not null,
  slug text not null unique,
  description text,
  icon_name text,
  sort_order integer not null default 0,
  is_active boolean not null default true,
  metadata jsonb not null default '{}'::jsonb,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now(),
  constraint categories_slug_format check (slug ~ '^[a-z0-9]+(?:-[a-z0-9]+)*$')
);

create table public.sale_events (
  id uuid primary key default gen_random_uuid(),
  seller_account_id uuid references public.seller_accounts(id) on delete set null,
  enterprise_seller_id uuid references public.enterprise_sellers(id) on delete set null,
  name text not null,
  slug text not null unique,
  event_type public.sale_event_type not null default 'online_auction',
  status public.sale_event_status not null default 'draft',
  description text,
  hero_image_url text,
  timezone text not null default 'UTC',
  starts_at timestamptz,
  ends_at timestamptz,
  settlement_due_at timestamptz,
  terms_url text,
  metadata jsonb not null default '{}'::jsonb,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now(),
  constraint sale_events_one_seller_target check (
    num_nonnulls(seller_account_id, enterprise_seller_id) = 1
  ),
  constraint sale_events_time_order check (ends_at is null or starts_at is null or ends_at > starts_at),
  constraint sale_events_slug_format check (slug ~ '^[a-z0-9]+(?:-[a-z0-9]+)*$')
);

create table public.horse_listings (
  id uuid primary key default gen_random_uuid(),
  seller_account_id uuid not null references public.seller_accounts(id) on delete restrict,
  category_id uuid references public.categories(id) on delete set null,
  sale_event_id uuid references public.sale_events(id) on delete set null,
  title text not null,
  slug text not null unique,
  status public.listing_status not null default 'draft',
  sale_mode public.sale_mode not null default 'auction',
  short_description text,
  description text,
  currency char(3) not null default 'USD',
  asking_price numeric(14,2) check (asking_price is null or asking_price >= 0),
  location_text text,
  city text,
  region text,
  country_code char(2),
  foaled_date date,
  age_years integer check (age_years is null or age_years >= 0),
  color text,
  sex public.horse_sex not null default 'unknown',
  gait public.horse_gait not null default 'unknown',
  breed text not null default 'Standardbred',
  sire text,
  dam text,
  dam_sire text,
  best_mile text,
  starts integer check (starts is null or starts >= 0),
  wins integer check (wins is null or wins >= 0),
  places integer check (places is null or places >= 0),
  earnings numeric(14,2) check (earnings is null or earnings >= 0),
  vet_report_url text,
  pedigree jsonb not null default '{}'::jsonb,
  racing_record jsonb not null default '{}'::jsonb,
  specs jsonb not null default '{}'::jsonb,
  metadata jsonb not null default '{}'::jsonb,
  view_count integer not null default 0 check (view_count >= 0),
  watcher_count integer not null default 0 check (watcher_count >= 0),
  published_at timestamptz,
  sold_at timestamptz,
  expires_at timestamptz,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now(),
  constraint horse_listings_slug_format check (slug ~ '^[a-z0-9]+(?:-[a-z0-9]+)*$')
);

create table public.marketplace_listings (
  id uuid primary key default gen_random_uuid(),
  seller_account_id uuid not null references public.seller_accounts(id) on delete restrict,
  category_id uuid references public.categories(id) on delete set null,
  title text not null,
  slug text not null unique,
  status public.listing_status not null default 'draft',
  sale_mode public.sale_mode not null default 'classified',
  description text,
  condition public.marketplace_condition not null default 'used',
  currency char(3) not null default 'USD',
  price numeric(14,2) check (price is null or price >= 0),
  accepts_offers boolean not null default true,
  shipping_available boolean not null default false,
  domestic_shipping_price numeric(12,2) check (domestic_shipping_price is null or domestic_shipping_price >= 0),
  international_shipping_notes text,
  brand text,
  model text,
  manufacture_year integer,
  location_text text,
  city text,
  region text,
  country_code char(2),
  specs jsonb not null default '{}'::jsonb,
  metadata jsonb not null default '{}'::jsonb,
  view_count integer not null default 0 check (view_count >= 0),
  watcher_count integer not null default 0 check (watcher_count >= 0),
  featured_until timestamptz,
  published_at timestamptz,
  sold_at timestamptz,
  expires_at timestamptz,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now(),
  constraint marketplace_listings_slug_format check (slug ~ '^[a-z0-9]+(?:-[a-z0-9]+)*$')
);

create table public.auctions (
  id uuid primary key default gen_random_uuid(),
  horse_listing_id uuid unique references public.horse_listings(id) on delete cascade,
  marketplace_listing_id uuid unique references public.marketplace_listings(id) on delete cascade,
  sale_event_id uuid references public.sale_events(id) on delete set null,
  status public.auction_status not null default 'draft',
  currency char(3) not null default 'USD',
  starts_at timestamptz not null,
  ends_at timestamptz not null,
  soft_close_seconds integer not null default 300 check (soft_close_seconds >= 0),
  starting_bid numeric(14,2) not null check (starting_bid >= 0),
  reserve_price numeric(14,2) check (reserve_price is null or reserve_price >= 0),
  bid_increment numeric(14,2) not null default 100 check (bid_increment > 0),
  current_bid numeric(14,2) check (current_bid is null or current_bid >= 0),
  bid_count integer not null default 0 check (bid_count >= 0),
  winner_profile_id uuid references public.profiles(id) on delete set null,
  reserve_met boolean not null default false,
  settled_at timestamptz,
  metadata jsonb not null default '{}'::jsonb,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now(),
  constraint auctions_one_listing_target check (
    num_nonnulls(horse_listing_id, marketplace_listing_id) = 1
  ),
  constraint auctions_time_order check (ends_at > starts_at),
  constraint auctions_current_bid_floor check (current_bid is null or current_bid >= starting_bid)
);

create table public.bids (
  id uuid primary key default gen_random_uuid(),
  auction_id uuid not null references public.auctions(id) on delete cascade,
  bidder_profile_id uuid not null references public.profiles(id) on delete restrict,
  amount numeric(14,2) not null check (amount > 0),
  max_proxy_amount numeric(14,2) check (max_proxy_amount is null or max_proxy_amount >= amount),
  status public.bid_status not null default 'active',
  ip_hash text,
  user_agent text,
  metadata jsonb not null default '{}'::jsonb,
  placed_at timestamptz not null default now(),
  created_at timestamptz not null default now()
);

create table public.watchlists (
  id uuid primary key default gen_random_uuid(),
  profile_id uuid not null references public.profiles(id) on delete cascade,
  horse_listing_id uuid references public.horse_listings(id) on delete cascade,
  marketplace_listing_id uuid references public.marketplace_listings(id) on delete cascade,
  auction_id uuid references public.auctions(id) on delete cascade,
  note text,
  created_at timestamptz not null default now(),
  constraint watchlists_one_target check (
    num_nonnulls(horse_listing_id, marketplace_listing_id, auction_id) = 1
  )
);

create table public.listing_images (
  id uuid primary key default gen_random_uuid(),
  horse_listing_id uuid references public.horse_listings(id) on delete cascade,
  marketplace_listing_id uuid references public.marketplace_listings(id) on delete cascade,
  storage_bucket text,
  storage_path text,
  image_url text,
  alt_text text,
  position integer not null default 0 check (position >= 0),
  is_primary boolean not null default false,
  metadata jsonb not null default '{}'::jsonb,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now(),
  constraint listing_images_one_target check (
    num_nonnulls(horse_listing_id, marketplace_listing_id) = 1
  ),
  constraint listing_images_source_present check (
    image_url is not null or (storage_bucket is not null and storage_path is not null)
  )
);

create table public.enquiries (
  id uuid primary key default gen_random_uuid(),
  sender_profile_id uuid references public.profiles(id) on delete set null,
  seller_account_id uuid not null references public.seller_accounts(id) on delete cascade,
  horse_listing_id uuid references public.horse_listings(id) on delete set null,
  marketplace_listing_id uuid references public.marketplace_listings(id) on delete set null,
  sale_event_id uuid references public.sale_events(id) on delete set null,
  subject text,
  message text not null,
  contact_name text,
  contact_email citext,
  contact_phone text,
  status public.enquiry_status not null default 'open',
  read_at timestamptz,
  replied_at timestamptz,
  metadata jsonb not null default '{}'::jsonb,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now(),
  constraint enquiries_one_target check (
    num_nonnulls(horse_listing_id, marketplace_listing_id, sale_event_id) = 1
  )
);

-- Payment placeholders only. No provider connection, webhook logic, or funds movement is configured here.
create table public.payment_accounts (
  id uuid primary key default gen_random_uuid(),
  seller_account_id uuid not null unique references public.seller_accounts(id) on delete cascade,
  provider text,
  provider_account_reference text,
  status public.payment_status not null default 'placeholder',
  onboarding_url text,
  charges_enabled boolean not null default false,
  payouts_enabled boolean not null default false,
  requirements jsonb not null default '{}'::jsonb,
  metadata jsonb not null default '{}'::jsonb,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);

create table public.payment_intents (
  id uuid primary key default gen_random_uuid(),
  buyer_profile_id uuid references public.profiles(id) on delete set null,
  seller_account_id uuid references public.seller_accounts(id) on delete set null,
  auction_id uuid references public.auctions(id) on delete set null,
  horse_listing_id uuid references public.horse_listings(id) on delete set null,
  marketplace_listing_id uuid references public.marketplace_listings(id) on delete set null,
  provider text,
  provider_intent_reference text,
  status public.payment_status not null default 'placeholder',
  currency char(3) not null default 'USD',
  amount numeric(14,2) not null check (amount >= 0),
  platform_fee_amount numeric(14,2) check (platform_fee_amount is null or platform_fee_amount >= 0),
  seller_net_amount numeric(14,2) check (seller_net_amount is null or seller_net_amount >= 0),
  metadata jsonb not null default '{}'::jsonb,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now(),
  constraint payment_intents_target_present check (
    num_nonnulls(auction_id, horse_listing_id, marketplace_listing_id) >= 1
  )
);

create table public.payment_events (
  id uuid primary key default gen_random_uuid(),
  payment_intent_id uuid references public.payment_intents(id) on delete cascade,
  provider text,
  provider_event_reference text,
  event_type text not null,
  payload jsonb not null default '{}'::jsonb,
  received_at timestamptz not null default now(),
  created_at timestamptz not null default now()
);

create trigger set_profiles_updated_at before update on public.profiles for each row execute function public.set_updated_at();
create trigger set_seller_accounts_updated_at before update on public.seller_accounts for each row execute function public.set_updated_at();
create trigger set_enterprise_sellers_updated_at before update on public.enterprise_sellers for each row execute function public.set_updated_at();
create trigger set_categories_updated_at before update on public.categories for each row execute function public.set_updated_at();
create trigger set_sale_events_updated_at before update on public.sale_events for each row execute function public.set_updated_at();
create trigger set_horse_listings_updated_at before update on public.horse_listings for each row execute function public.set_updated_at();
create trigger set_marketplace_listings_updated_at before update on public.marketplace_listings for each row execute function public.set_updated_at();
create trigger set_auctions_updated_at before update on public.auctions for each row execute function public.set_updated_at();
create trigger set_listing_images_updated_at before update on public.listing_images for each row execute function public.set_updated_at();
create trigger set_enquiries_updated_at before update on public.enquiries for each row execute function public.set_updated_at();
create trigger set_payment_accounts_updated_at before update on public.payment_accounts for each row execute function public.set_updated_at();
create trigger set_payment_intents_updated_at before update on public.payment_intents for each row execute function public.set_updated_at();

create index profiles_role_idx on public.profiles(role);
create index seller_accounts_owner_idx on public.seller_accounts(owner_profile_id);
create index seller_accounts_slug_idx on public.seller_accounts(slug);
create index seller_accounts_verified_active_idx on public.seller_accounts(verification_status, is_active);
create index enterprise_sellers_account_idx on public.enterprise_sellers(seller_account_id);
create index categories_parent_idx on public.categories(parent_id);
create index categories_type_active_sort_idx on public.categories(category_type, is_active, sort_order);
create index sale_events_seller_idx on public.sale_events(seller_account_id);
create index sale_events_enterprise_idx on public.sale_events(enterprise_seller_id);
create index sale_events_status_dates_idx on public.sale_events(status, starts_at, ends_at);
create index horse_listings_seller_idx on public.horse_listings(seller_account_id);
create index horse_listings_category_idx on public.horse_listings(category_id);
create index horse_listings_event_idx on public.horse_listings(sale_event_id);
create index horse_listings_status_published_idx on public.horse_listings(status, published_at desc);
create index horse_listings_search_idx on public.horse_listings using gin (
  to_tsvector('english', coalesce(title, '') || ' ' || coalesce(short_description, '') || ' ' || coalesce(description, '') || ' ' || coalesce(sire, '') || ' ' || coalesce(dam, ''))
);
create index marketplace_listings_seller_idx on public.marketplace_listings(seller_account_id);
create index marketplace_listings_category_idx on public.marketplace_listings(category_id);
create index marketplace_listings_status_published_idx on public.marketplace_listings(status, published_at desc);
create index marketplace_listings_price_idx on public.marketplace_listings(price) where status = 'published';
create index marketplace_listings_search_idx on public.marketplace_listings using gin (
  to_tsvector('english', coalesce(title, '') || ' ' || coalesce(description, '') || ' ' || coalesce(brand, '') || ' ' || coalesce(model, ''))
);
create index auctions_status_dates_idx on public.auctions(status, starts_at, ends_at);
create index auctions_event_idx on public.auctions(sale_event_id);
create index bids_auction_amount_idx on public.bids(auction_id, amount desc, placed_at desc);
create index bids_bidder_idx on public.bids(bidder_profile_id, placed_at desc);
create index watchlists_profile_idx on public.watchlists(profile_id, created_at desc);
create unique index watchlists_profile_horse_unique on public.watchlists(profile_id, horse_listing_id) where horse_listing_id is not null;
create unique index watchlists_profile_marketplace_unique on public.watchlists(profile_id, marketplace_listing_id) where marketplace_listing_id is not null;
create unique index watchlists_profile_auction_unique on public.watchlists(profile_id, auction_id) where auction_id is not null;
create index listing_images_horse_position_idx on public.listing_images(horse_listing_id, position) where horse_listing_id is not null;
create index listing_images_marketplace_position_idx on public.listing_images(marketplace_listing_id, position) where marketplace_listing_id is not null;
create unique index listing_images_horse_primary_unique on public.listing_images(horse_listing_id) where horse_listing_id is not null and is_primary;
create unique index listing_images_marketplace_primary_unique on public.listing_images(marketplace_listing_id) where marketplace_listing_id is not null and is_primary;
create index enquiries_sender_idx on public.enquiries(sender_profile_id, created_at desc);
create index enquiries_seller_status_idx on public.enquiries(seller_account_id, status, created_at desc);
create index payment_accounts_seller_idx on public.payment_accounts(seller_account_id);
create index payment_intents_buyer_idx on public.payment_intents(buyer_profile_id, created_at desc);
create index payment_intents_seller_idx on public.payment_intents(seller_account_id, created_at desc);
create index payment_events_intent_idx on public.payment_events(payment_intent_id, received_at desc);

alter table public.profiles enable row level security;
alter table public.seller_accounts enable row level security;
alter table public.enterprise_sellers enable row level security;
alter table public.categories enable row level security;
alter table public.sale_events enable row level security;
alter table public.horse_listings enable row level security;
alter table public.marketplace_listings enable row level security;
alter table public.auctions enable row level security;
alter table public.bids enable row level security;
alter table public.watchlists enable row level security;
alter table public.listing_images enable row level security;
alter table public.enquiries enable row level security;
alter table public.payment_accounts enable row level security;
alter table public.payment_intents enable row level security;
alter table public.payment_events enable row level security;

create policy "Profiles are readable by authenticated users"
  on public.profiles for select
  to authenticated
  using (true);

create policy "Users can insert their own profile"
  on public.profiles for insert
  to authenticated
  with check (id = auth.uid());

create policy "Users can update their own profile"
  on public.profiles for update
  to authenticated
  using (id = auth.uid() or public.is_admin())
  with check (id = auth.uid() or public.is_admin());

create policy "Public can read active verified seller accounts"
  on public.seller_accounts for select
  to anon, authenticated
  using (is_active and verification_status = 'verified');

create policy "Seller owners can read their accounts"
  on public.seller_accounts for select
  to authenticated
  using (owner_profile_id = auth.uid() or public.is_admin());

create policy "Authenticated users can create seller accounts"
  on public.seller_accounts for insert
  to authenticated
  with check (owner_profile_id = auth.uid() or public.is_admin());

create policy "Seller owners can update their accounts"
  on public.seller_accounts for update
  to authenticated
  using (owner_profile_id = auth.uid() or public.is_admin())
  with check (owner_profile_id = auth.uid() or public.is_admin());

create policy "Public can read active enterprise sellers"
  on public.enterprise_sellers for select
  to anon, authenticated
  using (
    onboarding_status = 'active'
    and exists (
      select 1 from public.seller_accounts sa
      where sa.id = seller_account_id
        and sa.is_active
        and sa.verification_status = 'verified'
    )
  );

create policy "Enterprise sellers are manageable by owner or admin"
  on public.enterprise_sellers for all
  to authenticated
  using (
    public.is_admin()
    or exists (
      select 1 from public.seller_accounts sa
      where sa.id = seller_account_id and sa.owner_profile_id = auth.uid()
    )
  )
  with check (
    public.is_admin()
    or exists (
      select 1 from public.seller_accounts sa
      where sa.id = seller_account_id and sa.owner_profile_id = auth.uid()
    )
  );

create policy "Public can read active categories"
  on public.categories for select
  to anon, authenticated
  using (is_active);

create policy "Admins manage categories"
  on public.categories for all
  to authenticated
  using (public.is_admin())
  with check (public.is_admin());

create policy "Public can read visible sale events"
  on public.sale_events for select
  to anon, authenticated
  using (status in ('scheduled', 'live', 'closed', 'settled'));

create policy "Sellers manage sale events"
  on public.sale_events for all
  to authenticated
  using (
    public.is_admin()
    or exists (
      select 1 from public.seller_accounts sa
      where sa.id = seller_account_id and sa.owner_profile_id = auth.uid()
    )
    or exists (
      select 1
      from public.enterprise_sellers es
      join public.seller_accounts sa on sa.id = es.seller_account_id
      where es.id = enterprise_seller_id and sa.owner_profile_id = auth.uid()
    )
  )
  with check (
    public.is_admin()
    or exists (
      select 1 from public.seller_accounts sa
      where sa.id = seller_account_id and sa.owner_profile_id = auth.uid()
    )
    or exists (
      select 1
      from public.enterprise_sellers es
      join public.seller_accounts sa on sa.id = es.seller_account_id
      where es.id = enterprise_seller_id and sa.owner_profile_id = auth.uid()
    )
  );

create policy "Public can read published horse listings"
  on public.horse_listings for select
  to anon, authenticated
  using (status in ('published', 'under_offer', 'sold') and published_at is not null);

create policy "Sellers manage horse listings"
  on public.horse_listings for all
  to authenticated
  using (
    public.is_admin()
    or exists (
      select 1 from public.seller_accounts sa
      where sa.id = seller_account_id and sa.owner_profile_id = auth.uid()
    )
  )
  with check (
    public.is_admin()
    or exists (
      select 1 from public.seller_accounts sa
      where sa.id = seller_account_id and sa.owner_profile_id = auth.uid()
    )
  );

create policy "Public can read published marketplace listings"
  on public.marketplace_listings for select
  to anon, authenticated
  using (status in ('published', 'under_offer', 'sold') and published_at is not null);

create policy "Sellers manage marketplace listings"
  on public.marketplace_listings for all
  to authenticated
  using (
    public.is_admin()
    or exists (
      select 1 from public.seller_accounts sa
      where sa.id = seller_account_id and sa.owner_profile_id = auth.uid()
    )
  )
  with check (
    public.is_admin()
    or exists (
      select 1 from public.seller_accounts sa
      where sa.id = seller_account_id and sa.owner_profile_id = auth.uid()
    )
  );

create policy "Public can read visible auctions"
  on public.auctions for select
  to anon, authenticated
  using (status in ('scheduled', 'live', 'extended', 'closed', 'settled'));

create policy "Sellers manage auctions"
  on public.auctions for all
  to authenticated
  using (
    public.is_admin()
    or exists (
      select 1
      from public.horse_listings hl
      join public.seller_accounts sa on sa.id = hl.seller_account_id
      where hl.id = horse_listing_id and sa.owner_profile_id = auth.uid()
    )
    or exists (
      select 1
      from public.marketplace_listings ml
      join public.seller_accounts sa on sa.id = ml.seller_account_id
      where ml.id = marketplace_listing_id and sa.owner_profile_id = auth.uid()
    )
  )
  with check (
    public.is_admin()
    or exists (
      select 1
      from public.horse_listings hl
      join public.seller_accounts sa on sa.id = hl.seller_account_id
      where hl.id = horse_listing_id and sa.owner_profile_id = auth.uid()
    )
    or exists (
      select 1
      from public.marketplace_listings ml
      join public.seller_accounts sa on sa.id = ml.seller_account_id
      where ml.id = marketplace_listing_id and sa.owner_profile_id = auth.uid()
    )
  );

create policy "Authenticated users can read visible auction bids"
  on public.bids for select
  to authenticated
  using (
    bidder_profile_id = auth.uid()
    or public.is_admin()
    or exists (
      select 1 from public.auctions a
      where a.id = auction_id and a.status in ('live', 'extended', 'closed', 'settled')
    )
  );

create policy "Authenticated users can place their own live bids"
  on public.bids for insert
  to authenticated
  with check (
    bidder_profile_id = auth.uid()
    and exists (
      select 1 from public.auctions a
      where a.id = auction_id
        and a.status in ('live', 'extended')
        and a.starts_at <= now()
        and a.ends_at > now()
    )
  );

create policy "Admins can manage bids"
  on public.bids for all
  to authenticated
  using (public.is_admin())
  with check (public.is_admin());

create policy "Users manage their watchlist"
  on public.watchlists for all
  to authenticated
  using (profile_id = auth.uid() or public.is_admin())
  with check (profile_id = auth.uid() or public.is_admin());

create policy "Public can read images for published listings"
  on public.listing_images for select
  to anon, authenticated
  using (
    exists (
      select 1 from public.horse_listings hl
      where hl.id = horse_listing_id
        and hl.status in ('published', 'under_offer', 'sold')
        and hl.published_at is not null
    )
    or exists (
      select 1 from public.marketplace_listings ml
      where ml.id = marketplace_listing_id
        and ml.status in ('published', 'under_offer', 'sold')
        and ml.published_at is not null
    )
  );

create policy "Sellers manage listing images"
  on public.listing_images for all
  to authenticated
  using (
    public.is_admin()
    or exists (
      select 1
      from public.horse_listings hl
      join public.seller_accounts sa on sa.id = hl.seller_account_id
      where hl.id = horse_listing_id and sa.owner_profile_id = auth.uid()
    )
    or exists (
      select 1
      from public.marketplace_listings ml
      join public.seller_accounts sa on sa.id = ml.seller_account_id
      where ml.id = marketplace_listing_id and sa.owner_profile_id = auth.uid()
    )
  )
  with check (
    public.is_admin()
    or exists (
      select 1
      from public.horse_listings hl
      join public.seller_accounts sa on sa.id = hl.seller_account_id
      where hl.id = horse_listing_id and sa.owner_profile_id = auth.uid()
    )
    or exists (
      select 1
      from public.marketplace_listings ml
      join public.seller_accounts sa on sa.id = ml.seller_account_id
      where ml.id = marketplace_listing_id and sa.owner_profile_id = auth.uid()
    )
  );

create policy "Enquiry participants can read enquiries"
  on public.enquiries for select
  to authenticated
  using (
    sender_profile_id = auth.uid()
    or public.is_admin()
    or exists (
      select 1 from public.seller_accounts sa
      where sa.id = seller_account_id and sa.owner_profile_id = auth.uid()
    )
  );

create policy "Authenticated users can create enquiries"
  on public.enquiries for insert
  to authenticated
  with check (sender_profile_id = auth.uid() or public.is_admin());

create policy "Sellers and senders can update enquiries"
  on public.enquiries for update
  to authenticated
  using (
    sender_profile_id = auth.uid()
    or public.is_admin()
    or exists (
      select 1 from public.seller_accounts sa
      where sa.id = seller_account_id and sa.owner_profile_id = auth.uid()
    )
  )
  with check (
    sender_profile_id = auth.uid()
    or public.is_admin()
    or exists (
      select 1 from public.seller_accounts sa
      where sa.id = seller_account_id and sa.owner_profile_id = auth.uid()
    )
  );

create policy "Sellers manage payment accounts"
  on public.payment_accounts for all
  to authenticated
  using (
    public.is_admin()
    or exists (
      select 1 from public.seller_accounts sa
      where sa.id = seller_account_id and sa.owner_profile_id = auth.uid()
    )
  )
  with check (
    public.is_admin()
    or exists (
      select 1 from public.seller_accounts sa
      where sa.id = seller_account_id and sa.owner_profile_id = auth.uid()
    )
  );

create policy "Payment intent participants can read placeholders"
  on public.payment_intents for select
  to authenticated
  using (
    buyer_profile_id = auth.uid()
    or public.is_admin()
    or exists (
      select 1 from public.seller_accounts sa
      where sa.id = seller_account_id and sa.owner_profile_id = auth.uid()
    )
  );

create policy "Authenticated users can create placeholder payment intents"
  on public.payment_intents for insert
  to authenticated
  with check (buyer_profile_id = auth.uid() or public.is_admin());

create policy "Admins manage payment intents"
  on public.payment_intents for all
  to authenticated
  using (public.is_admin())
  with check (public.is_admin());

create policy "Payment events are admin only"
  on public.payment_events for all
  to authenticated
  using (public.is_admin())
  with check (public.is_admin());


-- ============================================================================
-- supabase/migrations/202605280002_seed_categories.sql
-- ============================================================================
-- Baseline HarnessBid v2 categories inferred from the v0 UI.

insert into public.categories (category_type, name, slug, sort_order, metadata)
values
  ('horse', 'Pacers', 'horses-pacers', 10, '{"ui_path":"/auctions?category=pacers"}'),
  ('horse', 'Trotters', 'horses-trotters', 20, '{"ui_path":"/auctions?category=trotters"}'),
  ('horse', 'Yearlings', 'horses-yearlings', 30, '{"ui_path":"/auctions?category=yearlings"}'),
  ('horse', 'Broodmares', 'horses-broodmares', 40, '{"ui_path":"/auctions?category=broodmares"}'),
  ('horse', 'Stallions', 'horses-stallions', 50, '{"ui_path":"/auctions?category=stallions"}'),
  ('marketplace', 'Equipment', 'equipment', 90, '{"ui_path":"/marketplace/equipment"}'),
  ('marketplace', 'Race Bikes & Sulkies', 'bikes-sulkies', 100, '{"ui_path":"/marketplace/bikes-sulkies"}'),
  ('marketplace', 'Harness & Tack', 'harness-tack', 110, '{"ui_path":"/marketplace/harness-tack"}'),
  ('marketplace', 'Helmets & Safety Gear', 'safety-gear', 120, '{"ui_path":"/marketplace/safety-gear"}'),
  ('marketplace', 'Walking Machines', 'walking-machines', 130, '{"ui_path":"/marketplace/walking-machines"}'),
  ('marketplace', 'Joggers & Training Carts', 'joggers', 140, '{"ui_path":"/marketplace/joggers"}'),
  ('marketplace', 'Memorabilia', 'memorabilia', 150, '{"ui_path":"/marketplace/memorabilia"}'),
  ('marketplace', 'Vehicles & Floats', 'vehicles', 160, '{"ui_path":"/marketplace/vehicles"}'),
  ('service', 'Services', 'services', 200, '{"ui_path":"/marketplace/services"}'),
  ('marketplace', 'Property', 'property', 210, '{"ui_path":"/marketplace/property"}'),
  ('marketplace', 'Feed & Supplements', 'feed', 220, '{"ui_path":"/marketplace/feed"}'),
  ('service', 'Breeding', 'breeding', 230, '{"ui_path":"/marketplace/breeding"}'),
  ('marketplace', 'Apparel', 'apparel', 240, '{"ui_path":"/marketplace/apparel"}'),
  ('marketplace', 'Other', 'other', 250, '{"ui_path":"/marketplace/other"}')
on conflict (slug) do update
set
  category_type = excluded.category_type,
  name = excluded.name,
  sort_order = excluded.sort_order,
  metadata = public.categories.metadata || excluded.metadata,
  updated_at = now();


-- ============================================================================
-- supabase/migrations/202605290001_auth_profile_trigger.sql
-- ============================================================================
-- Auto-create a public.profiles row whenever a new auth user is created.
-- This is the canonical Supabase pattern and keeps profile creation reliable
-- even when email confirmation is enabled (no client session at signup time)
-- or when accounts are created via OAuth/admin APIs.
--
-- The application also performs a best-effort profile upsert after password
-- signup as a fallback; `on conflict do nothing` here keeps the two paths
-- from clobbering each other.

create or replace function public.handle_new_user()
returns trigger
language plpgsql
security definer
set search_path = public
as $$
begin
  insert into public.profiles (id, email, full_name, display_name)
  values (
    new.id,
    new.email,
    nullif(new.raw_user_meta_data ->> 'full_name', ''),
    coalesce(
      nullif(new.raw_user_meta_data ->> 'full_name', ''),
      split_part(coalesce(new.email, 'member'), '@', 1)
    )
  )
  on conflict (id) do nothing;
  return new;
end;
$$;

drop trigger if exists on_auth_user_created on auth.users;

create trigger on_auth_user_created
  after insert on auth.users
  for each row execute function public.handle_new_user();


-- ============================================================================
-- supabase/migrations/202605290002_listing_images_storage.sql
-- ============================================================================
-- Storage bucket + policies for seller listing image uploads (Phase 13).
--
-- Path convention: <auth.uid()>/<listing-scope>/<filename>
-- The first path segment must equal the uploader's auth uid, so sellers can
-- only write under their own prefix. Reads are public so listing images render
-- on public marketplace/auction pages. DB-level access to the listing_images
-- table remains governed by the existing RLS policies.

insert into storage.buckets (id, name, public, file_size_limit, allowed_mime_types)
values (
  'listing-images',
  'listing-images',
  true,
  10485760, -- 10 MiB per file
  array['image/jpeg', 'image/png', 'image/webp', 'image/avif']
)
on conflict (id) do update
  set public = excluded.public,
      file_size_limit = excluded.file_size_limit,
      allowed_mime_types = excluded.allowed_mime_types;

-- Public read of listing images.
drop policy if exists "Public read listing images" on storage.objects;
create policy "Public read listing images"
  on storage.objects for select
  to anon, authenticated
  using (bucket_id = 'listing-images');

-- Authenticated sellers can upload under their own uid prefix.
drop policy if exists "Sellers upload own listing images" on storage.objects;
create policy "Sellers upload own listing images"
  on storage.objects for insert
  to authenticated
  with check (
    bucket_id = 'listing-images'
    and (storage.foldername(name))[1] = auth.uid()::text
  );

drop policy if exists "Sellers update own listing images" on storage.objects;
create policy "Sellers update own listing images"
  on storage.objects for update
  to authenticated
  using (
    bucket_id = 'listing-images'
    and (storage.foldername(name))[1] = auth.uid()::text
  )
  with check (
    bucket_id = 'listing-images'
    and (storage.foldername(name))[1] = auth.uid()::text
  );

drop policy if exists "Sellers delete own listing images" on storage.objects;
create policy "Sellers delete own listing images"
  on storage.objects for delete
  to authenticated
  using (
    bucket_id = 'listing-images'
    and (storage.foldername(name))[1] = auth.uid()::text
  );


-- ============================================================================
-- supabase/migrations/202605290003_notifications_saved_searches.sql
-- ============================================================================
-- Phase 14B: notifications + saved searches (engagement layer).
--
-- Notifications are created server-side only:
--  - cross-user notifications (e.g. enquiry -> seller) are produced by a
--    SECURITY DEFINER trigger, so no service-role key is needed on the client.
--  - users can read/update/delete only their own notification rows via RLS.

-- ---------------------------------------------------------------------------
-- notifications
-- ---------------------------------------------------------------------------
create table public.notifications (
  id uuid primary key default gen_random_uuid(),
  profile_id uuid not null references public.profiles(id) on delete cascade,
  type text not null,
  title text not null,
  body text,
  link_url text,
  related_entity_type text,
  related_entity_id uuid,
  read_at timestamptz,
  created_at timestamptz not null default now()
);

create index notifications_profile_idx on public.notifications(profile_id, created_at desc);
create index notifications_unread_idx on public.notifications(profile_id) where read_at is null;

alter table public.notifications enable row level security;

create policy "Users read their own notifications"
  on public.notifications for select
  to authenticated
  using (profile_id = auth.uid() or public.is_admin());

create policy "Users insert their own notifications"
  on public.notifications for insert
  to authenticated
  with check (profile_id = auth.uid() or public.is_admin());

create policy "Users update their own notifications"
  on public.notifications for update
  to authenticated
  using (profile_id = auth.uid() or public.is_admin())
  with check (profile_id = auth.uid() or public.is_admin());

create policy "Users delete their own notifications"
  on public.notifications for delete
  to authenticated
  using (profile_id = auth.uid() or public.is_admin());

-- ---------------------------------------------------------------------------
-- saved_searches
-- ---------------------------------------------------------------------------
create table public.saved_searches (
  id uuid primary key default gen_random_uuid(),
  profile_id uuid not null references public.profiles(id) on delete cascade,
  name text not null,
  search_type text not null,
  filters jsonb not null default '{}'::jsonb,
  alert_enabled boolean not null default false,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);

create index saved_searches_profile_idx on public.saved_searches(profile_id, created_at desc);

create trigger set_saved_searches_updated_at
  before update on public.saved_searches
  for each row execute function public.set_updated_at();

alter table public.saved_searches enable row level security;

create policy "Users manage their saved searches"
  on public.saved_searches for all
  to authenticated
  using (profile_id = auth.uid() or public.is_admin())
  with check (profile_id = auth.uid() or public.is_admin());

-- ---------------------------------------------------------------------------
-- Enquiry -> seller notification (server-side, no service role).
-- SECURITY DEFINER so it can write a notification row for the seller's owner
-- profile regardless of who created the enquiry. One notification per enquiry
-- insert, so no duplicates.
-- ---------------------------------------------------------------------------
create or replace function public.notify_seller_of_enquiry()
returns trigger
language plpgsql
security definer
set search_path = public
as $$
declare
  owner_id uuid;
begin
  select owner_profile_id into owner_id
  from public.seller_accounts
  where id = new.seller_account_id;

  if owner_id is not null then
    insert into public.notifications (
      profile_id, type, title, body, link_url, related_entity_type, related_entity_id
    )
    values (
      owner_id,
      'enquiry_received',
      coalesce(new.subject, 'New enquiry'),
      left(new.message, 280),
      '/dashboard/messages',
      'enquiry',
      new.id
    );
  end if;

  return new;
end;
$$;

drop trigger if exists on_enquiry_created on public.enquiries;

create trigger on_enquiry_created
  after insert on public.enquiries
  for each row execute function public.notify_seller_of_enquiry();


-- ============================================================================
-- supabase/migrations/202605290004_auction_bidding_engine.sql
-- ============================================================================
-- Phase 15: Auction bidding engine.
--
-- All bid mutation logic lives in SECURITY DEFINER Postgres functions that lock
-- the auction row (FOR UPDATE) so concurrent bids are serialised and races are
-- impossible. The client/server only calls these RPCs; it never does
-- read-modify-write on auctions/bids. auth.uid() is read inside the functions
-- (from the request JWT) so identity is enforced server-side.
--
-- Proxy bidding: each bid stores `amount` (the visible price committed at that
-- moment) and `max_proxy_amount` (the bidder's secret ceiling). The current
-- leader is the single bid row with status 'winning'; its `amount` is kept in
-- sync with auctions.current_bid as competing proxies push the price up
-- (eBay-style automatic bidding).

-- ---------------------------------------------------------------------------
-- Outbid notification (reuses the Phase 14B notifications table). Definer so it
-- can write a notification for another user. De-duped within a short window.
-- ---------------------------------------------------------------------------
create or replace function public._notify_outbid(p_profile uuid, p_auction uuid)
returns void
language plpgsql
security definer
set search_path = public
as $$
declare
  v_title text;
  v_slug text;
begin
  if p_profile is null then return; end if;

  select hl.title, hl.slug into v_title, v_slug
  from public.auctions a
  join public.horse_listings hl on hl.id = a.horse_listing_id
  where a.id = p_auction;

  -- Avoid duplicate spam: one outbid notification per auction per 2 minutes.
  if exists (
    select 1 from public.notifications
    where profile_id = p_profile
      and type = 'outbid'
      and related_entity_id = p_auction
      and created_at > now() - interval '2 minutes'
  ) then
    return;
  end if;

  insert into public.notifications (
    profile_id, type, title, body, link_url, related_entity_type, related_entity_id
  )
  values (
    p_profile,
    'outbid',
    'You have been outbid',
    coalesce('You were outbid on ' || v_title, 'You were outbid on an auction'),
    '/auctions/' || coalesce(v_slug, p_auction::text),
    'auction',
    p_auction
  );
end;
$$;

-- ---------------------------------------------------------------------------
-- place_bid: the single entry point for bidding. p_max_amount is the bidder's
-- maximum (proxy ceiling); a one-shot bid simply sets max = desired amount.
-- Returns a jsonb result: { ok, error?, current_bid?, bid_count?, reserve_met?,
-- ends_at?, leading? }.
-- ---------------------------------------------------------------------------
create or replace function public.place_bid(p_auction_id uuid, p_max_amount numeric)
returns jsonb
language plpgsql
security definer
set search_path = public
as $$
declare
  v_uid uuid := auth.uid();
  a public.auctions%rowtype;
  v_owner uuid;
  v_inc numeric;
  v_min numeric;
  v_leader public.bids%rowtype;
  v_visible numeric;
  v_now timestamptz := now();
  v_result jsonb;
begin
  if v_uid is null then
    return jsonb_build_object('ok', false, 'error', 'auth');
  end if;
  if p_max_amount is null or p_max_amount <= 0 then
    return jsonb_build_object('ok', false, 'error', 'invalid_amount');
  end if;

  -- Serialise concurrent bids on this auction.
  select * into a from public.auctions where id = p_auction_id for update;
  if not found then
    return jsonb_build_object('ok', false, 'error', 'not_found');
  end if;

  if a.starts_at > v_now then
    return jsonb_build_object('ok', false, 'error', 'not_started');
  end if;
  if a.ends_at <= v_now or a.status in ('closed', 'settled', 'cancelled', 'draft') then
    return jsonb_build_object('ok', false, 'error', 'closed');
  end if;

  -- Resolve listing owner; sellers cannot bid on their own lot.
  if a.horse_listing_id is not null then
    select sa.owner_profile_id into v_owner
    from public.horse_listings hl
    join public.seller_accounts sa on sa.id = hl.seller_account_id
    where hl.id = a.horse_listing_id;
  elsif a.marketplace_listing_id is not null then
    select sa.owner_profile_id into v_owner
    from public.marketplace_listings ml
    join public.seller_accounts sa on sa.id = ml.seller_account_id
    where ml.id = a.marketplace_listing_id;
  end if;
  if v_owner = v_uid then
    return jsonb_build_object('ok', false, 'error', 'self');
  end if;

  v_inc := a.bid_increment;

  select * into v_leader
  from public.bids
  where auction_id = a.id and status = 'winning'
  order by placed_at desc
  limit 1;

  if v_leader.id is null then
    v_min := a.starting_bid;
  else
    v_min := coalesce(a.current_bid, a.starting_bid) + v_inc;
  end if;

  if p_max_amount < v_min then
    return jsonb_build_object('ok', false, 'error', 'too_low', 'min', v_min);
  end if;

  if v_leader.id is null then
    -- First bid: visible price sits at the starting bid.
    v_visible := a.starting_bid;
    insert into public.bids (auction_id, bidder_profile_id, amount, max_proxy_amount, status)
    values (a.id, v_uid, v_visible, p_max_amount, 'winning');
    update public.auctions set current_bid = v_visible, bid_count = bid_count + 1 where id = a.id;

  elsif v_leader.bidder_profile_id = v_uid then
    -- Current leader raising their own proxy ceiling.
    if p_max_amount <= v_leader.max_proxy_amount then
      return jsonb_build_object('ok', false, 'error', 'already_leading');
    end if;
    update public.bids set max_proxy_amount = p_max_amount where id = v_leader.id;
    v_visible := a.current_bid;

  elsif p_max_amount > v_leader.max_proxy_amount then
    -- Challenger outbids the leader's proxy and becomes the new leader.
    v_visible := least(p_max_amount, v_leader.max_proxy_amount + v_inc);
    update public.bids set status = 'outbid' where id = v_leader.id;
    insert into public.bids (auction_id, bidder_profile_id, amount, max_proxy_amount, status)
    values (a.id, v_uid, v_visible, p_max_amount, 'winning');
    update public.auctions set current_bid = v_visible, bid_count = bid_count + 1 where id = a.id;
    perform public._notify_outbid(v_leader.bidder_profile_id, a.id);

  else
    -- Challenger is covered by the leader's proxy: leader stays, price rises,
    -- challenger is immediately outbid.
    v_visible := least(v_leader.max_proxy_amount, p_max_amount + v_inc);
    insert into public.bids (auction_id, bidder_profile_id, amount, max_proxy_amount, status)
    values (a.id, v_uid, p_max_amount, p_max_amount, 'outbid');
    update public.bids set amount = v_visible where id = v_leader.id;
    update public.auctions set current_bid = v_visible, bid_count = bid_count + 1 where id = a.id;
    perform public._notify_outbid(v_uid, a.id);
  end if;

  -- Reserve.
  if a.reserve_price is not null then
    update public.auctions
    set reserve_met = (current_bid >= a.reserve_price)
    where id = a.id;
  end if;

  -- Anti-sniping: a bid inside the soft-close window extends the auction.
  if v_now > (a.ends_at - make_interval(secs => a.soft_close_seconds)) then
    update public.auctions
    set ends_at = v_now + make_interval(secs => a.soft_close_seconds),
        status = 'extended'
    where id = a.id;
  elsif a.status = 'scheduled' then
    update public.auctions set status = 'live' where id = a.id;
  end if;

  select jsonb_build_object(
    'ok', true,
    'current_bid', current_bid,
    'bid_count', bid_count,
    'reserve_met', reserve_met,
    'ends_at', ends_at,
    'leading', exists (
      select 1 from public.bids
      where auction_id = a.id and status = 'winning' and bidder_profile_id = v_uid
    )
  ) into v_result
  from public.auctions where id = a.id;

  return v_result;
end;
$$;

-- ---------------------------------------------------------------------------
-- Close handling + winner assignment (shared internal helper).
-- ---------------------------------------------------------------------------
create or replace function public._close_auction(p_auction uuid)
returns void
language plpgsql
security definer
set search_path = public
as $$
declare
  a public.auctions%rowtype;
  v_leader public.bids%rowtype;
  v_reserve_ok boolean;
begin
  select * into a from public.auctions where id = p_auction for update;
  if not found or a.status in ('closed', 'settled', 'cancelled') then
    return;
  end if;

  select * into v_leader
  from public.bids
  where auction_id = a.id and status = 'winning'
  order by placed_at desc
  limit 1;

  v_reserve_ok := (a.reserve_price is null or coalesce(a.current_bid, 0) >= a.reserve_price);

  if v_leader.id is not null and v_reserve_ok then
    update public.bids set status = 'won' where id = v_leader.id;
    update public.auctions
    set status = 'closed', winner_profile_id = v_leader.bidder_profile_id, reserve_met = true
    where id = a.id;

    insert into public.notifications (
      profile_id, type, title, body, link_url, related_entity_type, related_entity_id
    )
    select v_leader.bidder_profile_id,
           'auction_won',
           'You won the auction',
           'Congratulations — you won ' || coalesce(hl.title, 'an auction') || '.',
           '/auctions/' || coalesce(hl.slug, a.id::text),
           'auction',
           a.id
    from public.horse_listings hl
    where hl.id = a.horse_listing_id;
  else
    -- No winner (no bids, or reserve not met). settled remains a later phase.
    update public.auctions set status = 'closed' where id = a.id;
  end if;
end;
$$;

-- Lazy close: callable by the app when an ended auction is viewed.
create or replace function public.close_auction_if_ended(p_auction uuid)
returns void
language plpgsql
security definer
set search_path = public
as $$
declare
  v_status public.auction_status;
  v_ends timestamptz;
begin
  select status, ends_at into v_status, v_ends from public.auctions where id = p_auction;
  if not found then return; end if;
  if v_ends <= now() and v_status in ('scheduled', 'live', 'extended') then
    perform public._close_auction(p_auction);
  end if;
end;
$$;

-- Scheduled sweep: flips scheduled->live and closes ended auctions. Intended to
-- be run by pg_cron (preferred) every minute.
create or replace function public.process_auction_transitions()
returns void
language plpgsql
security definer
set search_path = public
as $$
declare
  r record;
begin
  update public.auctions
  set status = 'live'
  where status = 'scheduled' and starts_at <= now() and ends_at > now();

  for r in
    select id from public.auctions
    where status in ('scheduled', 'live', 'extended') and ends_at <= now()
  loop
    perform public._close_auction(r.id);
  end loop;
end;
$$;

-- Anonymised, public-safe bid history for visible auctions.
create or replace function public.get_auction_bid_history(p_auction uuid)
returns table (bidder_label text, amount numeric, status public.bid_status, placed_at timestamptz)
language sql
security definer
stable
set search_path = public
as $$
  select 'Bidder ' || upper(left(md5(b.bidder_profile_id::text), 4)) as bidder_label,
         b.amount,
         b.status,
         b.placed_at
  from public.bids b
  join public.auctions a on a.id = b.auction_id
  where b.auction_id = p_auction
    and a.status in ('live', 'extended', 'closed', 'settled')
  order by b.placed_at desc
  limit 50;
$$;

-- ---------------------------------------------------------------------------
-- Permissions: lock down internals; expose only the safe entry points.
-- ---------------------------------------------------------------------------
revoke all on function public._notify_outbid(uuid, uuid) from public;
revoke all on function public._close_auction(uuid) from public;
revoke all on function public.process_auction_transitions() from public;

grant execute on function public.place_bid(uuid, numeric) to authenticated;
grant execute on function public.close_auction_if_ended(uuid) to authenticated;
grant execute on function public.get_auction_bid_history(uuid) to anon, authenticated;

-- Scheduling (run in the Supabase SQL editor once pg_cron is enabled):
--   select cron.schedule(
--     'auction-transitions', '* * * * *',
--     $$select public.process_auction_transitions();$$
--   );


-- ============================================================================
-- supabase/migrations/202605290005_admin_moderation.sql
-- ============================================================================
-- Phase 16: admin + moderation layer.
--
-- Adds staff roles, a staff predicate, additive (permissive) RLS so staff can
-- manage listings/auctions/enterprise/sale events, and lightweight reports +
-- moderation-log tables. Existing seller/owner/admin policies are untouched —
-- the new policies sit alongside them.

-- New roles. We compare role::text in is_staff() (below) rather than using the
-- new enum literals directly, so this stays safe within a single migration tx.
alter type public.app_role add value if not exists 'moderator';
alter type public.app_role add value if not exists 'enterprise_manager';

-- Staff predicate: admins and moderators.
create or replace function public.is_staff()
returns boolean
language sql
stable
security definer
set search_path = public
as $$
  select coalesce(public.current_profile_role()::text in ('admin', 'moderator'), false)
$$;

-- ---------------------------------------------------------------------------
-- Additive staff management policies (PERMISSIVE => OR'd with existing ones).
-- `for all` covers select too, so staff can see pending/draft/rejected rows.
-- ---------------------------------------------------------------------------
create policy "Staff manage horse listings"
  on public.horse_listings for all
  to authenticated
  using (public.is_staff()) with check (public.is_staff());

create policy "Staff manage marketplace listings"
  on public.marketplace_listings for all
  to authenticated
  using (public.is_staff()) with check (public.is_staff());

create policy "Staff manage auctions"
  on public.auctions for all
  to authenticated
  using (public.is_staff()) with check (public.is_staff());

create policy "Staff manage enterprise sellers"
  on public.enterprise_sellers for all
  to authenticated
  using (public.is_staff()) with check (public.is_staff());

create policy "Staff read all seller accounts"
  on public.seller_accounts for select
  to authenticated
  using (public.is_staff());

create policy "Staff manage sale events"
  on public.sale_events for all
  to authenticated
  using (public.is_staff()) with check (public.is_staff());

-- ---------------------------------------------------------------------------
-- listing_reports: user-submitted reports of listings.
-- ---------------------------------------------------------------------------
create table public.listing_reports (
  id uuid primary key default gen_random_uuid(),
  reporter_profile_id uuid references public.profiles(id) on delete set null,
  horse_listing_id uuid references public.horse_listings(id) on delete cascade,
  marketplace_listing_id uuid references public.marketplace_listings(id) on delete cascade,
  reason text not null,
  details text,
  status text not null default 'open',
  reviewer_profile_id uuid references public.profiles(id) on delete set null,
  reviewed_at timestamptz,
  created_at timestamptz not null default now(),
  constraint listing_reports_one_target check (
    num_nonnulls(horse_listing_id, marketplace_listing_id) = 1
  )
);

create index listing_reports_status_idx on public.listing_reports(status, created_at desc);

alter table public.listing_reports enable row level security;

create policy "Users create their own reports"
  on public.listing_reports for insert
  to authenticated
  with check (reporter_profile_id = auth.uid());

create policy "Reporters and staff read reports"
  on public.listing_reports for select
  to authenticated
  using (reporter_profile_id = auth.uid() or public.is_staff());

create policy "Staff manage reports"
  on public.listing_reports for update
  to authenticated
  using (public.is_staff()) with check (public.is_staff());

-- ---------------------------------------------------------------------------
-- moderation_logs: lightweight audit trail / admin action tracking / status
-- history placeholder. Staff-only.
-- ---------------------------------------------------------------------------
create table public.moderation_logs (
  id uuid primary key default gen_random_uuid(),
  actor_profile_id uuid references public.profiles(id) on delete set null,
  action text not null,
  entity_type text not null,
  entity_id uuid,
  notes text,
  created_at timestamptz not null default now()
);

create index moderation_logs_entity_idx on public.moderation_logs(entity_type, entity_id, created_at desc);

alter table public.moderation_logs enable row level security;

create policy "Staff read moderation logs"
  on public.moderation_logs for select
  to authenticated
  using (public.is_staff());

create policy "Staff write moderation logs"
  on public.moderation_logs for insert
  to authenticated
  with check (public.is_staff() and actor_profile_id = auth.uid());


-- ============================================================================
-- supabase/migrations/202605290006_payments_commercial.sql
-- ============================================================================
-- Phase 17: payments + commercial logic (architecture / placeholders).
--
-- Adds commercial status fields and billing tables WITHOUT changing existing
-- listing/auction flows. All new status columns are text (forward-proof, no
-- enum-alter churn) with conservative defaults so existing rows stay valid.
-- No funds move here — this is the schema + status foundation for Stripe.

-- --------------------------------------------------------------------------
-- Seller billing model. Individual sellers default to per-listing fees;
-- enterprise sellers are typically invoiced; admins can grant fee exemptions.
-- --------------------------------------------------------------------------
alter table public.seller_accounts
  add column if not exists billing_mode text not null default 'per_listing',
  add column if not exists fee_exempt boolean not null default false;

-- --------------------------------------------------------------------------
-- Listing fee + featured status placeholders. featured_until added to
-- horse_listings to match marketplace_listings (admin/featured controls).
-- Status values: none | due | paid | waived | exempt
-- --------------------------------------------------------------------------
alter table public.horse_listings
  add column if not exists listing_fee_status text not null default 'none',
  add column if not exists featured_fee_status text not null default 'none',
  add column if not exists featured_until timestamptz;

alter table public.marketplace_listings
  add column if not exists listing_fee_status text not null default 'none',
  add column if not exists featured_fee_status text not null default 'none';

-- --------------------------------------------------------------------------
-- Auction deposits + settlement placeholders.
-- settlement_status: pending | invoiced | settled | cancelled
-- --------------------------------------------------------------------------
alter table public.auctions
  add column if not exists deposit_required boolean not null default false,
  add column if not exists deposit_amount numeric(14,2),
  add column if not exists settlement_status text not null default 'pending';

-- --------------------------------------------------------------------------
-- payment_intents: classify intent purpose + track refunds.
-- purpose: listing_fee | featured_upgrade | auction_deposit |
--          auction_settlement | other
-- --------------------------------------------------------------------------
alter table public.payment_intents
  add column if not exists purpose text not null default 'other',
  add column if not exists refunded_amount numeric(14,2);

-- --------------------------------------------------------------------------
-- auction_deposits: bidder eligibility / refundable deposit placeholders.
-- status: none | held | captured | refunded | forfeited
-- --------------------------------------------------------------------------
create table if not exists public.auction_deposits (
  id uuid primary key default gen_random_uuid(),
  auction_id uuid not null references public.auctions(id) on delete cascade,
  bidder_profile_id uuid not null references public.profiles(id) on delete cascade,
  amount numeric(14,2) not null default 0 check (amount >= 0),
  status text not null default 'none',
  payment_intent_id uuid references public.payment_intents(id) on delete set null,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now(),
  unique (auction_id, bidder_profile_id)
);

create index if not exists auction_deposits_auction_idx on public.auction_deposits(auction_id);

alter table public.auction_deposits enable row level security;

create policy "Bidders read their deposits"
  on public.auction_deposits for select
  to authenticated
  using (bidder_profile_id = auth.uid() or public.is_staff());

create policy "Bidders create their deposits"
  on public.auction_deposits for insert
  to authenticated
  with check (bidder_profile_id = auth.uid());

create policy "Staff manage deposits"
  on public.auction_deposits for all
  to authenticated
  using (public.is_staff()) with check (public.is_staff());

-- --------------------------------------------------------------------------
-- invoices: enterprise + event billing (APG/Nutrien-style), admin-managed.
-- status: draft | issued | paid | void | overdue
-- --------------------------------------------------------------------------
create table if not exists public.invoices (
  id uuid primary key default gen_random_uuid(),
  seller_account_id uuid references public.seller_accounts(id) on delete set null,
  enterprise_seller_id uuid references public.enterprise_sellers(id) on delete set null,
  sale_event_id uuid references public.sale_events(id) on delete set null,
  status text not null default 'draft',
  currency char(3) not null default 'USD',
  amount numeric(14,2) not null default 0 check (amount >= 0),
  due_at timestamptz,
  issued_at timestamptz,
  paid_at timestamptz,
  provider_reference text,
  line_items jsonb not null default '[]'::jsonb,
  metadata jsonb not null default '{}'::jsonb,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);

create index if not exists invoices_seller_idx on public.invoices(seller_account_id, created_at desc);

alter table public.invoices enable row level security;

create policy "Sellers read their invoices"
  on public.invoices for select
  to authenticated
  using (
    public.is_staff()
    or exists (
      select 1 from public.seller_accounts sa
      where sa.id = seller_account_id and sa.owner_profile_id = auth.uid()
    )
  );

create policy "Staff manage invoices"
  on public.invoices for all
  to authenticated
  using (public.is_staff()) with check (public.is_staff());

-- --------------------------------------------------------------------------
-- payouts: seller payout / settlement tracking placeholders.
-- status: pending | processing | paid | failed | reversed
-- --------------------------------------------------------------------------
create table if not exists public.payouts (
  id uuid primary key default gen_random_uuid(),
  seller_account_id uuid not null references public.seller_accounts(id) on delete cascade,
  amount numeric(14,2) not null default 0 check (amount >= 0),
  currency char(3) not null default 'USD',
  status text not null default 'pending',
  provider_reference text,
  payment_intent_id uuid references public.payment_intents(id) on delete set null,
  scheduled_at timestamptz,
  paid_at timestamptz,
  metadata jsonb not null default '{}'::jsonb,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);

create index if not exists payouts_seller_idx on public.payouts(seller_account_id, created_at desc);

alter table public.payouts enable row level security;

create policy "Sellers read their payouts"
  on public.payouts for select
  to authenticated
  using (
    public.is_staff()
    or exists (
      select 1 from public.seller_accounts sa
      where sa.id = seller_account_id and sa.owner_profile_id = auth.uid()
    )
  );

create policy "Staff manage payouts"
  on public.payouts for all
  to authenticated
  using (public.is_staff()) with check (public.is_staff());

-- updated_at triggers for new tables.
create trigger set_auction_deposits_updated_at
  before update on public.auction_deposits
  for each row execute function public.set_updated_at();
create trigger set_invoices_updated_at
  before update on public.invoices
  for each row execute function public.set_updated_at();
create trigger set_payouts_updated_at
  before update on public.payouts
  for each row execute function public.set_updated_at();


-- ============================================================================
-- supabase/migrations/202605290007_sale_event_catalogue.sql
-- ============================================================================
-- Phase 18: enterprise sale events + catalogue mode.
--
-- Adds lot numbering/ordering to listings and event ordering/featured flags.
-- Listings are assigned to an event via the existing `sale_event_id` column;
-- these fields add catalogue presentation. No RLS changes — existing public
-- read (published listings / visible events) and staff-manage policies apply.

alter table public.sale_events
  add column if not exists featured boolean not null default false,
  add column if not exists sort_order integer not null default 0;

alter table public.horse_listings
  add column if not exists lot_number text,
  add column if not exists lot_order integer;

-- marketplace_listings can also be lots in a sale event (e.g. dispersal sales).
alter table public.marketplace_listings
  add column if not exists sale_event_id uuid references public.sale_events(id) on delete set null,
  add column if not exists lot_number text,
  add column if not exists lot_order integer;

create index if not exists horse_listings_sale_event_idx
  on public.horse_listings(sale_event_id, lot_order);
create index if not exists marketplace_listings_sale_event_idx
  on public.marketplace_listings(sale_event_id, lot_order);
create index if not exists sale_events_featured_idx
  on public.sale_events(featured, sort_order);


-- ============================================================================
-- supabase/migrations/202605290008_conversations_messages.sql
-- ============================================================================
-- Phase 19: conversations + messages (buyer/seller threads).
--
-- Enquiries are preserved exactly as-is. A SECURITY DEFINER trigger turns each
-- enquiry into a conversation seeded with the enquiry text as the first
-- message, so existing enquiry creation (and the Phase 14B seller notification)
-- keeps working unchanged. Messages drive a 'message' notification to the
-- recipient (reusing the notifications system) and keep the conversation's
-- last_message_at fresh for inbox sorting. Unread is computed from messages
-- (no counters) to avoid race conditions.

create table public.conversations (
  id uuid primary key default gen_random_uuid(),
  enquiry_id uuid unique references public.enquiries(id) on delete set null,
  buyer_profile_id uuid not null references public.profiles(id) on delete cascade,
  seller_account_id uuid not null references public.seller_accounts(id) on delete cascade,
  seller_owner_profile_id uuid not null references public.profiles(id) on delete cascade,
  horse_listing_id uuid references public.horse_listings(id) on delete set null,
  marketplace_listing_id uuid references public.marketplace_listings(id) on delete set null,
  sale_event_id uuid references public.sale_events(id) on delete set null,
  subject text,
  last_message_at timestamptz not null default now(),
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);

create index conversations_buyer_idx on public.conversations(buyer_profile_id, last_message_at desc);
create index conversations_seller_idx on public.conversations(seller_owner_profile_id, last_message_at desc);

create table public.messages (
  id uuid primary key default gen_random_uuid(),
  conversation_id uuid not null references public.conversations(id) on delete cascade,
  sender_profile_id uuid not null references public.profiles(id) on delete cascade,
  body text not null,
  attachment_url text,
  attachment_type text,
  read_at timestamptz,
  created_at timestamptz not null default now()
);

create index messages_conversation_idx on public.messages(conversation_id, created_at);

-- --------------------------------------------------------------------------
-- RLS: only participants (buyer / seller owner) or staff may access.
-- --------------------------------------------------------------------------
alter table public.conversations enable row level security;

create policy "Participants read conversations"
  on public.conversations for select
  to authenticated
  using (
    buyer_profile_id = auth.uid()
    or seller_owner_profile_id = auth.uid()
    or public.is_staff()
  );

create policy "Participants create conversations"
  on public.conversations for insert
  to authenticated
  with check (buyer_profile_id = auth.uid() or seller_owner_profile_id = auth.uid());

create policy "Participants update conversations"
  on public.conversations for update
  to authenticated
  using (buyer_profile_id = auth.uid() or seller_owner_profile_id = auth.uid())
  with check (buyer_profile_id = auth.uid() or seller_owner_profile_id = auth.uid());

alter table public.messages enable row level security;

create policy "Participants read messages"
  on public.messages for select
  to authenticated
  using (
    public.is_staff()
    or exists (
      select 1 from public.conversations c
      where c.id = conversation_id
        and (c.buyer_profile_id = auth.uid() or c.seller_owner_profile_id = auth.uid())
    )
  );

create policy "Participants send messages"
  on public.messages for insert
  to authenticated
  with check (
    sender_profile_id = auth.uid()
    and exists (
      select 1 from public.conversations c
      where c.id = conversation_id
        and (c.buyer_profile_id = auth.uid() or c.seller_owner_profile_id = auth.uid())
    )
  );

create policy "Participants mark messages read"
  on public.messages for update
  to authenticated
  using (
    exists (
      select 1 from public.conversations c
      where c.id = conversation_id
        and (c.buyer_profile_id = auth.uid() or c.seller_owner_profile_id = auth.uid())
    )
  )
  with check (
    exists (
      select 1 from public.conversations c
      where c.id = conversation_id
        and (c.buyer_profile_id = auth.uid() or c.seller_owner_profile_id = auth.uid())
    )
  );

create trigger set_conversations_updated_at
  before update on public.conversations
  for each row execute function public.set_updated_at();

-- --------------------------------------------------------------------------
-- Enquiry -> conversation + first message (SECURITY DEFINER).
-- --------------------------------------------------------------------------
create or replace function public.create_conversation_from_enquiry()
returns trigger
language plpgsql
security definer
set search_path = public
as $$
declare
  v_owner uuid;
  v_conversation uuid;
begin
  if new.sender_profile_id is null then
    return new;
  end if;

  select owner_profile_id into v_owner
  from public.seller_accounts
  where id = new.seller_account_id;
  if v_owner is null or v_owner = new.sender_profile_id then
    return new; -- no distinct counterpart
  end if;

  insert into public.conversations (
    enquiry_id, buyer_profile_id, seller_account_id, seller_owner_profile_id,
    horse_listing_id, marketplace_listing_id, sale_event_id, subject, last_message_at
  )
  values (
    new.id, new.sender_profile_id, new.seller_account_id, v_owner,
    new.horse_listing_id, new.marketplace_listing_id, new.sale_event_id,
    coalesce(new.subject, 'Enquiry'), now()
  )
  returning id into v_conversation;

  insert into public.messages (conversation_id, sender_profile_id, body)
  values (v_conversation, new.sender_profile_id, new.message);

  return new;
end;
$$;

drop trigger if exists on_enquiry_create_conversation on public.enquiries;
create trigger on_enquiry_create_conversation
  after insert on public.enquiries
  for each row execute function public.create_conversation_from_enquiry();

-- --------------------------------------------------------------------------
-- New message -> bump conversation + notify the recipient (deduped).
-- --------------------------------------------------------------------------
create or replace function public.handle_new_message()
returns trigger
language plpgsql
security definer
set search_path = public
as $$
declare
  c public.conversations%rowtype;
  v_recipient uuid;
begin
  select * into c from public.conversations where id = new.conversation_id;
  if not found then return new; end if;

  update public.conversations set last_message_at = new.created_at where id = c.id;

  v_recipient := case
    when new.sender_profile_id = c.buyer_profile_id then c.seller_owner_profile_id
    else c.buyer_profile_id
  end;

  if v_recipient is null or v_recipient = new.sender_profile_id then
    return new;
  end if;

  -- Dedupe: at most one unread 'message' notification per conversation/minute.
  if exists (
    select 1 from public.notifications
    where profile_id = v_recipient
      and type = 'message'
      and related_entity_id = c.id
      and read_at is null
      and created_at > now() - interval '1 minute'
  ) then
    return new;
  end if;

  insert into public.notifications (
    profile_id, type, title, body, link_url, related_entity_type, related_entity_id
  )
  values (
    v_recipient,
    'message',
    'New message',
    left(new.body, 200),
    '/dashboard/messages/' || c.id,
    'conversation',
    c.id
  );

  return new;
end;
$$;

drop trigger if exists on_message_insert on public.messages;
create trigger on_message_insert
  after insert on public.messages
  for each row execute function public.handle_new_message();

-- --------------------------------------------------------------------------
-- Backfill: turn existing enquiries into conversations + first messages.
-- --------------------------------------------------------------------------
insert into public.conversations (
  enquiry_id, buyer_profile_id, seller_account_id, seller_owner_profile_id,
  horse_listing_id, marketplace_listing_id, sale_event_id, subject, last_message_at, created_at
)
select e.id, e.sender_profile_id, e.seller_account_id, sa.owner_profile_id,
       e.horse_listing_id, e.marketplace_listing_id, e.sale_event_id,
       coalesce(e.subject, 'Enquiry'), e.created_at, e.created_at
from public.enquiries e
join public.seller_accounts sa on sa.id = e.seller_account_id
where e.sender_profile_id is not null
  and sa.owner_profile_id is not null
  and sa.owner_profile_id <> e.sender_profile_id
  and not exists (select 1 from public.conversations c where c.enquiry_id = e.id);

insert into public.messages (conversation_id, sender_profile_id, body, created_at)
select c.id, e.sender_profile_id, e.message, e.created_at
from public.conversations c
join public.enquiries e on e.id = c.enquiry_id
where not exists (select 1 from public.messages m where m.conversation_id = c.id);


-- ============================================================================
-- supabase/migrations/202605290009_cron_grants.sql
-- ============================================================================
-- Phase 20: allow the service role to run the auction transition sweep so it
-- can be invoked from a secured server route (Vercel Cron / external scheduler)
-- as well as pg_cron. The function remains revoked from public/authenticated.

grant execute on function public.process_auction_transitions() to service_role;


-- ============================================================================
-- supabase/migrations/202605290010_revoke_definer_triggers.sql
-- ============================================================================
-- Phase 21 hardening: revoke PUBLIC execute on the remaining SECURITY DEFINER
-- trigger functions, mirroring the auction internals in 202605290004.
--
-- These are all `returns trigger` functions, so they cannot be invoked usefully
-- outside of the triggers that own them (a direct call fails without a trigger
-- context). Revoking PUBLIC execute is defense-in-depth and makes the security
-- audit (supabase/audit/rls_check.sql, check 3) return zero rows.

revoke all on function public.handle_new_user() from public;
revoke all on function public.notify_seller_of_enquiry() from public;
revoke all on function public.create_conversation_from_enquiry() from public;
revoke all on function public.handle_new_message() from public;

