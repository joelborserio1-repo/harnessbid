import {
  createSupabaseServerAuthClient,
  hasSupabaseEnv,
} from "@/lib/supabase/auth-server"

export type BidHistoryItem = { bidder: string; time: string; amount: number }

function timeAgo(iso: string): string {
  const mins = Math.floor((Date.now() - new Date(iso).getTime()) / 60000)
  if (mins < 1) return "just now"
  if (mins < 60) return `${mins}m ago`
  const hrs = Math.floor(mins / 60)
  if (hrs < 24) return `${hrs}h ago`
  return `${Math.floor(hrs / 24)}d ago`
}

/**
 * Anonymised, public-safe bid history via the `get_auction_bid_history` RPC.
 * Works for unauthenticated viewers (the RPC is SECURITY DEFINER and only
 * returns rows for visible auctions).
 */
export async function getAuctionBidHistory(auctionId: string): Promise<BidHistoryItem[]> {
  if (!hasSupabaseEnv()) return []
  const supabase = await createSupabaseServerAuthClient()
  const { data, error } = await supabase.rpc("get_auction_bid_history", { p_auction: auctionId })
  if (error || !data) return []
  return (data as Array<{ bidder_label: string; amount: number; placed_at: string }>).map((b) => ({
    bidder: b.bidder_label,
    time: timeAgo(b.placed_at),
    amount: Number(b.amount),
  }))
}

/** Lazily close an auction whose end time has passed (no-op otherwise). */
export async function closeAuctionIfEnded(auctionId: string): Promise<void> {
  if (!hasSupabaseEnv()) return
  const supabase = await createSupabaseServerAuthClient()
  await supabase.rpc("close_auction_if_ended", { p_auction: auctionId })
}

export type ViewerAuctionState = {
  authenticated: boolean
  leading: boolean
  hasBid: boolean
}

/** Whether the current viewer is the leader / has bid on this auction. */
export async function getViewerAuctionState(auctionId: string): Promise<ViewerAuctionState> {
  if (!hasSupabaseEnv()) return { authenticated: false, leading: false, hasBid: false }
  const supabase = await createSupabaseServerAuthClient()
  const {
    data: { user },
  } = await supabase.auth.getUser()
  if (!user) return { authenticated: false, leading: false, hasBid: false }

  const { data } = await supabase
    .from("bids")
    .select("status")
    .eq("auction_id", auctionId)
    .eq("bidder_profile_id", user.id)

  const rows = data ?? []
  return {
    authenticated: true,
    hasBid: rows.length > 0,
    leading: rows.some((r) => r.status === "winning"),
  }
}
