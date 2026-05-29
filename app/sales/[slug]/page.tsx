import type { Metadata } from "next"
import { Header } from "@/components/header"
import { Footer } from "@/components/footer"
import { EmptyStatePage } from "@/components/static-pages"
import { CalendarDays } from "lucide-react"
import { EventHero } from "@/components/events/event-hero"
import { ConsignorSection } from "@/components/events/consignor-section"
import { Catalogue } from "@/components/events/catalogue"
import { getEventCatalogue, getSaleEventBySlug, type LotFilters } from "@/lib/events/queries"

export const dynamic = "force-dynamic"

type SearchParams = Promise<{
  consignor?: string
  gait?: string
  sex?: string
  status?: string
  search?: string
  view?: string
}>

export async function generateMetadata({
  params,
}: {
  params: Promise<{ slug: string }>
}): Promise<Metadata> {
  const { slug } = await params
  const event = await getSaleEventBySlug(slug)
  if (!event) return { title: "Sale event | HarnessBid" }
  return {
    title: `${event.name} | HarnessBid`,
    description: event.description,
    openGraph: {
      title: event.name,
      description: event.description,
      type: "website",
      images: event.image && event.image !== "/placeholder.jpg" ? [event.image] : undefined,
    },
  }
}

export default async function SaleEventPage({
  params,
  searchParams,
}: {
  params: Promise<{ slug: string }>
  searchParams: SearchParams
}) {
  const { slug } = await params
  const sp = await searchParams

  const event = await getSaleEventBySlug(slug)
  if (!event) {
    return (
      <EmptyStatePage
        icon={CalendarDays}
        eyebrow="Sale event"
        title="This sale event is not available"
        description="The event may be a draft, cancelled, or not yet published. Browse current sales instead."
        primary={{ label: "All sales", href: "/sales" }}
        secondary={{ label: "Browse auctions", href: "/auctions", variant: "outline" }}
      />
    )
  }

  const filters: LotFilters = {
    consignor: sp.consignor,
    gait: sp.gait,
    sex: sp.sex,
    status: sp.status,
    search: sp.search,
  }
  const view = sp.view === "list" ? "list" : "grid"
  const { lots, consignors } = await getEventCatalogue(event.id, filters)

  // SEO: structured-data placeholder (Event schema).
  const jsonLd = {
    "@context": "https://schema.org",
    "@type": "Event",
    name: event.name,
    description: event.description,
    eventStatus: event.status,
    startDate: event.startsAt ?? undefined,
    endDate: event.endsAt ?? undefined,
  }

  return (
    <div className="min-h-screen flex flex-col">
      <Header />
      <main className="flex-1">
        <script
          type="application/ld+json"
          dangerouslySetInnerHTML={{ __html: JSON.stringify(jsonLd) }}
        />
        <EventHero event={event} lotCount={lots.length} consignorCount={consignors.length} />

        <div className="mx-auto max-w-7xl space-y-10 px-4 py-10 sm:px-6 lg:px-8">
          <ConsignorSection slug={slug} consignors={consignors} />
          <section>
            <h2 className="font-sora text-xl font-semibold text-foreground">Catalogue</h2>
            <p className="mt-1 text-sm text-muted-foreground">
              {lots.length} lot(s){filters.consignor ? " · filtered by consignor" : ""}.
            </p>
            <div className="mt-4">
              <Catalogue slug={slug} lots={lots} consignors={consignors} filters={filters} view={view} />
            </div>
          </section>
        </div>
      </main>
      <Footer />
    </div>
  )
}
