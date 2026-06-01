import Link from "next/link"
import { ChevronDown } from "lucide-react"
import { ListingCard } from "@/components/marketplace-browse"
import {
  MarketplaceFilters,
  MarketplaceSort,
  MarketplaceActiveFilters,
  MarketplacePagination,
} from "@/components/marketplace/marketplace-controls"
import { SaveSearchButton } from "@/components/saved-searches/save-search-button"
import type { MarketplaceCard, MarketplaceCategory, MarketplaceSearchResult } from "@/lib/supabase/queries"

/**
 * Server-rendered marketplace catalogue. All filtering/sort/pagination state
 * lives in the URL (driven by the client controls) so it persists on refresh
 * and is shareable; the data is queried server-side via searchMarketplace.
 */
export function MarketplaceCatalogue({
  result,
  categories,
  categoryName,
}: {
  result: MarketplaceSearchResult
  categories: MarketplaceCategory[]
  categoryName?: string
}) {
  const { items, total, page, totalPages } = result

  return (
    <div className="min-h-screen bg-background">
      {/* Page header */}
      <div className="bg-primary py-8 lg:py-12">
        <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
          <nav className="flex items-center gap-2 text-sm text-primary-foreground/60 mb-4">
            <Link href="/" className="hover:text-primary-foreground">Home</Link>
            <ChevronDown className="h-4 w-4 rotate-[-90deg]" />
            <span className="text-primary-foreground">Marketplace</span>
          </nav>
          <h1 className="font-cinzel text-3xl lg:text-4xl font-medium text-primary-foreground">Marketplace</h1>
          <p className="mt-2 text-primary-foreground/70">
            {total.toLocaleString()} listing{total === 1 ? "" : "s"} available
          </p>
        </div>
      </div>

      <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8">
        <div className="flex flex-col lg:flex-row gap-8">
          {/* Sidebar */}
          <aside className="hidden lg:block w-64 flex-shrink-0">
            <div className="sticky top-20">
              <MarketplaceFilters categories={categories} />
            </div>
          </aside>

          <div className="flex-1">
            {/* Controls */}
            <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 pb-6 border-b border-border">
              <div className="flex items-center gap-4">
                <MarketplaceSort />
                <SaveSearchButton searchType="marketplace" />
              </div>
            </div>

            <MarketplaceActiveFilters categoryName={categoryName} />

            {items.length === 0 ? (
              <div className="rounded-lg border border-border bg-card p-10 text-center">
                <h2 className="font-cinzel text-xl font-medium text-foreground">No listings match your filters</h2>
                <p className="mx-auto mt-2 max-w-md text-sm leading-6 text-muted-foreground">
                  Try widening your price range, clearing a filter, or browsing all categories.
                </p>
              </div>
            ) : (
              <div className="grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
                {items.map((listing: MarketplaceCard) => (
                  <ListingCard key={listing.id} listing={listing} view="grid" />
                ))}
              </div>
            )}

            <MarketplacePagination page={page} totalPages={totalPages} />
          </div>
        </div>
      </div>
    </div>
  )
}
