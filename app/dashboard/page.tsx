import { redirect } from "next/navigation"
import { Header } from "@/components/header"
import { Footer } from "@/components/footer"
import { SellerDashboard } from "@/components/seller-dashboard"
import { SellerOnboarding } from "@/components/seller-onboarding"
import {
  getCurrentProfile,
  getOwnedSellerAccount,
  getSessionUser,
} from "@/lib/supabase/auth-server"

// Auth depends on per-request cookies; never statically prerender.
export const dynamic = "force-dynamic"

const ACCOUNT_TYPE_LABELS: Record<string, string> = {
  individual: "Individual seller",
  business: "Business seller",
  enterprise: "Enterprise seller",
}

export default async function DashboardPage() {
  // Defense-in-depth: middleware already guards /dashboard, but re-check here.
  const user = await getSessionUser()
  if (!user) redirect("/login?redirect=/dashboard")

  const [profile, sellerAccount] = await Promise.all([
    getCurrentProfile(),
    getOwnedSellerAccount(),
  ])

  return (
    <div className="min-h-screen flex flex-col">
      <Header />
      <main className="flex-1">
        {sellerAccount ? (
          <SellerDashboard
            seller={{
              name: sellerAccount.displayName,
              accountTypeLabel:
                ACCOUNT_TYPE_LABELS[sellerAccount.accountType] ?? "Seller",
              verified: sellerAccount.verificationStatus === "verified",
              isEnterprise: sellerAccount.accountType === "enterprise",
              enterpriseRequested: sellerAccount.enterpriseRequested,
            }}
          />
        ) : (
          <SellerOnboarding
            defaultName={profile?.displayName ?? profile?.fullName ?? undefined}
          />
        )}
      </main>
      <Footer />
    </div>
  )
}
