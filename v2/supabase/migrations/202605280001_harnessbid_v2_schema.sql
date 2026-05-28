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
