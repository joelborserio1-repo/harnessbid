-- Auto-create a public.profiles row whenever a new auth user is created.
-- This is the canonical Supabase pattern and keeps profile creation reliable
-- even when email confirmation is enabled (no client session at signup time)
-- or when accounts are created via OAuth/admin APIs.
--
-- The application also performs a best-effort profile upsert after password
-- signup as a fallback; `on conflict do nothing` here keeps the two paths
-- from clobbering each other.

create or replace function public.handle_new_user()
returns trigger
language plpgsql
security definer
set search_path = public
as $$
begin
  insert into public.profiles (id, email, full_name, display_name)
  values (
    new.id,
    new.email,
    nullif(new.raw_user_meta_data ->> 'full_name', ''),
    coalesce(
      nullif(new.raw_user_meta_data ->> 'full_name', ''),
      split_part(coalesce(new.email, 'member'), '@', 1)
    )
  )
  on conflict (id) do nothing;
  return new;
end;
$$;

drop trigger if exists on_auth_user_created on auth.users;

create trigger on_auth_user_created
  after insert on auth.users
  for each row execute function public.handle_new_user();
