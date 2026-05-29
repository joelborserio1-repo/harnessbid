"use client"

import { useEffect, useState } from "react"
import { createSupabaseBrowserClient } from "@/lib/supabase/client"

export type AuthUserState = {
  /** True once the initial auth check has resolved on the client. */
  loaded: boolean
  email: string | null
  isAuthenticated: boolean
  /** Seller slug if the signed-in user owns a seller account. */
  sellerSlug: string | null
  isEnterprise: boolean
}

const hasEnv = Boolean(
  process.env.NEXT_PUBLIC_SUPABASE_URL && process.env.NEXT_PUBLIC_SUPABASE_ANON_KEY,
)

/**
 * Client-side auth state for the header. Renders the logged-out state during
 * SSR and the first client render (loaded=false) to avoid hydration mismatch,
 * then resolves the real session after mount and on any auth change. Also
 * fetches the user's seller account (slug + enterprise flag) via RLS-scoped
 * owner reads to drive seller-aware nav links.
 */
export function useAuthUser(): AuthUserState {
  const [email, setEmail] = useState<string | null>(null)
  const [sellerSlug, setSellerSlug] = useState<string | null>(null)
  const [isEnterprise, setIsEnterprise] = useState(false)
  const [loaded, setLoaded] = useState(false)

  useEffect(() => {
    if (!hasEnv) {
      setLoaded(true)
      return
    }

    const supabase = createSupabaseBrowserClient()
    let active = true

    async function resolveSeller(userId: string | undefined) {
      if (!userId) {
        setSellerSlug(null)
        setIsEnterprise(false)
        return
      }
      const { data } = await supabase
        .from("seller_accounts")
        .select("slug, account_type")
        .eq("owner_profile_id", userId)
        .limit(1)
        .maybeSingle()
      if (!active) return
      setSellerSlug(data?.slug ?? null)
      setIsEnterprise(data?.account_type === "enterprise")
    }

    supabase.auth.getUser().then(async ({ data }) => {
      if (!active) return
      setEmail(data.user?.email ?? null)
      await resolveSeller(data.user?.id)
      if (active) setLoaded(true)
    })

    const {
      data: { subscription },
    } = supabase.auth.onAuthStateChange((_event, session) => {
      setEmail(session?.user?.email ?? null)
      resolveSeller(session?.user?.id)
      setLoaded(true)
    })

    return () => {
      active = false
      subscription.unsubscribe()
    }
  }, [])

  return {
    loaded,
    email,
    isAuthenticated: loaded && email !== null,
    sellerSlug,
    isEnterprise,
  }
}
