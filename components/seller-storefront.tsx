import Image from "next/image"
import Link from "next/link"
import { BadgeCheck, Building2, CalendarDays, ExternalLink, Gavel, MapPin, Package, Star } from "lucide-react"
import type { LucideIcon } from "lucide-react"
import { Header } from "@/components/header"
import { Footer } from "@/components/footer"
import { Badge } from "@/components/ui/badge"
import { Button } from "@/components/ui/button"
import { Card, CardContent } from "@/components/ui/card"
import type { SellerStorefront } from "@/lib/supabase/queries"
import { EnquiryDialog, type EnquiryTarget } from "@/components/enquiries/enquiry-dialog"

function formatCurrency(amount: number) {
  return new Intl.NumberFormat("en-US", {
    style: "currency",
    currency: "USD",
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(amount)
}

function formatDate(value: string | null) {
  if (!value) return "Dates on request"
  return new Intl.DateTimeFormat("en", {
    month: "short",
    day: "numeric",
    year: "numeric",
  }).format(new Date(value))
}

export function SellerStorefrontPage({
  storefront,
  isOwner = false,
}: {
  storefront: SellerStorefront
  isOwner?: boolean
}) {
  const { seller, enterprise, saleEvents, marketplaceListings, horseAuctions } = storefront
  const hasActivity = saleEvents.length > 0 || marketplaceListings.length > 0 || horseAuctions.length > 0

  const enquiryTargets: EnquiryTarget[] = [
    ...horseAuctions.map((a) => ({ id: a.recordId, kind: "horse" as const, label: a.name || a.title })),
    ...marketplaceListings.map((m) => ({ id: m.recordId, kind: "marketplace" as const, label: m.title })),
  ]

  return (
    <div className="min-h-screen bg-background">
      <Header />
      <main>
        <section className="bg-primary py-10 lg:py-14">
          <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div className="grid gap-8 lg:grid-cols-[1fr_320px] lg:items-end">
              <div className="flex flex-col gap-5 sm:flex-row sm:items-center">
                <div className="relative h-24 w-24 shrink-0 overflow-hidden rounded-lg border border-primary-foreground/15 bg-primary-foreground/10">
                  <Image src={seller.logo} alt={seller.name} fill className="object-cover" />
                </div>
                <div>
                  <div className="flex flex-wrap items-center gap-2">
                    <h1 className="font-sora text-3xl font-semibold tracking-wide text-primary-foreground lg:text-4xl">
                      {seller.name}
                    </h1>
                    {seller.verified && <BadgeCheck className="h-6 w-6 text-accent" />}
                  </div>
                  <div className="mt-3 flex flex-wrap gap-2">
                    <Badge className="bg-accent text-accent-foreground">
                      {enterprise ? "Enterprise Seller" : "Verified Seller"}
                    </Badge>
                    {enterprise && <Badge variant="secondary">{enterprise.tier.replace(/_/g, " ")} tier</Badge>}
                    {enterprise?.featured && <Badge variant="secondary">Featured partner</Badge>}
                  </div>
                  <p className="mt-4 max-w-3xl text-sm leading-6 text-primary-foreground/75 sm:text-base">
                    {seller.bio}
                  </p>
                </div>
              </div>

              <Card className="border-primary-foreground/15 bg-primary-foreground/10 text-primary-foreground">
                <CardContent className="grid grid-cols-2 gap-4 p-5 text-sm">
                  <div>
                    <p className="text-primary-foreground/60">Location</p>
                    <p className="mt-1 font-medium">{seller.location}</p>
                  </div>
                  <div>
                    <p className="text-primary-foreground/60">Member since</p>
                    <p className="mt-1 font-medium">{seller.memberSince}</p>
                  </div>
                  <div>
                    <p className="text-primary-foreground/60">Listings</p>
                    <p className="mt-1 font-medium">{seller.totalListings}</p>
                  </div>
                  <div>
                    <p className="text-primary-foreground/60">Response</p>
                    <p className="mt-1 font-medium">{seller.responseTime}</p>
                  </div>
                </CardContent>
              </Card>

              <div className="mt-4">
                <EnquiryDialog
                  targets={enquiryTargets}
                  isOwner={isOwner}
                  triggerLabel={
                    isOwner
                      ? "This is your storefront"
                      : enquiryTargets.length
                        ? "Contact seller"
                        : "No listings to enquire about"
                  }
                />
              </div>
            </div>
          </div>
        </section>

        <section className="border-b border-border bg-card">
          <div className="mx-auto grid max-w-7xl gap-4 px-4 py-5 sm:grid-cols-2 sm:px-6 lg:grid-cols-4 lg:px-8">
            <Metric icon={Package} label="Public listings" value={seller.totalListings.toLocaleString()} />
            <Metric icon={BadgeCheck} label="Completed sales" value={seller.totalSales.toLocaleString()} />
            <Metric icon={Star} label="Seller rating" value={`${seller.rating.toFixed(1)} (${seller.reviewCount})`} />
            <Metric icon={Building2} label="Account type" value={enterprise ? "Enterprise" : seller.accountType} />
          </div>
        </section>

        <section className="mx-auto max-w-7xl space-y-10 px-4 py-10 sm:px-6 lg:px-8">
          {!hasActivity && (
            <Card className="border-border/70">
              <CardContent className="p-8 text-center">
                <Building2 className="mx-auto mb-4 h-9 w-9 text-primary" />
                <h2 className="font-sora text-xl font-semibold text-foreground">No public seller activity yet</h2>
                <p className="mx-auto mt-2 max-w-2xl text-sm leading-6 text-muted-foreground">
                  Verified seller details are connected. Active listings and sale events will appear here when published through Supabase.
                </p>
                <div className="mt-5 flex flex-wrap justify-center gap-3">
                  <Link href="/marketplace">
                    <Button>Browse marketplace</Button>
                  </Link>
                  <Link href="/contact">
                    <Button variant="outline">Contact HarnessBid</Button>
                  </Link>
                </div>
              </CardContent>
            </Card>
          )}

          {saleEvents.length > 0 && (
            <section>
              <SectionTitle eyebrow="Sale events" title="Seller sale events" />
              <div className="mt-5 grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                {saleEvents.map((event) => (
                  <SaleEventCard key={event.id} event={event} />
                ))}
              </div>
            </section>
          )}

          {horseAuctions.length > 0 && (
            <section>
              <SectionTitle eyebrow="Horse auctions" title="Active horse auction listings" />
              <div className="mt-5 grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                {horseAuctions.map((auction) => (
                  <Link key={auction.id} href={`/auctions/${auction.id}`}>
                    <Card className="group h-full overflow-hidden border-border/70 card-hover">
                      <div className="relative aspect-[4/3] overflow-hidden">
                        <Image src={auction.image} alt={auction.title} fill className="object-cover transition-transform duration-500 group-hover:scale-105" />
                        <Badge className="absolute left-3 top-3 bg-accent text-accent-foreground">
                          <Gavel className="mr-1 h-3 w-3" />
                          Auction
                        </Badge>
                      </div>
                      <CardContent className="p-5">
                        <h3 className="font-sora text-lg font-semibold text-foreground">{auction.title}</h3>
                        <p className="mt-2 line-clamp-2 text-sm leading-6 text-muted-foreground">{auction.description}</p>
                        <div className="mt-4 flex items-end justify-between border-t border-border pt-4">
                          <div>
                            <p className="text-xs text-muted-foreground">Current bid</p>
                            <p className="text-lg font-semibold text-foreground">{formatCurrency(auction.currentBid)}</p>
                          </div>
                          <p className="text-sm text-muted-foreground">{auction.endTime}</p>
                        </div>
                      </CardContent>
                    </Card>
                  </Link>
                ))}
              </div>
            </section>
          )}

          {marketplaceListings.length > 0 && (
            <section>
              <SectionTitle eyebrow="Marketplace" title="Active marketplace listings" />
              <div className="mt-5 grid gap-5 md:grid-cols-2 xl:grid-cols-4">
                {marketplaceListings.map((listing) => (
                  <Link key={listing.id} href={`/marketplace/${listing.id}`}>
                    <Card className="group h-full overflow-hidden border-border/70 card-hover">
                      <div className="relative aspect-[4/3] overflow-hidden">
                        <Image src={listing.image} alt={listing.title} fill className="object-cover transition-transform duration-500 group-hover:scale-105" />
                      </div>
                      <CardContent className="p-4">
                        <Badge variant="secondary" className="mb-3">{listing.category}</Badge>
                        <h3 className="line-clamp-2 font-medium text-foreground">{listing.title}</h3>
                        <p className="mt-2 text-lg font-semibold text-foreground">{formatCurrency(listing.price)}</p>
                        <p className="mt-2 flex items-center gap-1 text-sm text-muted-foreground">
                          <MapPin className="h-4 w-4" />
                          {listing.location}
                        </p>
                      </CardContent>
                    </Card>
                  </Link>
                ))}
              </div>
            </section>
          )}
        </section>
      </main>
      <Footer />
    </div>
  )
}

function Metric({ icon: Icon, label, value }: { icon: LucideIcon; label: string; value: string }) {
  return (
    <div className="flex items-center gap-3">
      <div className="flex h-10 w-10 items-center justify-center rounded-md bg-accent/15">
        <Icon className="h-5 w-5 text-primary" />
      </div>
      <div>
        <p className="text-xs text-muted-foreground">{label}</p>
        <p className="font-semibold text-foreground">{value}</p>
      </div>
    </div>
  )
}

function SectionTitle({ eyebrow, title }: { eyebrow: string; title: string }) {
  return (
    <div>
      <p className="text-xs font-semibold uppercase tracking-wider text-primary">{eyebrow}</p>
      <h2 className="mt-1 font-sora text-2xl font-semibold text-foreground">{title}</h2>
    </div>
  )
}

function SaleEventCard({ event }: { event: SellerStorefront["saleEvents"][number] }) {
  return (
    <Card className="h-full overflow-hidden border-border/70">
      <div className="relative aspect-[16/9] overflow-hidden">
        <Image src={event.image} alt={event.name} fill className="object-cover" />
        <Badge className="absolute left-3 top-3 bg-primary text-primary-foreground">
          <CalendarDays className="mr-1 h-3 w-3" />
          {event.status}
        </Badge>
      </div>
      <CardContent className="p-5">
        <p className="text-xs font-semibold uppercase tracking-wider text-primary">{event.eventType}</p>
        <h3 className="mt-2 font-sora text-lg font-semibold text-foreground">{event.name}</h3>
        <p className="mt-2 line-clamp-2 text-sm leading-6 text-muted-foreground">{event.description}</p>
        <div className="mt-4 flex items-center justify-between border-t border-border pt-4 text-sm text-muted-foreground">
          <span>{formatDate(event.startsAt)}</span>
          <ExternalLink className="h-4 w-4" />
        </div>
      </CardContent>
    </Card>
  )
}
