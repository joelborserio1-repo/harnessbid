import { cache } from "react"
import type { Metadata } from "next"
import { Header } from "@/components/header"
import { Footer } from "@/components/footer"
import { AuctionListingDetail } from "@/components/listing-detail"
import { EmptyStatePage } from "@/components/static-pages"
import { Gavel } from "lucide-react"
import { getHorseAuction } from "@/lib/supabase/queries"
import { WatchButton } from "@/components/listings/watch-button"
import { EnquiryDialog } from "@/components/enquiries/enquiry-dialog"
import { BidPanel } from "@/components/auctions/bid-panel"
import { ReportListingButton } from "@/components/listings/report-listing-button"
import { isListingWatched } from "@/lib/listings/watchlist"
import { viewerOwnsListing } from "@/lib/enquiries/queries"
import {
  closeAuctionIfEnded,
  getAuctionBidHistory,
  getViewerAuctionState,
} from "@/lib/auctions/queries"

const loadAuction = cache(getHorseAuction)

export async function generateMetadata({
  params,
}: {
  params: Promise<{ id: string }>
}): Promise<Metadata> {
  const { id } = await params
  const result = await loadAuction(id)
  const a = result.data
  if (!a) return { title: "Horse auction | HarnessBid" }
  return {
    title: `${a.name} | HarnessBid Auctions`,
    description: a.description?.slice(0, 160),
    openGraph: {
      title: a.name,
      description: a.description?.slice(0, 160),
      type: "website",
      images: a.image && a.image !== "/placeholder.jpg" ? [a.image] : undefined,
    },
  }
}

export default async function AuctionDetailPage({
  params,
}: {
  params: Promise<{ id: string }>
}) {
  const { id } = await params
  const auction = await loadAuction(id)

  if (auction.error) {
    return (
      <EmptyStatePage
        icon={Gavel}
        eyebrow="Auction unavailable"
        title="Horse auction could not load"
        description="The auction page is available, but the live horse auction record did not load cleanly. Try returning to the auction list or check again shortly."
        primary={{ label: "Browse auctions", href: "/auctions" }}
        secondary={{ label: "Back to homepage", href: "/", variant: "outline" }}
      />
    )
  }

  if (!auction.data) {
    return (
      <EmptyStatePage
        icon={Gavel}
        eyebrow="Auction not available"
        title="This horse auction is not live"
        description="The auction may be scheduled, closed, or not yet published. Browse the live auction marketplace for current opportunities."
        primary={{ label: "Browse auctions", href: "/auctions" }}
        secondary={{ label: "Back to homepage", href: "/", variant: "outline" }}
      />
    )
  }

  // Lazily settle the auction if its end time has passed (no-op otherwise),
  // then load the post-close state.
  await closeAuctionIfEnded(auction.data.id)

  const [watched, isOwner, bidHistory, viewerState] = await Promise.all([
    isListingWatched(auction.data.recordId, "horse"),
    viewerOwnsListing("horse", auction.data.recordId),
    getAuctionBidHistory(auction.data.id),
    getViewerAuctionState(auction.data.id),
  ])

  const a = auction.data

  return (
    <div className="min-h-screen flex flex-col">
      <Header />
      <main className="flex-1 pb-20 lg:pb-0">
        <AuctionListingDetail
          listing={a}
          bidHistory={bidHistory}
          actionBar={
            <>
              <WatchButton
                listingId={a.recordId}
                kind="horse"
                initialWatched={watched}
                variant="full"
              />
              <EnquiryDialog
                targets={[{ id: a.recordId, kind: "horse", label: a.title }]}
                isOwner={isOwner}
              />
              <ReportListingButton listingId={a.recordId} kind="horse" />
            </>
          }
          bidPanel={
            isOwner ? undefined : (
              <BidPanel
                auctionId={a.id}
                listingSlug={a.listingSlug}
                authenticated={viewerState.authenticated}
                initialLeading={viewerState.leading}
                currentBid={a.currentBid}
                bidCount={a.bids}
                bidIncrement={a.bidIncrement}
                reservePrice={a.reservePrice}
                reserveMet={a.reserveMet}
                startingBid={a.startingBid}
              />
            )
          }
        />
      </main>
      <Footer />
    </div>
  )
}
