-- Storage bucket + policies for seller listing image uploads (Phase 13).
--
-- Path convention: <auth.uid()>/<listing-scope>/<filename>
-- The first path segment must equal the uploader's auth uid, so sellers can
-- only write under their own prefix. Reads are public so listing images render
-- on public marketplace/auction pages. DB-level access to the listing_images
-- table remains governed by the existing RLS policies.

insert into storage.buckets (id, name, public, file_size_limit, allowed_mime_types)
values (
  'listing-images',
  'listing-images',
  true,
  10485760, -- 10 MiB per file
  array['image/jpeg', 'image/png', 'image/webp', 'image/avif']
)
on conflict (id) do update
  set public = excluded.public,
      file_size_limit = excluded.file_size_limit,
      allowed_mime_types = excluded.allowed_mime_types;

-- Public read of listing images.
drop policy if exists "Public read listing images" on storage.objects;
create policy "Public read listing images"
  on storage.objects for select
  to anon, authenticated
  using (bucket_id = 'listing-images');

-- Authenticated sellers can upload under their own uid prefix.
drop policy if exists "Sellers upload own listing images" on storage.objects;
create policy "Sellers upload own listing images"
  on storage.objects for insert
  to authenticated
  with check (
    bucket_id = 'listing-images'
    and (storage.foldername(name))[1] = auth.uid()::text
  );

drop policy if exists "Sellers update own listing images" on storage.objects;
create policy "Sellers update own listing images"
  on storage.objects for update
  to authenticated
  using (
    bucket_id = 'listing-images'
    and (storage.foldername(name))[1] = auth.uid()::text
  )
  with check (
    bucket_id = 'listing-images'
    and (storage.foldername(name))[1] = auth.uid()::text
  );

drop policy if exists "Sellers delete own listing images" on storage.objects;
create policy "Sellers delete own listing images"
  on storage.objects for delete
  to authenticated
  using (
    bucket_id = 'listing-images'
    and (storage.foldername(name))[1] = auth.uid()::text
  );
