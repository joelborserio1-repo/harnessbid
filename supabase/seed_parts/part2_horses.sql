-- ===========================================================================
-- Richer horse listings (original descriptions, real sire/dam lines)
-- ===========================================================================
insert into public.horse_listings (id, seller_account_id, sale_event_id, title, slug, status, sale_mode, currency, asking_price, short_description, description, location_text, breed, sex, gait, age_years, color, sire, dam, best_mile, featured_until, published_at, sold_at, lot_number, lot_order)
select
  gen_random_uuid(),
  sa.id,
  null,
  (array[
    'Bettors Reign','Lou''s Legacy','Captain''s Pride','Sundons Gift','Ideal Tribute',
    'Majestic Art','Rocknroll Heaven Bay','Always Be Sweet','Somebeach Star','American Dream Girl'
  ])[1 + (g % 10)] || ' (Lot ' || (100 + g) || ')',
  'xhorse-' || g,
  (array['published','published','published','published','sold','under_offer'])[1 + (g % 6)]::public.listing_status,
  (array['auction','buy_now'])[1 + (g % 2)]::public.sale_mode,
  'USD',
  case when g % 2 = 1 then 12000 + g * 850 else null end,
  (array[
    'Strong-gaited youngster with an excellent attitude and natural early speed.',
    'Tried winner ready to step up in grade - sound and racing in great order.',
    'Beautifully bred filly from a prolific producing family. Future broodmare prospect.',
    'Honest trotting type with good manners and a genuine will to win.'
  ])[1 + (g % 4)],
  (array[
    'Presented in outstanding order and working a treat at the trials. This one shows above-average gate speed and a high cruising tempo, with the temperament to match. Vet records and recent x-rays available to genuine buyers. An exciting prospect for a progressive stable.',
    'A racetrack-proven performer with multiple wins and consistent place form against quality fields. Eats well, travels well, and has never missed a beat. Suit an owner looking to step straight into the winners'' circle.',
    'From one of the breed''s most influential maternal families, this individual has the page to back up the looks. Correct, mobile and forward - exactly the kind of type that develops into a stakes-class performer or foundation broodmare.',
    'A genuine, no-fuss racehorse with a great constitution. Strong through the line and improving with every run. Ready to continue racing immediately or carry on as a breeding proposition down the track.'
  ])[1 + (g % 4)],
  (array['Menangle, NSW','Bendigo, VIC','Christchurch, NZ','Toowoomba, QLD','Cranbourne, VIC'])[1 + (g % 5)],
  'Standardbred',
  (array['colt','filly','gelding','mare','stallion'])[1 + (g % 5)]::public.horse_sex,
  (array['pacer','pacer','trotter','dual_gaited'])[1 + (g % 4)]::public.horse_gait,
  1 + (g % 9),
  (array['Bay','Brown','Black','Chestnut','Roan'])[1 + (g % 5)],
  (array['Bettors Delight','Sweet Lou','Captaintreacherous','Sundon','American Ideal','Art Major','Always B Miki','Somebeachsomewhere'])[1 + (g % 8)],
  (array['Reign Of Fire','Legacy Lady','Pride Of Place','Gift Horse','Tribute Belle','Artistic Flair','Bay Of Plenty','Sweet Surrender'])[1 + (g % 8)],
  case when g % 3 = 0 then '1:5' || (1 + g % 8) || '.' || (g % 9) else null end,
  case when g % 9 = 0 then now() + interval '21 days' else null end,
  case when (1 + (g % 6)) in (1,2,3,4,6) then now() - ((g * 2 || ' hours')::interval) else null end,
  case when (1 + (g % 6)) = 5 then now() - interval '3 days' else null end,
  (100 + g)::text,
  100 + g
from generate_series(1, 30) g
cross join lateral (
  select id from public.seller_accounts
  where slug like 'vendor-%' or slug like 'seed-seller-%'
  order by id offset (g % greatest(1,(select count(*) from public.seller_accounts where slug like 'vendor-%' or slug like 'seed-seller-%'))) limit 1
) sa
on conflict (slug) do nothing;

-- ===========================================================================
-- Richer equipment / marketplace listings (original copy)
