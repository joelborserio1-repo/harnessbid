"use server"

import { revalidatePath } from "next/cache"
import {
  createSupabaseServerAuthClient,
  hasSupabaseEnv,
} from "@/lib/supabase/auth-server"
import { LIMITS, rateLimit } from "@/lib/rate-limit"

export type PlaceBidResult = {
  ok: boolean
  error?: string
  message?: string
  currentBid?: number
  bidCount?: number
  reserveMet?: boolean
  endsAt?: string
  leading?: boolean
}

const ERROR_MESSAGES: Record<string, string> = {
  auth: "Please sign in to place a bid.",
  invalid_amount: "Enter a valid bid amount.",
  not_found: "This auction is no longer available.",
  not_started: "This auction has not started yet.",
  closed: "This auction has closed.",
  self: "You can't bid on your own listing.",
  too_low: "Your bid is below the minimum.",
  already_leading: "You're already the highest bidder.",
}

/**
 * Places a (proxy) bid via the atomic `place_bid` RPC. `maxAmount` is the
 * bidder's maximum; for a single bid pass the desired amount. All validation,
 * proxy resolution, reserve, and anti-sniping happen server-side in Postgres.
 */
export async function placeBidAction(
  auctionId: string,
  maxAmount: number,
  listingSlug?: string,
): Promise<PlaceBidResult> {
  if (!hasSupabaseEnv()) return { ok: false, error: "Bidding is not available yet." }
  if (!auctionId || !Number.isFinite(maxAmount) || maxAmount <= 0) {
    return { ok: false, error: ERROR_MESSAGES.invalid_amount }
  }

  const supabase = await createSupabaseServerAuthClient()
  const {
    data: { user },
  } = await supabase.auth.getUser()
  if (user) {
    const limited = rateLimit(`bid:${user.id}`, LIMITS.bid.limit, LIMITS.bid.windowMs)
    if (!limited.allowed) {
      return { ok: false, error: "Too many bids in a short time. Please wait a moment." }
    }
  }

  const { data, error } = await supabase.rpc("place_bid", {
    p_auction_id: auctionId,
    p_max_amount: maxAmount,
  })

  if (error) return { ok: false, error: "Could not place your bid. Please try again." }

  const result = (data ?? {}) as {
    ok: boolean
    error?: string
    min?: number
    current_bid?: number
    bid_count?: number
    reserve_met?: boolean
    ends_at?: string
    leading?: boolean
  }

  if (!result.ok) {
    const base = ERROR_MESSAGES[result.error ?? ""] ?? "Your bid could not be placed."
    const msg =
      result.error === "too_low" && result.min
        ? `Your bid must be at least ${new Intl.NumberFormat("en-US", {
            style: "currency",
            currency: "USD",
            maximumFractionDigits: 0,
          }).format(Number(result.min))}.`
        : base
    return { ok: false, error: msg }
  }

  if (listingSlug) revalidatePath(`/auctions/${listingSlug}`)

  return {
    ok: true,
    message: result.leading
      ? "You're the highest bidder."
      : "Bid placed — you were outbid by an existing proxy bid.",
    currentBid: result.current_bid != null ? Number(result.current_bid) : undefined,
    bidCount: result.bid_count,
    reserveMet: result.reserve_met,
    endsAt: result.ends_at,
    leading: result.leading,
  }
}
