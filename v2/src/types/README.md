# Supabase Types TODO

`src/types/supabase.ts` must be generated from the applied Supabase migrations. It is intentionally not checked in from a hand-written or copied type file.

Run this after the Supabase CLI is available and the local database has been reset/applied:

```sh
supabase db reset
npm run supabase:types
```

Direct command:

```sh
supabase gen types typescript --local > src/types/supabase.ts
```

If generating from a linked remote project instead of local Supabase, use the project ref:

```sh
supabase gen types typescript --project-id "$SUPABASE_PROJECT_REF" --schema public > src/types/supabase.ts
```

After generation, update app imports from `lib/supabase/database.types` to `src/types/supabase` in a separate checked change.
