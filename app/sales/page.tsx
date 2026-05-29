import { CalendarDays } from "lucide-react"
import { Header } from "@/components/header"
import { Footer } from "@/components/footer"
import { Card, CardContent } from "@/components/ui/card"
import { EventCard } from "@/components/events/event-card"
import { getPublicSaleEvents, type SaleEventSummary } from "@/lib/events/queries"

export const dynamic = "force-dynamic"

export const metadata = {
  title: "Sales & Events | HarnessBid",
  description:
    "Premium harness racing sales and catalogue events — APG, Nutrien, studs, and enterprise vendors on HarnessBid.",
  openGraph: {
    title: "HarnessBid Sales & Events",
    description: "Premium harness racing sales and catalogue events.",
    type: "website",
  },
}

function Section({ title, events }: { title: string; events: SaleEventSummary[] }) {
  if (events.length === 0) return null
  return (
    <section>
      <h2 className="font-sora text-xl font-semibold text-foreground">{title}</h2>
      <div className="mt-4 grid gap-5 md:grid-cols-2 lg:grid-cols-3">
        {events.map((e) => (
          <EventCard key={e.id} event={e} />
        ))}
      </div>
    </section>
  )
}

export default async function SalesPage() {
  const groups = await getPublicSaleEvents()
  const hasAny =
    groups.featured.length + groups.live.length + groups.upcoming.length + groups.completed.length > 0

  return (
    <div className="min-h-screen flex flex-col">
      <Header />
      <main className="flex-1">
        <section className="bg-primary py-12 lg:py-16">
          <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <p className="text-sm font-semibold uppercase tracking-wider text-accent">Sales &amp; events</p>
            <h1 className="mt-3 font-sora text-3xl font-semibold tracking-wide text-primary-foreground sm:text-4xl lg:text-5xl">
              Premium harness racing sales
            </h1>
            <p className="mt-4 max-w-3xl text-base leading-7 text-primary-foreground/80 sm:text-lg">
              Catalogue events from APG, Nutrien, studs, and enterprise vendors — bloodstock auctions and
              curated sales campaigns.
            </p>
          </div>
        </section>

        <div className="mx-auto max-w-7xl space-y-10 px-4 py-10 sm:px-6 lg:px-8">
          {!hasAny ? (
            <Card className="border-border/70">
              <CardContent className="p-10 text-center">
                <CalendarDays className="mx-auto mb-4 h-9 w-9 text-primary" />
                <h2 className="font-sora text-xl font-semibold text-foreground">No sale events yet</h2>
                <p className="mx-auto mt-2 max-w-xl text-sm leading-6 text-muted-foreground">
                  Enterprise sale events appear here once scheduled and published.
                </p>
              </CardContent>
            </Card>
          ) : (
            <>
              <Section title="Featured sales" events={groups.featured} />
              <Section title="Live now" events={groups.live} />
              <Section title="Upcoming sales" events={groups.upcoming} />
              <Section title="Completed sales" events={groups.completed} />
            </>
          )}
        </div>
      </main>
      <Footer />
    </div>
  )
}
