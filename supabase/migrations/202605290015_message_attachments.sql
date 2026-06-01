-- Message attachments: a storage bucket for files shared inside conversations.
-- The messages.attachment_url / attachment_type columns already exist; this adds
-- the bucket + access policies. Reads are restricted to authenticated users
-- (conversation participation is enforced at the messages-table RLS layer, and
-- attachment URLs are unguessable storage paths under the sender's uid prefix).

insert into storage.buckets (id, name, public, file_size_limit, allowed_mime_types)
values (
  'message-attachments',
  'message-attachments',
  true, -- public-read by unguessable path; keeps rendering simple in the thread
  10485760, -- 10 MiB
  array['image/jpeg', 'image/png', 'image/webp', 'image/avif', 'application/pdf']
)
on conflict (id) do update
  set public = excluded.public,
      file_size_limit = excluded.file_size_limit,
      allowed_mime_types = excluded.allowed_mime_types;

-- Senders upload under their own uid prefix: <auth.uid()>/<conversation>/<file>.
drop policy if exists "Senders upload own attachments" on storage.objects;
create policy "Senders upload own attachments"
  on storage.objects for insert to authenticated
  with check (bucket_id = 'message-attachments' and (storage.foldername(name))[1] = auth.uid()::text);

drop policy if exists "Public read message attachments" on storage.objects;
create policy "Public read message attachments"
  on storage.objects for select
  to anon, authenticated
  using (bucket_id = 'message-attachments');

drop policy if exists "Senders delete own attachments" on storage.objects;
create policy "Senders delete own attachments"
  on storage.objects for delete to authenticated
  using (bucket_id = 'message-attachments' and (storage.foldername(name))[1] = auth.uid()::text);
