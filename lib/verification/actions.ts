"use server"

import { revalidatePath } from "next/cache"
import { createSupabaseServerAuthClient, hasSupabaseEnv, getOwnedSellerAccount } from "@/lib/supabase/auth-server"
import { requireStaff, logModeration } from "@/lib/admin/guard"
import { createSupabaseServiceClient, hasServiceRoleEnv } from "@/lib/supabase/admin"
import { logger } from "@/lib/logger"

export type VerificationState = { error?: string; success?: boolean; message?: string }

const DOC_TYPES = ["drivers_license", "passport", "national_id", "other"] as const

/**
 * Seller submits ID documents for verification. The files are uploaded to the
 * private `seller-id-documents` bucket client-side (under the user's own uid
 * prefix, enforced by storage RLS); this action records the storage paths and
 * moves the seller account to `pending` review.
 */
export async function submitVerificationAction(
  _prev: VerificationState,
  formData: FormData,
): Promise<VerificationState> {
  if (!hasSupabaseEnv()) return { error: "Verification is not available yet." }

  const supabase = await createSupabaseServerAuthClient()
  const {
    data: { user },
  } = await supabase.auth.getUser()
  if (!user) return { error: "Please sign in to verify your account." }

  const seller = await getOwnedSellerAccount()
  if (!seller) return { error: "Create a seller account before requesting verification." }

  const docType = String(formData.get("docType") ?? "drivers_license")
  const fullLegalName = String(formData.get("fullLegalName") ?? "").trim()
  const frontPath = String(formData.get("frontPath") ?? "").trim()
  const backPath = String(formData.get("backPath") ?? "").trim()

  if (!DOC_TYPES.includes(docType as (typeof DOC_TYPES)[number])) {
    return { error: "Choose a valid document type." }
  }
  if (!fullLegalName) return { error: "Enter your full legal name as it appears on the ID." }
  if (!frontPath) return { error: "Upload the front of your ID." }
  // Passports are single-page; back optional for that type.
  if (docType !== "passport" && !backPath) return { error: "Upload the back of your ID." }

  // Paths must live under the user's own prefix (defense-in-depth vs. RLS).
  if (!frontPath.startsWith(`${user.id}/`) || (backPath && !backPath.startsWith(`${user.id}/`))) {
    return { error: "Invalid upload reference. Please re-upload your documents." }
  }

  const { error: insErr } = await supabase.from("verification_documents").insert({
    seller_account_id: seller.id,
    profile_id: user.id,
    doc_type: docType,
    full_legal_name: fullLegalName,
    front_path: frontPath,
    back_path: backPath || null,
    status: "pending",
  } as never)
  if (insErr) {
    logger.error("verification_submit_failed", { error: insErr.message })
    return { error: "Could not submit your documents. Please try again." }
  }

  // Move the seller account into pending review.
  await supabase
    .from("seller_accounts")
    .update({ verification_status: "pending" } as never)
    .eq("id", seller.id)

  revalidatePath("/account")
  revalidatePath("/dashboard")
  return { success: true, message: "Documents submitted. We'll review your verification shortly." }
}

/* --- Admin review ---------------------------------------------------------- */

/** Generate short-lived signed URLs for staff to view a verification's docs. */
export async function getVerificationDocUrls(
  frontPath: string,
  backPath: string | null,
): Promise<{ front?: string; back?: string }> {
  await requireStaff()
  if (!hasServiceRoleEnv()) return {}
  const svc = createSupabaseServiceClient()
  const out: { front?: string; back?: string } = {}
  const f = await svc.storage.from("seller-id-documents").createSignedUrl(frontPath, 300)
  if (f.data?.signedUrl) out.front = f.data.signedUrl
  if (backPath) {
    const b = await svc.storage.from("seller-id-documents").createSignedUrl(backPath, 300)
    if (b.data?.signedUrl) out.back = b.data.signedUrl
  }
  return out
}

/** Staff approve or reject a verification request. */
export async function reviewVerificationAction(formData: FormData): Promise<void> {
  const ctx = await requireStaff()
  const id = String(formData.get("id") ?? "")
  const action = String(formData.get("action") ?? "")
  const notes = String(formData.get("notes") ?? "").trim() || null
  if (!id || !["approve", "reject"].includes(action)) return

  const svc = createSupabaseServiceClient()

  const { data: rec } = await svc
    .from("verification_documents")
    .select("id, seller_account_id")
    .eq("id", id)
    .maybeSingle()
  if (!rec) return

  const newStatus = action === "approve" ? "verified" : "rejected"

  await svc
    .from("verification_documents")
    .update({
      status: newStatus,
      review_notes: notes,
      reviewed_by: ctx.userId,
      reviewed_at: new Date().toISOString(),
    } as never)
    .eq("id", id)

  await svc
    .from("seller_accounts")
    .update({
      verification_status: newStatus,
      verified_at: action === "approve" ? new Date().toISOString() : null,
    } as never)
    .eq("id", (rec as { seller_account_id: string }).seller_account_id)

  await logModeration(`verification_${action}`, "seller_account", (rec as { seller_account_id: string }).seller_account_id)
  revalidatePath("/admin/verifications")
}
