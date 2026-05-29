"use client"

import { createBrowserClient } from "@supabase/ssr"
import type { Database } from "./database.types"

/**
 * Browser Supabase client (anon key only). Safe for client components.
 * Reads/writes are still constrained by RLS. Never use the service role
 * key here.
 */
export function createSupabaseBrowserClient() {
  const url = process.env.NEXT_PUBLIC_SUPABASE_URL
  const anonKey = process.env.NEXT_PUBLIC_SUPABASE_ANON_KEY

  if (!url || !anonKey) {
    throw new Error(
      "Missing NEXT_PUBLIC_SUPABASE_URL or NEXT_PUBLIC_SUPABASE_ANON_KEY",
    )
  }

  return createBrowserClient<Database>(url, anonKey)
}
