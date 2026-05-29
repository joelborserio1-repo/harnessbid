"use client"

import Link from "next/link"
import { Gavel, Package, Pencil, Plus, Trash2 } from "lucide-react"
import { Button } from "@/components/ui/button"
import { Badge } from "@/components/ui/badge"
import { Card, CardContent } from "@/components/ui/card"
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs"
import { deleteListingAction } from "@/lib/listings/actions"
import type { OwnedListing, OwnedListingBuckets } from "@/lib/listings/queries"

function formatCurrency(amount: number | null) {
  if (amount == null) return "—"
  return new Intl.NumberFormat("en-US", {
    style: "currency",
    currency: "USD",
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(amount)
}

const STATUS_LABELS: Record<string, string> = {
  draft: "Draft",
  pending_review: "Pending review",
  published: "Published",
  under_offer: "Under offer",
  sold: "Sold",
  expired: "Expired",
  paused: "Paused",
  archived: "Archived",
  rejected: "Rejected",
}

function EmptyState({ label }: { label: string }) {
  return (
    <div className="rounded-lg border border-border bg-secondary/50 p-6 text-center">
      <div className="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-lg border border-accent/40 bg-accent/10">
        <Package className="h-6 w-6 text-primary" />
      </div>
      <h3 className="font-sora text-lg font-semibold text-foreground">{label}</h3>
      <p className="mx-auto mt-2 max-w-sm text-sm leading-6 text-muted-foreground">
        Create a listing to start building your seller storefront.
      </p>
      <Link href="/sell">
        <Button className="mt-5 bg-accent text-accent-foreground hover:bg-accent/90">
          <Plus className="mr-2 h-4 w-4" />
          Create listing
        </Button>
      </Link>
    </div>
  )
}

function ListingRow({ listing }: { listing: OwnedListing }) {
  const Icon = listing.kind === "horse" ? Gavel : Package
  return (
    <div className="flex flex-col gap-4 rounded-lg bg-secondary p-3 sm:flex-row sm:items-center">
      <div className="relative h-20 w-full overflow-hidden rounded-md bg-background sm:h-16 sm:w-20">
        {listing.image ? (
          // eslint-disable-next-line @next/next/no-img-element
          <img src={listing.image} alt="" className="h-full w-full object-cover" />
        ) : (
          <div className="flex h-full w-full items-center justify-center">
            <Icon className="h-6 w-6 text-muted-foreground" />
          </div>
        )}
      </div>
      <div className="min-w-0 flex-1">
        <div className="flex flex-wrap items-center gap-2">
          <h3 className="truncate font-medium text-foreground">{listing.title}</h3>
          <Badge variant="secondary">{STATUS_LABELS[listing.status] ?? listing.status}</Badge>
        </div>
        <p className="text-sm font-semibold text-foreground">{formatCurrency(listing.price)}</p>
        <p className="text-xs text-muted-foreground">
          {listing.kind === "horse" ? "Horse" : "Marketplace"} · {listing.views} views · {listing.watchers} watching
        </p>
      </div>
      <div className="flex gap-2">
        <Button asChild variant="outline" size="sm">
          <Link href={`/dashboard/listings/${listing.id}/edit`}>
            <Pencil className="mr-1 h-3.5 w-3.5" />
            Edit
          </Link>
        </Button>
        <form action={deleteListingAction}>
          <input type="hidden" name="id" value={listing.id} />
          <input type="hidden" name="kind" value={listing.kind} />
          <Button type="submit" variant="ghost" size="sm" className="text-destructive">
            <Trash2 className="mr-1 h-3.5 w-3.5" />
            Delete
          </Button>
        </form>
      </div>
    </div>
  )
}

function ListingList({ items, emptyLabel }: { items: OwnedListing[]; emptyLabel: string }) {
  if (items.length === 0) return <EmptyState label={emptyLabel} />
  return (
    <div className="space-y-3">
      {items.map((l) => (
        <ListingRow key={`${l.kind}-${l.id}`} listing={l} />
      ))}
    </div>
  )
}

export function SellerListingsManager({ buckets }: { buckets: OwnedListingBuckets }) {
  return (
    <Card>
      <CardContent className="p-4 sm:p-6">
        <Tabs defaultValue="active">
          <TabsList className="mb-4 flex-wrap">
            <TabsTrigger value="active">Active ({buckets.active.length})</TabsTrigger>
            <TabsTrigger value="drafts">Drafts ({buckets.drafts.length})</TabsTrigger>
            <TabsTrigger value="pending">Pending ({buckets.pending.length})</TabsTrigger>
            <TabsTrigger value="sold">Sold / Closed ({buckets.sold.length})</TabsTrigger>
          </TabsList>
          <TabsContent value="active">
            <ListingList items={buckets.active} emptyLabel="No active listings yet" />
          </TabsContent>
          <TabsContent value="drafts">
            <ListingList items={buckets.drafts} emptyLabel="No draft listings" />
          </TabsContent>
          <TabsContent value="pending">
            <ListingList items={buckets.pending} emptyLabel="Nothing pending review" />
          </TabsContent>
          <TabsContent value="sold">
            <ListingList items={buckets.sold} emptyLabel="No sold or closed listings" />
          </TabsContent>
        </Tabs>
      </CardContent>
    </Card>
  )
}
