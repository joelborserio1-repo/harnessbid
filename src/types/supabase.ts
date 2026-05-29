/**
 * Canonical Supabase schema types for the app.
 *
 * Until the Supabase CLI can be run against the project, the maintained types
 * in `lib/supabase/database.types.ts` are the single source of truth and are
 * re-exported here so `@/src/types/supabase` is a stable import path.
 *
 * To replace this with CLI-generated output once project access is available:
 *   npm run supabase:types
 * (defined as: supabase gen types typescript --local > src/types/supabase.ts)
 */
export type { Database, Json } from "@/lib/supabase/database.types"
