import { Header } from "@/components/header"
import { Footer } from "@/components/footer"
import { HorseCardGrid } from "@/components/horse-card-grid"
import { EmptyStatePage } from "@/components/static-pages"
import { BadgeCheck } from "lucide-react"
import { getBuyNowHorses } from "@/lib/supabase/queries"

export const metadata = { title: "Buy Now Horses | HarnessBid" }
export const dynamic = "force-dynamic"

export default async function BuyNowHorsesPage() {
  const result = await getBuyNowHorses(48)

  if (result.error || (result.data?.length ?? 0) === 0) {
    return (
      <EmptyStatePage
        icon={BadgeCheck}
        eyebrow="Buy now horses"
        title="No fixed-price horses are listed right now"
        description="HarnessBid supports fixed-price horse listings with pedigree details, seller verification, and enquiry-first purchasing. Check back soon or browse the live auctions."
        primary={{ label: "Browse live auctions", href: "/auctions" }}
        secondary={{ label: "Sell your horse", href: "/sell/horse", variant: "outline" }}
        points={["Pedigree-first listings", "Verified seller profiles", "Premium horse imagery"]}
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
              Buy now horses
            </p>
            <h1 className="text-3xl lg:text-4xl font-serif">Fixed-price standardbreds</h1>
            <p className="text-primary-foreground/80 mt-2 max-w-2xl">
              Pedigree-first listings from verified sellers — enquire directly and buy without waiting for an auction.
            </p>
          </div>
        </section>
        <div className="container mx-auto px-4 py-10">
          <HorseCardGrid listings={result.data} hrefBase="/horses/buy-now" />
        </div>
      </main>
      <Footer />
    </div>
  )
}
