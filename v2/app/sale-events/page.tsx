import { EmptyStatePage } from "@/components/static-pages"
import { SaleEventsList } from "@/components/sale-events-list"
import { getSaleEvents } from "@/lib/supabase/queries"
import { Building2, Search } from "lucide-react"

export default async function SaleEventsPage() {
  const events = await getSaleEvents(24)

  if (events.error) {
    return (
      <EmptyStatePage
        icon={Search}
        eyebrow="Sale events unavailable"
        title="Sale events could not load"
        description="The sale event calendar is available, but live event data did not load cleanly. Try again shortly or browse current horse auctions."
        primary={{ label: "Browse auctions", href: "/auctions" }}
        secondary={{ label: "Enterprise accounts", href: "/enterprise", variant: "outline" }}
      />
    )
  }

  if (events.data.length === 0) {
    return (
      <EmptyStatePage
        icon={Building2}
        eyebrow="Sale events"
        title="No sale events are listed yet"
        description="Enterprise sale events for studs, farms, auction houses, and equipment vendors will appear here as they are scheduled."
        primary={{ label: "Browse auctions", href: "/auctions" }}
        secondary={{ label: "Enterprise accounts", href: "/enterprise", variant: "outline" }}
        points={["Enterprise sellers", "Timed sale events", "Premium catalogue pages"]}
      />
    )
  }

  return <SaleEventsList events={events.data} />
}
