"use server"

import { revalidatePath } from "next/cache"
import { createSupabaseServerAuthClient, hasSupabaseEnv } from "@/lib/supabase/auth-server"
import { logger } from "@/lib/logger"

export type ReviewState = { error?: string; success?: boolean; message?: string }

/**
 * Submit (or update) a review for a seller account. Authenticated buyers only;
 * cannot review their own account (enforced by RLS too). One review per buyer
 * per seller — re-submitting updates the existing one (upsert on the unique
 * (seller_account_id, reviewer_profile_id) constraint). The seller's aggregate
 * rating/review_count is recomputed by a DB trigger.
 */
export async function submitReviewAction(_prev: ReviewState, formData: FormData): Promise<ReviewState> {
  if (!hasSupabaseEnv()) return { error: "Reviews are not available yet." }

  const supabase = await createSupabaseServerAuthClient()
  const {
    data: { user },
  } = await supabase.auth.getUser()
  if (!user) return { error: "Please sign in to leave a review." }

  const sellerAccountId = String(formData.get("sellerAccountId") ?? "").trim()
  const sellerSlug = String(formData.get("sellerSlug") ?? "").trim()
  const rating = Number(formData.get("rating"))
  const comment = String(formData.get("comment") ?? "").trim() || null

  if (!sellerAccountId) return { error: "Missing seller." }
  if (!Number.isInteger(rating) || rating < 1 || rating > 5) return { error: "Choose a rating from 1 to 5 stars." }

  const { error } = await supabase
    .from("seller_reviews")
    .upsert(
      { seller_account_id: sellerAccountId, reviewer_profile_id: user.id, rating, comment } as never,
      { onConflict: "seller_account_id,reviewer_profile_id" },
    )

  if (error) {
    // RLS blocks reviewing your own account, etc.
    if (/row-level security|violates/i.test(error.message)) {
      return { error: "You can't review your own seller account." }
    }
    logger.error("review_submit_failed", { error: error.message })
    return { error: "Could not save your review. Please try again." }
  }

  if (sellerSlug) {
    revalidatePath(`/sellers/${sellerSlug}`)
    revalidatePath(`/seller/${sellerSlug}`)
  }
  return { success: true, message: "Thanks — your review has been posted." }
}
