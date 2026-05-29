import { Badge } from "@/components/ui/badge"
import { Button } from "@/components/ui/button"
import { Card, CardContent } from "@/components/ui/card"
import { getReports } from "@/lib/admin/queries"
import { updateReportStatusAction } from "@/lib/admin/actions"

export const dynamic = "force-dynamic"

function StatusForm({ id, status, label }: { id: string; status: string; label: string }) {
  return (
    <form action={updateReportStatusAction}>
      <input type="hidden" name="id" value={id} />
      <input type="hidden" name="status" value={status} />
      <Button type="submit" size="sm" variant="outline">
        {label}
      </Button>
    </form>
  )
}

export default async function AdminReportsPage() {
  const reports = await getReports()

  return (
    <div className="space-y-6">
      <div>
        <h1 className="font-sora text-2xl font-semibold text-foreground">Reported listings</h1>
        <p className="mt-1 text-sm text-muted-foreground">
          User-submitted reports for moderation review.
        </p>
      </div>

      {reports.length === 0 ? (
        <Card className="border-border/70">
          <CardContent className="p-8 text-center text-sm text-muted-foreground">
            No reports submitted.
          </CardContent>
        </Card>
      ) : (
        <div className="space-y-3">
          {reports.map((r) => (
            <Card key={r.id} className="border-border/70">
              <CardContent className="flex flex-col gap-3 p-4 lg:flex-row lg:items-start">
                <div className="min-w-0 flex-1">
                  <div className="flex flex-wrap items-center gap-2">
                    <span className="font-medium text-foreground">{r.listingTitle}</span>
                    <Badge variant="secondary">{r.listingKind}</Badge>
                    <Badge
                      className={r.status === "open" ? "bg-primary text-primary-foreground" : undefined}
                      variant={r.status === "open" ? undefined : "secondary"}
                    >
                      {r.status}
                    </Badge>
                  </div>
                  <p className="mt-1 text-sm text-foreground">{r.reason}</p>
                  {r.details && <p className="mt-1 text-sm text-muted-foreground">{r.details}</p>}
                </div>
                <div className="flex flex-wrap gap-2">
                  <StatusForm id={r.id} status="reviewing" label="Reviewing" />
                  <StatusForm id={r.id} status="actioned" label="Actioned" />
                  <StatusForm id={r.id} status="dismissed" label="Dismiss" />
                </div>
              </CardContent>
            </Card>
          ))}
        </div>
      )}
    </div>
  )
}
