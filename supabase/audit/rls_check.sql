-- HarnessBid RLS / security audit (Phase 21).
-- Run in the Supabase SQL editor (or psql) against the target project.
-- All three checks below should return ZERO rows for a healthy configuration.

-- 1) Every base table in `public` must have RLS enabled.
select n.nspname as schema, c.relname as table
from pg_class c
join pg_namespace n on n.oid = c.relnamespace
where n.nspname = 'public'
  and c.relkind = 'r'
  and c.relrowsecurity = false
order by c.relname;

-- 2) RLS-enabled tables that have NO policies (would deny all / misconfigured).
select n.nspname as schema, c.relname as table
from pg_class c
join pg_namespace n on n.oid = c.relnamespace
where n.nspname = 'public'
  and c.relkind = 'r'
  and c.relrowsecurity = true
  and not exists (
    select 1 from pg_policies p
    where p.schemaname = 'public' and p.tablename = c.relname
  )
order by c.relname;

-- 3) SECURITY DEFINER functions still executable by PUBLIC (should be revoked
--    for the auction internals). Review anything listed.
select p.proname as function,
       pg_get_function_identity_arguments(p.oid) as args
from pg_proc p
join pg_namespace n on n.oid = p.pronamespace
where n.nspname = 'public'
  and p.prosecdef = true
  and has_function_privilege('public', p.oid, 'execute')
  and p.proname in (
    '_notify_outbid', '_close_auction', 'process_auction_transitions',
    'notify_seller_of_enquiry', 'create_conversation_from_enquiry',
    'handle_new_message', 'handle_new_user'
  )
order by p.proname;

-- Reference: full policy inventory (informational, not a failure check).
-- select schemaname, tablename, policyname, cmd, roles
-- from pg_policies where schemaname = 'public' order by tablename, policyname;
