import Link from "next/link"
import { Clock, ShieldCheck } from "lucide-react"
import { Badge } from "@/components/ui/badge"
import { Card, CardContent } from "@/components/ui/card"
import type { CatalogueLot } from "@/lib/events/queries"

function currency(value: number) {
  return new Intl.NumberFormat("en-US", { style: "currency", currency: "USD", maximumFractionDigits: 0 }).format(value)
}

function timeLeft(iso: string | null) {
  if (!iso) return null
  const ms = new Date(iso).getTime() - Date.now()
  if (ms <= 0) return "Closed"
  const d = Math.floor(ms / 86_400_000)
  const h = Math.floor((ms % 86_400_000) / 3_600_000)
  return d > 0 ? `${d}d ${h}h` : `${Math.max(1, h)}h`
}

/** Premium catalogue lot card (grid). Image-first, pedigree-forward. */
export function LotCard({ lot, view = "grid" }: { lot: CatalogueLot; view?: "grid" | "list" }) {
  const countdown = timeLeft(lot.endsAt)
  const chips = [lot.sex, lot.gait, lot.ageYears ? `${lot.ageYears}yo` : null].filter(Boolean) as string[]

  if (view === "list") {
    return (
      <Card id={lot.lotNumber ? `lot-${lot.lotNumber}` : undefined} className="overflow-hidden border-border/70">
        <CardContent className="flex gap-4 p-3">
          <div className="relative h-24 w-32 shrink-0 overflow-hidden rounded-md bg-secondary">
            {/* eslint-disable-next-line @next/next/no-img-element */}
            <img src={lot.image} alt="" className="h-full w-full object-cover" />
            {lot.lotNumber && (
              <span className="absolute left-1 top-1 rounded bg-primary/90 px-1.5 py-0.5 text-[10px] font-semibold text-primary-foreground">
                Lot {lot.lotNumber}
              </span>
            )}
          </div>
          <div className="min-w-0 flex-1">
            <Link href={lot.href} className="font-sora font-semibold text-foreground hover:text-primary">
              {lot.title}
            </Link>
            <p className="mt-0.5 truncate text-xs text-muted-foreground">{lot.sellerName}</p>
            {(lot.sire || lot.dam) && (
              <p className="mt-1 truncate text-xs text-muted-foreground">
                {lot.sire ?? "—"} × {lot.dam ?? "—"}
              </p>
            )}
            <div className="mt-2 flex items-center justify-between">
              <p className="text-sm font-semibold text-foreground">
                {lot.priceLabel}: {currency(lot.price)}
              </p>
              {countdown && (
                <span className="flex items-center gap-1 text-xs text-foreground">
                  <Clock className="h-3 w-3 text-accent" />
                  {countdown}
                </span>
              )}
            </div>
          </div>
        </CardContent>
      </Card>
    )
  }

  return (
    <Card
      id={lot.lotNumber ? `lot-${lot.lotNumber}` : undefined}
      className="group overflow-hidden border-border/70"
    >
      <Link href={lot.href} className="block">
        <div className="relative aspect-[4/3] overflow-hidden bg-secondary">
          {/* eslint-disable-next-line @next/next/no-img-element */}
          <img
            src={lot.image}
            alt={lot.title}
            className="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
          />
          {lot.lotNumber && (
            <span className="absolute left-2 top-2 rounded bg-primary/90 px-2 py-1 text-xs font-semibold text-primary-foreground">
              Lot {lot.lotNumber}
            </span>
          )}
          {lot.isAuction && lot.reserveMet && (
            <Badge className="absolute right-2 top-2 bg-accent text-accent-foreground">
              <ShieldCheck className="mr-1 h-3 w-3" />
              Reserve met
            </Badge>
          )}
        </div>
      </Link>
      <CardContent className="p-4">
        <Link href={lot.href} className="block">
          <h3 className="truncate font-sora text-lg font-semibold text-foreground hover:text-primary">
            {lot.title}
          </h3>
        </Link>
        <p className="mt-0.5 truncate text-xs text-muted-foreground">{lot.sellerName}</p>
        {(lot.sire || lot.dam) && (
          <p className="mt-1 truncate text-sm text-muted-foreground">
            {lot.sire ?? "—"} × {lot.dam ?? "—"}
          </p>
        )}
        {chips.length > 0 && (
          <div className="mt-2 flex flex-wrap gap-1">
            {chips.map((c) => (
              <Badge key={c} variant="secondary" className="capitalize">
                {c.replace(/_/g, " ")}
              </Badge>
            ))}
          </div>
        )}
        <div className="mt-3 flex items-end justify-between border-t border-border pt-3">
          <div>
            <p className="text-xs text-muted-foreground">{lot.priceLabel}</p>
            <p className="text-lg font-semibold text-foreground">{currency(lot.price)}</p>
          </div>
          {countdown && (
            <span className="flex items-center gap-1 text-xs font-medium text-foreground">
              <Clock className="h-3.5 w-3.5 text-accent" />
              {countdown}
            </span>
          )}
        </div>
      </CardContent>
    </Card>
  )
}
