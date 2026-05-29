import "server-only"

import { createClient } from "@supabase/supabase-js"
import type { Database } from "./database.types"

/**
 * SERVER-ONLY service-role Supabase client. Bypasses RLS — use ONLY in trusted
 * server contexts that have no user session (e.g. verified Stripe webhooks).
 * The `server-only` import makes the build fail if this is ever imported into a
 * client component. Never expose SUPABASE_SERVICE_ROLE_KEY to the browser.
 */
export function createSupabaseServiceClient() {
  const url = process.env.NEXT_PUBLIC_SUPABASE_URL
  const serviceKey = process.env.SUPABASE_SERVICE_ROLE_KEY
  if (!url || !serviceKey) {
    throw new Error(
      "Service-role client requires NEXT_PUBLIC_SUPABASE_URL and SUPABASE_SERVICE_ROLE_KEY",
    )
  }
  return createClient<Database>(url, serviceKey, {
    auth: { persistSession: false, autoRefreshToken: false },
  })
}

export function hasServiceRoleEnv(): boolean {
  return Boolean(process.env.NEXT_PUBLIC_SUPABASE_URL && process.env.SUPABASE_SERVICE_ROLE_KEY)
}
