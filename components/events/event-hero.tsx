import { Building2, CalendarDays, Package, Users } from "lucide-react"
import { Badge } from "@/components/ui/badge"
import { EventCountdown } from "@/components/events/event-countdown"
import type { SaleEventDetail } from "@/lib/events/queries"

function formatDate(value: string | null) {
  if (!value) return "Dates on request"
  return new Intl.DateTimeFormat("en", { month: "short", day: "numeric", year: "numeric" }).format(new Date(value))
}

export function EventHero({
  event,
  lotCount,
  consignorCount,
}: {
  event: SaleEventDetail
  lotCount: number
  consignorCount: number
}) {
  const countdownTarget = event.status === "live" ? event.endsAt : event.startsAt
  const countdownLabel = event.status === "live" ? "Sale closes in" : "Sale opens in"

  return (
    <section className="relative overflow-hidden bg-primary">
      {event.image && event.image !== "/placeholder.jpg" && (
        <>
          {/* eslint-disable-next-line @next/next/no-img-element */}
          <img src={event.image} alt="" className="absolute inset-0 h-full w-full object-cover opacity-30" />
          <div className="absolute inset-0 bg-primary/70" />
        </>
      )}
      <div className="relative mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8 lg:py-16">
        <div className="flex flex-wrap items-center gap-2">
          <Badge className="bg-accent text-accent-foreground capitalize">{event.eventType}</Badge>
          <Badge variant="secondary" className="capitalize">
            {event.status}
          </Badge>
          {event.enterprise && (
            <Badge variant="secondary" className="flex items-center gap-1">
              <Building2 className="h-3 w-3" />
              {event.enterprise.tradingName}
            </Badge>
          )}
        </div>

        <h1 className="mt-4 font-sora text-3xl font-semibold tracking-wide text-primary-foreground sm:text-4xl lg:text-5xl">
          {event.name}
        </h1>
        <p className="mt-4 max-w-3xl text-base leading-7 text-primary-foreground/80 sm:text-lg">
          {event.description}
        </p>

        <div className="mt-8 grid gap-6 lg:grid-cols-[1fr_auto] lg:items-end">
          <div className="flex flex-wrap gap-6 text-primary-foreground">
            <div className="flex items-center gap-2">
              <CalendarDays className="h-5 w-5 text-accent" />
              <div>
                <p className="text-xs text-primary-foreground/60">Sale dates</p>
                <p className="text-sm font-medium">
                  {formatDate(event.startsAt)} — {formatDate(event.endsAt)}
                </p>
              </div>
            </div>
            <div className="flex items-center gap-2">
              <Package className="h-5 w-5 text-accent" />
              <div>
                <p className="text-xs text-primary-foreground/60">Lots</p>
                <p className="text-sm font-medium">{lotCount}</p>
              </div>
            </div>
            <div className="flex items-center gap-2">
              <Users className="h-5 w-5 text-accent" />
              <div>
                <p className="text-xs text-primary-foreground/60">Consignors</p>
                <p className="text-sm font-medium">{consignorCount}</p>
              </div>
            </div>
          </div>
          <EventCountdown target={countdownTarget} label={countdownLabel} />
        </div>
      </div>
    </section>
  )
}
