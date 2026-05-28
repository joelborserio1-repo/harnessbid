import { Header } from "@/components/header"
import { Footer } from "@/components/footer"
import { MarketplaceBrowse } from "@/components/marketplace-browse"
import { EmptyStatePage } from "@/components/static-pages"
import { Search } from "lucide-react"
import { getMarketplaceCategories, getMarketplaceListings } from "@/lib/supabase/queries"

export default async function MarketplacePage({
  searchParams,
}: {
  searchParams?: Promise<{ q?: string; status?: string }>
}) {
  const params = searchParams ? await searchParams : {}
  const [listings, categories] = await Promise.all([
    getMarketplaceListings(24),
    getMarketplaceCategories(),
  ])

  if (listings.error || categories.error) {
    return (
      <EmptyStatePage
        icon={Search}
        eyebrow="Marketplace unavailable"
        title="Marketplace listings could not load"
        description="The marketplace shell is available, but live listing data did not load cleanly. Try again shortly or browse horse auctions while we reconnect."
        primary={{ label: "Back to homepage", href: "/" }}
        secondary={{ label: "Browse auctions", href: "/auctions", variant: "outline" }}
      />
    )
  }

  if (params.q || params.status === "empty") {
    return (
      <EmptyStatePage
        icon={Search}
        eyebrow="No marketplace results"
        title="No marketplace listings match this search"
        description="Try clearing filters, browsing all marketplace listings, or starting a seller listing if you have an item to add."
        primary={{ label: "Browse marketplace", href: "/marketplace" }}
        secondary={{ label: "Sell an item", href: "/sell/equipment", variant: "outline" }}
      />
    )
  }

  return (
    <div className="min-h-screen flex flex-col">
      <Header />
      <main className="flex-1">
        <MarketplaceBrowse listings={listings.data} categories={categories.data} />
      </main>
      <Footer />
    </div>
  )
}
