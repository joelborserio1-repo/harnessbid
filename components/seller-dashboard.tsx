import Link from "next/link"
import {
  BadgeCheck,
  BarChart3,
  Building2,
  CheckCircle2,
  Clock,
  Eye,
  FileText,
  Gavel,
  Heart,
  MapPin,
  Package,
  Plus,
  ShieldCheck,
  Store,
} from "lucide-react"
import { Button } from "@/components/ui/button"
import { Badge } from "@/components/ui/badge"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs"
import { EnterpriseRequestCard } from "@/components/enterprise-request-card"
import type { SellerAuctionItem } from "@/lib/auctions/dashboard"
import type { SellerBillingSummary } from "@/lib/payments/queries"

export type SellerDashboardData = {
  name: string
  slug: string
  accountTypeLabel: string
  isEnterprise: boolean
  verified: boolean
  verificationStatus: string
  location: string | null
  bio: string | null
  website: string | null
  memberSince: string | null
  enterpriseRequested: boolean
  enterpriseStatus: string | null
}

const VERIFICATION_COPY: Record<string, { label: string; tone: "accent" | "muted" }> = {
  verified: { label: "Verified seller", tone: "accent" },
  pending: { label: "Verification pending", tone: "muted" },
  unverified: { label: "Verification pending", tone: "muted" },
  rejected: { label: "Verification needs attention", tone: "muted" },
  suspended: { label: "Account suspended", tone: "muted" },
}

function StatCard({ icon: Icon, value, label }: { icon: typeof Eye; value: string; label: string }) {
  return (
    <Card>
      <CardContent className="p-4">
        <div className="flex items-center gap-3">
          <div className="flex h-10 w-10 items-center justify-center rounded-full bg-accent/10">
            <Icon className="h-5 w-5 text-accent" />
          </div>
          <div>
            <p className="text-2xl font-bold text-foreground">{value}</p>
            <p className="text-xs text-muted-foreground">{label}</p>
          </div>
        </div>
      </CardContent>
    </Card>
  )
}

function ListingsEmptyState() {
  return (
    <div className="rounded-lg border border-border bg-secondary/50 p-6 text-center">
      <div className="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-lg border border-accent/40 bg-accent/10">
        <Package className="h-6 w-6 text-primary" />
      </div>
      <h3 className="font-sora text-lg font-semibold text-foreground">No listings yet</h3>
      <p className="mx-auto mt-2 max-w-sm text-sm leading-6 text-muted-foreground">
        Your horse auctions and marketplace listings will appear here once listing creation is
        connected for your account.
      </p>
      <Link href="/sell/new">
        <Button className="mt-5 bg-accent text-accent-foreground hover:bg-accent/90">
          <Plus className="mr-2 h-4 w-4" />
          Create listing
        </Button>
      </Link>
    </div>
  )
}

function currencyShort(value: number) {
  return new Intl.NumberFormat("en-US", {
    style: "currency",
    currency: "USD",
    maximumFractionDigits: 0,
  }).format(value)
}

function SellerAuctionsCard({ auctions }: { auctions: SellerAuctionItem[] }) {
  return (
    <Card>
      <CardHeader>
        <CardTitle className="font-sora text-lg">Your auctions</CardTitle>
      </CardHeader>
      <CardContent>
        {auctions.length === 0 ? (
          <div className="rounded-lg border border-border bg-secondary/50 p-6 text-center text-sm text-muted-foreground">
            No horse auctions yet.{" "}
            <Link href="/sell/horse-auction" className="font-medium text-primary hover:underline">
              Create an auction
            </Link>
            .
          </div>
        ) : (
          <div className="space-y-3">
            {auctions.map((a) => (
              <div key={a.auctionId} className="flex flex-col gap-2 rounded-lg bg-secondary p-3 sm:flex-row sm:items-center">
                <div className="min-w-0 flex-1">
                  <div className="flex flex-wrap items-center gap-2">
                    <Link href={a.href} className="truncate font-medium text-foreground hover:text-primary">
                      {a.title}
                    </Link>
                    <Badge variant="secondary">{a.status}</Badge>
                    {a.reservePrice != null && (
                      <Badge
                        className={a.reserveMet ? "bg-accent text-accent-foreground" : undefined}
                        variant={a.reserveMet ? undefined : "secondary"}
                      >
                        {a.reserveMet ? "Reserve met" : "Reserve not met"}
                      </Badge>
                    )}
                    {a.closingSoon && (
                      <Badge variant="secondary" className="text-destructive">
                        Closing soon
                      </Badge>
                    )}
                  </div>
                  <p className="mt-1 text-xs text-muted-foreground">
                    {currencyShort(a.currentBid)} · {a.bidCount} bids
                  </p>
                </div>
                <Button asChild variant="outline" size="sm">
                  <Link href={a.href}>View</Link>
                </Button>
              </div>
            ))}
          </div>
        )}
      </CardContent>
    </Card>
  )
}

const BILLING_LABELS: Record<string, string> = {
  per_listing: "Per-listing fees",
  invoiced: "Invoiced (enterprise)",
  exempt: "Fee exempt",
}

function BillingCard({ billing }: { billing: SellerBillingSummary }) {
  return (
    <Card>
      <CardHeader>
        <CardTitle className="font-sora text-lg">Billing</CardTitle>
      </CardHeader>
      <CardContent className="space-y-3">
        <div className="flex items-center justify-between text-sm">
          <span className="text-muted-foreground">Billing mode</span>
          <Badge variant="secondary">
            {billing.feeExempt ? "Fee exempt" : BILLING_LABELS[billing.billingMode] ?? billing.billingMode}
          </Badge>
        </div>
        <div className="flex items-center justify-between text-sm">
          <span className="text-muted-foreground">Open invoices</span>
          <span className="font-medium text-foreground">
            {billing.openInvoiceTotal > 0 ? currencyShort(billing.openInvoiceTotal) : "None"}
          </span>
        </div>
        <div className="flex items-center justify-between text-sm">
          <span className="text-muted-foreground">Payouts</span>
          <span className="font-medium text-foreground">{billing.payoutCount}</span>
        </div>
        <p className="text-xs text-muted-foreground">
          Listing fees, featured upgrades, and payouts connect when payments go live.
        </p>
      </CardContent>
    </Card>
  )
}

export function SellerDashboard({
  seller,
  auctions = [],
  billing,
}: {
  seller: SellerDashboardData
  auctions?: SellerAuctionItem[]
  billing?: SellerBillingSummary | null
}) {
  const verification = VERIFICATION_COPY[seller.verificationStatus] ?? VERIFICATION_COPY.unverified
  const storefrontHref = `/seller/${seller.slug}`

  const onboardingSteps = [
    { label: "Account type selected", done: true },
    { label: "Profile details added", done: true },
    { label: "Contact details added", done: true },
    { label: "Seller verification", done: seller.verified },
  ]

  return (
    <div className="min-h-screen bg-background">
      {/* Dashboard Header */}
      <div className="bg-primary py-8">
        <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
          <div className="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
            <div className="flex items-center gap-4">
              <div className="flex h-16 w-16 shrink-0 items-center justify-center rounded-full border border-primary-foreground/15 bg-primary-foreground/10">
                {seller.isEnterprise ? (
                  <Building2 className="h-7 w-7 text-primary-foreground/80" />
                ) : (
                  <Store className="h-7 w-7 text-primary-foreground/80" />
                )}
              </div>
              <div>
                <div className="flex items-center gap-2">
                  <h1 className="font-sora text-xl font-semibold text-primary-foreground">
                    {seller.name}
                  </h1>
                  {seller.verified && <BadgeCheck className="h-5 w-5 text-accent" />}
                </div>
                <div className="mt-2 flex flex-wrap gap-2">
                  <Badge className="bg-accent text-accent-foreground">{seller.accountTypeLabel}</Badge>
                  <Badge variant="secondary">{verification.label}</Badge>
                  {seller.isEnterprise && seller.enterpriseStatus && (
                    <Badge variant="secondary">Enterprise: {seller.enterpriseStatus}</Badge>
                  )}
                </div>
              </div>
            </div>
            <div className="flex gap-2">
              <Link href="/sell/new">
                <Button className="bg-accent text-accent-foreground hover:bg-accent/90">
                  <Plus className="mr-2 h-4 w-4" />
                  New Listing
                </Button>
              </Link>
              <Link href="/account">
                <Button
                  variant="outline"
                  className="border-primary-foreground/30 text-primary-foreground hover:bg-primary-foreground/10"
                >
                  Account
                </Button>
              </Link>
            </div>
          </div>
        </div>
      </div>

      <div className="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        {/* Stats Grid (placeholders until listings are connected) */}
        <div className="mb-8 grid grid-cols-2 gap-4 md:grid-cols-4">
          <StatCard icon={Package} value="0" label="Active Listings" />
          <StatCard icon={FileText} value="0" label="Drafts" />
          <StatCard icon={Eye} value="0" label="Total Views" />
          <StatCard icon={Heart} value="0" label="Watchers" />
        </div>

        <div className="grid gap-8 lg:grid-cols-3">
          {/* Listings */}
          <div className="lg:col-span-2 space-y-8">
            <Card>
              <CardHeader>
                <CardTitle className="font-sora text-lg">Your Listings</CardTitle>
              </CardHeader>
              <CardContent>
                <Tabs defaultValue="active">
                  <TabsList className="mb-4">
                    <TabsTrigger value="active">Active</TabsTrigger>
                    <TabsTrigger value="drafts">Drafts</TabsTrigger>
                    <TabsTrigger value="sold">Sold</TabsTrigger>
                  </TabsList>
                  <TabsContent value="active">
                    <ListingsEmptyState />
                  </TabsContent>
                  <TabsContent value="drafts">
                    <ListingsEmptyState />
                  </TabsContent>
                  <TabsContent value="sold">
                    <ListingsEmptyState />
                  </TabsContent>
                </Tabs>
              </CardContent>
            </Card>

            <SellerAuctionsCard auctions={auctions} />

            {/* Seller profile summary */}
            <Card>
              <CardHeader>
                <CardTitle className="font-sora text-lg">Seller profile</CardTitle>
              </CardHeader>
              <CardContent className="space-y-4">
                <div className="flex items-center gap-2 text-sm text-muted-foreground">
                  <Store className="h-4 w-4" />
                  <span>Storefront:</span>
                  <Link href={storefrontHref} className="font-medium text-primary hover:underline">
                    {storefrontHref}
                  </Link>
                </div>
                {seller.location && (
                  <div className="flex items-center gap-2 text-sm text-muted-foreground">
                    <MapPin className="h-4 w-4" />
                    <span>{seller.location}</span>
                  </div>
                )}
                {seller.memberSince && (
                  <div className="flex items-center gap-2 text-sm text-muted-foreground">
                    <Clock className="h-4 w-4" />
                    <span>Seller since {seller.memberSince}</span>
                  </div>
                )}
                <p className="text-sm leading-6 text-foreground">
                  {seller.bio || "Add a bio from your account settings to introduce your operation to buyers."}
                </p>
              </CardContent>
            </Card>
          </div>

          {/* Status & Activity */}
          <div className="space-y-6">
            <Card>
              <CardHeader>
                <CardTitle className="font-sora text-lg">Onboarding status</CardTitle>
              </CardHeader>
              <CardContent className="space-y-3">
                {onboardingSteps.map((step) => (
                  <div key={step.label} className="flex items-center gap-3">
                    <CheckCircle2
                      className={
                        "h-5 w-5 " + (step.done ? "text-accent" : "text-muted-foreground/40")
                      }
                    />
                    <span
                      className={
                        "text-sm " + (step.done ? "text-foreground" : "text-muted-foreground")
                      }
                    >
                      {step.label}
                    </span>
                  </div>
                ))}
              </CardContent>
            </Card>

            <Card>
              <CardHeader>
                <CardTitle className="font-sora text-lg">Verification</CardTitle>
              </CardHeader>
              <CardContent className="space-y-3">
                <div className="flex items-center gap-3">
                  <ShieldCheck
                    className={"h-5 w-5 " + (seller.verified ? "text-accent" : "text-muted-foreground")}
                  />
                  <span className="text-sm font-medium text-foreground">{verification.label}</span>
                </div>
                <p className="text-sm text-muted-foreground">
                  {seller.verified
                    ? "Your account is verified. Verified sellers appear in public search and storefronts."
                    : "New seller accounts start unverified. Verified status unlocks public storefront visibility."}
                </p>
              </CardContent>
            </Card>

            {seller.isEnterprise ? (
              <Card>
                <CardHeader>
                  <CardTitle className="font-sora text-lg">Enterprise application</CardTitle>
                </CardHeader>
                <CardContent className="space-y-3">
                  <Badge variant="secondary">{seller.enterpriseStatus ?? "in review"}</Badge>
                  <p className="text-sm text-muted-foreground">
                    Enterprise onboarding is reviewed by the HarnessBid team. Branded storefronts and
                    sale events unlock once your application is approved.
                  </p>
                </CardContent>
              </Card>
            ) : (
              <EnterpriseRequestCard requested={seller.enterpriseRequested} />
            )}

            {billing && <BillingCard billing={billing} />}

            <Card>
              <CardHeader>
                <CardTitle className="font-sora text-lg">Quick Actions</CardTitle>
              </CardHeader>
              <CardContent className="space-y-2">
                <Link href="/sell/new">
                  <Button variant="outline" className="w-full justify-start">
                    <Plus className="mr-2 h-4 w-4" />
                    Create New Listing
                  </Button>
                </Link>
                <Link href="/sell/auction">
                  <Button variant="outline" className="w-full justify-start">
                    <Gavel className="mr-2 h-4 w-4" />
                    Start an Auction
                  </Button>
                </Link>
                <Link href="/dashboard/analytics">
                  <Button variant="outline" className="w-full justify-start">
                    <BarChart3 className="mr-2 h-4 w-4" />
                    View Analytics
                  </Button>
                </Link>
                <Link href={storefrontHref}>
                  <Button variant="outline" className="w-full justify-start">
                    <Store className="mr-2 h-4 w-4" />
                    View Storefront
                  </Button>
                </Link>
              </CardContent>
            </Card>
          </div>
        </div>
      </div>
    </div>
  )
}
