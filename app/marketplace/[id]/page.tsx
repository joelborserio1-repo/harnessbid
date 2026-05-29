import { EquipmentListingDetail } from "@/components/listing-detail"
import { EmptyStatePage, PageFrame, categoryPages } from "@/components/static-pages"
import { Search } from "lucide-react"
import { getMarketplaceListing } from "@/lib/supabase/queries"
import { WatchButton } from "@/components/listings/watch-button"
import { EnquiryDialog } from "@/components/enquiries/enquiry-dialog"
import { isListingWatched } from "@/lib/listings/watchlist"
import { viewerOwnsListing } from "@/lib/enquiries/queries"

export default async function MarketplaceDetailPage({
  params,
}: {
  params: Promise<{ id: string }>
}) {
  const { id } = await params
  const categoryPage = categoryPages[id]

  if (categoryPage) {
    return <EmptyStatePage {...categoryPage} />
  }

  const listing = await getMarketplaceListing(id)

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
