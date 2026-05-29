import { redirect } from "next/navigation"
import { Header } from "@/components/header"
import { Footer } from "@/components/footer"
import { OnboardingWizard } from "@/components/onboarding/onboarding-wizard"
import {
  getCurrentProfile,
  getOwnedSellerAccount,
  getSessionUser,
} from "@/lib/supabase/auth-server"

// Onboarding depends on per-request auth state; never statically prerender.
export const dynamic = "force-dynamic"

export default async function OnboardingPage() {
  const user = await getSessionUser()
  if (!user) redirect("/login?redirect=/onboarding")

  // If a seller account already exists, onboarding is complete.
  const sellerAccount = await getOwnedSellerAccount()
  if (sellerAccount) redirect("/dashboard")

  const profile = await getCurrentProfile()

  return (
    <div className="min-h-screen flex flex-col">
      <Header />
      <main className="flex-1">
        <OnboardingWizard
          defaultName={profile?.displayName ?? profile?.fullName ?? undefined}
        />
      </main>
      <Footer />
    </div>
  )
}
