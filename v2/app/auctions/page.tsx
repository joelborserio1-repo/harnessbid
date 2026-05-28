import { Header } from "@/components/header"
import { Footer } from "@/components/footer"
import { AuctionBrowse } from "@/components/auction-browse"
import { EmptyStatePage } from "@/components/static-pages"
import { Search } from "lucide-react"
import { getHorseAuctions } from "@/lib/supabase/queries"

export default async function AuctionsPage({
  searchParams,
}: {
  searchParams?: Promise<{ q?: string; status?: string }>
}) {
  const params = searchParams ? await searchParams : {}
  const auctions = await getHorseAuctions(24)

  if (auctions.error) {
    return (
      <EmptyStatePage
        icon={Search}
        eyebrow="Auctions unavailable"
        title="Horse auctions could not load"
        description="The horse auction page is available, but live auction data did not load cleanly. Try again shortly or browse the marketplace."
        primary={{ label: "Back to homepage", href: "/" }}
        secondary={{ label: "Browse marketplace", href: "/marketplace", variant: "outline" }}
      />
    )
  }

  if (params.q || params.status === "empty") {
    return (
      <EmptyStatePage
        icon={Search}
        eyebrow="No auction results"
        title="No horse auctions match this search"
        description="Try clearing filters, browsing all live auctions, or returning to the marketplace while new sale events are being prepared."
        primary={{ label: "Browse all auctions", href: "/auctions" }}
        secondary={{ label: "Browse marketplace", href: "/marketplace", variant: "outline" }}
      />
    )
  }

  return (
    <div className="min-h-screen flex flex-col">
      <Header />
      <main className="flex-1">
        <AuctionBrowse auctions={auctions.data} />
      </main>
      <Footer />
    </div>
  )
}
