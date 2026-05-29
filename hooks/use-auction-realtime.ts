"use client"

import { useEffect } from "react"
import { createSupabaseBrowserClient } from "@/lib/supabase/client"

const hasEnv = Boolean(
  process.env.NEXT_PUBLIC_SUPABASE_URL && process.env.NEXT_PUBLIC_SUPABASE_ANON_KEY,
)

/**
 * Minimal Supabase Realtime subscription for a single auction. Fires `onChange`
 * when the auction row updates (current bid / bid count / ends_at / status) or a
 * new bid is inserted, so the bid panel can refresh live.
 *
 * Requires Realtime enabled for `auctions` and `bids` in the Supabase
 * dashboard. Degrades gracefully (panel still updates on user actions /
 * navigation) if it is not.
 *
 * TODO (future, out of scope): broader live-auction room state, presence/
 * viewer counts, and live message threads.
 */
export function useAuctionRealtime(auctionId: string, onChange: () => void) {
  useEffect(() => {
    if (!hasEnv || !auctionId) return
    const supabase = createSupabaseBrowserClient()

    const channel = supabase
      .channel(`auction:${auctionId}`)
      .on(
        "postgres_changes",
        { event: "UPDATE", schema: "public", table: "auctions", filter: `id=eq.${auctionId}` },
        () => onChange(),
      )
      .on(
        "postgres_changes",
        { event: "INSERT", schema: "public", table: "bids", filter: `auction_id=eq.${auctionId}` },
        () => onChange(),
      )
      .subscribe()

    return () => {
      supabase.removeChannel(channel)
    }
  }, [auctionId, onChange])
}
