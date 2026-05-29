import Link from "next/link"
import { CalendarDays } from "lucide-react"
import { Badge } from "@/components/ui/badge"
import { Card, CardContent } from "@/components/ui/card"
import type { SaleEventSummary } from "@/lib/events/queries"

function formatDate(value: string | null) {
  if (!value) return "Dates on request"
  return new Intl.DateTimeFormat("en", { month: "short", day: "numeric", year: "numeric" }).format(new Date(value))
}

export function EventCard({ event }: { event: SaleEventSummary }) {
  return (
    <Link href={`/sales/${event.slug}`}>
      <Card className="group h-full overflow-hidden border-border/70 card-hover">
        <div className="relative aspect-[16/9] overflow-hidden bg-secondary">
          {/* eslint-disable-next-line @next/next/no-img-element */}
          <img
            src={event.image}
            alt={event.name}
            className="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
          />
          <Badge className="absolute left-3 top-3 bg-accent text-accent-foreground capitalize">
            {event.eventType}
          </Badge>
          {event.featured && (
            <Badge className="absolute right-3 top-3 bg-primary text-primary-foreground">Featured</Badge>
          )}
        </div>
        <CardContent className="p-5">
          <h3 className="font-sora text-lg font-semibold text-foreground">{event.name}</h3>
          <p className="mt-2 line-clamp-2 text-sm leading-6 text-muted-foreground">{event.description}</p>
          <p className="mt-3 flex items-center gap-1 text-xs text-muted-foreground">
            <CalendarDays className="h-3.5 w-3.5" />
            {formatDate(event.startsAt)} — {formatDate(event.endsAt)}
          </p>
        </CardContent>
      </Card>
    </Link>
  )
}
