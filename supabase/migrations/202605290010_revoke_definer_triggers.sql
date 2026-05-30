-- Phase 21 hardening: revoke PUBLIC execute on the remaining SECURITY DEFINER
-- trigger functions, mirroring the auction internals in 202605290004.
--
-- These are all `returns trigger` functions, so they cannot be invoked usefully
-- outside of the triggers that own them (a direct call fails without a trigger
-- context). Revoking PUBLIC execute is defense-in-depth and makes the security
-- audit (supabase/audit/rls_check.sql, check 3) return zero rows.

revoke all on function public.handle_new_user() from public;
revoke all on function public.notify_seller_of_enquiry() from public;
revoke all on function public.create_conversation_from_enquiry() from public;
revoke all on function public.handle_new_message() from public;
