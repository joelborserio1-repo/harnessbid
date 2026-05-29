import Link from "next/link"
import { Building2, Store } from "lucide-react"
import { Badge } from "@/components/ui/badge"
import { Card, CardContent } from "@/components/ui/card"
import type { ConsignorSummary } from "@/lib/events/queries"

/** Consignor grouping for an event; enterprise consignors are featured first. */
export function ConsignorSection({
  slug,
  consignors,
}: {
  slug: string
  consignors: ConsignorSummary[]
}) {
  if (consignors.length === 0) return null
  const sorted = [...consignors].sort((a, b) => Number(b.enterprise) - Number(a.enterprise) || b.lotCount - a.lotCount)

  return (
    <section>
      <h2 className="font-sora text-xl font-semibold text-foreground">Consignors</h2>
      <p className="mt-1 text-sm text-muted-foreground">Filter the catalogue by consignor.</p>
      <div className="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
        {sorted.map((c) => (
          <Card key={c.id} className="border-border/70">
            <CardContent className="flex items-center gap-3 p-4">
              <div className="flex h-10 w-10 items-center justify-center rounded-full bg-accent/10">
                {c.enterprise ? (
                  <Building2 className="h-5 w-5 text-primary" />
                ) : (
                  <Store className="h-5 w-5 text-primary" />
                )}
              </div>
              <div className="min-w-0 flex-1">
                <Link
                  href={`/sales/${slug}?consignor=${c.id}`}
                  className="block truncate font-medium text-foreground hover:text-primary"
                >
                  {c.name}
                </Link>
                <p className="text-xs text-muted-foreground">{c.lotCount} lot(s)</p>
              </div>
              {c.enterprise && <Badge className="bg-accent text-accent-foreground">Enterprise</Badge>}
            </CardContent>
          </Card>
        ))}
      </div>
    </section>
  )
}
