import { redirect } from "next/navigation"
import { Header } from "@/components/header"
import { Footer } from "@/components/footer"
import { HorseAuctionWizard } from "@/components/listings/horse-auction-wizard"
import {
  getOwnedSellerContext,
  getSessionUser,
  toSellerPreview,
} from "@/lib/supabase/auth-server"

export const dynamic = "force-dynamic"

export default async function HorseAuctionPage() {
  const user = await getSessionUser()
  if (!user) redirect("/login?redirect=/sell/horse-auction")
  const seller = await getOwnedSellerContext()
  if (!seller) redirect("/onboarding")

  return (
    <div className="min-h-screen flex flex-col">
      <Header />
      <main className="flex-1">
        <HorseAuctionWizard seller={toSellerPreview(seller)} />
      </main>
      <Footer />
    </div>
  )
}
