-- Phase 14B: notifications + saved searches (engagement layer).
--
-- Notifications are created server-side only:
--  - cross-user notifications (e.g. enquiry -> seller) are produced by a
--    SECURITY DEFINER trigger, so no service-role key is needed on the client.
--  - users can read/update/delete only their own notification rows via RLS.

-- ---------------------------------------------------------------------------
-- notifications
-- ---------------------------------------------------------------------------
create table public.notifications (
  id uuid primary key default gen_random_uuid(),
  profile_id uuid not null references public.profiles(id) on delete cascade,
  type text not null,
  title text not null,
  body text,
  link_url text,
  related_entity_type text,
  related_entity_id uuid,
  read_at timestamptz,
  created_at timestamptz not null default now()
);

create index notifications_profile_idx on public.notifications(profile_id, created_at desc);
create index notifications_unread_idx on public.notifications(profile_id) where read_at is null;

alter table public.notifications enable row level security;

create policy "Users read their own notifications"
  on public.notifications for select
  to authenticated
  using (profile_id = auth.uid() or public.is_admin());

create policy "Users insert their own notifications"
  on public.notifications for insert
  to authenticated
  with check (profile_id = auth.uid() or public.is_admin());

create policy "Users update their own notifications"
  on public.notifications for update
  to authenticated
  using (profile_id = auth.uid() or public.is_admin())
  with check (profile_id = auth.uid() or public.is_admin());

create policy "Users delete their own notifications"
  on public.notifications for delete
  to authenticated
  using (profile_id = auth.uid() or public.is_admin());

-- ---------------------------------------------------------------------------
-- saved_searches
-- ---------------------------------------------------------------------------
create table public.saved_searches (
  id uuid primary key default gen_random_uuid(),
  profile_id uuid not null references public.profiles(id) on delete cascade,
  name text not null,
  search_type text not null,
  filters jsonb not null default '{}'::jsonb,
  alert_enabled boolean not null default false,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);

create index saved_searches_profile_idx on public.saved_searches(profile_id, created_at desc);

create trigger set_saved_searches_updated_at
  before update on public.saved_searches
  for each row execute function public.set_updated_at();

alter table public.saved_searches enable row level security;

create policy "Users manage their saved searches"
  on public.saved_searches for all
  to authenticated
  using (profile_id = auth.uid() or public.is_admin())
  with check (profile_id = auth.uid() or public.is_admin());

-- ---------------------------------------------------------------------------
-- Enquiry -> seller notification (server-side, no service role).
-- SECURITY DEFINER so it can write a notification row for the seller's owner
-- profile regardless of who created the enquiry. One notification per enquiry
-- insert, so no duplicates.
-- ---------------------------------------------------------------------------
create or replace function public.notify_seller_of_enquiry()
returns trigger
language plpgsql
security definer
set search_path = public
as $$
declare
  owner_id uuid;
begin
  select owner_profile_id into owner_id
  from public.seller_accounts
  where id = new.seller_account_id;

  if owner_id is not null then
    insert into public.notifications (
      profile_id, type, title, body, link_url, related_entity_type, related_entity_id
    )
    values (
      owner_id,
      'enquiry_received',
      coalesce(new.subject, 'New enquiry'),
      left(new.message, 280),
      '/dashboard/messages',
      'enquiry',
      new.id
    );
  end if;

  return new;
end;
$$;

drop trigger if exists on_enquiry_created on public.enquiries;

create trigger on_enquiry_created
  after insert on public.enquiries
  for each row execute function public.notify_seller_of_enquiry();
