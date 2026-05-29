import Link from "next/link"
import { Building2, Gavel, Package } from "lucide-react"
import { PageFrame } from "@/components/static-pages"
import { Button } from "@/components/ui/button"
import { Card, CardContent } from "@/components/ui/card"

const options = [
  {
    icon: Gavel,
    title: "Horse auction",
    body: "List a Standardbred for auction with pedigree, reserve, bidding terms, and timing.",
    href: "/sell/horse-auction",
  },
  {
    icon: Building2,
    title: "Buy now horse",
    body: "Sell a horse at a fixed price with pedigree details, photos, and enquiry-first contact.",
    href: "/sell/buy-now-horse",
  },
  {
    icon: Package,
    title: "Marketplace listing",
    body: "List equipment, sulkies, tack, vehicles, services, feed, and racing supplies.",
    href: "/sell/marketplace",
  },
]

export default function SellPage() {
  return (
    <PageFrame>
      <section className="bg-primary py-10 sm:py-12 lg:py-16">
        <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
          <p className="text-sm font-semibold uppercase tracking-wider text-accent">Seller workspace</p>
          <h1 className="mt-3 font-sora text-3xl font-semibold tracking-wide text-primary-foreground sm:text-4xl lg:text-5xl">
            Sell on HarnessBid
          </h1>
          <p className="mt-4 max-w-3xl text-base leading-7 text-primary-foreground/75 sm:text-lg">
            Choose the listing type that fits your sale. Horse auctions, buy now horses, and
            marketplace listings each follow their own guided flow.
          </p>
        </div>
      </section>

      <section className="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8 lg:py-14">
        <div className="grid gap-5 lg:grid-cols-3">
          {options.map(({ icon: Icon, title, body, href }) => (
            <Card key={title} className="border-border/70 bg-card">
              <CardContent className="flex h-full flex-col p-6">
                <div className="mb-5 flex h-12 w-12 items-center justify-center rounded-lg border border-accent/40 bg-accent/10">
                  <Icon className="h-6 w-6 text-primary" />
                </div>
                <h2 className="font-sora text-xl font-semibold text-foreground">{title}</h2>
                <p className="mt-3 flex-1 text-sm leading-6 text-muted-foreground">{body}</p>
                <Button asChild className="mt-6 w-full bg-accent text-accent-foreground hover:bg-accent/90">
                  <Link href={href}>Start listing</Link>
                </Button>
              </CardContent>
            </Card>
          ))}
        </div>
      </section>
    </PageFrame>
  )
}
