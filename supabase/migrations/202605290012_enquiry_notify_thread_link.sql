-- Make enquiry notifications deep-link to the exact conversation thread, so a
-- seller can click the notification and land directly on the reply view.
--
-- Before: the older notify_seller_of_enquiry trigger linked to /dashboard/messages
-- (the inbox list), and create_conversation_from_enquiry created the thread but
-- sent no notification. After: the conversation trigger notifies the seller with
-- /dashboard/messages/<conversation_id>, and the old trigger no longer notifies
-- (to avoid a duplicate, less-useful notification).

-- 1) Conversation trigger: notify the seller owner with the precise thread link.
create or replace function public.create_conversation_from_enquiry()
returns trigger
language plpgsql
security definer
set search_path = public
as $$
declare
  v_owner uuid;
  v_conversation uuid;
  v_listing text;
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

  -- Notify the seller, deep-linking straight to the conversation thread.
  insert into public.notifications (
    profile_id, type, title, body, link_url, related_entity_type, related_entity_id
  )
  values (
    v_owner,
    'enquiry_received',
    coalesce(new.subject, 'New enquiry'),
    left(new.message, 280),
    '/dashboard/messages/' || v_conversation::text,
    'conversation',
    v_conversation
  );

  return new;
end;
$$;

-- 2) Older enquiry trigger: stop sending the generic /dashboard/messages
--    notification so sellers don't get two notifications for one enquiry. The
--    conversation trigger above now owns enquiry notifications. (We keep the
--    function defined but make it a no-op insert path.)
create or replace function public.notify_seller_of_enquiry()
returns trigger
language plpgsql
security definer
set search_path = public
as $$
begin
  -- Superseded by create_conversation_from_enquiry, which notifies with the
  -- exact conversation thread link. Intentionally does nothing here.
  return new;
end;
$$;
