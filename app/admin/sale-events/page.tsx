import { Badge } from "@/components/ui/badge"
import { Button } from "@/components/ui/button"
import { Card, CardContent } from "@/components/ui/card"
import { CreateSaleEventForm } from "@/components/admin/create-sale-event-form"
import { getAdminSaleEvents } from "@/lib/admin/queries"
import { updateSaleEventStatusAction } from "@/lib/admin/actions"

export const dynamic = "force-dynamic"

const NEXT_STATUS: Array<{ status: string; label: string }> = [
  { status: "scheduled", label: "Schedule" },
  { status: "live", label: "Go live" },
  { status: "closed", label: "Close" },
  { status: "archived", label: "Archive" },
]

export default async function AdminSaleEventsPage() {
  const events = await getAdminSaleEvents()

  return (
    <div className="space-y-6">
      <div>
        <h1 className="font-sora text-2xl font-semibold text-foreground">Sale events</h1>
        <p className="mt-1 text-sm text-muted-foreground">
          Catalogue events for APG, Nutrien, and enterprise sales.
        </p>
      </div>

      <Card className="border-border/70">
        <CardContent className="p-4 sm:p-6">
          <CreateSaleEventForm />
        </CardContent>
      </Card>

      {events.length === 0 ? (
        <Card className="border-border/70">
          <CardContent className="p-8 text-center text-sm text-muted-foreground">
            No sale events yet.
          </CardContent>
        </Card>
      ) : (
        <div className="space-y-3">
          {events.map((e) => (
            <Card key={e.id} className="border-border/70">
              <CardContent className="flex flex-col gap-3 p-4 lg:flex-row lg:items-center">
                <div className="min-w-0 flex-1">
                  <div className="flex flex-wrap items-center gap-2">
                    <span className="font-medium text-foreground">{e.name}</span>
                    <Badge variant="secondary">{e.eventType.replace(/_/g, " ")}</Badge>
                    <Badge
                      className={e.status === "live" ? "bg-accent text-accent-foreground" : undefined}
                      variant={e.status === "live" ? undefined : "secondary"}
                    >
                      {e.status}
                    </Badge>
                  </div>
                  <p className="mt-1 text-xs text-muted-foreground">/{e.slug}</p>
                </div>
                <div className="flex flex-wrap gap-2">
                  {NEXT_STATUS.filter((s) => s.status !== e.status).map((s) => (
                    <form action={updateSaleEventStatusAction} key={s.status}>
                      <input type="hidden" name="id" value={e.id} />
                      <input type="hidden" name="status" value={s.status} />
                      <Button type="submit" size="sm" variant="outline">
                        {s.label}
                      </Button>
                    </form>
                  ))}
                </div>
              </CardContent>
            </Card>
          ))}
        </div>
      )}
    </div>
  )
}
