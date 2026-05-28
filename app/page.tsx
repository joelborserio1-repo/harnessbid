import { Header } from "@/components/header"
import { Footer } from "@/components/footer"
import {
  HeroSection,
  FeaturedHorseAuctions,
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

export const dynamic = "force-dynamic"

export default async function HomePage() {
  const [horseAuctions, marketplaceCategories, marketplaceListings] = await Promise.all([
    getHorseAuctions(3),
    getMarketplaceCategories(),
    getMarketplaceListings(4),
  ])

  return (
    <div className="min-h-screen flex flex-col">
      <Header />
      <main className="flex-1">
        <HeroSection />
        <FeaturedHorseAuctions auctions={horseAuctions.data ?? []} />
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
