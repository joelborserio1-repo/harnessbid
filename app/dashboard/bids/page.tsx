import Link from "next/link"
import { redirect } from "next/navigation"
import { Clock, Gavel } from "lucide-react"
import { Header } from "@/components/header"
import { Footer } from "@/components/footer"
import { Badge } from "@/components/ui/badge"
import { Button } from "@/components/ui/button"
import { Card, CardContent } from "@/components/ui/card"
import { getSessionUser } from "@/lib/supabase/auth-server"
import { getBuyerBidActivity, type BuyerBidItem } from "@/lib/auctions/dashboard"

export const dynamic = "force-dynamic"

function currency(value: number) {
  return new Intl.NumberFormat("en-US", { style: "currency", currency: "USD", maximumFractionDigits: 0 }).format(value)
}

function timeLeft(iso: string) {
  const ms = new Date(iso).getTime() - Date.now()
  if (ms <= 0) return "Ended"
  const days = Math.floor(ms / 86_400_000)
  const hours = Math.floor((ms % 86_400_000) / 3_600_000)
  return days > 0 ? `${days}d ${hours}h left` : `${Math.max(1, hours)}h left`
}

const STATUS: Record<BuyerBidItem["status"], { label: string; className?: string }> = {
  leading: { label: "Leading", className: "bg-accent text-accent-foreground" },
  outbid: { label: "Outbid" },
  won: { label: "Won", className: "bg-primary text-primary-foreground" },
  closed: { label: "Closed" },
}

export default async function BuyerBidsPage() {
  const user = await getSessionUser()
  if (!user) redirect("/login?redirect=/dashboard/bids")

  const bids = await getBuyerBidActivity()

  return (
    <div className="min-h-screen flex flex-col">
      <Header />
      <main className="flex-1">
        <section className="bg-primary py-8">
          <div className="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <p className="text-sm font-semibold uppercase tracking-wider text-accent">Activity</p>
            <h1 className="mt-2 font-sora text-2xl font-semibold text-primary-foreground sm:text-3xl">
              My bids
            </h1>
          </div>
        </section>

        <section className="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8">
          {bids.length === 0 ? (
            <Card className="border-border/70">
              <CardContent className="flex flex-col items-center gap-4 p-10 text-center">
                <div className="flex h-14 w-14 items-center justify-center rounded-lg border border-accent/40 bg-accent/10">
                  <Gavel className="h-7 w-7 text-primary" />
                </div>
                <h2 className="font-sora text-xl font-semibold text-foreground">No bids yet</h2>
                <p className="max-w-md text-sm leading-6 text-muted-foreground">
                  Place a bid on a live horse auction and track your standing here.
                </p>
                <Button asChild className="bg-accent text-accent-foreground hover:bg-accent/90">
                  <Link href="/auctions">Browse auctions</Link>
                </Button>
              </CardContent>
            </Card>
          ) : (
            <div className="space-y-3">
              {bids.map((bid) => {
                const status = STATUS[bid.status]
                return (
                  <Card key={bid.auctionId} className="border-border/70">
                    <CardContent className="flex flex-col gap-3 p-4 sm:flex-row sm:items-center">
                      <div className="min-w-0 flex-1">
                        <div className="flex flex-wrap items-center gap-2">
                          <Link href={bid.href} className="font-sora font-semibold text-foreground hover:text-primary">
                            {bid.title}
                          </Link>
                          <Badge className={status.className} variant={status.className ? undefined : "secondary"}>
                            {status.label}
                          </Badge>
                        </div>
                        <p className="mt-1 flex items-center gap-3 text-xs text-muted-foreground">
                          <span>Current bid {currency(bid.currentBid)}</span>
                          <span className="flex items-center gap-1">
                            <Clock className="h-3 w-3" />
                            {timeLeft(bid.endsAt)}
                          </span>
                        </p>
                      </div>
                      <Button asChild variant="outline" size="sm">
                        <Link href={bid.href}>View auction</Link>
                      </Button>
                    </CardContent>
                  </Card>
                )
              })}
            </div>
          )}
        </section>
      </main>
      <Footer />
    </div>
  )
}
