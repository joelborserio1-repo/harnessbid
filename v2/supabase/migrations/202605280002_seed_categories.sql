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
