"use server"

import {
  createSupabaseServerAuthClient,
  hasSupabaseEnv,
} from "@/lib/supabase/auth-server"

export type ReportActionState = { error?: string; success?: boolean; message?: string }

/** Lets an authenticated user report a listing for moderation review. */
export async function createReportAction(
  _prev: ReportActionState,
  formData: FormData,
): Promise<ReportActionState> {
  if (!hasSupabaseEnv()) return { error: "Reporting is not available yet." }
  const supabase = await createSupabaseServerAuthClient()
  const {
    data: { user },
  } = await supabase.auth.getUser()
  if (!user) return { error: "Please sign in to report a listing." }

  const kind = String(formData.get("kind") ?? "")
  const listingId = String(formData.get("listingId") ?? "")
  const reason = String(formData.get("reason") ?? "").trim()
  const details = String(formData.get("details") ?? "").trim()

  if (!listingId || (kind !== "horse" && kind !== "marketplace")) {
    return { error: "This listing can't be reported right now." }
  }
  if (!reason) return { error: "Choose a reason." }

  const { error } = await supabase.from("listing_reports").insert({
    reporter_profile_id: user.id,
    horse_listing_id: kind === "horse" ? listingId : null,
    marketplace_listing_id: kind === "marketplace" ? listingId : null,
    reason,
    details: details || null,
  })
  if (error) return { error: "Could not submit your report. Please try again." }

  return { success: true, message: "Thanks — our team will review this listing." }
}
