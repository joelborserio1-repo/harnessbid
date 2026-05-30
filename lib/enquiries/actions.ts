"use server"

import { revalidatePath } from "next/cache"
import {
  createSupabaseServerAuthClient,
  hasSupabaseEnv,
} from "@/lib/supabase/auth-server"
import type { ActionState } from "@/lib/auth/actions"

const EMAIL_PATTERN = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
const NOT_CONFIGURED: ActionState = {
  error: "Enquiries are not available yet. Please try again later.",
}

type Kind = "horse" | "marketplace"

/**
 * Creates an enquiry to a seller about a specific listing. Authenticated only.
 * Resolves the seller account from the listing, blocks self-enquiries, and
 * inserts under RLS (sender_profile_id = auth.uid()).
 *
 * TODO(rate-limit): add per-user/per-listing throttling before launch.
 */
export async function createEnquiryAction(
  _prev: ActionState,
  formData: FormData,
): Promise<ActionState> {
  if (!hasSupabaseEnv()) return NOT_CONFIGURED

  const supabase = await createSupabaseServerAuthClient()
  const {
    data: { user },
  } = await supabase.auth.getUser()
  if (!user) return { error: "Please sign in to contact the seller." }

  const listingId = String(formData.get("listingId") ?? "").trim()
  const kind = String(formData.get("kind") ?? "") as Kind
  const message = String(formData.get("message") ?? "").trim()
  const contactName = String(formData.get("contactName") ?? "").trim()
  const contactEmail = String(formData.get("contactEmail") ?? "").trim()
  const contactPhone = String(formData.get("contactPhone") ?? "").trim()
  const contactPreference = String(formData.get("contactPreference") ?? "either")

  if (!listingId || (kind !== "horse" && kind !== "marketplace")) {
    return { error: "This listing can't receive enquiries right now." }
  }
  if (message.length < 10) {
    return { error: "Enter a message of at least 10 characters." }
  }
  if (contactEmail && !EMAIL_PATTERN.test(contactEmail)) {
    return { error: "Enter a valid contact email address." }
  }

  const table = kind === "horse" ? "horse_listings" : "marketplace_listings"
  const { data: listing } = await supabase
    .from(table)
    .select("id, title, seller_account_id")
    .eq("id", listingId)
    .maybeSingle()

  if (!listing) {
    return { error: "This listing is no longer available." }
  }

  // Self-enquiry guard: if the current user owns the seller account, block.
  const { data: ownAccount } = await supabase
    .from("seller_accounts")
    .select("id")
    .eq("id", listing.seller_account_id)
    .eq("owner_profile_id", user.id)
    .maybeSingle()
  if (ownAccount) {
    return { error: "You can't send an enquiry on your own listing." }
  }

  // Conservative anti-spam rate limit (placeholder; a durable solution would
  // use a counter table or edge rate limiter):
  //  - block a repeat enquiry to the same listing within 60 seconds
  //  - cap enquiries to the same seller at 5 per rolling hour
  const listingColumn = kind === "horse" ? "horse_listing_id" : "marketplace_listing_id"
  const sixtySecondsAgo = new Date(Date.now() - 60_000).toISOString()
  const oneHourAgo = new Date(Date.now() - 3_600_000).toISOString()

  const { data: recentSameListing } = await supabase
    .from("enquiries")
    .select("id")
    .eq("sender_profile_id", user.id)
    .eq(listingColumn, listingId)
    .gte("created_at", sixtySecondsAgo)
    .limit(1)
    .maybeSingle()
  if (recentSameListing) {
    return { error: "You just sent an enquiry about this listing. Please wait a moment before sending another." }
  }

  const { count: sellerHourCount } = await supabase
    .from("enquiries")
    .select("id", { count: "exact", head: true })
    .eq("sender_profile_id", user.id)
    .eq("seller_account_id", listing.seller_account_id)
    .gte("created_at", oneHourAgo)
  if ((sellerHourCount ?? 0) >= 5) {
    return { error: "You've sent several enquiries to this seller recently. Please try again later." }
  }

  const { error } = await supabase.from("enquiries").insert({
    sender_profile_id: user.id,
    seller_account_id: listing.seller_account_id,
    horse_listing_id: kind === "horse" ? listingId : null,
    marketplace_listing_id: kind === "marketplace" ? listingId : null,
    subject: `Enquiry: ${listing.title}`,
    message,
    contact_name: contactName || null,
    contact_email: contactEmail || null,
    contact_phone: contactPhone || null,
    metadata: { contact_preference: contactPreference },
  })

  if (error) {
    return { error: "Could not send your enquiry. Please try again." }
  }

  // The seller's "enquiry_received" notification is created by the
  // `notify_seller_of_enquiry` SECURITY DEFINER trigger (one per enquiry),
  // so no app-side notification insert is needed here (avoids duplicates).

  revalidatePath("/dashboard/messages")
  return { success: true, message: "Your enquiry has been sent to the seller." }
}

const ALLOWED_STATUS = ["open", "replied", "closed", "archived"] as const

export async function updateEnquiryStatusAction(
  _prev: ActionState,
  formData: FormData,
): Promise<ActionState> {
  if (!hasSupabaseEnv()) return NOT_CONFIGURED

  const supabase = await createSupabaseServerAuthClient()
  const {
    data: { user },
  } = await supabase.auth.getUser()
  if (!user) return { error: "Please sign in." }

  const id = String(formData.get("id") ?? "")
  const status = String(formData.get("status") ?? "")
  if (!id || !ALLOWED_STATUS.includes(status as (typeof ALLOWED_STATUS)[number])) {
    return { error: "Invalid enquiry update." }
  }

  // RLS restricts updates to enquiry participants (sender or seller owner).
  const { error } = await supabase
    .from("enquiries")
    .update({ status: status as never, replied_at: status === "replied" ? new Date().toISOString() : null })
    .eq("id", id)

  if (error) return { error: "Could not update the enquiry." }

  revalidatePath("/dashboard/messages")
  return { success: true }
}
