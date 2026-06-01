import "server-only"
import { createSupabaseServerClient } from "@/lib/supabase/server"
import { createSupabaseServerAuthClient, hasSupabaseEnv } from "@/lib/supabase/auth-server"

export type SellerReview = {
  id: string
  rating: number
  comment: string | null
  reviewerName: string
  createdAt: string
  isMine: boolean
}

/** Public reviews for a seller account, newest first, with reviewer display names. */
export async function getSellerReviews(sellerAccountId: string): Promise<SellerReview[]> {
  if (!hasSupabaseEnv()) return []
  const supabase = createSupabaseServerClient()

  const { data: rows } = await supabase
    .from("seller_reviews")
    .select("id, rating, comment, reviewer_profile_id, created_at")
    .eq("seller_account_id", sellerAccountId)
    .order("created_at", { ascending: false })
    .limit(50)

  if (!rows || rows.length === 0) return []

  // Resolve reviewer display names.
  const ids = [...new Set(rows.map((r) => r.reviewer_profile_id))]
  const { data: profiles } = await supabase.from("profiles").select("id, display_name, full_name").in("id", ids)
  const nameOf = new Map((profiles ?? []).map((p) => [p.id, p.display_name || p.full_name || "HarnessBid member"]))

  // Who's viewing (to flag their own review).
  let viewerId: string | null = null
  try {
    const auth = await createSupabaseServerAuthClient()
    const { data } = await auth.auth.getUser()
    viewerId = data.user?.id ?? null
  } catch {
    viewerId = null
  }

  return rows.map((r) => ({
    id: r.id,
    rating: r.rating,
    comment: r.comment,
    reviewerName: nameOf.get(r.reviewer_profile_id) ?? "HarnessBid member",
    createdAt: r.created_at,
    isMine: viewerId === r.reviewer_profile_id,
  }))
}
