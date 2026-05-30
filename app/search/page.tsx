import { Header } from "@/components/header"
import { Footer } from "@/components/footer"
import { HorseCardGrid } from "@/components/horse-card-grid"
import { EmptyStatePage } from "@/components/static-pages"
import { Input } from "@/components/ui/input"
import { Search } from "lucide-react"
import { searchListings } from "@/lib/supabase/queries"

export const metadata = { title: "Search | HarnessBid" }

export default async function SearchPage({
  searchParams,
}: {
  searchParams?: Promise<{ q?: string }>
}) {
  const params = searchParams ? await searchParams : {}
  const q = (params.q ?? "").trim()

  if (!q) {
    return (
      <EmptyStatePage
        icon={Search}
        eyebrow="Search"
        title="Search HarnessBid"
        description="Search across horse auctions, buy-now horses, and marketplace equipment by name or keyword. Use the search box in the header to begin."
        primary={{ label: "Browse marketplace", href: "/marketplace" }}
        secondary={{ label: "Browse auctions", href: "/auctions", variant: "outline" }}
        points={["Horse auctions", "Marketplace listings", "Enterprise sellers"]}
      />
    )
  }

  const result = await searchListings(q)
  const horses = result.data?.horses ?? []
  const marketplace = result.data?.marketplace ?? []
  const total = horses.length + marketplace.length

  return (
    <div className="min-h-screen flex flex-col">
      <Header />
      <main className="flex-1">
        <section className="bg-primary text-primary-foreground py-10">
          <div className="container mx-auto px-4">
            <p className="text-sm uppercase tracking-wider text-primary-foreground/70 mb-2">Search</p>
            <h1 className="text-3xl font-serif">
              {total} result{total === 1 ? "" : "s"} for “{q}”
            </h1>
            <form action="/search" className="mt-4 max-w-xl relative">
              <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
              <Input
                name="q"
                defaultValue={q}
                className="bg-card text-foreground pl-9"
                placeholder="Search horses, equipment, sellers..."
              />
            </form>
          </div>
        </section>

        <div className="container mx-auto px-4 py-10 space-y-12">
          {total === 0 && (
            <p className="text-muted-foreground">
              No published listings matched “{q}”. Try a different term, or browse the{" "}
              <a className="text-primary underline" href="/marketplace">marketplace</a> and{" "}
              <a className="text-primary underline" href="/auctions">auctions</a>.
            </p>
          )}

          {horses.length > 0 && (
            <section>
              <h2 className="text-xl font-semibold mb-4">Horses ({horses.length})</h2>
              <HorseCardGrid listings={horses} showWatch={false} />
            </section>
          )}

          {marketplace.length > 0 && (
            <section>
              <h2 className="text-xl font-semibold mb-4">Marketplace ({marketplace.length})</h2>
              <HorseCardGrid listings={marketplace} hrefBase="/marketplace" showWatch={false} />
            </section>
          )}
        </div>
      </main>
      <Footer />
    </div>
  )
}
