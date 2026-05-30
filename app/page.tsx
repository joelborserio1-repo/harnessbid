import { Header } from "@/components/header"
import { Footer } from "@/components/footer"
import {
  HeroSection,
  MarketplaceCategoryStrip,
  LatestMarketplaceListings,
  EnterpriseSellers,
  TrustStrip,
  SellerCTA,
} from "@/components/homepage-sections"
import {
  getHorseAuctions,
  getMarketplaceCategories,
  getMarketplaceListings,
} from "@/lib/supabase/queries"
import { FeaturedSales } from "@/components/events/featured-sales"
import { getFeaturedSaleEvents } from "@/lib/events/queries"
import { LotBoard } from "@/components/home/lot-board"

export const dynamic = "force-dynamic"

export default async function HomePage() {
  const [horseAuctions, marketplaceCategories, marketplaceListings, featuredSales] = await Promise.all([
    getHorseAuctions(12),
    getMarketplaceCategories(),
    getMarketplaceListings(4),
    getFeaturedSaleEvents(3),
  ])

  const auctions = horseAuctions.data ?? []

  return (
    <div className="min-h-screen flex flex-col">
      <Header />
      <main className="flex-1">
        <HeroSection auctions={auctions.slice(0, 3)} />
        <LotBoard lots={auctions} />
        <FeaturedSales events={featuredSales} />
        <MarketplaceCategoryStrip categories={marketplaceCategories.data ?? []} />
        <LatestMarketplaceListings listings={marketplaceListings.data ?? []} />
        <EnterpriseSellers />
        <TrustStrip />
        <SellerCTA />
      </main>
      <Footer />
    </div>
  )
}
