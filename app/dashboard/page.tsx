import { redirect } from "next/navigation"
import { Header } from "@/components/header"
import { Footer } from "@/components/footer"
import { SellerDashboard } from "@/components/seller-dashboard"
import { getOwnedSellerContext, getSessionUser } from "@/lib/supabase/auth-server"

// Auth depends on per-request cookies; never statically prerender.
export const dynamic = "force-dynamic"

const ACCOUNT_TYPE_LABELS: Record<string, string> = {
  individual: "Individual seller",
  business: "Business seller",
  enterprise: "Enterprise seller",
}

export default async function DashboardPage() {
  // Defense-in-depth: proxy already guards /dashboard, but re-check here.
  const user = await getSessionUser()
  if (!user) redirect("/login?redirect=/dashboard")

  // Sellers must complete onboarding before reaching dashboard tools.
  const seller = await getOwnedSellerContext()
  if (!seller) redirect("/onboarding")

  return (
    <div className="min-h-screen flex flex-col">
      <Header />
      <main className="flex-1">
        <SellerDashboard
          seller={{
            name: seller.displayName,
            slug: seller.slug,
            accountTypeLabel: ACCOUNT_TYPE_LABELS[seller.accountType] ?? "Seller",
            isEnterprise: seller.isEnterprise,
            verified: seller.verificationStatus === "verified",
            verificationStatus: seller.verificationStatus,
            location: seller.location,
            bio: seller.bio,
            website: seller.website,
            memberSince: seller.createdAt
              ? new Date(seller.createdAt).getFullYear().toString()
              : null,
            enterpriseRequested: seller.enterpriseRequested,
            enterpriseStatus: seller.enterprise
              ? seller.enterprise.onboardingStatus.replace(/_/g, " ")
              : null,
          }}
        />
      </main>
      <Footer />
    </div>
  )
}
