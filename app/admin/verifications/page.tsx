import { Badge } from "@/components/ui/badge"
import { Button } from "@/components/ui/button"
import { Card, CardContent } from "@/components/ui/card"
import { requireStaff } from "@/lib/admin/guard"
import { createSupabaseServiceClient, hasServiceRoleEnv } from "@/lib/supabase/admin"
import { reviewVerificationAction } from "@/lib/verification/actions"
import { ViewDocsButton } from "@/components/admin/verification-review"

export const dynamic = "force-dynamic"

type Row = {
  id: string
  seller_account_id: string
  doc_type: string
  full_legal_name: string | null
  front_path: string
  back_path: string | null
  status: string
  review_notes: string | null
  created_at: string
}

async function getPendingVerifications(): Promise<{ rows: Row[]; sellerNames: Record<string, string> }> {
  if (!hasServiceRoleEnv()) return { rows: [], sellerNames: {} }
  const svc = createSupabaseServiceClient()
  const { data } = await svc
    .from("verification_documents")
    .select("id, seller_account_id, doc_type, full_legal_name, front_path, back_path, status, review_notes, created_at")
    .order("created_at", { ascending: true })
    .limit(100)
  const rows = (data ?? []) as Row[]

  const ids = [...new Set(rows.map((r) => r.seller_account_id))]
  const sellerNames: Record<string, string> = {}
  if (ids.length) {
    const { data: sellers } = await svc.from("seller_accounts").select("id, display_name").in("id", ids)
    for (const s of sellers ?? []) sellerNames[(s as { id: string }).id] = (s as { display_name: string }).display_name
  }
  return { rows, sellerNames }
}

function ReviewForm({ id, action, label, variant }: { id: string; action: string; label: string; variant: "default" | "outline" }) {
  return (
    <form action={reviewVerificationAction} className="contents">
      <input type="hidden" name="id" value={id} />
      <input type="hidden" name="action" value={action} />
      <Button type="submit" size="sm" variant={variant}>{label}</Button>
    </form>
  )
}

const STATUS_TONE: Record<string, string> = {
  pending: "bg-accent/15 text-accent",
  verified: "bg-emerald-500/15 text-emerald-600",
  rejected: "bg-destructive/15 text-destructive",
}

export default async function AdminVerificationsPage() {
  await requireStaff()
  const { rows, sellerNames } = await getPendingVerifications()
  const pending = rows.filter((r) => r.status === "pending")
  const reviewed = rows.filter((r) => r.status !== "pending")

  return (
    <div className="space-y-6">
      <div>
        <h1 className="font-sora text-2xl font-semibold text-foreground">Seller verifications</h1>
        <p className="mt-1 text-sm text-muted-foreground">
          Review uploaded ID documents and approve or reject seller verification.
        </p>
      </div>

      <section className="space-y-3">
        <h2 className="text-sm font-medium text-foreground">Pending review ({pending.length})</h2>
        {pending.length === 0 ? (
          <Card className="border-border/70"><CardContent className="p-8 text-center text-sm text-muted-foreground">No verifications awaiting review.</CardContent></Card>
        ) : (
          pending.map((r) => (
            <Card key={r.id} className="border-border/70">
              <CardContent className="p-4 flex flex-wrap items-center gap-4 justify-between">
                <div className="min-w-0">
                  <p className="font-medium text-foreground">{sellerNames[r.seller_account_id] ?? "Seller"}</p>
                  <p className="text-sm text-muted-foreground">
                    {r.full_legal_name ?? "—"} · {r.doc_type.replace(/_/g, " ")} · submitted {new Date(r.created_at).toLocaleDateString()}
                  </p>
                </div>
                <div className="flex items-center gap-2">
                  <ViewDocsButton frontPath={r.front_path} backPath={r.back_path} />
                  <ReviewForm id={r.id} action="approve" label="Approve" variant="default" />
                  <ReviewForm id={r.id} action="reject" label="Reject" variant="outline" />
                </div>
              </CardContent>
            </Card>
          ))
        )}
      </section>

      {reviewed.length > 0 && (
        <section className="space-y-3">
          <h2 className="text-sm font-medium text-foreground">Reviewed</h2>
          {reviewed.map((r) => (
            <Card key={r.id} className="border-border/70">
              <CardContent className="p-4 flex flex-wrap items-center gap-4 justify-between">
                <div>
                  <p className="font-medium text-foreground">{sellerNames[r.seller_account_id] ?? "Seller"}</p>
                  <p className="text-sm text-muted-foreground">{r.full_legal_name ?? "—"} · {r.doc_type.replace(/_/g, " ")}</p>
                </div>
                <Badge className={STATUS_TONE[r.status] ?? ""}>{r.status}</Badge>
              </CardContent>
            </Card>
          ))}
        </section>
      )}
    </div>
  )
}
