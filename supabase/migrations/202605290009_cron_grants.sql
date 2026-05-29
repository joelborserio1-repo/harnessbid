-- Phase 20: allow the service role to run the auction transition sweep so it
-- can be invoked from a secured server route (Vercel Cron / external scheduler)
-- as well as pg_cron. The function remains revoked from public/authenticated.

grant execute on function public.process_auction_transitions() to service_role;
