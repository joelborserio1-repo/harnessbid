import { EmptyStatePage } from "@/components/static-pages"
import { Search } from "lucide-react"

export default function SearchPage() {
  return (
    <EmptyStatePage
      icon={Search}
      eyebrow="Search"
      title="No search results yet"
      description="Search is ready for auctions, horses, equipment, sellers, and sale events. Try browsing the main marketplace while indexing is connected."
      primary={{ label: "Browse marketplace", href: "/marketplace" }}
      secondary={{ label: "Browse auctions", href: "/auctions", variant: "outline" }}
      points={["Horse auctions", "Marketplace listings", "Enterprise sellers"]}
    />
  )
}
