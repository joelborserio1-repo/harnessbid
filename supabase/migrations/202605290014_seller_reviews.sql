-- Seller reviews: buyers leave a 1-5 star rating + comment for a seller account.
-- A trigger keeps seller_accounts.rating (avg) and review_count in sync, so the
-- existing UI fields stop showing 0.

create table public.seller_reviews (
  id uuid primary key default gen_random_uuid(),
  seller_account_id uuid not null references public.seller_accounts(id) on delete cascade,
  reviewer_profile_id uuid not null references public.profiles(id) on delete cascade,
  rating smallint not null check (rating between 1 and 5),
  comment text,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now(),
  -- One review per buyer per seller (they can update it).
  unique (seller_account_id, reviewer_profile_id)
);

create index seller_reviews_seller_idx on public.seller_reviews(seller_account_id, created_at desc);

create trigger set_seller_reviews_updated_at
  before update on public.seller_reviews
  for each row execute function public.set_updated_at();

-- Recompute the seller's aggregate rating + count whenever reviews change.
create or replace function public.recompute_seller_rating()
returns trigger
language plpgsql
security definer
set search_path = public
as $$
declare
  v_seller uuid := coalesce(new.seller_account_id, old.seller_account_id);
begin
  update public.seller_accounts sa
  set rating = coalesce((select round(avg(rating)::numeric, 2) from public.seller_reviews where seller_account_id = v_seller), 0),
      review_count = (select count(*) from public.seller_reviews where seller_account_id = v_seller)
  where sa.id = v_seller;
  return null;
end;
$$;

create trigger on_seller_review_change
  after insert or update or delete on public.seller_reviews
  for each row execute function public.recompute_seller_rating();

revoke all on function public.recompute_seller_rating() from public;

alter table public.seller_reviews enable row level security;

-- Anyone can read reviews (they're public trust signals).
create policy "Public read seller reviews"
  on public.seller_reviews for select to anon, authenticated
  using (true);

-- Authenticated users can write their own review, but not for their own account.
create policy "Users write their own review"
  on public.seller_reviews for insert to authenticated
  with check (
    reviewer_profile_id = auth.uid()
    and not exists (
      select 1 from public.seller_accounts sa
      where sa.id = seller_account_id and sa.owner_profile_id = auth.uid()
    )
  );

create policy "Users update their own review"
  on public.seller_reviews for update to authenticated
  using (reviewer_profile_id = auth.uid())
  with check (reviewer_profile_id = auth.uid());

create policy "Users delete their own review"
  on public.seller_reviews for delete to authenticated
  using (reviewer_profile_id = auth.uid() or public.is_admin());

grant all on public.seller_reviews to anon, authenticated, service_role;
