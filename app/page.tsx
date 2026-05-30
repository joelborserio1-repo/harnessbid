import { Header } from "@/components/header"
import { Footer } from "@/components/footer"
import {
  HeroSection,
  LatestMarketplaceListings,
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
        <LatestMarketplaceListings
          listings={marketplaceListings.data ?? []}
          categories={marketplaceCategories.data ?? []}
        />
        <SellerCTA />
      </main>
      <Footer />
    </div>
  )
}
