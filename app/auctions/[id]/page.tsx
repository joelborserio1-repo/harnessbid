import { Header } from "@/components/header"
import { Footer } from "@/components/footer"
import { AuctionListingDetail } from "@/components/listing-detail"
import { EmptyStatePage } from "@/components/static-pages"
import { Gavel } from "lucide-react"
import { getHorseAuction } from "@/lib/supabase/queries"

export default async function AuctionDetailPage({
  params,
}: {
  params: Promise<{ id: string }>
}) {
  const { id } = await params
  const auction = await getHorseAuction(id)

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

  return (
    <div className="min-h-screen flex flex-col">
      <Header />
      <main className="flex-1 pb-20 lg:pb-0">
        <AuctionListingDetail listing={auction.data} />
      </main>
      <Footer />
    </div>
  )
}
