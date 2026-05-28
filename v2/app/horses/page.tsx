import Link from "next/link"
import { Gavel, BadgeCheck, Clock, Dna } from "lucide-react"
import { Header } from "@/components/header"
import { Footer } from "@/components/footer"
import { Button } from "@/components/ui/button"
import { Card, CardContent } from "@/components/ui/card"

const sections = [
  {
    icon: Gavel,
    title: "Live Auctions",
    body: "Browse current horse auctions with sale timing, reserve notes, pedigree details, and seller verification across the HarnessLink network.",
    href: "/auctions",
    cta: "Browse live auctions",
  },
  {
    icon: BadgeCheck,
    title: "Buy Now Horses",
    body: "Fixed-price horse listings with pedigree details, veterinary history, seller verification, and enquiry-first purchasing. Coming soon.",
    href: "/horses/buy-now",
    cta: "View buy now horses",
  },
  {
    icon: Clock,
    title: "Closing Soon",
    body: "Auctions approaching their end time. Time-sensitive opportunities for buyers who have already reviewed pedigree and pricing.",
    href: "/auctions?sort=ending",
    cta: "View closing soon",
  },
  {
    icon: Dna,
    title: "Recently Sold",
    body: "Completed sale records build buyer and seller confidence through transparent auction outcomes and premium listing history.",
    href: "/horses/sold",
    cta: "View recently sold",
  },
]

export default function HorsesPage() {
  return (
    <div className="min-h-screen flex flex-col">
      <Header />
      <main className="flex-1">
        {/* Hero */}
        <section className="bg-primary py-10 sm:py-12 lg:py-16">
          <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <p className="text-sm font-semibold uppercase tracking-wider text-accent">
              Horse sales
            </p>
            <h1 className="mt-3 font-sora text-3xl font-semibold tracking-wide text-primary-foreground sm:text-4xl lg:text-5xl">
              Harness racing horses
            </h1>
            <p className="mt-4 max-w-3xl text-base leading-7 text-primary-foreground/75 sm:text-lg">
              HarnessBid connects buyers and sellers of standardbred racehorses through premium auction listings, verified seller profiles, and transparent sale records across the HarnessLink ecosystem.
            </p>
            <div className="mt-6 flex flex-col gap-3 sm:flex-row">
              <Button asChild className="bg-accent text-accent-foreground hover:bg-accent/90">
                <Link href="/auctions">Browse live auctions</Link>
              </Button>
              <Button asChild variant="outline" className="border-primary-foreground/30 text-primary-foreground hover:bg-primary-foreground/10">
                <Link href="/sell/horse">Sell your horse</Link>
              </Button>
            </div>
          </div>
        </section>

        {/* Section cards */}
        <section className="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8 lg:py-14">
          <div className="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
            {sections.map(({ icon: Icon, title, body, href, cta }) => (
              <Card key={title} className="border-border/70 bg-card flex flex-col">
                <CardContent className="flex flex-1 flex-col p-6">
                  <div className="mb-5 flex h-11 w-11 items-center justify-center rounded-lg border border-accent/40 bg-accent/10">
                    <Icon className="h-5 w-5 text-primary" />
                  </div>
                  <h2 className="font-sora text-xl font-semibold text-foreground">{title}</h2>
                  <p className="mt-3 flex-1 text-sm leading-6 text-muted-foreground">{body}</p>
                  <Button asChild variant="outline" className="mt-6 w-full">
                    <Link href={href}>{cta}</Link>
                  </Button>
                </CardContent>
              </Card>
            ))}
          </div>
        </section>

        {/* Sell CTA */}
        <section className="border-t border-border bg-secondary/30 py-10 sm:py-12">
          <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div className="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
              <div>
                <h2 className="font-sora text-2xl font-semibold text-foreground">Ready to sell a horse?</h2>
                <p className="mt-2 max-w-2xl text-sm leading-6 text-muted-foreground">
                  Prepare a premium horse auction listing with pedigree details, race records, verified seller information, and professional imagery.
                </p>
              </div>
              <div className="flex shrink-0 flex-col gap-3 sm:flex-row">
                <Button asChild className="bg-accent text-accent-foreground hover:bg-accent/90">
                  <Link href="/sell/horse">List a horse</Link>
                </Button>
                <Button asChild variant="outline">
                  <Link href="/guides/seller">Seller guide</Link>
                </Button>
              </div>
            </div>
          </div>
        </section>
      </main>
      <Footer />
    </div>
  )
}
