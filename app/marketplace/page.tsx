import { Header } from "@/components/header"
import { Footer } from "@/components/footer"
import { MarketplaceCatalogue } from "@/components/marketplace/marketplace-catalogue"
import { EmptyStatePage } from "@/components/static-pages"
import { Search } from "lucide-react"
import { getMarketplaceCategories, searchMarketplace } from "@/lib/supabase/queries"

export const dynamic = "force-dynamic"

type SP = {
  q?: string
  category?: string
  condition?: string
  min?: string
  max?: string
  sort?: string
  page?: string
}

export default async function MarketplacePage({
  searchParams,
}: {
  searchParams?: Promise<SP>
}) {
  const sp = searchParams ? await searchParams : {}

  const [result, categories] = await Promise.all([
    searchMarketplace({
      q: sp.q,
      categorySlug: sp.category,
      condition: sp.condition,
      minPrice: sp.min ? Number(sp.min) : undefined,
      maxPrice: sp.max ? Number(sp.max) : undefined,
      sort: (sp.sort as "newest" | "price-low" | "price-high" | undefined) ?? "newest",
      page: sp.page ? Number(sp.page) : 1,
      perPage: 24,
    }),
    getMarketplaceCategories(),
  ])

  if (result.error || categories.error) {
    return (
      <EmptyStatePage
        icon={Search}
        eyebrow="Marketplace unavailable"
        title="Marketplace listings could not load"
        description="The marketplace shell is available, but live listing data did not load cleanly. Try again shortly or browse horse auctions while we reconnect."
        primary={{ label: "Back to homepage", href: "/" }}
        secondary={{ label: "Browse auctions", href: "/auctions", variant: "outline" }}
      />
    )
  }

  const categoryName = sp.category
    ? categories.data.find((c) => c.id === sp.category)?.name
    : undefined

  return (
    <div className="min-h-screen flex flex-col">
      <Header />
      <main className="flex-1">
        <MarketplaceCatalogue result={result.data} categories={categories.data} categoryName={categoryName} />
      </main>
      <Footer />
    </div>
  )
}
