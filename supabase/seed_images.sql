-- HarnessBid demo images — attach a real stock photo to every seeded listing.
--
-- The seed (seed.sql) inserts horse + marketplace listings but no listing_images,
-- so everything renders the /placeholder.jpg fallback. This script adds one
-- primary image per seeded listing using stable public stock-photo URLs, so the
-- demo site looks complete. Safe to re-run (clears seed images first).
--
-- LOCAL / STAGING / DEMO ONLY. Run after seed.sql in the Supabase SQL Editor.
-- next.config has images.unoptimized = true, so external URLs render directly.

-- Clear any previously-seeded demo images (idempotent re-run).
delete from public.listing_images
where storage_path is null
  and (
    horse_listing_id in (select id from public.horse_listings where slug like 'seed-horse-%')
    or marketplace_listing_id in (select id from public.marketplace_listings where slug like 'seed-mkt-%')
  );

-- Horses: rotate through a set of standardbred / harness-racing photos.
with horse_pics(idx, url) as (
  values
    (0, 'https://images.unsplash.com/photo-1553284965-83fd3e82fa5a?w=1200&q=80'),
    (1, 'https://images.unsplash.com/photo-1534773728080-33d31da27ae5?w=1200&q=80'),
    (2, 'https://images.unsplash.com/photo-1599582909646-8e0d6c1b1d6e?w=1200&q=80'),
    (3, 'https://images.unsplash.com/photo-1598974357801-cbca100e65d3?w=1200&q=80'),
    (4, 'https://images.unsplash.com/photo-1511994714008-b6d68a8b32a2?w=1200&q=80'),
    (5, 'https://images.unsplash.com/photo-1602491453631-e2a5ad90a131?w=1200&q=80')
),
numbered_horses as (
  select id, row_number() over (order by created_at, id) - 1 as rn
  from public.horse_listings
  where slug like 'seed-horse-%'
)
insert into public.listing_images (horse_listing_id, image_url, alt_text, position, is_primary)
select h.id, p.url, 'Standardbred racing horse', 0, true
from numbered_horses h
join horse_pics p on p.idx = (h.rn % 6);

-- Marketplace: rotate through equipment / harness-gear photos.
with mkt_pics(idx, url) as (
  values
    (0, 'https://images.unsplash.com/photo-1605479808382-2d2d2c0a8f3f?w=1200&q=80'),
    (1, 'https://images.unsplash.com/photo-1520673317854-9a4e3b9e3a07?w=1200&q=80'),
    (2, 'https://images.unsplash.com/photo-1551122089-4e3e72477432?w=1200&q=80'),
    (3, 'https://images.unsplash.com/photo-1568605117036-5fe5e7bab0b7?w=1200&q=80'),
    (4, 'https://images.unsplash.com/photo-1583511655857-d19b40a7a54e?w=1200&q=80')
),
numbered_mkt as (
  select id, row_number() over (order by created_at, id) - 1 as rn
  from public.marketplace_listings
  where slug like 'seed-mkt-%'
)
insert into public.listing_images (marketplace_listing_id, image_url, alt_text, position, is_primary)
select m.id, p.url, 'Harness racing equipment', 0, true
from numbered_mkt m
join mkt_pics p on p.idx = (m.rn % 5);

-- Report how many images now exist.
select
  (select count(*) from public.listing_images where horse_listing_id is not null) as horse_images,
  (select count(*) from public.listing_images where marketplace_listing_id is not null) as marketplace_images;
