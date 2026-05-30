-- Email outbox support: track which notifications have been emailed so a cron
-- sweep can deliver transactional emails (outbid, auction won, enquiry, saved-
-- search) exactly once, regardless of whether the notification row was created
-- by app code or by a SQL function/trigger.

alter table public.notifications
  add column if not exists emailed_at timestamptz;

-- Partial index for the sweep: quickly find recent, unread, un-emailed rows.
create index if not exists notifications_email_pending_idx
  on public.notifications (created_at)
  where emailed_at is null;
