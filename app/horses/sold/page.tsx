import { Header } from "@/components/header"
import { Footer } from "@/components/footer"
import { HorseCardGrid } from "@/components/horse-card-grid"
import { EmptyStatePage } from "@/components/static-pages"
import { Gavel } from "lucide-react"
import { getSoldHorses } from "@/lib/supabase/queries"

export const metadata = { title: "Recently Sold Horses | HarnessBid" }
export const dynamic = "force-dynamic"

export default async function SoldHorsesPage() {
  const result = await getSoldHorses(48)

  if (result.error || (result.data?.length ?? 0) === 0) {
    return (
      <EmptyStatePage
        icon={Gavel}
        eyebrow="Recently sold"
        title="No completed sales to show yet"
        description="Completed sale records will give buyers and sellers confidence through transparent auction results and premium listing history."
        primary={{ label: "Browse auctions", href: "/auctions" }}
        secondary={{ label: "Browse marketplace", href: "/marketplace", variant: "outline" }}
        points={["Auction outcomes", "Sale history", "Market confidence"]}
      />
    )
  }

  return (
    <div className="min-h-screen flex flex-col">
      <Header />
      <main className="flex-1">
        <section className="bg-primary text-primary-foreground py-12">
          <div className="container mx-auto px-4">
            <p className="text-sm uppercase tracking-wider text-primary-foreground/70 mb-2">
              Recently sold
            </p>
            <h1 className="text-3xl lg:text-4xl font-serif">Recent sale results</h1>
            <p className="text-primary-foreground/80 mt-2 max-w-2xl">
              Transparent outcomes from completed HarnessBid auctions and fixed-price sales.
            </p>
          </div>
        </section>
        <div className="container mx-auto px-4 py-10">
          <HorseCardGrid listings={result.data} showWatch={false} />
        </div>
      </main>
      <Footer />
    </div>
  )
}
