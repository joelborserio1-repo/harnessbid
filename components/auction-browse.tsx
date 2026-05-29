import Image from "next/image"
import Link from "next/link"
import { BadgeCheck, ChevronDown, Clock, Gavel, MapPin, Search, SlidersHorizontal } from "lucide-react"
import { Badge } from "@/components/ui/badge"
import { Button } from "@/components/ui/button"
import { Card, CardContent } from "@/components/ui/card"
import { Input } from "@/components/ui/input"
import { WatchButton } from "@/components/listings/watch-button"
import { SaveSearchButton } from "@/components/saved-searches/save-search-button"
import type { HorseAuctionCard } from "@/lib/supabase/queries"

function formatCurrency(amount: number) {
  return new Intl.NumberFormat("en-US", {
    style: "currency",
    currency: "USD",
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(amount)
}

export function AuctionBrowse({ auctions }: { auctions: HorseAuctionCard[] }) {
  return (
    <div className="min-h-screen bg-background">
      <section className="bg-primary py-8 lg:py-12">
        <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
          <nav className="mb-4 flex items-center gap-2 text-sm text-primary-foreground/60">
            <Link href="/" className="hover:text-primary-foreground">Home</Link>
            <ChevronDown className="h-4 w-4 rotate-[-90deg]" />
            <span className="text-primary-foreground">Horse Auctions</span>
          </nav>
          <div className="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
            <div>
              <Badge className="mb-4 bg-accent text-accent-foreground">
                <Gavel className="mr-1 h-3 w-3" />
                Live horse sales
              </Badge>
              <h1 className="font-sora text-3xl font-semibold tracking-wide text-primary-foreground lg:text-4xl">
                HORSE AUCTIONS
              </h1>
              <p className="mt-3 max-w-2xl text-primary-foreground/75">
                Premium standardbred auctions with pedigree, race record, seller verification, and clear sale timing.
              </p>
            </div>
            <div className="flex flex-col gap-3 sm:flex-row lg:min-w-[420px]">
              <div className="relative flex-1">
                <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                <Input className="bg-card pl-9" placeholder="Search horses, sires, sellers..." />
              </div>
              <Button variant="outline" className="border-primary-foreground/30 text-primary-foreground hover:bg-primary-foreground/10">
                <SlidersHorizontal className="mr-2 h-4 w-4" />
                Filters
              </Button>
            </div>
          </div>
        </div>
      </section>

      <section className="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8 lg:py-10">
        <div className="mb-6 flex flex-col gap-3 border-b border-border pb-5 sm:flex-row sm:items-center sm:justify-between">
          <p className="text-sm text-muted-foreground">
            {auctions.length} live horse auctions
          </p>
          <div className="flex flex-wrap items-center gap-2">
            <Badge variant="secondary">Live now</Badge>
            <Badge variant="secondary">Verified sellers</Badge>
            <SaveSearchButton searchType="horse_auction" />
          </div>
        </div>

        <div className="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
          {auctions.length === 0 && (
            <div className="col-span-full rounded-lg border border-border bg-card p-8 text-center">
              <Gavel className="mx-auto mb-4 h-8 w-8 text-primary" />
              <h2 className="font-sora text-xl font-semibold text-foreground">No horse auctions are live yet</h2>
              <p className="mx-auto mt-2 max-w-xl text-sm leading-6 text-muted-foreground">
                Supabase horse auction rows will appear here when auctions are scheduled or live and connected to published horse listings.
              </p>
              <Link href="/sell/horse">
                <Button className="mt-5 bg-accent text-accent-foreground hover:bg-accent/90">
                  Sell your horse
                </Button>
              </Link>
            </div>
          )}
          {auctions.map((auction) => (
            <Link key={auction.id} href={`/auctions/${auction.id}`}>
              <Card className="group h-full overflow-hidden border-border/60 bg-card card-hover">
                <div className="relative aspect-[4/3] overflow-hidden">
                  <Image
                    src={auction.image}
                    alt={auction.title}
                    fill
                    className="object-cover transition-transform duration-500 group-hover:scale-105"
                  />
                  <Badge className="absolute left-3 top-3 bg-accent text-accent-foreground">
                    <Gavel className="mr-1 h-3 w-3" />
                    Horse Auction
                  </Badge>
                  <div className="absolute bottom-3 left-3 rounded-md bg-primary/90 px-3 py-2 text-xs text-primary-foreground backdrop-blur-sm">
                    <Clock className="mr-1 inline h-3 w-3 text-accent" />
                    {auction.endTime}
                  </div>
                  <div className="absolute bottom-3 right-3 opacity-0 transition-opacity group-hover:opacity-100">
                    <WatchButton listingId={auction.recordId} kind="horse" variant="icon" />
                  </div>
                </div>
                <CardContent className="p-5">
                  <h2 className="font-sora text-lg font-semibold text-foreground">{auction.title}</h2>
                  <p className="mt-2 line-clamp-2 text-sm leading-6 text-muted-foreground">{auction.description}</p>
                  <div className="mt-3 flex items-center gap-1 text-sm text-muted-foreground">
                    <MapPin className="h-4 w-4" />
                    {auction.location}
                  </div>
                  <div className="mt-5 flex items-end justify-between border-t border-border pt-4">
                    <div>
                      <p className="text-xs text-muted-foreground">Current bid</p>
                      <p className="text-xl font-semibold text-foreground">{formatCurrency(auction.currentBid)}</p>
                    </div>
                    <div className="text-right text-sm text-muted-foreground">
                      <div className="flex items-center justify-end gap-1">
                        <BadgeCheck className="h-4 w-4 text-accent" />
                        {auction.seller}
                      </div>
                      <p>{auction.bids} bids</p>
                    </div>
                  </div>
                </CardContent>
              </Card>
            </Link>
          ))}
        </div>
      </section>
    </div>
  )
}
