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
