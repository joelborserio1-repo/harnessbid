import Link from "next/link"
import { redirect } from "next/navigation"
import { Plus } from "lucide-react"
import { Header } from "@/components/header"
import { Footer } from "@/components/footer"
import { Button } from "@/components/ui/button"
import { SellerListingsManager } from "@/components/listings/seller-listings-manager"
import { getOwnedSellerAccount, getSessionUser } from "@/lib/supabase/auth-server"
import { getOwnedListings } from "@/lib/listings/queries"

// Seller tools depend on per-request auth; never statically prerender.
export const dynamic = "force-dynamic"

export default async function DashboardListingsPage() {
  const user = await getSessionUser()
  if (!user) redirect("/login?redirect=/dashboard/listings")

  // Prevent incomplete (non-onboarded) accounts from reaching seller tools.
  const seller = await getOwnedSellerAccount()
  if (!seller) redirect("/onboarding")

  const buckets = await getOwnedListings()

  return (
    <div className="min-h-screen flex flex-col">
      <Header />
      <main className="flex-1">
        <section className="bg-primary py-8">
          <div className="mx-auto flex max-w-7xl flex-col justify-between gap-4 px-4 sm:flex-row sm:items-center sm:px-6 lg:px-8">
            <div>
              <p className="text-sm font-semibold uppercase tracking-wider text-accent">Seller workspace</p>
              <h1 className="mt-2 font-sora text-2xl font-semibold text-primary-foreground sm:text-3xl">
                My Listings
              </h1>
            </div>
            <Link href="/sell">
              <Button className="bg-accent text-accent-foreground hover:bg-accent/90">
                <Plus className="mr-2 h-4 w-4" />
                New Listing
              </Button>
            </Link>
          </div>
        </section>

        <section className="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
          <SellerListingsManager buckets={buckets} />
        </section>
      </main>
      <Footer />
    </div>
  )
}
