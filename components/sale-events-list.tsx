import Image from "next/image"
import Link from "next/link"
import { Building2, CalendarDays } from "lucide-react"
import { Header } from "@/components/header"
import { Footer } from "@/components/footer"
import { Badge } from "@/components/ui/badge"
import { Button } from "@/components/ui/button"
import { Card, CardContent } from "@/components/ui/card"
import type { SellerSaleEvent } from "@/lib/supabase/queries"

function formatDate(value: string | null) {
  if (!value) return "Dates on request"
  return new Intl.DateTimeFormat("en", {
    month: "short",
    day: "numeric",
    year: "numeric",
  }).format(new Date(value))
}

export function SaleEventsList({ events }: { events: SellerSaleEvent[] }) {
  return (
    <div className="min-h-screen bg-background">
      <Header />
      <main>
        <section className="bg-primary py-10 lg:py-14">
          <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <Badge className="mb-4 bg-accent text-accent-foreground">
              <Building2 className="mr-1 h-3 w-3" />
              Enterprise sale calendar
            </Badge>
            <h1 className="font-sora text-3xl font-semibold tracking-wide text-primary-foreground lg:text-4xl">
              SALE EVENTS
            </h1>
            <p className="mt-3 max-w-2xl text-primary-foreground/75">
              Premium HarnessBid sale events from verified sellers, enterprise partners, farms, auction houses, and racing marketplace operators.
            </p>
          </div>
        </section>

        <section className="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
          <div className="mb-6 flex flex-col gap-3 border-b border-border pb-5 sm:flex-row sm:items-center sm:justify-between">
            <p className="text-sm text-muted-foreground">{events.length} sale events</p>
            <Link href="/enterprise">
              <Button variant="outline">Enterprise accounts</Button>
            </Link>
          </div>

          {events.length === 0 ? (
            <Card className="border-border/70">
              <CardContent className="p-8 text-center">
                <Building2 className="mx-auto mb-4 h-9 w-9 text-primary" />
                <h2 className="font-sora text-xl font-semibold text-foreground">No sale events are listed yet</h2>
                <p className="mx-auto mt-2 max-w-2xl text-sm leading-6 text-muted-foreground">
                  Enterprise sale events for studs, farms, auction houses, and equipment vendors will appear here as they are scheduled.
                </p>
                <div className="mt-5 flex flex-wrap justify-center gap-3">
                  <Link href="/auctions">
                    <Button>Browse auctions</Button>
                  </Link>
                  <Link href="/enterprise">
                    <Button variant="outline">Enterprise accounts</Button>
                  </Link>
                </div>
              </CardContent>
            </Card>
          ) : (
            <div className="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
              {events.map((event) => (
                <Card key={event.id} className="h-full overflow-hidden border-border/70 card-hover">
                  <div className="relative aspect-[16/9] overflow-hidden">
                    <Image src={event.image} alt={event.name} fill className="object-cover" />
                    <Badge className="absolute left-3 top-3 bg-primary text-primary-foreground">
                      <CalendarDays className="mr-1 h-3 w-3" />
                      {event.status}
                    </Badge>
                  </div>
                  <CardContent className="p-5">
                    <p className="text-xs font-semibold uppercase tracking-wider text-primary">{event.eventType}</p>
                    <h2 className="mt-2 font-sora text-lg font-semibold text-foreground">{event.name}</h2>
                    <p className="mt-2 line-clamp-2 text-sm leading-6 text-muted-foreground">{event.description}</p>
                    <div className="mt-4 flex items-center justify-between border-t border-border pt-4 text-sm text-muted-foreground">
                      <span>{formatDate(event.startsAt)}</span>
                      <span>{event.endsAt ? `Ends ${formatDate(event.endsAt)}` : "End date TBC"}</span>
                    </div>
                  </CardContent>
                </Card>
              ))}
            </div>
          )}
        </section>
      </main>
      <Footer />
    </div>
  )
}
