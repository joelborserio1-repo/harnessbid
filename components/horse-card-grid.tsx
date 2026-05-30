import Image from "next/image"
import Link from "next/link"
import { MapPin, BadgeCheck } from "lucide-react"
import { Badge } from "@/components/ui/badge"
import { Card, CardContent } from "@/components/ui/card"
import { WatchButton } from "@/components/listings/watch-button"
import type { MarketplaceCard } from "@/lib/supabase/queries"

function formatCurrency(value: number) {
  if (!value) return "POA"
  return new Intl.NumberFormat("en-AU", { style: "currency", currency: "USD", maximumFractionDigits: 0 }).format(value)
}

/**
 * Simple responsive grid of horse listing cards. Used by the buy-now and sold
 * horse results pages. `hrefBase` controls the detail link (e.g.
 * "/horses/buy-now"); `watchKind` toggles the watchlist button target.
 */
export function HorseCardGrid({
  listings,
  hrefBase,
  showWatch = true,
}: {
  listings: MarketplaceCard[]
  /** Detail link base, e.g. "/horses/buy-now". Omit to render non-clickable cards. */
  hrefBase?: string
  showWatch?: boolean
}) {
  return (
    <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
      {listings.map((listing) => {
        const card = (
          <Card className="group overflow-hidden border-border/50 bg-card card-hover h-full">
            <div className="relative aspect-[4/3] overflow-hidden">
              <Image
                src={listing.image}
                alt={listing.title}
                fill
                className="object-cover transition-transform duration-500 group-hover:scale-105"
              />
              {listing.featured && (
                <Badge className="absolute top-3 left-3 bg-accent text-accent-foreground font-semibold">
                  Featured
                </Badge>
              )}
              <Badge className="absolute top-3 right-3 bg-card/90 text-foreground text-xs">
                {listing.condition}
              </Badge>
              {showWatch && (
                <div className="absolute bottom-3 right-3 opacity-0 transition-opacity group-hover:opacity-100">
                  <WatchButton listingId={listing.recordId} kind="horse" variant="icon" />
                </div>
              )}
            </div>
            <CardContent className="p-4">
              <p className="text-xs text-muted-foreground uppercase tracking-wider mb-1">
                {listing.category}
              </p>
              <h3 className="font-medium text-foreground mb-2 line-clamp-2">{listing.title}</h3>
              <div className="flex items-center gap-1 text-sm text-muted-foreground mb-3">
                <MapPin className="h-3 w-3" />
                {listing.location}
              </div>
              <div className="flex items-center justify-between pt-3 border-t border-border">
                <p className="text-lg font-semibold text-foreground">{formatCurrency(listing.price)}</p>
                {listing.verified && <BadgeCheck className="h-4 w-4 text-accent" />}
              </div>
            </CardContent>
          </Card>
        )

        return hrefBase ? (
          <Link key={listing.id} href={`${hrefBase}/${listing.id}`} className="block">
            {card}
          </Link>
        ) : (
          <div key={listing.id}>{card}</div>
        )
      })}
    </div>
  )
}
