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
