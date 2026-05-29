import { redirect } from "next/navigation"
import { DashboardEmptyPage } from "@/components/static-pages"
import { Package } from "lucide-react"
import { getOwnedSellerAccount, getSessionUser } from "@/lib/supabase/auth-server"

// Seller tools depend on per-request auth; never statically prerender.
export const dynamic = "force-dynamic"

export default async function DashboardListingsPage() {
  const user = await getSessionUser()
  if (!user) redirect("/login?redirect=/dashboard/listings")

  // Prevent incomplete (non-onboarded) accounts from reaching seller tools.
  const seller = await getOwnedSellerAccount()
  if (!seller) redirect("/onboarding")

  return (
    <DashboardEmptyPage
      icon={Package}
      title="No seller listings yet"
      description="Your seller listing workspace is ready for active, pending, paused, sold, and archived HarnessBid listings."
    />
  )
}
