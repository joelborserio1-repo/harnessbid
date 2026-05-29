import {
  createSupabaseServerAuthClient,
  hasSupabaseEnv,
} from "@/lib/supabase/auth-server"
import type { Database } from "@/lib/supabase/database.types"

export type OwnedListing = {
  id: string
  kind: "horse" | "marketplace"
  title: string
  slug: string
  status: Database["public"]["Enums"]["listing_status"]
  saleMode: Database["public"]["Enums"]["sale_mode"]
  price: number | null
  image: string | null
  views: number
  watchers: number
  createdAt: string | null
  updatedAt: string | null
}

export type OwnedListingBuckets = {
  active: OwnedListing[]
  drafts: OwnedListing[]
  sold: OwnedListing[]
  pending: OwnedListing[]
  all: OwnedListing[]
}

const EMPTY: OwnedListingBuckets = { active: [], drafts: [], sold: [], pending: [], all: [] }

async function sellerAccountId(
  supabase: Awaited<ReturnType<typeof createSupabaseServerAuthClient>>,
  userId: string,
): Promise<string | null> {
  const { data } = await supabase
    .from("seller_accounts")
    .select("id")
    .eq("owner_profile_id", userId)
    .limit(1)
    .maybeSingle()
  return data?.id ?? null
}

/**
 * Returns the current seller's listings (horse + marketplace) bucketed by
 * status for dashboard management. Reads run as the owner via RLS.
 */
export async function getOwnedListings(): Promise<OwnedListingBuckets> {
  if (!hasSupabaseEnv()) return EMPTY
  const supabase = await createSupabaseServerAuthClient()
  const {
    data: { user },
  } = await supabase.auth.getUser()
  if (!user) return EMPTY

  const accountId = await sellerAccountId(supabase, user.id)
  if (!accountId) return EMPTY

  const [{ data: horses }, { data: marketplace }, { data: images }] = await Promise.all([
    supabase
      .from("horse_listings")
      .select("id, title, slug, status, sale_mode, asking_price, view_count, watcher_count, created_at, updated_at")
      .eq("seller_account_id", accountId)
      .order("updated_at", { ascending: false }),
    supabase
      .from("marketplace_listings")
      .select("id, title, slug, status, sale_mode, price, view_count, watcher_count, created_at, updated_at")
      .eq("seller_account_id", accountId)
      .order("updated_at", { ascending: false }),
    supabase
      .from("listing_images")
      .select("horse_listing_id, marketplace_listing_id, image_url, is_primary, position")
      .order("position", { ascending: true }),
  ])

  const primaryFor = (key: "horse_listing_id" | "marketplace_listing_id", id: string) => {
    const matches = (images ?? []).filter((img) => img[key] === id)
    return matches.find((m) => m.is_primary)?.image_url ?? matches[0]?.image_url ?? null
  }

  const all: OwnedListing[] = [
    ...(horses ?? []).map((h) => ({
      id: h.id,
      kind: "horse" as const,
      title: h.title,
      slug: h.slug,
      status: h.status,
      saleMode: h.sale_mode,
      price: h.asking_price,
      image: primaryFor("horse_listing_id", h.id),
      views: h.view_count,
      watchers: h.watcher_count,
      createdAt: h.created_at,
      updatedAt: h.updated_at,
    })),
    ...(marketplace ?? []).map((m) => ({
      id: m.id,
      kind: "marketplace" as const,
      title: m.title,
      slug: m.slug,
      status: m.status,
      saleMode: m.sale_mode,
      price: m.price,
      image: primaryFor("marketplace_listing_id", m.id),
      views: m.view_count,
      watchers: m.watcher_count,
      createdAt: m.created_at,
      updatedAt: m.updated_at,
    })),
  ].sort((a, b) => (b.updatedAt ?? "").localeCompare(a.updatedAt ?? ""))

  return {
    all,
    active: all.filter((l) => l.status === "published" || l.status === "under_offer"),
    drafts: all.filter((l) => l.status === "draft"),
    pending: all.filter((l) => l.status === "pending_review"),
    sold: all.filter((l) => l.status === "sold" || l.status === "expired" || l.status === "archived"),
  }
}

export type OwnedListingDetail = {
  id: string
  kind: "horse" | "marketplace"
  title: string
  description: string | null
  status: Database["public"]["Enums"]["listing_status"]
  price: number | null
}

/** Loads a single owned listing for editing (RLS owner read). */
export async function getOwnedListing(id: string): Promise<OwnedListingDetail | null> {
  if (!hasSupabaseEnv()) return null
  const supabase = await createSupabaseServerAuthClient()
  const {
    data: { user },
  } = await supabase.auth.getUser()
  if (!user) return null

  const accountId = await sellerAccountId(supabase, user.id)
  if (!accountId) return null

  const { data: horse } = await supabase
    .from("horse_listings")
    .select("id, title, description, status, asking_price")
    .eq("id", id)
    .eq("seller_account_id", accountId)
    .maybeSingle()

  if (horse) {
    return {
      id: horse.id,
      kind: "horse",
      title: horse.title,
      description: horse.description,
      status: horse.status,
      price: horse.asking_price,
    }
  }

  const { data: mkt } = await supabase
    .from("marketplace_listings")
    .select("id, title, description, status, price")
    .eq("id", id)
    .eq("seller_account_id", accountId)
    .maybeSingle()

  if (mkt) {
    return {
      id: mkt.id,
      kind: "marketplace",
      title: mkt.title,
      description: mkt.description,
      status: mkt.status,
      price: mkt.price,
    }
  }

  return null
}
