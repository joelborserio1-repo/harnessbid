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
