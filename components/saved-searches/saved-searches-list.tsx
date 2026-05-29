"use client"

import Link from "next/link"
import { Bell, BellOff, Search, Trash2 } from "lucide-react"
import { Badge } from "@/components/ui/badge"
import { Button } from "@/components/ui/button"
import { Card, CardContent } from "@/components/ui/card"
import {
  deleteSavedSearchAction,
  toggleSavedSearchAlerts,
} from "@/lib/saved-searches/actions"
import type { SavedSearchView } from "@/lib/saved-searches/queries"

export function SavedSearchesList({ items }: { items: SavedSearchView[] }) {
  if (items.length === 0) {
    return (
      <Card className="border-border/70">
        <CardContent className="flex flex-col items-center gap-4 p-10 text-center">
          <div className="flex h-14 w-14 items-center justify-center rounded-lg border border-accent/40 bg-accent/10">
            <Search className="h-7 w-7 text-primary" />
          </div>
          <h2 className="font-sora text-xl font-semibold text-foreground">No saved searches yet</h2>
          <p className="max-w-md text-sm leading-6 text-muted-foreground">
            Save a filtered view from the marketplace or auctions to quickly return to it.
          </p>
          <div className="flex flex-col gap-3 sm:flex-row">
            <Button asChild className="bg-accent text-accent-foreground hover:bg-accent/90">
              <Link href="/auctions">Browse auctions</Link>
            </Button>
            <Button asChild variant="outline">
              <Link href="/marketplace">Browse marketplace</Link>
            </Button>
          </div>
        </CardContent>
      </Card>
    )
  }

  return (
    <div className="space-y-3">
      {items.map((s) => (
        <Card key={s.id} className="border-border/70">
          <CardContent className="flex flex-col gap-3 p-4 sm:flex-row sm:items-center">
            <div className="min-w-0 flex-1">
              <div className="flex flex-wrap items-center gap-2">
                <Link href={s.href} className="font-sora font-semibold text-foreground hover:text-primary">
                  {s.name}
                </Link>
                <Badge variant="secondary">{s.searchTypeLabel}</Badge>
                {s.alertEnabled && <Badge className="bg-accent text-accent-foreground">Alerts on</Badge>}
              </div>
              {Object.keys(s.filters).length > 0 && (
                <p className="mt-1 text-xs text-muted-foreground">
                  {Object.entries(s.filters)
                    .map(([k, v]) => `${k}: ${String(v)}`)
                    .join(" · ")}
                </p>
              )}
            </div>
            <div className="flex gap-2">
              <Button asChild variant="outline" size="sm">
                <Link href={s.href}>Open</Link>
              </Button>
              <form action={toggleSavedSearchAlerts}>
                <input type="hidden" name="id" value={s.id} />
                <input type="hidden" name="enabled" value={(!s.alertEnabled).toString()} />
                <Button type="submit" variant="ghost" size="sm" title="Toggle alerts (structure only)">
                  {s.alertEnabled ? <BellOff className="h-4 w-4" /> : <Bell className="h-4 w-4" />}
                </Button>
              </form>
              <form action={deleteSavedSearchAction}>
                <input type="hidden" name="id" value={s.id} />
                <Button type="submit" variant="ghost" size="sm" className="text-destructive">
                  <Trash2 className="h-4 w-4" />
                </Button>
              </form>
            </div>
          </CardContent>
        </Card>
      ))}
    </div>
  )
}
