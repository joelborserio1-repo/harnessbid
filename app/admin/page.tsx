import Link from "next/link"
import {
  BadgeCheck,
  BarChart3,
  Building2,
  FileWarning,
  Gavel,
  MessageSquare,
  Package,
  Star,
} from "lucide-react"
import { Card, CardContent } from "@/components/ui/card"
import { getAdminStats } from "@/lib/admin/queries"

export const dynamic = "force-dynamic"

function StatCard({
  icon: Icon,
  label,
  value,
  href,
}: {
  icon: typeof BarChart3
  label: string
  value: number
  href?: string
}) {
  const body = (
    <Card className="h-full border-border/70">
      <CardContent className="p-5">
        <div className="flex items-center gap-3">
          <div className="flex h-10 w-10 items-center justify-center rounded-full bg-accent/10">
            <Icon className="h-5 w-5 text-accent" />
          </div>
          <div>
            <p className="text-2xl font-bold text-foreground">{value.toLocaleString()}</p>
            <p className="text-xs text-muted-foreground">{label}</p>
          </div>
        </div>
      </CardContent>
    </Card>
  )
  return href ? <Link href={href}>{body}</Link> : body
}

export default async function AdminOverviewPage() {
  const stats = await getAdminStats()

  return (
    <div className="space-y-8">
      <div>
        <h1 className="font-sora text-2xl font-semibold text-foreground">Platform overview</h1>
        <p className="mt-1 text-sm text-muted-foreground">
          Operational snapshot across listings, auctions, sellers, and moderation.
        </p>
      </div>

      <div className="grid grid-cols-2 gap-4 md:grid-cols-4">
        <StatCard icon={Package} label="Total listings" value={stats.totalListings} />
        <StatCard icon={Gavel} label="Active horse auctions" value={stats.activeHorseAuctions} />
        <StatCard icon={Package} label="Active marketplace" value={stats.activeMarketplace} />
        <StatCard
          icon={BadgeCheck}
          label="Pending approvals"
          value={stats.pendingApprovals}
          href="/admin/listings"
        />
        <StatCard
          icon={Building2}
          label="Enterprise applications"
          value={stats.enterpriseApplications}
          href="/admin/enterprise"
        />
        <StatCard icon={FileWarning} label="Open reports" value={stats.openReports} href="/admin/reports" />
        <StatCard icon={Star} label="Bids today" value={stats.bidsToday} />
        <StatCard icon={MessageSquare} label="Enquiries" value={stats.enquiries} />
      </div>

      <Card className="border-border/70">
        <CardContent className="p-6">
          <h2 className="font-sora text-lg font-semibold text-foreground">Activity summary</h2>
          <p className="mt-2 text-sm leading-6 text-muted-foreground">
            {stats.pendingApprovals > 0
              ? `${stats.pendingApprovals} listing(s) await moderation.`
              : "No listings are awaiting moderation."}{" "}
            {stats.enterpriseApplications > 0
              ? `${stats.enterpriseApplications} enterprise application(s) pending review.`
              : "No enterprise applications pending."}{" "}
            {stats.openReports > 0
              ? `${stats.openReports} open report(s) need attention.`
              : "No open reports."}
          </p>
        </CardContent>
      </Card>
    </div>
  )
}
