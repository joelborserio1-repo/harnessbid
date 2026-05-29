-- Phase 19: conversations + messages (buyer/seller threads).
--
-- Enquiries are preserved exactly as-is. A SECURITY DEFINER trigger turns each
-- enquiry into a conversation seeded with the enquiry text as the first
-- message, so existing enquiry creation (and the Phase 14B seller notification)
-- keeps working unchanged. Messages drive a 'message' notification to the
-- recipient (reusing the notifications system) and keep the conversation's
-- last_message_at fresh for inbox sorting. Unread is computed from messages
-- (no counters) to avoid race conditions.

create table public.conversations (
  id uuid primary key default gen_random_uuid(),
  enquiry_id uuid unique references public.enquiries(id) on delete set null,
  buyer_profile_id uuid not null references public.profiles(id) on delete cascade,
  seller_account_id uuid not null references public.seller_accounts(id) on delete cascade,
  seller_owner_profile_id uuid not null references public.profiles(id) on delete cascade,
  horse_listing_id uuid references public.horse_listings(id) on delete set null,
  marketplace_listing_id uuid references public.marketplace_listings(id) on delete set null,
  sale_event_id uuid references public.sale_events(id) on delete set null,
  subject text,
  last_message_at timestamptz not null default now(),
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);

create index conversations_buyer_idx on public.conversations(buyer_profile_id, last_message_at desc);
create index conversations_seller_idx on public.conversations(seller_owner_profile_id, last_message_at desc);

create table public.messages (
  id uuid primary key default gen_random_uuid(),
  conversation_id uuid not null references public.conversations(id) on delete cascade,
  sender_profile_id uuid not null references public.profiles(id) on delete cascade,
  body text not null,
  attachment_url text,
  attachment_type text,
  read_at timestamptz,
  created_at timestamptz not null default now()
);

create index messages_conversation_idx on public.messages(conversation_id, created_at);

-- --------------------------------------------------------------------------
-- RLS: only participants (buyer / seller owner) or staff may access.
-- --------------------------------------------------------------------------
alter table public.conversations enable row level security;

create policy "Participants read conversations"
  on public.conversations for select
  to authenticated
  using (
    buyer_profile_id = auth.uid()
    or seller_owner_profile_id = auth.uid()
    or public.is_staff()
  );

create policy "Participants create conversations"
  on public.conversations for insert
  to authenticated
  with check (buyer_profile_id = auth.uid() or seller_owner_profile_id = auth.uid());

create policy "Participants update conversations"
  on public.conversations for update
  to authenticated
  using (buyer_profile_id = auth.uid() or seller_owner_profile_id = auth.uid())
  with check (buyer_profile_id = auth.uid() or seller_owner_profile_id = auth.uid());

alter table public.messages enable row level security;

create policy "Participants read messages"
  on public.messages for select
  to authenticated
  using (
    public.is_staff()
    or exists (
      select 1 from public.conversations c
      where c.id = conversation_id
        and (c.buyer_profile_id = auth.uid() or c.seller_owner_profile_id = auth.uid())
    )
  );

create policy "Participants send messages"
  on public.messages for insert
  to authenticated
  with check (
    sender_profile_id = auth.uid()
    and exists (
      select 1 from public.conversations c
      where c.id = conversation_id
        and (c.buyer_profile_id = auth.uid() or c.seller_owner_profile_id = auth.uid())
    )
  );

create policy "Participants mark messages read"
  on public.messages for update
  to authenticated
  using (
    exists (
      select 1 from public.conversations c
      where c.id = conversation_id
        and (c.buyer_profile_id = auth.uid() or c.seller_owner_profile_id = auth.uid())
    )
  )
  with check (
    exists (
      select 1 from public.conversations c
      where c.id = conversation_id
        and (c.buyer_profile_id = auth.uid() or c.seller_owner_profile_id = auth.uid())
    )
  );

create trigger set_conversations_updated_at
  before update on public.conversations
  for each row execute function public.set_updated_at();

-- --------------------------------------------------------------------------
-- Enquiry -> conversation + first message (SECURITY DEFINER).
-- --------------------------------------------------------------------------
create or replace function public.create_conversation_from_enquiry()
returns trigger
language plpgsql
security definer
set search_path = public
as $$
declare
  v_owner uuid;
  v_conversation uuid;
begin
  if new.sender_profile_id is null then
    return new;
  end if;

  select owner_profile_id into v_owner
  from public.seller_accounts
  where id = new.seller_account_id;
  if v_owner is null or v_owner = new.sender_profile_id then
    return new; -- no distinct counterpart
  end if;

  insert into public.conversations (
    enquiry_id, buyer_profile_id, seller_account_id, seller_owner_profile_id,
    horse_listing_id, marketplace_listing_id, sale_event_id, subject, last_message_at
  )
  values (
    new.id, new.sender_profile_id, new.seller_account_id, v_owner,
    new.horse_listing_id, new.marketplace_listing_id, new.sale_event_id,
    coalesce(new.subject, 'Enquiry'), now()
  )
  returning id into v_conversation;

  insert into public.messages (conversation_id, sender_profile_id, body)
  values (v_conversation, new.sender_profile_id, new.message);

  return new;
end;
$$;

drop trigger if exists on_enquiry_create_conversation on public.enquiries;
create trigger on_enquiry_create_conversation
  after insert on public.enquiries
  for each row execute function public.create_conversation_from_enquiry();

-- --------------------------------------------------------------------------
-- New message -> bump conversation + notify the recipient (deduped).
-- --------------------------------------------------------------------------
create or replace function public.handle_new_message()
returns trigger
language plpgsql
security definer
set search_path = public
as $$
declare
  c public.conversations%rowtype;
  v_recipient uuid;
begin
  select * into c from public.conversations where id = new.conversation_id;
  if not found then return new; end if;

  update public.conversations set last_message_at = new.created_at where id = c.id;

  v_recipient := case
    when new.sender_profile_id = c.buyer_profile_id then c.seller_owner_profile_id
    else c.buyer_profile_id
  end;

  if v_recipient is null or v_recipient = new.sender_profile_id then
    return new;
  end if;

  -- Dedupe: at most one unread 'message' notification per conversation/minute.
  if exists (
    select 1 from public.notifications
    where profile_id = v_recipient
      and type = 'message'
      and related_entity_id = c.id
      and read_at is null
      and created_at > now() - interval '1 minute'
  ) then
    return new;
  end if;

  insert into public.notifications (
    profile_id, type, title, body, link_url, related_entity_type, related_entity_id
  )
  values (
    v_recipient,
    'message',
    'New message',
    left(new.body, 200),
    '/dashboard/messages/' || c.id,
    'conversation',
    c.id
  );

  return new;
end;
$$;

drop trigger if exists on_message_insert on public.messages;
create trigger on_message_insert
  after insert on public.messages
  for each row execute function public.handle_new_message();

-- --------------------------------------------------------------------------
-- Backfill: turn existing enquiries into conversations + first messages.
-- --------------------------------------------------------------------------
insert into public.conversations (
  enquiry_id, buyer_profile_id, seller_account_id, seller_owner_profile_id,
  horse_listing_id, marketplace_listing_id, sale_event_id, subject, last_message_at, created_at
)
select e.id, e.sender_profile_id, e.seller_account_id, sa.owner_profile_id,
       e.horse_listing_id, e.marketplace_listing_id, e.sale_event_id,
       coalesce(e.subject, 'Enquiry'), e.created_at, e.created_at
from public.enquiries e
join public.seller_accounts sa on sa.id = e.seller_account_id
where e.sender_profile_id is not null
  and sa.owner_profile_id is not null
  and sa.owner_profile_id <> e.sender_profile_id
  and not exists (select 1 from public.conversations c where c.enquiry_id = e.id);

insert into public.messages (conversation_id, sender_profile_id, body, created_at)
select c.id, e.sender_profile_id, e.message, e.created_at
from public.conversations c
join public.enquiries e on e.id = c.enquiry_id
where not exists (select 1 from public.messages m where m.conversation_id = c.id);
