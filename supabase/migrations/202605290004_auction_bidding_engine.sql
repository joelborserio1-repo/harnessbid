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
