import { redirect } from "next/navigation"
import { Header } from "@/components/header"
import { Footer } from "@/components/footer"
import { EmptyStatePage } from "@/components/static-pages"
import { Card, CardContent } from "@/components/ui/card"
import { Package } from "lucide-react"
import { ListingEditForm } from "@/components/listings/listing-edit-form"
import { getOwnedSellerAccount, getSessionUser } from "@/lib/supabase/auth-server"
import { getOwnedListing } from "@/lib/listings/queries"

export const dynamic = "force-dynamic"

export default async function EditListingPage({
  params,
}: {
  params: Promise<{ id: string }>
}) {
  const { id } = await params

  const user = await getSessionUser()
  if (!user) redirect(`/login?redirect=/dashboard/listings/${id}/edit`)
  const seller = await getOwnedSellerAccount()
  if (!seller) redirect("/onboarding")

  const listing = await getOwnedListing(id)
  if (!listing) {
    return (
      <EmptyStatePage
        icon={Package}
        eyebrow="Listing not found"
        title="This listing is not available"
        description="It may have been deleted, or it does not belong to your seller account."
        primary={{ label: "Back to my listings", href: "/dashboard/listings" }}
        secondary={{ label: "Create listing", href: "/sell", variant: "outline" }}
      />
    )
  }

  return (
    <div className="min-h-screen flex flex-col">
      <Header />
      <main className="flex-1">
        <section className="bg-primary py-8">
          <div className="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <p className="text-sm font-semibold uppercase tracking-wider text-accent">
              Edit {listing.kind === "horse" ? "horse listing" : "marketplace listing"}
            </p>
            <h1 className="mt-2 font-sora text-2xl font-semibold text-primary-foreground sm:text-3xl">
              {listing.title}
            </h1>
          </div>
        </section>

        <section className="mx-auto max-w-3xl px-4 py-10 sm:px-6 lg:px-8 lg:py-14">
          <Card className="border-border/70 bg-card shadow-sm">
            <CardContent className="p-6 sm:p-8">
              <ListingEditForm listing={listing} />
            </CardContent>
          </Card>
        </section>
      </main>
      <Footer />
    </div>
  )
}
