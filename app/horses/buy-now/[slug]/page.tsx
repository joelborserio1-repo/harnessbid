import { EquipmentListingDetail } from "@/components/listing-detail"
import { EmptyStatePage, PageFrame } from "@/components/static-pages"
import { BadgeCheck } from "lucide-react"
import { getBuyNowHorse } from "@/lib/supabase/queries"
import { WatchButton } from "@/components/listings/watch-button"
import { EnquiryDialog } from "@/components/enquiries/enquiry-dialog"
import { isListingWatched } from "@/lib/listings/watchlist"
import { viewerOwnsListing } from "@/lib/enquiries/queries"
import { ReportListingButton } from "@/components/listings/report-listing-button"

export default async function BuyNowHorseDetailPage({
  params,
}: {
  params: Promise<{ slug: string }>
}) {
  const { slug } = await params
  const listing = await getBuyNowHorse(slug)

  if (listing.error) {
    return (
      <EmptyStatePage
        icon={BadgeCheck}
        eyebrow="Listing unavailable"
        title="Buy now horse could not load"
        description="The listing page is available, but the live record did not load cleanly. Try the buy now horses list or check again shortly."
        primary={{ label: "Buy now horses", href: "/horses/buy-now" }}
        secondary={{ label: "Browse auctions", href: "/auctions", variant: "outline" }}
      />
    )
  }

  if (!listing.data) {
    return (
      <EmptyStatePage
        icon={BadgeCheck}
        eyebrow="Horse not available"
        title="This buy now horse is not published"
        description="The listing may be a draft, sold, or not yet published. Browse current buy now horses and live auctions instead."
        primary={{ label: "Buy now horses", href: "/horses/buy-now" }}
        secondary={{ label: "Browse auctions", href: "/auctions", variant: "outline" }}
      />
    )
  }

  const [watched, isOwner] = await Promise.all([
    isListingWatched(listing.data.recordId, "horse"),
    viewerOwnsListing("horse", listing.data.recordId),
  ])

  return (
    <PageFrame>
      <EquipmentListingDetail
        listing={listing.data}
        actionBar={
          <>
            <WatchButton
              listingId={listing.data.recordId}
              kind="horse"
              initialWatched={watched}
              variant="full"
            />
            <EnquiryDialog
              targets={[{ id: listing.data.recordId, kind: "horse", label: listing.data.title }]}
              isOwner={isOwner}
            />
            <ReportListingButton listingId={listing.data.recordId} kind="horse" />
          </>
        }
      />
    </PageFrame>
  )
}
