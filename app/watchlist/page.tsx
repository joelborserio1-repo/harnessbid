import Link from "next/link"
import { redirect } from "next/navigation"
import { Clock, Heart, MapPin } from "lucide-react"
import { Header } from "@/components/header"
import { Footer } from "@/components/footer"
import { Badge } from "@/components/ui/badge"
import { Button } from "@/components/ui/button"
import { Card, CardContent } from "@/components/ui/card"
import { WatchButton } from "@/components/listings/watch-button"
import { getSessionUser } from "@/lib/supabase/auth-server"
import { getUserWatchlist, type WatchlistItem } from "@/lib/listings/watchlist"

export const dynamic = "force-dynamic"

function formatPrice(value: number | null) {
  if (value == null) return "Price on request"
  return new Intl.NumberFormat("en-US", {
    style: "currency",
    currency: "USD",
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(value)
}

function timeLeft(iso: string | null) {
  if (!iso) return null
  const ms = new Date(iso).getTime() - Date.now()
  if (ms <= 0) return "Closed"
  const days = Math.floor(ms / 86_400_000)
  const hours = Math.floor((ms % 86_400_000) / 3_600_000)
  return days > 0 ? `${days}d ${hours}h left` : `${Math.max(1, hours)}h left`
}

function WatchCard({ item }: { item: WatchlistItem }) {
  const countdown = timeLeft(item.endsAt)
  return (
    <Card className="overflow-hidden border-border/70">
      <div className="relative aspect-[4/3] bg-secondary">
        {/* eslint-disable-next-line @next/next/no-img-element */}
        <img src={item.image} alt="" className="h-full w-full object-cover" />
        <div className="absolute left-2 top-2">
          <Badge
            className={
              item.kind === "marketplace"
                ? "bg-primary text-primary-foreground"
                : "bg-accent text-accent-foreground"
            }
          >
            {item.badge}
          </Badge>
        </div>
        <div className="absolute right-2 top-2">
          <WatchButton listingId={item.recordId} kind={item.kind} initialWatched variant="icon" />
        </div>
      </div>
      <CardContent className="space-y-2 p-4">
        <Link href={item.href} className="block">
          <h3 className="truncate font-sora font-semibold text-foreground hover:text-primary">
            {item.title}
          </h3>
        </Link>
        <div className="flex items-center gap-3 text-xs text-muted-foreground">
          <span className="truncate">{item.seller}</span>
          <span className="flex items-center gap-1">
            <MapPin className="h-3 w-3" />
            <span className="truncate">{item.location}</span>
          </span>
        </div>
        <div className="flex items-center justify-between pt-1">
          <div>
            <p className="text-xs text-muted-foreground">{item.priceLabel}</p>
            <p className="font-semibold text-foreground">{formatPrice(item.price)}</p>
          </div>
          {countdown && (
            <span className="flex items-center gap-1 text-xs font-medium text-foreground">
              <Clock className="h-3.5 w-3.5 text-accent" />
              {countdown}
            </span>
          )}
        </div>
      </CardContent>
    </Card>
  )
}

export default async function WatchlistPage() {
  const user = await getSessionUser()
  if (!user) redirect("/login?redirect=/watchlist")

  const items = await getUserWatchlist()

  return (
    <div className="min-h-screen flex flex-col">
      <Header />
      <main className="flex-1">
        <section className="bg-primary py-10 sm:py-12">
          <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <p className="text-sm font-semibold uppercase tracking-wider text-accent">Watchlist</p>
            <h1 className="mt-3 font-sora text-3xl font-semibold tracking-wide text-primary-foreground sm:text-4xl">
              Saved listings
            </h1>
            <p className="mt-4 max-w-2xl text-primary-foreground/75">
              Horse auctions, buy now horses, and marketplace listings you&apos;re watching.
            </p>
          </div>
        </section>

        <section className="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
          {items.length === 0 ? (
            <Card className="border-border/70">
              <CardContent className="flex flex-col items-center gap-4 p-10 text-center">
                <div className="flex h-14 w-14 items-center justify-center rounded-lg border border-accent/40 bg-accent/10">
                  <Heart className="h-7 w-7 text-primary" />
                </div>
                <h2 className="font-sora text-xl font-semibold text-foreground">
                  Your watchlist is empty
                </h2>
                <p className="max-w-md text-sm leading-6 text-muted-foreground">
                  Save auctions, horses, and marketplace listings to compare them and revisit before
                  contacting sellers.
                </p>
                <div className="flex flex-col gap-3 sm:flex-row">
                  <Button asChild className="bg-accent text-accent-foreground hover:bg-accent/90">
                    <Link href="/auctions">Browse auctions</Link>
                  </Button>
                  <Button asChild variant="outline">
                    <Link href="/marketplace">Browse marketplace</Link>
                  </Button>
                </div>
              </CardContent>
            </Card>
          ) : (
            <div className="grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
              {items.map((item) => (
                <WatchCard key={`${item.kind}-${item.recordId}`} item={item} />
              ))}
            </div>
          )}
        </section>
      </main>
      <Footer />
    </div>
  )
}
