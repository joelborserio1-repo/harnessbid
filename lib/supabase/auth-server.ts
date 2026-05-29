import { cookies } from "next/headers"
import { createServerClient } from "@supabase/ssr"
import type { Database } from "./database.types"
import { hasSupabaseEnv } from "./server"

export { hasSupabaseEnv }

/**
 * Cookie-aware Supabase client for Server Components, Route Handlers, and
 * Server Actions. Uses the anon key plus the user's session cookies, so all
 * access stays within RLS. The service role key is never referenced here.
 *
 * In a pure Server Component render, Next.js disallows writing cookies; those
 * writes are swallowed and the session is refreshed by middleware instead.
 */
export async function createSupabaseServerAuthClient() {
  const url = process.env.NEXT_PUBLIC_SUPABASE_URL
  const anonKey = process.env.NEXT_PUBLIC_SUPABASE_ANON_KEY

  if (!url || !anonKey) {
    throw new Error(
      "Missing NEXT_PUBLIC_SUPABASE_URL or NEXT_PUBLIC_SUPABASE_ANON_KEY",
    )
  }

  const cookieStore = await cookies()

  return createServerClient<Database>(url, anonKey, {
    cookies: {
      getAll() {
        return cookieStore.getAll()
      },
      setAll(cookiesToSet) {
        try {
          for (const { name, value, options } of cookiesToSet) {
            cookieStore.set(name, value, options)
          }
        } catch {
          // Called from a Server Component render where cookies are read-only.
          // Session refresh is handled by middleware, so this is safe to ignore.
        }
      },
    },
  })
}

export type SessionUser = {
  id: string
  email: string | null
}

/** Returns the authenticated user, validated against the Supabase auth server. */
export async function getSessionUser(): Promise<SessionUser | null> {
  if (!hasSupabaseEnv()) return null
  const supabase = await createSupabaseServerAuthClient()
  const {
    data: { user },
  } = await supabase.auth.getUser()
  if (!user) return null
  return { id: user.id, email: user.email ?? null }
}

export type ProfileSummary = {
  id: string
  email: string | null
  fullName: string | null
  displayName: string | null
  role: Database["public"]["Enums"]["app_role"]
}

/** Returns the current user's profile row, or null if unauthenticated. */
export async function getCurrentProfile(): Promise<ProfileSummary | null> {
  if (!hasSupabaseEnv()) return null
  const supabase = await createSupabaseServerAuthClient()
  const {
    data: { user },
  } = await supabase.auth.getUser()
  if (!user) return null

  const { data } = await supabase
    .from("profiles")
    .select("id, email, full_name, display_name, role")
    .eq("id", user.id)
    .maybeSingle()

  if (!data) {
    return {
      id: user.id,
      email: user.email ?? null,
      fullName: null,
      displayName: null,
      role: "buyer",
    }
  }

  return {
    id: data.id,
    email: data.email,
    fullName: data.full_name,
    displayName: data.display_name,
    role: data.role,
  }
}

export type SellerAccountSummary = {
  id: string
  displayName: string
  slug: string
  accountType: Database["public"]["Enums"]["seller_account_type"]
  verificationStatus: Database["public"]["Enums"]["verification_status"]
  enterpriseRequested: boolean
}

/** Returns the seller account owned by the current user, or null if none. */
export async function getOwnedSellerAccount(): Promise<SellerAccountSummary | null> {
  if (!hasSupabaseEnv()) return null
  const supabase = await createSupabaseServerAuthClient()
  const {
    data: { user },
  } = await supabase.auth.getUser()
  if (!user) return null

  const { data } = await supabase
    .from("seller_accounts")
    .select("id, display_name, slug, account_type, verification_status, metadata")
    .eq("owner_profile_id", user.id)
    .order("created_at", { ascending: true })
    .limit(1)
    .maybeSingle()

  if (!data) return null

  const metadata = (data.metadata ?? {}) as Record<string, unknown>

  return {
    id: data.id,
    displayName: data.display_name,
    slug: data.slug,
    accountType: data.account_type,
    verificationStatus: data.verification_status,
    enterpriseRequested: Boolean(metadata.enterprise_requested),
  }
}
