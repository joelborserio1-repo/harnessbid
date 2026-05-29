import {
  createSupabaseServerAuthClient,
  hasSupabaseEnv,
} from "@/lib/supabase/auth-server"

export type BuyerBidItem = {
  auctionId: string
  title: string
  href: string
  currentBid: number
  status: "leading" | "outbid" | "won" | "closed"
  endsAt: string
}

/** Auctions the current user has bid on, with their standing. */
export async function getBuyerBidActivity(): Promise<BuyerBidItem[]> {
  if (!hasSupabaseEnv()) return []
  const supabase = await createSupabaseServerAuthClient()
  const {
    data: { user },
  } = await supabase.auth.getUser()
  if (!user) return []

  const { data: bids } = await supabase
    .from("bids")
    .select("auction_id, status")
    .eq("bidder_profile_id", user.id)
  if (!bids || bids.length === 0) return []

  const auctionIds = [...new Set(bids.map((b) => b.auction_id))]
  const leadingSet = new Set(bids.filter((b) => b.status === "winning").map((b) => b.auction_id))
  const wonSet = new Set(bids.filter((b) => b.status === "won").map((b) => b.auction_id))

  const { data: auctions } = await supabase
    .from("auctions")
    .select("id, horse_listing_id, current_bid, starting_bid, status, ends_at, winner_profile_id")
    .in("id", auctionIds)
  if (!auctions) return []

  const horseIds = auctions.map((a) => a.horse_listing_id).filter((v): v is string => Boolean(v))
  const { data: horses } = horseIds.length
    ? await supabase.from("horse_listings").select("id, title, slug").in("id", horseIds)
    : { data: [] as Array<{ id: string; title: string; slug: string }> }
  const horseById = new Map((horses ?? []).map((h) => [h.id, h]))

  return auctions.map((a) => {
    const horse = a.horse_listing_id ? horseById.get(a.horse_listing_id) : undefined
    const closed = a.status === "closed" || a.status === "settled"
    let status: BuyerBidItem["status"]
    if (wonSet.has(a.id) || (closed && a.winner_profile_id === user.id)) status = "won"
    else if (closed) status = "closed"
    else if (leadingSet.has(a.id)) status = "leading"
    else status = "outbid"

    return {
      auctionId: a.id,
      title: horse?.title ?? "Horse auction",
      href: `/auctions/${horse?.slug ?? a.id}`,
      currentBid: Number(a.current_bid ?? a.starting_bid ?? 0),
      status,
      endsAt: a.ends_at,
    }
  })
}

export type SellerAuctionItem = {
  auctionId: string
  title: string
  href: string
  currentBid: number
  bidCount: number
  reservePrice: number | null
  reserveMet: boolean
  status: string
  endsAt: string
  closingSoon: boolean
}

/** The current seller's auctions with reserve + bid status for the dashboard. */
export async function getSellerAuctionSummary(): Promise<SellerAuctionItem[]> {
  if (!hasSupabaseEnv()) return []
  const supabase = await createSupabaseServerAuthClient()
  const {
    data: { user },
  } = await supabase.auth.getUser()
  if (!user) return []

  const { data: account } = await supabase
    .from("seller_accounts")
    .select("id")
    .eq("owner_profile_id", user.id)
    .limit(1)
    .maybeSingle()
  if (!account) return []

  const { data: horses } = await supabase
    .from("horse_listings")
    .select("id, title, slug")
    .eq("seller_account_id", account.id)
  if (!horses || horses.length === 0) return []
  const horseById = new Map(horses.map((h) => [h.id, h]))

  const { data: auctions } = await supabase
    .from("auctions")
    .select("id, horse_listing_id, current_bid, starting_bid, bid_count, reserve_price, reserve_met, status, ends_at")
    .in(
      "horse_listing_id",
      horses.map((h) => h.id),
    )
    .order("ends_at", { ascending: true })
  if (!auctions) return []

  const soonMs = 24 * 3_600_000
  return auctions.map((a) => {
    const horse = a.horse_listing_id ? horseById.get(a.horse_listing_id) : undefined
    const endMs = new Date(a.ends_at).getTime() - Date.now()
    return {
      auctionId: a.id,
      title: horse?.title ?? "Horse auction",
      href: `/auctions/${horse?.slug ?? a.id}`,
      currentBid: Number(a.current_bid ?? a.starting_bid ?? 0),
      bidCount: a.bid_count,
      reservePrice: a.reserve_price != null ? Number(a.reserve_price) : null,
      reserveMet: a.reserve_met,
      status: a.status,
      endsAt: a.ends_at,
      closingSoon:
        (a.status === "live" || a.status === "extended") && endMs > 0 && endMs < soonMs,
    }
  })
}
