"use server"

import { revalidatePath } from "next/cache"
import {
  createSupabaseServerAuthClient,
  hasSupabaseEnv,
} from "@/lib/supabase/auth-server"
import { logModeration, requireStaff } from "@/lib/admin/guard"
import { slugCandidates } from "@/lib/seller/slug"

export type AdminActionState = { error?: string; success?: boolean; message?: string }

const LISTING_STATUS: Record<string, string> = {
  approve: "published",
  reject: "rejected",
  archive: "archived",
  unpublish: "paused",
  sold: "sold",
}

/** Approve / reject / archive / unpublish / mark-sold a listing. */
export async function setListingStatusAction(formData: FormData): Promise<void> {
  await requireStaff()
  if (!hasSupabaseEnv()) return
  const supabase = await createSupabaseServerAuthClient()

  const kind = String(formData.get("kind") ?? "")
  const id = String(formData.get("id") ?? "")
  const action = String(formData.get("action") ?? "")
  const status = LISTING_STATUS[action]
  if (!id || (kind !== "horse" && kind !== "marketplace") || !status) return

  const table = kind === "horse" ? "horse_listings" : "marketplace_listings"
  const patch: Record<string, unknown> = { status }
  if (action === "approve") patch.published_at = new Date().toISOString()
  if (action === "sold") patch.sold_at = new Date().toISOString()

  await supabase.from(table).update(patch as never).eq("id", id)
  await logModeration(`listing_${action}`, kind === "horse" ? "horse_listing" : "marketplace_listing", id)
  revalidatePath("/admin/listings")
  revalidatePath("/admin")
}

/** Feature / unfeature a listing (30-day window placeholder). */
export async function setListingFeaturedAction(formData: FormData): Promise<void> {
  await requireStaff()
  if (!hasSupabaseEnv()) return
  const supabase = await createSupabaseServerAuthClient()

  const kind = String(formData.get("kind") ?? "")
  const id = String(formData.get("id") ?? "")
  const featured = String(formData.get("featured") ?? "") === "true"
  if (!id || (kind !== "horse" && kind !== "marketplace")) return

  const until = featured ? new Date(Date.now() + 30 * 86_400_000).toISOString() : null

  if (kind === "marketplace") {
    await supabase.from("marketplace_listings").update({ featured_until: until } as never).eq("id", id)
  } else {
    // horse_listings has no featured_until column; store on metadata.
    const { data } = await supabase.from("horse_listings").select("metadata").eq("id", id).maybeSingle()
    const metadata = { ...((data?.metadata ?? {}) as Record<string, unknown>), featured_until: until }
    await supabase.from("horse_listings").update({ metadata } as never).eq("id", id)
  }
  await logModeration(featured ? "listing_feature" : "listing_unfeature", kind, id)
  revalidatePath("/admin/listings")
}

/** Approve / reject an enterprise seller application. */
export async function setEnterpriseStatusAction(formData: FormData): Promise<void> {
  await requireStaff()
  if (!hasSupabaseEnv()) return
  const supabase = await createSupabaseServerAuthClient()

  const id = String(formData.get("id") ?? "")
  const sellerAccountId = String(formData.get("sellerAccountId") ?? "")
  const action = String(formData.get("action") ?? "")
  if (!id || !sellerAccountId || (action !== "approve" && action !== "reject")) return

  if (action === "approve") {
    await supabase.from("enterprise_sellers").update({ onboarding_status: "active" } as never).eq("id", id)
    await supabase
      .from("seller_accounts")
      .update({ verification_status: "verified", verified_at: new Date().toISOString() } as never)
      .eq("id", sellerAccountId)
  } else {
    await supabase.from("enterprise_sellers").update({ onboarding_status: "offboarded" } as never).eq("id", id)
    await supabase
      .from("seller_accounts")
      .update({ verification_status: "rejected" } as never)
      .eq("id", sellerAccountId)
  }
  await logModeration(`enterprise_${action}`, "enterprise_seller", id)
  revalidatePath("/admin/enterprise")
  revalidatePath("/admin")
}

/** Update a report's review status. */
export async function updateReportStatusAction(formData: FormData): Promise<void> {
  const ctx = await requireStaff()
  if (!hasSupabaseEnv()) return
  const supabase = await createSupabaseServerAuthClient()

  const id = String(formData.get("id") ?? "")
  const status = String(formData.get("status") ?? "")
  if (!id || !["open", "reviewing", "actioned", "dismissed"].includes(status)) return

  await supabase
    .from("listing_reports")
    .update({ status, reviewer_profile_id: ctx.userId, reviewed_at: new Date().toISOString() } as never)
    .eq("id", id)
  await logModeration(`report_${status}`, "listing_report", id)
  revalidatePath("/admin/reports")
  revalidatePath("/admin")
}

/** Update a sale event's status. */
export async function updateSaleEventStatusAction(formData: FormData): Promise<void> {
  await requireStaff()
  if (!hasSupabaseEnv()) return
  const supabase = await createSupabaseServerAuthClient()
  const id = String(formData.get("id") ?? "")
  const status = String(formData.get("status") ?? "")
  if (!id || !["draft", "scheduled", "live", "closed", "settled", "cancelled", "archived"].includes(status)) return
  await supabase.from("sale_events").update({ status } as never).eq("id", id)
  await logModeration("sale_event_status", "sale_event", id, status)
  revalidatePath("/admin/sale-events")
}

/** Create a sale event (admin catalogue events: APG, Nutrien, enterprise). */
export async function createSaleEventAction(
  _prev: AdminActionState,
  formData: FormData,
): Promise<AdminActionState> {
  await requireStaff()
  if (!hasSupabaseEnv()) return { error: "Not configured." }
  const supabase = await createSupabaseServerAuthClient()

  const name = String(formData.get("name") ?? "").trim()
  const eventType = String(formData.get("eventType") ?? "online_auction")
  if (!name) return { error: "Enter an event name." }

  let lastError: string | null = null
  for (const slug of slugCandidates(name, 4)) {
    const { error } = await supabase
      .from("sale_events")
      .insert({ name, slug, event_type: eventType, status: "scheduled" } as never)
    if (!error) {
      await logModeration("sale_event_create", "sale_event", null, name)
      revalidatePath("/admin/sale-events")
      return { success: true, message: "Sale event created." }
    }
    lastError = error.message
    if (error.code !== "23505") break
  }
  return { error: lastError ?? "Could not create the sale event." }
}

/** Feature / order / banner controls for a sale event. */
export async function updateSaleEventAdminAction(formData: FormData): Promise<void> {
  await requireStaff()
  if (!hasSupabaseEnv()) return
  const supabase = await createSupabaseServerAuthClient()

  const id = String(formData.get("id") ?? "")
  const field = String(formData.get("field") ?? "")
  const value = String(formData.get("value") ?? "")
  if (!id) return

  const patch: Record<string, unknown> = {}
  if (field === "featured") patch.featured = value === "true"
  else if (field === "sort_order") {
    const n = Number(value)
    if (Number.isFinite(n)) patch.sort_order = n
    else return
  } else if (field === "hero_image_url") patch.hero_image_url = value || null
  else return

  await supabase.from("sale_events").update(patch as never).eq("id", id)
  await logModeration("sale_event_admin", "sale_event", id, `${field}=${value}`)
  revalidatePath("/admin/sale-events")
}

/** Assign or unassign a listing to a sale event, with an optional lot number. */
export async function assignListingToEventAction(formData: FormData): Promise<void> {
  await requireStaff()
  if (!hasSupabaseEnv()) return
  const supabase = await createSupabaseServerAuthClient()

  const kind = String(formData.get("kind") ?? "")
  const listingId = String(formData.get("listingId") ?? "").trim()
  const eventId = String(formData.get("eventId") ?? "").trim()
  const lotNumber = String(formData.get("lotNumber") ?? "").trim()
  if (!listingId || (kind !== "horse" && kind !== "marketplace")) return

  const table = kind === "horse" ? "horse_listings" : "marketplace_listings"
  await supabase
    .from(table)
    .update({ sale_event_id: eventId || null, lot_number: lotNumber || null } as never)
    .eq("id", listingId)
  await logModeration(eventId ? "listing_assign_event" : "listing_unassign_event", kind, listingId, eventId)
  revalidatePath("/admin/sale-events")
}

/** Set a seller's billing model / fee exemption (commercial controls). */
export async function setSellerBillingAction(formData: FormData): Promise<void> {
  await requireStaff()
  if (!hasSupabaseEnv()) return
  const supabase = await createSupabaseServerAuthClient()

  const sellerAccountId = String(formData.get("sellerAccountId") ?? "")
  const field = String(formData.get("field") ?? "")
  const value = String(formData.get("value") ?? "")
  if (!sellerAccountId) return

  const patch: Record<string, unknown> = {}
  if (field === "fee_exempt") patch.fee_exempt = value === "true"
  else if (field === "billing_mode" && ["per_listing", "invoiced", "exempt"].includes(value)) {
    patch.billing_mode = value
  } else return

  await supabase.from("seller_accounts").update(patch as never).eq("id", sellerAccountId)
  await logModeration("seller_billing_update", "seller_account", sellerAccountId, `${field}=${value}`)
  revalidatePath("/admin/enterprise")
}

/** Toggle category visibility / featured flag, or set sort order. */
export async function updateCategoryAction(formData: FormData): Promise<void> {
  await requireStaff()
  if (!hasSupabaseEnv()) return
  const supabase = await createSupabaseServerAuthClient()

  const id = String(formData.get("id") ?? "")
  const field = String(formData.get("field") ?? "")
  const value = String(formData.get("value") ?? "")
  if (!id) return

  if (field === "is_active") {
    await supabase.from("categories").update({ is_active: value === "true" } as never).eq("id", id)
  } else if (field === "sort_order") {
    const n = Number(value)
    if (Number.isFinite(n)) await supabase.from("categories").update({ sort_order: n } as never).eq("id", id)
  } else if (field === "featured") {
    const { data } = await supabase.from("categories").select("metadata").eq("id", id).maybeSingle()
    const metadata = { ...((data?.metadata ?? {}) as Record<string, unknown>), featured: value === "true" }
    await supabase.from("categories").update({ metadata } as never).eq("id", id)
  } else {
    return
  }
  await logModeration("category_update", "category", id, `${field}=${value}`)
  revalidatePath("/admin/categories")
}
