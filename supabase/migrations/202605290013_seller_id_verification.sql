-- Seller ID verification: private document storage + a review record per
-- seller account. Sellers upload front/back of a government ID; staff review and
-- approve/reject, which drives seller_accounts.verification_status.
--
-- Documents live in a PRIVATE storage bucket (no public read). Access is via
-- short-lived signed URLs generated server-side for the owner and for staff.

-- 1) Private bucket for ID documents.
insert into storage.buckets (id, name, public, file_size_limit, allowed_mime_types)
values (
  'seller-id-documents',
  'seller-id-documents',
  false, -- PRIVATE
  10485760, -- 10 MiB
  array['image/jpeg', 'image/png', 'image/webp', 'application/pdf']
)
on conflict (id) do update
  set public = excluded.public,
      file_size_limit = excluded.file_size_limit,
      allowed_mime_types = excluded.allowed_mime_types;

-- Owner can write/read under their own uid prefix: <auth.uid()>/<filename>.
drop policy if exists "Owners upload own id docs" on storage.objects;
create policy "Owners upload own id docs"
  on storage.objects for insert to authenticated
  with check (bucket_id = 'seller-id-documents' and (storage.foldername(name))[1] = auth.uid()::text);

drop policy if exists "Owners read own id docs" on storage.objects;
create policy "Owners read own id docs"
  on storage.objects for select to authenticated
  using (bucket_id = 'seller-id-documents' and (storage.foldername(name))[1] = auth.uid()::text);

drop policy if exists "Owners update own id docs" on storage.objects;
create policy "Owners update own id docs"
  on storage.objects for update to authenticated
  using (bucket_id = 'seller-id-documents' and (storage.foldername(name))[1] = auth.uid()::text);

drop policy if exists "Owners delete own id docs" on storage.objects;
create policy "Owners delete own id docs"
  on storage.objects for delete to authenticated
  using (bucket_id = 'seller-id-documents' and (storage.foldername(name))[1] = auth.uid()::text);
-- (Staff access to documents is done server-side with the service role, which
--  bypasses these policies — so no broad staff storage policy is needed.)

-- 2) Verification request record.
create type public.id_doc_type as enum ('drivers_license', 'passport', 'national_id', 'other');

create table public.verification_documents (
  id uuid primary key default gen_random_uuid(),
  seller_account_id uuid not null references public.seller_accounts(id) on delete cascade,
  profile_id uuid not null references public.profiles(id) on delete cascade,
  doc_type public.id_doc_type not null default 'drivers_license',
  full_legal_name text,
  front_path text not null,
  back_path text,
  status public.verification_status not null default 'pending',
  review_notes text,
  reviewed_by uuid references public.profiles(id),
  reviewed_at timestamptz,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);

create index verification_documents_seller_idx on public.verification_documents(seller_account_id, created_at desc);
create index verification_documents_status_idx on public.verification_documents(status, created_at);

create trigger set_verification_documents_updated_at
  before update on public.verification_documents
  for each row execute function public.set_updated_at();

alter table public.verification_documents enable row level security;

-- Owner can create + read their own verification records; staff manage all.
create policy "Owners create their verification docs"
  on public.verification_documents for insert to authenticated
  with check (
    profile_id = auth.uid()
    and exists (
      select 1 from public.seller_accounts sa
      where sa.id = seller_account_id and sa.owner_profile_id = auth.uid()
    )
  );

create policy "Owners read their verification docs"
  on public.verification_documents for select to authenticated
  using (profile_id = auth.uid() or public.is_admin());

create policy "Staff manage verification docs"
  on public.verification_documents for all to authenticated
  using (public.is_admin())
  with check (public.is_admin());

grant all on public.verification_documents to anon, authenticated, service_role;
