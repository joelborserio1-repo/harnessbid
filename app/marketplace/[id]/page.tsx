import { cache } from "react"
import type { Metadata } from "next"
import { Header } from "@/components/header"
import { Footer } from "@/components/footer"
import { MarketplaceBrowse } from "@/components/marketplace-browse"
import { EquipmentListingDetail } from "@/components/listing-detail"
import { EmptyStatePage, PageFrame, categoryPages } from "@/components/static-pages"
import { Search } from "lucide-react"
import {
  getMarketplaceListing,
  getMarketplaceListings,
  getMarketplaceCategories,
} from "@/lib/supabase/queries"
import { WatchButton } from "@/components/listings/watch-button"
import { EnquiryDialog } from "@/components/enquiries/enquiry-dialog"
import { isListingWatched } from "@/lib/listings/watchlist"
import { viewerOwnsListing } from "@/lib/enquiries/queries"
import { ReportListingButton } from "@/components/listings/report-listing-button"

const loadListing = cache(getMarketplaceListing)

export async function generateMetadata({
  params,
}: {
  params: Promise<{ id: string }>
}): Promise<Metadata> {
  const { id } = await params
  if (categoryPages[id]) return { title: "Marketplace | HarnessBid" }
  const result = await loadListing(id)
  const l = result.data
  if (!l) return { title: "Marketplace | HarnessBid" }
  return {
    title: `${l.title} | HarnessBid Marketplace`,
    description: l.description?.slice(0, 160),
    openGraph: {
      title: l.title,
      description: l.description?.slice(0, 160),
      type: "website",
      images: l.image && l.image !== "/placeholder.jpg" ? [l.image] : undefined,
    },
  }
}

export default async function MarketplaceDetailPage({
  params,
}: {
  params: Promise<{ id: string }>
}) {
  const { id } = await params
  const categoryPage = categoryPages[id]

  // `id` matches a known marketplace category slug -> show the real, filtered
  // listing grid for that category (falling back to the curated empty-state
  // copy only when the category genuinely has no published listings yet).
  if (categoryPage) {
    const [listings, categories] = await Promise.all([
      getMarketplaceListings(48, { categorySlug: id }),
      getMarketplaceCategories(),
    ])

    if (!listings.error && (listings.data?.length ?? 0) > 0) {
      return (
        <div className="min-h-screen flex flex-col">
          <Header />
          <main className="flex-1">
            <MarketplaceBrowse listings={listings.data} categories={categories.data} />
          </main>
          <Footer />
        </div>
      )
    }

    return <EmptyStatePage {...categoryPage} />
  }

  const listing = await loadListing(id)

  if (listing.error) {
    return (
      <EmptyStatePage
        icon={Search}
        eyebrow="Listing unavailable"
        title="Marketplace listing could not load"
        description="The listing page is available, but the live marketplace record did not load cleanly. Try returning to the marketplace or check again shortly."
        primary={{ label: "Browse marketplace", href: "/marketplace" }}
        secondary={{ label: "Back to homepage", href: "/", variant: "outline" }}
      />
    )
  }

  if (listing.data) {
    const [watched, isOwner] = await Promise.all([
      isListingWatched(listing.data.recordId, "marketplace"),
      viewerOwnsListing("marketplace", listing.data.recordId),
    ])
    return (
      <PageFrame>
        <EquipmentListingDetail
          listing={listing.data}
          actionBar={
            <>
              <WatchButton
                listingId={listing.data.recordId}
                kind="marketplace"
                initialWatched={watched}
                variant="full"
              />
              <EnquiryDialog
                targets={[
                  { id: listing.data.recordId, kind: "marketplace", label: listing.data.title },
                ]}
                isOwner={isOwner}
              />
              <ReportListingButton listingId={listing.data.recordId} kind="marketplace" />
            </>
          }
        />
      </PageFrame>
    )
  }

  return (
    <EmptyStatePage
      icon={Search}
      eyebrow="No results"
      title="No marketplace listings match this route"
      description="This category or listing is not published yet. You can clear the route by returning to the full marketplace."
      primary={{ label: "Browse marketplace", href: "/marketplace" }}
      secondary={{ label: "Sell an item", href: "/sell/equipment", variant: "outline" }}
    />
  )
}
