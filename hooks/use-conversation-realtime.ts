"use client"

import { useEffect } from "react"
import { createSupabaseBrowserClient } from "@/lib/supabase/client"

const hasEnv = Boolean(
  process.env.NEXT_PUBLIC_SUPABASE_URL && process.env.NEXT_PUBLIC_SUPABASE_ANON_KEY,
)

/**
 * Minimal realtime for a single conversation: fires `onMessage` when a new
 * message is inserted into it. Requires Realtime enabled for `messages` in the
 * Supabase dashboard; degrades gracefully (the thread still updates on send /
 * navigation) if it is not. Reuses the notifications realtime pattern.
 */
export function useConversationRealtime(conversationId: string, onMessage: () => void) {
  useEffect(() => {
    if (!hasEnv || !conversationId) return
    const supabase = createSupabaseBrowserClient()
    const channel = supabase
      .channel(`conversation:${conversationId}`)
      .on(
        "postgres_changes",
        {
          event: "INSERT",
          schema: "public",
          table: "messages",
          filter: `conversation_id=eq.${conversationId}`,
        },
        () => onMessage(),
      )
      .subscribe()
    return () => {
      supabase.removeChannel(channel)
    }
  }, [conversationId, onMessage])
}
