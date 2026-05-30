"use client"

import Link from "next/link"
import Image from "next/image"
import {
  Search,
  MapPin,
  ChevronRight,
  ArrowRight,
  Shield,
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
export function HeroSection({ auctions = [] }: { auctions?: HorseAuctionCard[] }) {
  return (
    <section className="bg-background">
      <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-12 lg:py-16">
        <div className="grid lg:grid-cols-2 gap-10 lg:gap-12 items-center">
          {/* Left: positioning + conversions */}
          <div>
            <p className="font-sans text-xs uppercase tracking-[0.18em] text-accent mb-4">
              The global harness racing marketplace
            </p>
            <h1 className="font-cinzel text-4xl sm:text-5xl lg:text-6xl font-semibold tracking-tight text-balance text-foreground">
              Where champions <span className="text-accent">change hands</span>
            </h1>
            <p className="mt-5 text-base sm:text-lg text-muted-foreground max-w-xl leading-relaxed">
              Live auctions for standardbred bloodstock, plus a trusted marketplace for
              racing equipment, vehicles, and services — for trainers, breeders, and
              bloodstock professionals worldwide.
            </p>

            <div className="mt-7 flex flex-wrap gap-3">
              <Link href="/register">
                <Button size="lg" className="bg-accent text-accent-foreground hover:bg-accent/90">
                  <Gavel className="mr-2 h-4 w-4" />
                  Register to bid
                </Button>
              </Link>
              <Link href="/sell/horse">
                <Button
                  size="lg"
                  variant="outline"
                  className="border-border text-foreground hover:bg-secondary"
                >
                  Sell a horse
                </Button>
              </Link>
            </div>

            {/* Search */}
            <form action="/search" className="mt-6 max-w-md relative">
              <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
              <Input
                name="q"
                type="search"
                placeholder="Search horses, equipment, services..."
                className="bg-card text-foreground placeholder:text-muted-foreground pl-9 rounded-sm border-border"
              />
            </form>
          </div>

          {/* Right: live auctions strip (real lots, above the fold) */}
          <div className="lg:pl-6 lg:border-l lg:border-border">
            <div className="flex items-center justify-between mb-3">
              <span className="font-sans text-xs uppercase tracking-[0.16em] text-accent">
                Live now
              </span>
              <Link
                href="/auctions"
                className="text-xs text-muted-foreground hover:text-accent flex items-center gap-1"
              >
                All auctions <ChevronRight className="h-3 w-3" />
              </Link>
            </div>

            {auctions.length === 0 ? (
              <div className="rounded-sm border border-border bg-card p-6 text-sm text-muted-foreground">
                No auctions are live right now. Browse upcoming sale events and catalogues.
              </div>
            ) : (
              <ul className="space-y-2">
                {auctions.slice(0, 3).map((a) => (
                  <li key={a.id}>
                    <Link
                      href={`/auctions/${a.id}`}
                      className="flex items-center gap-3 rounded-sm border border-border bg-card p-3 shadow-sm hover:border-accent transition-colors"
                    >
                      <div className="relative h-14 w-20 shrink-0 overflow-hidden rounded-sm bg-muted">
                        <Image src={a.image} alt={a.name} fill className="object-cover" />
                      </div>
                      <div className="min-w-0 flex-1">
                        <p className="truncate font-medium text-sm text-foreground">{a.name}</p>
                        <p className="text-xs text-muted-foreground">{a.endTime} · {a.bids} bids</p>
                      </div>
                      <div className="text-right shrink-0">
                        <p className="text-[10px] uppercase tracking-wider text-muted-foreground">Current</p>
                        <p className="font-mono font-semibold text-sm text-foreground tabular-nums">{formatCurrency(a.currentBid)}</p>
                      </div>
                    </Link>
                  </li>
                ))}
              </ul>
            )}
          </div>
        </div>
      </div>

      {/* Consignor / trust bar — credibility above the fold, hairline divider */}
      <div className="border-t border-border bg-secondary">
        <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-3 flex flex-wrap items-center gap-x-6 gap-y-2 justify-center lg:justify-between">
          <span className="text-xs uppercase tracking-[0.14em] text-muted-foreground">
            Trusted consignors
          </span>
          <div className="flex flex-wrap items-center gap-x-6 gap-y-1">
            {consignors.map((c) => (
              <Link
                key={c.name}
                href={c.href}
                className="text-sm font-medium text-foreground/75 hover:text-accent transition-colors"
              >
                {c.name}
              </Link>
            ))}
          </div>
          <span className="text-xs text-muted-foreground flex items-center gap-2">
            <BadgeCheck className="h-3.5 w-3.5 text-accent" /> Verified sellers · Secure payments · HarnessLink network
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
              <Card className="group overflow-hidden border-border bg-card card-hover cursor-pointer rounded-sm shadow-sm">
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
              <Card className="group overflow-hidden border-border bg-card card-hover cursor-pointer rounded-sm shadow-sm">
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
    <section className="py-14 lg:py-20 bg-primary text-primary-foreground border-t border-border">
      <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 grid lg:grid-cols-2 gap-10 items-center">
        <div>
          <p className="font-sans text-xs uppercase tracking-[0.18em] text-accent mb-3">For sellers</p>
          <h2 className="font-cinzel text-3xl sm:text-4xl lg:text-5xl font-semibold mb-4">
            Ready to sell?
          </h2>
          <p className="text-primary-foreground/80 mb-7 leading-relaxed max-w-lg">
            List horses, equipment, or services to trainers, breeders, and bloodstock
            professionals worldwide. Register as a seller to get started.
          </p>
          <div className="flex flex-wrap gap-3">
            <Link href="/sell/horse">
              <Button size="lg" className="bg-accent text-accent-foreground hover:bg-accent/90">
                <Gavel className="mr-2 h-4 w-4" />
                Sell a horse
              </Button>
            </Link>
            <Link href="/sell/equipment">
              <Button
                size="lg"
                variant="outline"
                className="border-primary-foreground/30 text-primary-foreground hover:bg-primary-foreground/10"
              >
                <ShoppingBag className="mr-2 h-4 w-4" />
                List equipment
              </Button>
            </Link>
          </div>
        </div>

        <div className="grid grid-cols-2 gap-px bg-primary-foreground/15 rounded-sm overflow-hidden border border-primary-foreground/15">
          {[
            ["Standardbred focus", "Pacers, trotters, yearlings, broodmares"],
            ["Live + timed auctions", "Proxy bidding and soft-close"],
            ["Enterprise consignors", "APG, Nutrien, Tattersalls and more"],
            ["Global reach", "Buyers across AU, NZ and worldwide"],
          ].map(([t, d]) => (
            <div key={t} className="bg-primary p-5">
              <p className="font-medium text-primary-foreground text-sm">{t}</p>
              <p className="text-xs text-primary-foreground/65 mt-1">{d}</p>
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
