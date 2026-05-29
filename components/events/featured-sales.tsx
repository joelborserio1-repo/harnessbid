import Link from "next/link"
import { Button } from "@/components/ui/button"
import { EventCard } from "@/components/events/event-card"
import type { SaleEventSummary } from "@/lib/events/queries"

/** Homepage featured sales/events promotion block. Renders nothing if empty. */
export function FeaturedSales({ events }: { events: SaleEventSummary[] }) {
  if (events.length === 0) return null
  return (
    <section className="bg-secondary/40 py-12 lg:py-16">
      <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div className="flex flex-wrap items-end justify-between gap-3">
          <div>
            <p className="text-sm font-semibold uppercase tracking-wider text-accent">Sales &amp; events</p>
            <h2 className="mt-2 font-sora text-2xl font-semibold text-foreground sm:text-3xl">
              Featured sales
            </h2>
          </div>
          <Button asChild variant="outline">
            <Link href="/sales">View all sales</Link>
          </Button>
        </div>
        <div className="mt-6 grid gap-5 md:grid-cols-2 lg:grid-cols-3">
          {events.map((e) => (
            <EventCard key={e.id} event={e} />
          ))}
        </div>
      </div>
    </section>
  )
}
