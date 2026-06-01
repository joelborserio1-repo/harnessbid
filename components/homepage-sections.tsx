"use client"

import { useEffect, useState } from "react"
import Link from "next/link"
import Image from "next/image"
import {
  Search,
  MapPin,
  ChevronRight,
  ArrowRight,
  Shield,
  ShieldCheck,
  FileText,
  Clock,
  Truck,
  CreditCard,
  Headphones,
  Gavel,
  BadgeCheck,
  ShoppingBag,
} from "lucide-react"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { Badge } from "@/components/ui/badge"
import { Card, CardContent } from "@/components/ui/card"
import type { HorseAuctionCard, MarketplaceCard, MarketplaceCategory } from "@/lib/supabase/queries"

const assetPath = (path: string) => `${process.env.NEXT_PUBLIC_BASE_PATH || ""}${path}`

const consignors = [
  { name: "APG", full: "Australian Pacing Gold", kind: "Yearling sales", href: "/sellers/apg" },
  { name: "Nutrien", full: "Nutrien Bloodstock", kind: "Bloodstock agents", href: "/sellers/nutrien" },
  { name: "Tattersalls", full: "Tattersalls", kind: "Sales house", href: "/sellers/tattersalls" },
  { name: "Lexington", full: "Lexington Selected", kind: "Premier sale", href: "/sellers/lexington-selected" },
]

function formatCurrency(amount: number) {
  return new Intl.NumberFormat("en-US", {
    style: "currency",
    currency: "USD",
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(amount)
}

/* ---------------------------------------------------------------------------
 * Hero — compact, flat navy fill (no gradient/scrim). Value prop + the two
 * primary conversions (Register to Bid, Sell a Horse) + a live-auctions strip
 * pulled above the fold so first-timers see real activity immediately.
 * ------------------------------------------------------------------------- */
/* Soonest-closing live lot -> drives the "Next sale closes in" chip. */
function useNextClose(auctions: HorseAuctionCard[]) {
  const next = auctions
    .map((a) => a.endsAt)
    .filter(Boolean)
    .sort((a, b) => new Date(a).getTime() - new Date(b).getTime())[0]
  const [now, setNow] = useState(() => Date.now())
  useEffect(() => {
    if (!next) return
    const t = setInterval(() => setNow(Date.now()), 1000)
    return () => clearInterval(t)
  }, [next])
  if (!next) return null
  const ms = new Date(next).getTime() - now
  if (ms <= 0) return "closing"
  const h = Math.floor(ms / 3_600_000)
  const m = Math.floor((ms % 3_600_000) / 60_000)
  const s = Math.floor((ms % 60_000) / 1000)
  const pad = (n: number) => String(n).padStart(2, "0")
  return `${pad(h)}:${pad(m)}:${pad(s)}`
}

export function HeroSection({ auctions = [] }: { auctions?: HorseAuctionCard[] }) {
  const countdown = useNextClose(auctions)
  const liveCount = auctions.length

  return (
    <section className="relative isolate overflow-hidden bg-champagne text-foreground">
      {/* Cinematic horse image faded subtly behind the whole hero so the
          two-column content (text + featured lots) stays fully readable. */}
      <div className="pointer-events-none absolute inset-0 -z-10">
        <Image
          src={assetPath("/thekingman.jpg")}
          alt=""
          fill
          priority
          className="object-cover object-center"
        />
        {/* Light champagne veil + left fade so the text column stays legible
            while the photo shows almost fully. */}
        <div className="absolute inset-0 bg-gradient-to-r from-champagne/85 via-champagne/45 to-champagne/10" />
        <div className="absolute inset-0 bg-gradient-to-t from-champagne/45 via-transparent to-transparent" />
      </div>

      <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-16 sm:py-20 lg:py-24">
        <div className="grid lg:grid-cols-[1.05fr_0.95fr] gap-10 lg:gap-14 items-center">
          {/* Left: positioning + conversions */}
          <div className="max-w-xl">
            {/* Live-sale signal */}
            <div className="mb-6 flex flex-wrap items-center gap-2.5">
              <span className="inline-flex items-center gap-2 rounded-full border border-accent/40 bg-accent/10 px-3 py-1 text-[11px] font-medium tracking-wide text-gold-dark">
                <span className="relative flex h-1.5 w-1.5">
                  <span className="absolute inline-flex h-full w-full animate-ping rounded-full bg-accent/70" />
                  <span className="relative inline-flex h-1.5 w-1.5 rounded-full bg-accent" />
                </span>
                {liveCount > 0 ? `Live now · ${liveCount} lot${liveCount === 1 ? "" : "s"} open` : "Sale ring opening soon"}
              </span>
              {countdown && countdown !== "closing" && (
                <span className="inline-flex items-center gap-1.5 rounded-full border border-border bg-card/70 px-3 py-1 text-[11px] text-muted-foreground">
                  <Clock className="h-3 w-3 text-accent" />
                  Closes in <span className="font-mono tabular-nums text-foreground">{countdown}</span>
                </span>
              )}
            </div>

            <p className="font-sans text-[11px] sm:text-xs uppercase tracking-[0.24em] text-gold-dark mb-5">
              Online auctions for standardbred bloodstock
            </p>
            <h1 className="font-cinzel text-4xl sm:text-6xl lg:text-7xl font-medium leading-[1.04] tracking-tight text-balance text-primary">
              Where champions
              <span className="block italic text-gold-dark">change hands</span>
            </h1>
            <p className="mt-6 text-base sm:text-lg text-ink-soft/80 max-w-xl leading-relaxed">
              Live online auctions for standardbred racehorses, broodmares, yearlings and
              shares — every lot with a verified pedigree, and the sale ring at your fingertips.
            </p>

            <div className="mt-8 flex flex-wrap gap-3">
              <Link href="/register">
                <Button size="lg" className="bg-primary text-primary-foreground hover:bg-primary/90 shadow-sm rounded-sm">
                  <Gavel className="mr-2 h-4 w-4" />
                  Register to bid
                </Button>
              </Link>
              <Link href="/sell/horse">
                <Button size="lg" variant="outline" className="border-primary/30 text-primary hover:bg-primary/5 rounded-sm">
                  Sell a horse
                </Button>
              </Link>
            </div>

            {/* Search */}
            <form action="/search" className="mt-7 max-w-md relative">
              <Search className="absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
              <Input
                name="q"
                type="search"
                placeholder="Search horses, equipment, services…"
                className="h-12 bg-card/90 backdrop-blur text-foreground placeholder:text-muted-foreground pl-10 rounded-sm border border-border shadow-sm"
              />
            </form>
          </div>

          {/* Right: featured lots, beside the headline */}
          {auctions.length > 0 && (
            <div className="lg:pl-2">
              <div className="flex items-center justify-between mb-3">
                <span className="font-sans text-[11px] uppercase tracking-[0.18em] text-gold-dark">Featured lots</span>
                <Link href="/auctions" className="text-[11px] text-muted-foreground hover:text-primary flex items-center gap-1">
                  All auctions <ChevronRight className="h-3 w-3" />
                </Link>
              </div>
              <ul className="space-y-2.5">
                {auctions.slice(0, 4).map((a) => (
                  <li key={a.id}>
                    <Link
                      href={`/auctions/${a.id}`}
                      className="flex items-center gap-3 rounded-lg border border-border bg-card/90 backdrop-blur p-3 shadow-sm hover:border-accent transition-colors"
                    >
                      <div className="relative h-14 w-20 shrink-0 overflow-hidden rounded-sm bg-muted">
                        <Image src={a.image} alt={a.name} fill className="object-cover" />
                      </div>
                      <div className="min-w-0 flex-1">
                        <p className="truncate font-medium text-sm text-foreground">{a.name}</p>
                        <p className="text-[11px] text-muted-foreground">{a.endTime} · {a.bids} bids</p>
                      </div>
                      <div className="text-right shrink-0">
                        <p className="text-[9px] uppercase tracking-wider text-muted-foreground">Current</p>
                        <p className="font-mono font-semibold text-sm text-gold-dark tabular-nums">{formatCurrency(a.currentBid)}</p>
                      </div>
                    </Link>
                  </li>
                ))}
              </ul>
            </div>
          )}
        </div>
      </div>

      {/* Trust / credibility strip — navy band to split the page. Content unchanged. */}
      <div className="relative border-t border-primary/20 bg-primary text-primary-foreground">
        <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-4 flex flex-wrap items-center justify-center gap-x-10 gap-y-2.5 text-xs sm:text-[13px] text-primary-foreground/85">
          <span className="flex items-center gap-2.5">
            <BadgeCheck className="h-[18px] w-[18px] text-accent shrink-0" strokeWidth={1.75} /> Verified bidders
          </span>
          <span className="hidden sm:inline text-primary-foreground/25">·</span>
          <span className="flex items-center gap-2.5">
            <FileText className="h-[18px] w-[18px] text-accent shrink-0" strokeWidth={1.75} /> Professional extended pedigree on every lot
          </span>
          <span className="hidden sm:inline text-primary-foreground/25">·</span>
          <span className="flex items-center gap-2.5">
            <ShieldCheck className="h-[18px] w-[18px] text-accent shrink-0" strokeWidth={1.75} /> Secure ownership &amp; ID transfer
          </span>
        </div>
      </div>
    </section>
  )
}

/* ------------------------------------------------------------------------- */
export function FeaturedHorseAuctions({ auctions }: { auctions: HorseAuctionCard[] }) {
  return (
    <section className="py-12 lg:py-16 bg-background">
      <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div className="flex items-end justify-between mb-6 pb-4 border-b border-border">
          <div>
            <p className="font-sans text-xs uppercase tracking-[0.16em] text-accent mb-1">Horses</p>
            <h2 className="font-cinzel text-2xl sm:text-3xl font-semibold text-foreground">
              Featured horse auctions
            </h2>
          </div>
          <Link href="/auctions">
            <Button variant="ghost" className="hidden sm:flex text-primary hover:text-primary/80">
              View all <ChevronRight className="ml-1 h-4 w-4" />
            </Button>
          </Link>
        </div>

        <div className="grid gap-5 md:grid-cols-2 lg:grid-cols-3">
          {auctions.length === 0 && (
            <div className="col-span-full rounded-sm border border-border bg-card p-8 text-center">
              <h3 className="font-cinzel text-xl font-semibold text-foreground">No horse auctions are live yet</h3>
              <p className="mx-auto mt-2 max-w-xl text-sm leading-6 text-muted-foreground">
                Live standardbred auctions appear here as sellers publish catalogues.
              </p>
              <Link href="/auctions">
                <Button className="mt-5 bg-accent text-accent-foreground hover:bg-accent/90">Browse auctions</Button>
              </Link>
            </div>
          )}
          {auctions.map((auction) => (
            <Link key={auction.id} href={`/auctions/${auction.id}`}>
              <Card className="group overflow-hidden border-border bg-card card-hover cursor-pointer rounded-lg shadow-sm">
                <div className="relative aspect-[4/3] overflow-hidden">
                  <Image src={auction.image} alt={auction.name} fill className="object-cover" />
                  <Badge className="absolute top-3 left-3 bg-accent text-accent-foreground font-medium rounded-sm">
                    Live auction
                  </Badge>
                  <div className="absolute bottom-0 inset-x-0 bg-primary/90 px-3 py-1.5">
                    <span className="text-xs font-medium text-primary-foreground">{auction.endTime}</span>
                  </div>
                </div>
                <CardContent className="p-4">
                  <h3 className="font-cinzel text-lg font-semibold text-foreground">{auction.name}</h3>
                  <p className="text-sm text-muted-foreground line-clamp-1">{auction.description}</p>
                  <div className="mt-2 flex items-center gap-1.5 text-sm text-muted-foreground">
                    <MapPin className="h-3.5 w-3.5" />
                    {auction.location}
                  </div>
                  <div className="mt-3 flex items-center justify-between pt-3 border-t border-border">
                    <div>
                      <p className="text-[10px] text-muted-foreground uppercase tracking-wider">Current bid</p>
                      <p className="text-xl font-semibold text-foreground">{formatCurrency(auction.currentBid)}</p>
                    </div>
                    <div className="text-right text-sm text-muted-foreground">
                      <span className="flex items-center justify-end gap-1">
                        {auction.verified && <BadgeCheck className="h-4 w-4 text-accent" />}
                        {auction.seller}
                      </span>
                      <p className="text-xs">{auction.bids} bids</p>
                    </div>
                  </div>
                </CardContent>
              </Card>
            </Link>
          ))}
        </div>
      </div>
    </section>
  )
}

/* ------------------------------------------------------------------------- */
export function MarketplaceCategoryStrip({ categories }: { categories: MarketplaceCategory[] }) {
  return (
    <section className="py-10 bg-secondary border-y border-border">
      <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div className="mb-5">
          <p className="font-sans text-xs uppercase tracking-[0.16em] text-accent mb-1">Marketplace</p>
          <h2 className="font-cinzel text-xl sm:text-2xl font-semibold text-foreground">Browse by category</h2>
        </div>
        {categories.length === 0 ? (
          <p className="text-sm text-muted-foreground">Categories appear here once the marketplace is populated.</p>
        ) : (
          <div className="flex flex-wrap gap-2">
            {categories.map((category) => (
              <Link
                key={category.id}
                href={category.href}
                className="group inline-flex items-center gap-2 rounded-sm border border-border bg-card px-3 py-2 text-sm text-foreground hover:border-accent transition-colors"
              >
                <span className="font-medium">{category.name}</span>
                <span className="text-xs text-muted-foreground">{category.count}</span>
              </Link>
            ))}
          </div>
        )}
      </div>
    </section>
  )
}

/* ------------------------------------------------------------------------- */
export function LatestMarketplaceListings({
  listings,
  categories = [],
}: {
  listings: MarketplaceCard[]
  categories?: MarketplaceCategory[]
}) {
  return (
    <section className="py-12 lg:py-16 bg-card border-t border-border">
      <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div className="flex items-end justify-between mb-4 pb-4 border-b border-border">
          <div>
            <p className="font-sans text-xs uppercase tracking-[0.16em] text-accent mb-1">Marketplace</p>
            <h2 className="font-cinzel text-2xl sm:text-3xl font-semibold text-foreground">
              Equipment, vehicles &amp; services
            </h2>
          </div>
          <Link href="/marketplace">
            <Button variant="ghost" className="hidden sm:flex text-primary hover:text-primary/80">
              View all <ChevronRight className="ml-1 h-4 w-4" />
            </Button>
          </Link>
        </div>

        {/* Category chips merged in (was its own section) */}
        {categories.length > 0 && (
          <div className="flex flex-wrap gap-2 mb-6">
            {categories.slice(0, 10).map((category) => (
              <Link
                key={category.id}
                href={category.href}
                className="inline-flex items-center gap-1.5 rounded-sm border border-border bg-card px-2.5 py-1 text-xs text-foreground hover:border-accent transition-colors"
              >
                {category.name}
                <span className="text-muted-foreground">{category.count}</span>
              </Link>
            ))}
          </div>
        )}

        <div className="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
          {listings.length === 0 && (
            <div className="col-span-full rounded-sm border border-border bg-card p-8 text-center">
              <h3 className="font-cinzel text-xl font-semibold text-foreground">No marketplace listings yet</h3>
              <p className="mx-auto mt-2 max-w-xl text-sm leading-6 text-muted-foreground">
                Published equipment, vehicles, and services appear here.
              </p>
              <Link href="/marketplace">
                <Button className="mt-5 bg-accent text-accent-foreground hover:bg-accent/90">Browse marketplace</Button>
              </Link>
            </div>
          )}
          {listings.map((listing) => (
            <Link key={listing.id} href={`/marketplace/${listing.id}`}>
              <Card className="group overflow-hidden border-border bg-card card-hover cursor-pointer rounded-lg shadow-sm">
                <div className="relative aspect-[4/3] overflow-hidden">
                  <Image src={listing.image} alt={listing.title} fill className="object-cover" />
                  <Badge className="absolute top-3 left-3 bg-card text-foreground text-xs rounded-sm border border-border">
                    {listing.condition}
                  </Badge>
                </div>
                <CardContent className="p-4">
                  <p className="text-[10px] text-muted-foreground uppercase tracking-wider mb-1">{listing.category}</p>
                  <h3 className="font-medium text-foreground mb-2 line-clamp-1">{listing.title}</h3>
                  <div className="flex items-center justify-between">
                    <p className="text-lg font-semibold text-foreground">{formatCurrency(listing.price)}</p>
                    <span className="flex items-center gap-1 text-sm text-muted-foreground">
                      <MapPin className="h-3 w-3" />
                      {listing.location}
                    </span>
                  </div>
                </CardContent>
              </Card>
            </Link>
          ))}
        </div>
      </div>
    </section>
  )
}

/* ------------------------------------------------------------------------- */
export function EnterpriseSellers() {
  return (
    <section className="py-12 lg:py-16 bg-primary text-primary-foreground">
      <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div className="mb-8">
          <h2 className="font-cinzel text-2xl sm:text-3xl font-semibold">Trusted by industry leaders</h2>
          <p className="mt-2 text-primary-foreground/70">
            Professional sales companies and bloodstock agents worldwide.
          </p>
        </div>
        {/* Greyscale wordmark lockups — muted by default, full on hover.
            Swap each for a real <Image> logo when assets are supplied. */}
        <div className="grid grid-cols-2 md:grid-cols-4 border border-primary-foreground/15 rounded-sm overflow-hidden mb-8">
          {consignors.map((seller, i) => (
            <Link
              key={seller.name}
              href={seller.href}
              aria-label={seller.full}
              className={`group flex flex-col items-center justify-center gap-1 p-6 lg:p-10 opacity-60 hover:opacity-100 hover:bg-primary-foreground/5 transition-all ${
                i % 2 === 0 ? "border-r border-primary-foreground/15" : ""
              } ${i < 2 ? "border-b border-primary-foreground/15 md:border-b-0" : ""} ${
                i === 2 ? "md:border-r border-primary-foreground/15" : ""
              }`}
            >
              <span className="font-cinzel text-xl lg:text-2xl tracking-wide text-primary-foreground">
                {seller.name}
              </span>
              <span className="text-[10px] uppercase tracking-[0.16em] text-primary-foreground/55">
                {seller.kind}
              </span>
            </Link>
          ))}
        </div>
        <Link href="/enterprise">
          <Button variant="outline" className="border-primary-foreground/30 text-primary-foreground hover:bg-primary-foreground/10">
            Learn about enterprise accounts <ArrowRight className="ml-2 h-4 w-4" />
          </Button>
        </Link>
      </div>
    </section>
  )
}

/* ------------------------------------------------------------------------- */
export function TrustStrip() {
  const trustItems = [
    { icon: Shield, title: "Verified sellers", description: "Identity and history checked" },
    { icon: CreditCard, title: "Secure settlement", description: "Protected payment flow" },
    { icon: Truck, title: "Global logistics", description: "Worldwide transport support" },
    { icon: Headphones, title: "Expert support", description: "Bloodstock specialists" },
  ]

  return (
    <section className="py-10 bg-secondary border-y border-border">
      <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div className="grid grid-cols-2 lg:grid-cols-4 gap-6">
          {trustItems.map((item) => (
            <div key={item.title} className="flex items-center gap-3">
              <div className="w-10 h-10 rounded-sm bg-card border border-border flex items-center justify-center shrink-0">
                <item.icon className="h-5 w-5 text-accent" />
              </div>
              <div>
                <h3 className="font-medium text-foreground text-sm">{item.title}</h3>
                <p className="text-xs text-muted-foreground">{item.description}</p>
              </div>
            </div>
          ))}
        </div>
      </div>
    </section>
  )
}

/* ------------------------------------------------------------------------- */
export function SellerCTA() {
  return (
    <section className="py-14 lg:py-20 bg-stone border-t border-border">
      <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 grid lg:grid-cols-2 gap-10 items-center">
        <div>
          <p className="font-sans text-xs uppercase tracking-[0.2em] text-gold-dark mb-3">For sellers</p>
          <h2 className="font-cinzel text-3xl sm:text-4xl lg:text-5xl font-medium text-primary mb-4">
            Ready to sell?
          </h2>
          <p className="text-ink-soft/80 mb-7 leading-relaxed max-w-lg">
            List horses, equipment, or services to trainers, breeders, and bloodstock
            professionals worldwide. Register as a seller to get started.
          </p>
          <div className="flex flex-wrap gap-3">
            <Link href="/sell/horse">
              <Button size="lg" className="bg-primary text-primary-foreground hover:bg-primary/90 rounded-sm">
                <Gavel className="mr-2 h-4 w-4" />
                Sell a horse
              </Button>
            </Link>
            <Link href="/sell/equipment">
              <Button
                size="lg"
                variant="outline"
                className="border-primary/30 text-primary hover:bg-primary/5 rounded-sm"
              >
                <ShoppingBag className="mr-2 h-4 w-4" />
                List equipment
              </Button>
            </Link>
          </div>
        </div>

        <div className="grid grid-cols-2 gap-px bg-border rounded-sm overflow-hidden border border-border">
          {[
            ["Standardbred focus", "Pacers, trotters, yearlings, broodmares"],
            ["Live + timed auctions", "Proxy bidding and soft-close"],
            ["Enterprise consignors", "APG, Nutrien, Tattersalls and more"],
            ["Global reach", "Buyers across AU, NZ and worldwide"],
          ].map(([t, d]) => (
            <div key={t} className="bg-card p-5">
              <p className="font-medium text-foreground text-sm">{t}</p>
              <p className="text-xs text-muted-foreground mt-1">{d}</p>
            </div>
          ))}
        </div>
      </div>
    </section>
  )
}

// Legacy exports for backward compatibility
export { FeaturedHorseAuctions as FeaturedAuctions }
export { MarketplaceCategoryStrip as CategoryStrip }
export { LatestMarketplaceListings as LatestListings }
