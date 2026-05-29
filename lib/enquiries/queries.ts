import {
  createSupabaseServerAuthClient,
  hasSupabaseEnv,
} from "@/lib/supabase/auth-server"
import type { Database } from "@/lib/supabase/database.types"

/** True when the signed-in user owns the given seller account. */
export async function viewerOwnsSeller(sellerAccountId: string): Promise<boolean> {
  if (!hasSupabaseEnv() || !sellerAccountId) return false
  const supabase = await createSupabaseServerAuthClient()
  const {
    data: { user },
  } = await supabase.auth.getUser()
  if (!user) return false
  const { data } = await supabase
    .from("seller_accounts")
    .select("id")
    .eq("id", sellerAccountId)
    .eq("owner_profile_id", user.id)
    .maybeSingle()
  return Boolean(data)
}

/** True when the signed-in user owns the listing (via its seller account). */
export async function viewerOwnsListing(
  kind: "horse" | "marketplace",
  listingId: string,
): Promise<boolean> {
  if (!hasSupabaseEnv() || !listingId) return false
  const supabase = await createSupabaseServerAuthClient()
  const {
    data: { user },
  } = await supabase.auth.getUser()
  if (!user) return false

  const table = kind === "horse" ? "horse_listings" : "marketplace_listings"
  const { data: listing } = await supabase
    .from(table)
    .select("seller_account_id")
    .eq("id", listingId)
    .maybeSingle()
  if (!listing) return false

  const { data: account } = await supabase
    .from("seller_accounts")
    .select("id")
    .eq("id", listing.seller_account_id)
    .eq("owner_profile_id", user.id)
    .maybeSingle()
  return Boolean(account)
}

export type EnquiryView = {
  id: string
  direction: "received" | "sent"
  listingTitle: string
  message: string
  status: Database["public"]["Enums"]["enquiry_status"]
  createdAt: string
  counterpart: string
  contactEmail: string | null
  contactPhone: string | null
}

export type EnquiryInbox = {
  received: EnquiryView[]
  sent: EnquiryView[]
}

const EMPTY: EnquiryInbox = { received: [], sent: [] }

type EnquiryRow = {
  id: string
  sender_profile_id: string | null
  seller_account_id: string
  horse_listing_id: string | null
  marketplace_listing_id: string | null
  subject: string | null
  message: string
  status: Database["public"]["Enums"]["enquiry_status"]
  contact_email: string | null
  contact_phone: string | null
  created_at: string
}

/**
 * Returns the current user's enquiry inbox: enquiries received as a seller and
 * enquiries sent as a buyer. Reads run under RLS (participants only).
 */
export async function getUserEnquiries(): Promise<EnquiryInbox> {
  if (!hasSupabaseEnv()) return EMPTY
  const supabase = await createSupabaseServerAuthClient()
  const {
    data: { user },
  } = await supabase.auth.getUser()
  if (!user) return EMPTY

  const { data: sellerAccount } = await supabase
    .from("seller_accounts")
    .select("id")
    .eq("owner_profile_id", user.id)
    .limit(1)
    .maybeSingle()

  const [sentRes, receivedRes] = await Promise.all([
    supabase
      .from("enquiries")
      .select(
        "id, sender_profile_id, seller_account_id, horse_listing_id, marketplace_listing_id, subject, message, status, contact_email, contact_phone, created_at",
      )
      .eq("sender_profile_id", user.id)
      .order("created_at", { ascending: false }),
    sellerAccount
      ? supabase
          .from("enquiries")
          .select(
            "id, sender_profile_id, seller_account_id, horse_listing_id, marketplace_listing_id, subject, message, status, contact_email, contact_phone, created_at",
          )
          .eq("seller_account_id", sellerAccount.id)
          .order("created_at", { ascending: false })
      : Promise.resolve({ data: [] as EnquiryRow[] }),
  ])

  const sent = (sentRes.data ?? []) as EnquiryRow[]
  const received = (receivedRes.data ?? []) as EnquiryRow[]
  const all = [...sent, ...received]
  if (all.length === 0) return EMPTY

  // Resolve listing titles and counterpart names in batch.
  const horseIds = [...new Set(all.map((e) => e.horse_listing_id).filter(Boolean) as string[])]
  const marketIds = [...new Set(all.map((e) => e.marketplace_listing_id).filter(Boolean) as string[])]
  const senderIds = [...new Set(received.map((e) => e.sender_profile_id).filter(Boolean) as string[])]
  const sellerIds = [...new Set(sent.map((e) => e.seller_account_id))]

  const [horsesRes, marketRes, sendersRes, sellersRes] = await Promise.all([
    horseIds.length
      ? supabase.from("horse_listings").select("id, title").in("id", horseIds)
      : Promise.resolve({ data: [] as Array<{ id: string; title: string }> }),
    marketIds.length
      ? supabase.from("marketplace_listings").select("id, title").in("id", marketIds)
      : Promise.resolve({ data: [] as Array<{ id: string; title: string }> }),
    senderIds.length
      ? supabase.from("profiles").select("id, display_name, full_name").in("id", senderIds)
      : Promise.resolve({ data: [] as Array<{ id: string; display_name: string | null; full_name: string | null }> }),
    sellerIds.length
      ? supabase.from("seller_accounts").select("id, display_name").in("id", sellerIds)
      : Promise.resolve({ data: [] as Array<{ id: string; display_name: string }> }),
  ])

  const titleFor = (e: EnquiryRow) => {
    if (e.horse_listing_id) {
      return (horsesRes.data ?? []).find((h) => h.id === e.horse_listing_id)?.title ?? "Horse listing"
    }
    if (e.marketplace_listing_id) {
      return (
        (marketRes.data ?? []).find((m) => m.id === e.marketplace_listing_id)?.title ??
        "Marketplace listing"
      )
    }
    return e.subject ?? "Enquiry"
  }

  const senderName = new Map(
    (sendersRes.data ?? []).map((p) => [p.id, p.display_name || p.full_name || "Buyer"]),
  )
  const sellerName = new Map((sellersRes.data ?? []).map((s) => [s.id, s.display_name]))

  const toView = (e: EnquiryRow, direction: "received" | "sent"): EnquiryView => ({
    id: e.id,
    direction,
    listingTitle: titleFor(e),
    message: e.message,
    status: e.status,
    createdAt: e.created_at,
    counterpart:
      direction === "received"
        ? senderName.get(e.sender_profile_id ?? "") ?? "Buyer"
        : sellerName.get(e.seller_account_id) ?? "Seller",
    contactEmail: e.contact_email,
    contactPhone: e.contact_phone,
  })

  return {
    received: received.map((e) => toView(e, "received")),
    sent: sent.map((e) => toView(e, "sent")),
  }
}
