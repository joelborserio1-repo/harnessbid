-- Re-open seeded auctions for demo: push end times into the future and set live.
-- Idempotent and safe to re-run whenever the demo auctions have closed.
update public.auctions a
set status = 'live',
    starts_at = now() - interval '2 hours',
    ends_at = now() + (((2 + (extract(epoch from a.created_at)::bigint % 22)) || ' hours')::interval)
from public.horse_listings h
where a.horse_listing_id = h.id
  and (h.slug like 'seed-horse-%' or h.slug like 'xhorse-%')
  and a.status in ('live','scheduled','extended','closed');

-- Report how many are now live and their soonest close.
select count(*) as live_auctions, min(ends_at) as soonest_close
from public.auctions where status = 'live';
