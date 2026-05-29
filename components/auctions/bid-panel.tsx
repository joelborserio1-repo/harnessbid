"use client"

import { useCallback, useState, useTransition } from "react"
import Link from "next/link"
import { useRouter } from "next/navigation"
import { Gavel, ShieldCheck, ShieldAlert } from "lucide-react"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { createSupabaseBrowserClient } from "@/lib/supabase/client"
import { useAuctionRealtime } from "@/hooks/use-auction-realtime"
import { placeBidAction } from "@/lib/auctions/actions"

const hasEnv = Boolean(
  process.env.NEXT_PUBLIC_SUPABASE_URL && process.env.NEXT_PUBLIC_SUPABASE_ANON_KEY,
)

function currency(value: number) {
  return new Intl.NumberFormat("en-US", {
    style: "currency",
    currency: "USD",
    maximumFractionDigits: 0,
  }).format(value)
}

export type BidPanelProps = {
  auctionId: string
  listingSlug: string
  authenticated: boolean
  initialLeading: boolean
  currentBid: number
  bidCount: number
  bidIncrement: number
  reservePrice: number | null
  reserveMet: boolean
  startingBid: number
}

export function BidPanel(props: BidPanelProps) {
  const router = useRouter()
  const [currentBid, setCurrentBid] = useState(props.currentBid)
  const [bidCount, setBidCount] = useState(props.bidCount)
  const [reserveMet, setReserveMet] = useState(props.reserveMet)
  const [leading, setLeading] = useState(props.initialLeading)
  const [hasAnyBid, setHasAnyBid] = useState(props.bidCount > 0)
  const [amount, setAmount] = useState("")
  const [pending, startTransition] = useTransition()
  const [message, setMessage] = useState<string | null>(null)
  const [error, setError] = useState<string | null>(null)

  const minBid = hasAnyBid ? currentBid + props.bidIncrement : props.startingBid

  const refresh = useCallback(async () => {
    if (!hasEnv) return
    try {
      const supabase = createSupabaseBrowserClient()
      const { data: auction } = await supabase
        .from("auctions")
        .select("current_bid, bid_count, reserve_met")
        .eq("id", props.auctionId)
        .maybeSingle()
      if (auction) {
        setCurrentBid(Number(auction.current_bid ?? props.startingBid))
        setBidCount(auction.bid_count)
        setReserveMet(auction.reserve_met)
        setHasAnyBid(auction.bid_count > 0)
      }
      const {
        data: { user },
      } = await supabase.auth.getUser()
      if (user) {
        const { data: mine } = await supabase
          .from("bids")
          .select("status")
          .eq("auction_id", props.auctionId)
          .eq("bidder_profile_id", user.id)
        setLeading((mine ?? []).some((b) => b.status === "winning"))
      }
    } catch {
      /* ignore */
    }
  }, [props.auctionId, props.startingBid])

  useAuctionRealtime(props.auctionId, refresh)

  function submit() {
    setError(null)
    setMessage(null)
    const value = Number(amount.replace(/[^0-9.]/g, ""))
    if (!value || value < minBid) {
      setError(`Enter ${currency(minBid)} or more.`)
      return
    }
    startTransition(async () => {
      const result = await placeBidAction(props.auctionId, value, props.listingSlug)
      if (!result.ok) {
        setError(result.error ?? "Your bid could not be placed.")
        return
      }
      setMessage(result.message ?? "Bid placed.")
      if (result.currentBid != null) setCurrentBid(result.currentBid)
      if (result.bidCount != null) setBidCount(result.bidCount)
      if (result.reserveMet != null) setReserveMet(result.reserveMet)
      if (result.leading != null) setLeading(result.leading)
      setHasAnyBid(true)
      setAmount("")
      router.refresh()
    })
  }

  return (
    <div className="space-y-4">
      <div className="text-center">
        <p className="mb-1 text-sm text-muted-foreground">Current Bid</p>
        <p className="text-4xl font-bold text-foreground">{currency(currentBid)}</p>
        <p className="mt-1 text-sm text-muted-foreground">{bidCount} bids</p>
      </div>

      {props.reservePrice != null && (
        <div
          className={
            "flex items-center justify-center gap-2 rounded-md border px-3 py-2 text-sm " +
            (reserveMet
              ? "border-accent/40 bg-accent/10 text-foreground"
              : "border-border bg-secondary/50 text-muted-foreground")
          }
        >
          {reserveMet ? (
            <ShieldCheck className="h-4 w-4 text-accent" />
          ) : (
            <ShieldAlert className="h-4 w-4" />
          )}
          {reserveMet ? "Reserve met" : "Reserve not met"}
        </div>
      )}

      {leading && (
        <p className="rounded-md border border-accent/30 bg-accent/10 px-3 py-2 text-center text-sm font-medium text-foreground">
          You&apos;re the highest bidder
        </p>
      )}

      {!props.authenticated ? (
        <Button asChild className="w-full bg-accent text-accent-foreground hover:bg-accent/90">
          <Link href={`/login?redirect=/auctions/${props.listingSlug}`}>Log in to bid</Link>
        </Button>
      ) : (
        <div>
          <p className="mb-2 text-sm text-muted-foreground">
            Enter your maximum bid — {currency(minBid)} or more
          </p>
          <div className="flex gap-2">
            <div className="relative flex-1">
              <span className="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground">$</span>
              <Input
                type="number"
                inputMode="numeric"
                value={amount}
                onChange={(e) => setAmount(e.target.value)}
                placeholder={String(minBid)}
                className="pl-7"
                disabled={pending}
              />
            </div>
            <Button
              onClick={submit}
              disabled={pending}
              className="bg-accent text-accent-foreground hover:bg-accent/90"
            >
              <Gavel className="mr-2 h-4 w-4" />
              {pending ? "Placing…" : "Place bid"}
            </Button>
          </div>
          <p className="mt-2 text-xs text-muted-foreground">
            Proxy bidding: we bid up to your maximum in {currency(props.bidIncrement)} increments.
          </p>
        </div>
      )}

      {error && (
        <p className="rounded-md border border-destructive/30 bg-destructive/10 px-3 py-2 text-sm text-destructive">
          {error}
        </p>
      )}
      {message && !error && (
        <p className="rounded-md border border-accent/30 bg-accent/10 px-3 py-2 text-sm text-foreground">
          {message}
        </p>
      )}
    </div>
  )
}
