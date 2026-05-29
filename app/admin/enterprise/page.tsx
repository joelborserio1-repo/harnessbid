import Link from "next/link"
import { Badge } from "@/components/ui/badge"
import { Button } from "@/components/ui/button"
import { Card, CardContent } from "@/components/ui/card"
import { getEnterpriseApplications } from "@/lib/admin/queries"
import { setEnterpriseStatusAction, setSellerBillingAction } from "@/lib/admin/actions"

export const dynamic = "force-dynamic"

export default async function AdminEnterprisePage() {
  const apps = await getEnterpriseApplications()

  return (
    <div className="space-y-6">
      <div>
        <h1 className="font-sora text-2xl font-semibold text-foreground">Enterprise sellers</h1>
        <p className="mt-1 text-sm text-muted-foreground">
          Approve or reject enterprise seller applications. Approval verifies the seller account.
        </p>
      </div>

      {apps.length === 0 ? (
        <Card className="border-border/70">
          <CardContent className="p-8 text-center text-sm text-muted-foreground">
            No enterprise applications.
          </CardContent>
        </Card>
      ) : (
        <div className="space-y-3">
          {apps.map((app) => (
            <Card key={app.id} className="border-border/70">
              <CardContent className="flex flex-col gap-3 p-4 lg:flex-row lg:items-center">
                <div className="min-w-0 flex-1">
                  <div className="flex flex-wrap items-center gap-2">
                    <span className="font-medium text-foreground">
                      {app.tradingName || app.sellerName}
                    </span>
                    <Badge variant="secondary">{app.onboardingStatus.replace(/_/g, " ")}</Badge>
                    <Badge
                      className={app.verificationStatus === "verified" ? "bg-accent text-accent-foreground" : undefined}
                      variant={app.verificationStatus === "verified" ? undefined : "secondary"}
                    >
                      {app.verificationStatus}
                    </Badge>
                  </div>
                  {app.slug && (
                    <Link href={`/seller/${app.slug}`} className="mt-1 block text-xs text-primary hover:underline">
                      /seller/{app.slug}
                    </Link>
                  )}
                </div>
                <div className="flex flex-wrap items-center gap-2">
                  <ApproveReject id={app.id} sellerAccountId={app.sellerAccountId} />
                  <form action={setSellerBillingAction}>
                    <input type="hidden" name="sellerAccountId" value={app.sellerAccountId} />
                    <input type="hidden" name="field" value="billing_mode" />
                    <input type="hidden" name="value" value="invoiced" />
                    <Button type="submit" size="sm" variant="ghost">
                      Set invoiced
                    </Button>
                  </form>
                  <form action={setSellerBillingAction}>
                    <input type="hidden" name="sellerAccountId" value={app.sellerAccountId} />
                    <input type="hidden" name="field" value="fee_exempt" />
                    <input type="hidden" name="value" value="true" />
                    <Button type="submit" size="sm" variant="ghost">
                      Fee exempt
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

function ApproveReject({ id, sellerAccountId }: { id: string; sellerAccountId: string }) {
  return (
    <>
      <form action={setEnterpriseStatusAction}>
        <input type="hidden" name="id" value={id} />
        <input type="hidden" name="sellerAccountId" value={sellerAccountId} />
        <input type="hidden" name="action" value="approve" />
        <Button type="submit" size="sm" className="bg-accent text-accent-foreground hover:bg-accent/90">
          Approve
        </Button>
      </form>
      <form action={setEnterpriseStatusAction}>
        <input type="hidden" name="id" value={id} />
        <input type="hidden" name="sellerAccountId" value={sellerAccountId} />
        <input type="hidden" name="action" value="reject" />
        <Button type="submit" size="sm" variant="ghost" className="text-destructive">
          Reject
        </Button>
      </form>
    </>
  )
}
