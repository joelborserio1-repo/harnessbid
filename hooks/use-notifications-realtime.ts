"use client"

import { useEffect, useRef } from "react"
import { createSupabaseBrowserClient } from "@/lib/supabase/client"

const hasEnv = Boolean(
  process.env.NEXT_PUBLIC_SUPABASE_URL && process.env.NEXT_PUBLIC_SUPABASE_ANON_KEY,
)

/**
 * Lightweight Supabase Realtime subscription for the current user's
 * notifications. Calls `onInsert` whenever a new notification row is inserted
 * for this profile (e.g. a fresh enquiry), so the UI (unread badge) can update
 * live without polling.
 *
 * Realtime must be enabled for the `notifications` table in the Supabase
 * dashboard (Database -> Replication) for events to flow. If it is not enabled
 * the rest of the app is unaffected — the badge simply updates on navigation.
 *
 * TODO (future realtime, out of scope for Phase 14B):
 *  - outbid notifications during live auctions
 *  - live auction state/price updates
 *  - live enquiry/messages threads
 */
export function useNotificationsRealtime(onInsert: () => void) {
  const onInsertRef = useRef(onInsert)
  onInsertRef.current = onInsert

  useEffect(() => {
    if (!hasEnv) return
    let channel: ReturnType<ReturnType<typeof createSupabaseBrowserClient>["channel"]> | null = null
    let active = true

    const supabase = createSupabaseBrowserClient()

    supabase.auth.getUser().then(({ data }) => {
      const userId = data.user?.id
      if (!active || !userId) return

      channel = supabase
        .channel(`notifications:${userId}:${Math.random().toString(36).slice(2)}`)
        .on(
          "postgres_changes",
          {
            event: "INSERT",
            schema: "public",
            table: "notifications",
            filter: `profile_id=eq.${userId}`,
          },
          () => onInsertRef.current(),
        )
        .subscribe()
    })

    return () => {
      active = false
      if (channel) supabase.removeChannel(channel)
    }
  }, [])
}
