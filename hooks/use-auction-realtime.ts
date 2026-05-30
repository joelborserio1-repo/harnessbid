"use client"

import { useEffect, useRef } from "react"
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
 * Implementation notes:
 * - `onChange` is held in a ref so the effect depends only on `auctionId`.
 *   A new callback identity each render would otherwise re-run the effect, and
 *   because supabase-js caches channels by name the re-run attaches `.on()` to
 *   an already-subscribed channel and throws "cannot add postgres_changes
 *   callbacks ... after subscribe()".
 * - The channel name is unique per mount so a not-yet-finished async
 *   removeChannel() from a previous mount cannot collide with the new one.
 */
export function useAuctionRealtime(auctionId: string, onChange: () => void) {
  const onChangeRef = useRef(onChange)
  onChangeRef.current = onChange

  useEffect(() => {
    if (!hasEnv || !auctionId) return
    const supabase = createSupabaseBrowserClient()

    const channel = supabase
      .channel(`auction:${auctionId}:${Math.random().toString(36).slice(2)}`)
      .on(
        "postgres_changes",
        { event: "UPDATE", schema: "public", table: "auctions", filter: `id=eq.${auctionId}` },
        () => onChangeRef.current(),
      )
      .on(
        "postgres_changes",
        { event: "INSERT", schema: "public", table: "bids", filter: `auction_id=eq.${auctionId}` },
        () => onChangeRef.current(),
      )
      .subscribe()

    return () => {
      supabase.removeChannel(channel)
    }
  }, [auctionId])
}
