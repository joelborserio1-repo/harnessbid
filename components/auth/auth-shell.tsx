import { BadgeCheck } from "lucide-react"
import { PageFrame } from "@/components/static-pages"
import { Card, CardContent } from "@/components/ui/card"

const benefits: Array<[string, string]> = [
  ["Verified marketplace", "Follow trusted sellers and high-quality listings across the racing ecosystem."],
  ["Watchlists and enquiries", "Save horses, equipment, and sale events before you contact sellers."],
  ["Seller-ready structure", "Move from browsing to selling when your account is approved."],
]

/**
 * Shared auth page layout (hero + benefit cards). The form is passed in as
 * children so the interactive client form lives in its own component while the
 * surrounding marketing shell stays a server component. Matches existing
 * HarnessBid styling (navy hero, Sora headings, branded cards).
 */
export function AuthShell({
  mode,
  children,
}: {
  mode: "login" | "register"
  children: React.ReactNode
}) {
  const isRegister = mode === "register"
  return (
    <PageFrame>
      <section className="bg-primary py-10 sm:py-12">
        <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
          <p className="text-sm font-semibold uppercase tracking-wider text-accent">Account access</p>
          <h1 className="mt-3 font-sora text-3xl font-semibold tracking-wide text-primary-foreground sm:text-4xl">
            {isRegister ? "Create your HarnessBid account" : "Login to HarnessBid"}
          </h1>
          <p className="mt-4 max-w-2xl text-primary-foreground/75">
            {isRegister
              ? "Join the premium marketplace for harness racing auctions, equipment, services, and enterprise sale events."
              : "Access your watchlist, seller dashboard, enquiries, and saved marketplace activity."}
          </p>
        </div>
      </section>
      <section className="mx-auto grid max-w-6xl gap-8 px-4 py-10 sm:px-6 lg:grid-cols-[1fr_420px] lg:px-8 lg:py-14">
        <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-1">
          {benefits.map(([title, body]) => (
            <Card key={title} className="border-border/70 bg-card">
              <CardContent className="p-5">
                <BadgeCheck className="mb-3 h-5 w-5 text-accent" />
                <h2 className="font-sora text-lg font-semibold">{title}</h2>
                <p className="mt-2 text-sm leading-6 text-muted-foreground">{body}</p>
              </CardContent>
            </Card>
          ))}
        </div>
        <Card className="border-border/70 bg-card">
          <CardContent className="p-6 sm:p-8">{children}</CardContent>
        </Card>
      </section>
    </PageFrame>
  )
}
