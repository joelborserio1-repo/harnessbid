import { Badge } from "@/components/ui/badge"
import { Button } from "@/components/ui/button"
import { Card, CardContent } from "@/components/ui/card"
import { Input } from "@/components/ui/input"
import { CreateSaleEventForm } from "@/components/admin/create-sale-event-form"
import { getAdminSaleEvents } from "@/lib/admin/queries"
import {
  assignListingToEventAction,
  updateSaleEventAdminAction,
  updateSaleEventStatusAction,
} from "@/lib/admin/actions"

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
                  <form action={updateSaleEventAdminAction}>
                    <input type="hidden" name="id" value={e.id} />
                    <input type="hidden" name="field" value="featured" />
                    <input type="hidden" name="value" value="true" />
                    <Button type="submit" size="sm" variant="ghost">Feature</Button>
                  </form>
                  <form action={updateSaleEventAdminAction}>
                    <input type="hidden" name="id" value={e.id} />
                    <input type="hidden" name="field" value="featured" />
                    <input type="hidden" name="value" value="false" />
                    <Button type="submit" size="sm" variant="ghost">Unfeature</Button>
                  </form>
                </div>
              </CardContent>
              <CardContent className="flex flex-wrap items-end gap-2 border-t border-border p-4 pt-3">
                <form action={updateSaleEventAdminAction} className="flex items-end gap-1">
                  <input type="hidden" name="id" value={e.id} />
                  <input type="hidden" name="field" value="sort_order" />
                  <Input name="value" placeholder="Order" inputMode="numeric" className="h-9 w-20" aria-label="Sort order" />
                  <Button type="submit" size="sm" variant="outline">Order</Button>
                </form>
                <form action={updateSaleEventAdminAction} className="flex flex-1 items-end gap-1">
                  <input type="hidden" name="id" value={e.id} />
                  <input type="hidden" name="field" value="hero_image_url" />
                  <Input name="value" placeholder="Banner image URL" className="h-9 min-w-48 flex-1" aria-label="Banner URL" />
                  <Button type="submit" size="sm" variant="outline">Banner</Button>
                </form>
                <form action={assignListingToEventAction} className="flex flex-wrap items-end gap-1">
                  <input type="hidden" name="eventId" value={e.id} />
                  <select name="kind" defaultValue="horse" className="h-9 rounded-md border border-input bg-background px-2 text-sm" aria-label="Listing kind">
                    <option value="horse">Horse</option>
                    <option value="marketplace">Marketplace</option>
                  </select>
                  <Input name="listingId" placeholder="Listing ID (UUID)" className="h-9 w-44" aria-label="Listing ID" />
                  <Input name="lotNumber" placeholder="Lot #" className="h-9 w-20" aria-label="Lot number" />
                  <Button type="submit" size="sm" className="bg-accent text-accent-foreground hover:bg-accent/90">
                    Assign lot
                  </Button>
                </form>
              </CardContent>
            </Card>
          ))}
        </div>
      )}
    </div>
  )
}
