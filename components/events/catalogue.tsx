import Link from "next/link"
import { LayoutGrid, List } from "lucide-react"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { LotCard } from "@/components/events/lot-card"
import type { CatalogueLot, ConsignorSummary, LotFilters } from "@/lib/events/queries"

const GAITS = ["pacer", "trotter", "dual_gaited"]
const SEXES = ["colt", "filly", "gelding", "mare", "stallion", "ridgling"]
const STATUSES = ["scheduled", "live", "extended", "closed"]

function selectClass() {
  return "flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
}

export function Catalogue({
  slug,
  lots,
  consignors,
  filters,
  view,
}: {
  slug: string
  lots: CatalogueLot[]
  consignors: ConsignorSummary[]
  filters: LotFilters
  view: "grid" | "list"
}) {
  const action = `/sales/${slug}`
  const numbered = lots.filter((l) => l.lotNumber)

  return (
    <div className="space-y-6">
      {/* Quick lot navigation */}
      {numbered.length > 0 && (
        <div className="flex gap-2 overflow-x-auto pb-2">
          {numbered.map((l) => (
            <a
              key={l.recordId}
              href={`#lot-${l.lotNumber}`}
              className="shrink-0 rounded-md border border-border bg-card px-3 py-1.5 text-xs font-medium text-foreground hover:border-accent"
            >
              Lot {l.lotNumber}
            </a>
          ))}
        </div>
      )}

      {/* Filter bar (GET form preserves filters server-side) */}
      <form action={action} method="get" className="grid grid-cols-2 gap-2 rounded-lg border border-border bg-card p-3 sm:grid-cols-3 lg:grid-cols-6">
        <Input name="search" defaultValue={filters.search ?? ""} placeholder="Search lots" className="col-span-2 sm:col-span-1" />
        <select name="consignor" defaultValue={filters.consignor ?? ""} className={selectClass()} aria-label="Consignor">
          <option value="">All consignors</option>
          {consignors.map((c) => (
            <option key={c.id} value={c.id}>
              {c.name}
            </option>
          ))}
        </select>
        <select name="gait" defaultValue={filters.gait ?? ""} className={selectClass()} aria-label="Gait">
          <option value="">Any gait</option>
          {GAITS.map((g) => (
            <option key={g} value={g}>
              {g.replace(/_/g, " ")}
            </option>
          ))}
        </select>
        <select name="sex" defaultValue={filters.sex ?? ""} className={selectClass()} aria-label="Sex">
          <option value="">Any sex</option>
          {SEXES.map((s) => (
            <option key={s} value={s}>
              {s}
            </option>
          ))}
        </select>
        <select name="status" defaultValue={filters.status ?? ""} className={selectClass()} aria-label="Auction status">
          <option value="">Any status</option>
          {STATUSES.map((s) => (
            <option key={s} value={s}>
              {s}
            </option>
          ))}
        </select>
        <select name="view" defaultValue={view} className={selectClass()} aria-label="View">
          <option value="grid">Grid view</option>
          <option value="list">List view</option>
        </select>
        <div className="col-span-2 flex gap-2 sm:col-span-3 lg:col-span-6">
          <Button type="submit" className="bg-accent text-accent-foreground hover:bg-accent/90">
            Apply filters
          </Button>
          <Button asChild variant="outline">
            <Link href={action}>Clear</Link>
          </Button>
          <span className="ml-auto flex items-center gap-1 text-sm text-muted-foreground">
            {view === "grid" ? <LayoutGrid className="h-4 w-4" /> : <List className="h-4 w-4" />}
            {lots.length} lots
          </span>
        </div>
      </form>

      {lots.length === 0 ? (
        <div className="rounded-lg border border-border bg-secondary/50 p-10 text-center">
          <p className="font-sora text-lg font-semibold text-foreground">No lots match these filters</p>
          <p className="mt-2 text-sm text-muted-foreground">Try clearing filters to see the full catalogue.</p>
        </div>
      ) : view === "list" ? (
        <div className="space-y-3">
          {lots.map((lot) => (
            <LotCard key={`${lot.kind}-${lot.recordId}`} lot={lot} view="list" />
          ))}
        </div>
      ) : (
        <div className="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
          {lots.map((lot) => (
            <LotCard key={`${lot.kind}-${lot.recordId}`} lot={lot} view="grid" />
          ))}
        </div>
      )}
    </div>
  )
}
