import { Badge } from "@/components/ui/badge"
import { Button } from "@/components/ui/button"
import { Card, CardContent } from "@/components/ui/card"
import { getModerationQueue } from "@/lib/admin/queries"
import { setListingFeaturedAction, setListingStatusAction } from "@/lib/admin/actions"

export const dynamic = "force-dynamic"

function StatusButton({
  kind,
  id,
  action,
  label,
  variant = "outline",
}: {
  kind: string
  id: string
  action: string
  label: string
  variant?: "outline" | "ghost" | "default"
}) {
  return (
    <form action={setListingStatusAction}>
      <input type="hidden" name="kind" value={kind} />
      <input type="hidden" name="id" value={id} />
      <input type="hidden" name="action" value={action} />
      <Button
        type="submit"
        size="sm"
        variant={variant === "default" ? undefined : variant}
        className={
          action === "approve" ? "bg-accent text-accent-foreground hover:bg-accent/90" : undefined
        }
      >
        {label}
      </Button>
    </form>
  )
}

export default async function AdminListingsPage() {
  const queue = await getModerationQueue()

  return (
    <div className="space-y-6">
      <div>
        <h1 className="font-sora text-2xl font-semibold text-foreground">Listing moderation</h1>
        <p className="mt-1 text-sm text-muted-foreground">
          Pending and draft horse + marketplace listings awaiting review.
        </p>
      </div>

      {queue.length === 0 ? (
        <Card className="border-border/70">
          <CardContent className="p-8 text-center text-sm text-muted-foreground">
            The moderation queue is empty.
          </CardContent>
        </Card>
      ) : (
        <div className="space-y-3">
          {queue.map((item) => (
            <Card key={`${item.kind}-${item.id}`} className="border-border/70">
              <CardContent className="flex flex-col gap-3 p-4 lg:flex-row lg:items-center">
                <div className="min-w-0 flex-1">
                  <div className="flex flex-wrap items-center gap-2">
                    <span className="font-medium text-foreground">{item.title}</span>
                    <Badge variant="secondary">{item.kind}</Badge>
                    <Badge variant="secondary">{item.status.replace(/_/g, " ")}</Badge>
                  </div>
                  <p className="mt-1 text-xs text-muted-foreground">/{item.slug}</p>
                </div>
                <div className="flex flex-wrap gap-2">
                  <StatusButton kind={item.kind} id={item.id} action="approve" label="Approve" variant="default" />
                  <StatusButton kind={item.kind} id={item.id} action="reject" label="Reject" />
                  <StatusButton kind={item.kind} id={item.id} action="unpublish" label="Unpublish" />
                  <StatusButton kind={item.kind} id={item.id} action="archive" label="Archive" variant="ghost" />
                  <form action={setListingFeaturedAction}>
                    <input type="hidden" name="kind" value={item.kind} />
                    <input type="hidden" name="id" value={item.id} />
                    <input type="hidden" name="featured" value="true" />
                    <Button type="submit" size="sm" variant="ghost">
                      Feature
                    </Button>
                  </form>
                </div>
              </CardContent>
            </Card>
          ))}
        </div>
      )}
    </div>
  )
}
