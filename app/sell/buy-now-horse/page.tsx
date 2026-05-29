import { redirect } from "next/navigation"
import { Header } from "@/components/header"
import { Footer } from "@/components/footer"
import { BuyNowHorseWizard } from "@/components/listings/buy-now-horse-wizard"
import {
  getOwnedSellerContext,
  getSessionUser,
  toSellerPreview,
} from "@/lib/supabase/auth-server"

export const dynamic = "force-dynamic"

export default async function BuyNowHorsePage() {
  const user = await getSessionUser()
  if (!user) redirect("/login?redirect=/sell/buy-now-horse")
  const seller = await getOwnedSellerContext()
  if (!seller) redirect("/onboarding")

  return (
    <div className="min-h-screen flex flex-col">
      <Header />
      <main className="flex-1">
        <BuyNowHorseWizard seller={toSellerPreview(seller)} />
      </main>
      <Footer />
    </div>
  )
}
