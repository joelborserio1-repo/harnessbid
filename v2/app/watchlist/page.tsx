import { EmptyStatePage } from "@/components/static-pages"
import { Heart } from "lucide-react"

export default function WatchlistPage() {
  return (
    <EmptyStatePage
      icon={Heart}
      eyebrow="Watchlist"
      title="Your watchlist is empty"
      description="Save auctions, horses, marketplace listings, and sale events so you can compare them before contacting sellers."
      primary={{ label: "Browse auctions", href: "/auctions" }}
      secondary={{ label: "Browse marketplace", href: "/marketplace", variant: "outline" }}
      points={["Saved listings", "Auction reminders", "Seller comparison"]}
    />
  )
}
