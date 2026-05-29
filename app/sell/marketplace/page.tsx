import { redirect } from "next/navigation"
import { Header } from "@/components/header"
import { Footer } from "@/components/footer"
import { MarketplaceWizard } from "@/components/listings/marketplace-wizard"
import {
  getOwnedSellerContext,
  getSessionUser,
  toSellerPreview,
} from "@/lib/supabase/auth-server"
import { getMarketplaceCategoryOptions } from "@/lib/supabase/queries"

export const dynamic = "force-dynamic"

export default async function MarketplaceListingPage() {
  const user = await getSessionUser()
  if (!user) redirect("/login?redirect=/sell/marketplace")
  const seller = await getOwnedSellerContext()
  if (!seller) redirect("/onboarding")

  const categories = await getMarketplaceCategoryOptions()

  return (
    <div className="min-h-screen flex flex-col">
      <Header />
      <main className="flex-1">
        <MarketplaceWizard seller={toSellerPreview(seller)} categories={categories.data} />
      </main>
      <Footer />
    </div>
  )
}
