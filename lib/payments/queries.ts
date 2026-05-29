import {
  createSupabaseServerAuthClient,
  hasSupabaseEnv,
} from "@/lib/supabase/auth-server"

export type SellerBillingSummary = {
  billingMode: string
  feeExempt: boolean
  invoiceCount: number
  openInvoiceTotal: number
  payoutCount: number
}

/** Commercial summary for the current seller's dashboard. */
export async function getSellerBillingSummary(): Promise<SellerBillingSummary | null> {
  if (!hasSupabaseEnv()) return null
  const supabase = await createSupabaseServerAuthClient()
  const {
    data: { user },
  } = await supabase.auth.getUser()
  if (!user) return null

  const { data: account } = await supabase
    .from("seller_accounts")
    .select("id, billing_mode, fee_exempt")
    .eq("owner_profile_id", user.id)
    .limit(1)
    .maybeSingle()
  if (!account) return null

  const [{ data: invoices }, { count: payoutCount }] = await Promise.all([
    supabase
      .from("invoices")
      .select("amount, status")
      .eq("seller_account_id", account.id),
    supabase
      .from("payouts")
      .select("id", { count: "exact", head: true })
      .eq("seller_account_id", account.id),
  ])

  const open = (invoices ?? []).filter((i) => i.status === "issued" || i.status === "overdue")

  return {
    billingMode: account.billing_mode,
    feeExempt: account.fee_exempt,
    invoiceCount: (invoices ?? []).length,
    openInvoiceTotal: open.reduce((sum, i) => sum + Number(i.amount ?? 0), 0),
    payoutCount: payoutCount ?? 0,
  }
}
