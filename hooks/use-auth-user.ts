"use client"

import { useEffect, useState } from "react"
import { createSupabaseBrowserClient } from "@/lib/supabase/client"

export type AuthUserState = {
  /** True once the initial auth check has resolved on the client. */
  loaded: boolean
  email: string | null
  isAuthenticated: boolean
}

const hasEnv = Boolean(
  process.env.NEXT_PUBLIC_SUPABASE_URL && process.env.NEXT_PUBLIC_SUPABASE_ANON_KEY,
)

/**
 * Client-side auth state for the header. Renders the logged-out state during
 * SSR and the first client render (loaded=false) to avoid hydration mismatch,
 * then resolves the real session after mount and on any auth change.
 */
export function useAuthUser(): AuthUserState {
  const [email, setEmail] = useState<string | null>(null)
  const [loaded, setLoaded] = useState(false)

  useEffect(() => {
    if (!hasEnv) {
      setLoaded(true)
      return
    }

    const supabase = createSupabaseBrowserClient()
    let active = true

    supabase.auth.getUser().then(({ data }) => {
      if (!active) return
      setEmail(data.user?.email ?? null)
      setLoaded(true)
    })

    const {
      data: { subscription },
    } = supabase.auth.onAuthStateChange((_event, session) => {
      setEmail(session?.user?.email ?? null)
      setLoaded(true)
    })

    return () => {
      active = false
      subscription.unsubscribe()
    }
  }, [])

  return { loaded, email, isAuthenticated: loaded && email !== null }
}
