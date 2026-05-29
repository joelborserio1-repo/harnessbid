# Database setup — fastest path (no CLI required)

The whole schema can be applied by pasting **one file** into the Supabase
dashboard. Type generation is **not** required to run the app — the maintained
`lib/supabase/database.types.ts` is the source of truth (re-exported by
`src/types/supabase.ts`) and `tsc` validates it.

## Option A — Dashboard SQL Editor (recommended, ~5 min)

1. Supabase → your project → **SQL Editor** → **New query**.
2. Paste the entire contents of **`supabase/full_setup.sql`** → **Run**.
   (All 11 migrations, in order, in one shot.)
3. **Realtime:** Database → **Replication** → enable for `notifications`,
   `messages`, `auctions`, `bids`.
4. **(staging/demo only)** New query → paste **`supabase/seed.sql`** → Run.
5. **Verify:** New query → paste **`supabase/audit/rls_check.sql`** → Run.
   All three result sets should be **empty**.

That's it — the app will work against the project once env vars are set
(`NEXT_PUBLIC_SUPABASE_URL`, `NEXT_PUBLIC_SUPABASE_ANON_KEY`, and
`SUPABASE_SERVICE_ROLE_KEY` for cron/webhooks).

## Option B — Supabase CLI (if you prefer migration history)

Use the **individual** migration files (not full_setup.sql):

```bash
supabase link --project-ref <your-ref>
supabase db push                 # applies supabase/migrations/* in order
npm run supabase:types           # optional: regenerate src/types/supabase.ts
# staging only:
supabase db reset                # re-runs migrations + supabase/seed.sql
```

> Do not mix A and B on the same project — A applies the SQL directly without
> recording migration history, so a later `supabase db push` would try to
> re-run them. Pick one.

## After setup
- Set a real admin: `update public.profiles set role='admin' where id='<your-user-id>';`
- Configure the auction scheduler (pg_cron or Vercel Cron → `/api/cron/auctions`).
- See `LAUNCH_CHECKLIST.md` for the full go-live sequence.
