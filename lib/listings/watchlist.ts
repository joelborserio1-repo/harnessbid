"use server"

import { revalidatePath } from "next/cache"
import {
  createSupabaseServerAuthClient,
  hasSupabaseEnv,
} from "@/lib/supabase/auth-server"

export type WatchKind = "horse" | "marketplace"

const COLUMN: Record<WatchKind, "horse_listing_id" | "marketplace_listing_id"> = {
  horse: "horse_listing_id",
  marketplace: "marketplace_listing_id",
}

export type ToggleWatchlistResult = {
  watched: boolean
  error?: "auth" | "failed"
}

/**
 * Toggles a listing in the current user's watchlist. Authenticated only;
 * unauthenticated callers receive { error: "auth" } so the client can prompt
 * a login. Duplicate rows are prevented by checking for an existing row first.
 * Runs under RLS ("Users manage their watchlist" => profile_id = auth.uid()).
 */
export async function toggleWatchlistAction(
  listingId: string,
  kind: WatchKind,
): Promise<ToggleWatchlistResult> {
  if (!hasSupabaseEnv()) return { watched: false, error: "failed" }
  const supabase = await createSupabaseServerAuthClient()
  const {
    data: { user },
  } = await supabase.auth.getUser()
  if (!user) return { watched: false, error: "auth" }

  const column = COLUMN[kind]

  const { data: existing } = await supabase
    .from("watchlists")
    .select("id")
    .eq("profile_id", user.id)
    .eq(column, listingId)
    .limit(1)
    .maybeSingle()

  if (existing) {
    const { error } = await supabase.from("watchlists").delete().eq("id", existing.id)
    if (error) return { watched: true, error: "failed" }
    revalidatePath("/watchlist")
    return { watched: false }
  }

  const { error } = await supabase
    .from("watchlists")
    .insert({ profile_id: user.id, [column]: listingId } as never)
  if (error) return { watched: false, error: "failed" }

  revalidatePath("/watchlist")
  return { watched: true }
}

export async function isListingWatched(listingId: string, kind: WatchKind): Promise<boolean> {
  if (!hasSupabaseEnv()) return false
  const supabase = await createSupabaseServerAuthClient()
  const {
    data: { user },
  } = await supabase.auth.getUser()
  if (!user) return false

  const { data } = await supabase
    .from("watchlists")
    .select("id")
    .eq("profile_id", user.id)
    .eq(COLUMN[kind], listingId)
    .limit(1)
    .maybeSingle()
  return Boolean(data)
}

/** Returns the set of watched listing ids for the current user (one query). */
export async function getWatchedIds(): Promise<{ horse: string[]; marketplace: string[] }> {
  if (!hasSupabaseEnv()) return { horse: [], marketplace: [] }
  const supabase = await createSupabaseServerAuthClient()
  const {
    data: { user },
  } = await supabase.auth.getUser()
  if (!user) return { horse: [], marketplace: [] }

  const { data } = await supabase
    .from("watchlists")
    .select("horse_listing_id, marketplace_listing_id")
    .eq("profile_id", user.id)

  return {
    horse: (data ?? []).map((r) => r.horse_listing_id).filter((v): v is string => Boolean(v)),
    marketplace: (data ?? [])
      .map((r) => r.marketplace_listing_id)
      .filter((v): v is string => Boolean(v)),
  }
}

export type WatchlistItem = {
  recordId: string
  kind: WatchKind
  title: string
  href: string
  image: string
  price: number | null
  priceLabel: string
  location: string
  seller: string
  badge: string
  endsAt: string | null
}

const PLACEHOLDER = "/placeholder.jpg"

function locationOf(row: {
  location_text: string | null
  city: string | null
  region: string | null
  country_code: string | null
}) {
  if (row.location_text) return row.location_text
  return [row.city, row.region, row.country_code].filter(Boolean).join(", ") || "Location on request"
}

/** Returns the current user's watched horse + marketplace listings for display. */
export async function getUserWatchlist(): Promise<WatchlistItem[]> {
  if (!hasSupabaseEnv()) return []
  const supabase = await createSupabaseServerAuthClient()
  const {
    data: { user },
  } = await supabase.auth.getUser()
  if (!user) return []

  const { data: rows } = await supabase
    .from("watchlists")
    .select("horse_listing_id, marketplace_listing_id, created_at")
    .eq("profile_id", user.id)
    .order("created_at", { ascending: false })

  const horseIds = (rows ?? []).map((r) => r.horse_listing_id).filter((v): v is string => Boolean(v))
  const marketIds = (rows ?? [])
    .map((r) => r.marketplace_listing_id)
    .filter((v): v is string => Boolean(v))

  if (horseIds.length === 0 && marketIds.length === 0) return []

  // .in() with an empty array returns no rows (no error), so we can run these
  // unconditionally and keep the typed results.
  const [horsesRes, marketRes, auctionsRes, imagesRes] = await Promise.all([
    supabase
      .from("horse_listings")
      .select("id, title, slug, sale_mode, asking_price, location_text, city, region, country_code, seller_account_id")
      .in("id", horseIds),
    supabase
      .from("marketplace_listings")
      .select("id, title, slug, price, location_text, city, region, country_code, seller_account_id")
      .in("id", marketIds),
    supabase
      .from("auctions")
      .select("horse_listing_id, current_bid, starting_bid, ends_at")
      .in("horse_listing_id", horseIds),
    supabase
      .from("listing_images")
      .select("horse_listing_id, marketplace_listing_id, image_url, is_primary, position")
      .in("horse_listing_id", horseIds.length ? horseIds : ["00000000-0000-0000-0000-000000000000"]),
  ])

  const horses = horsesRes.data ?? []
  const market = marketRes.data ?? []
  const auctions = auctionsRes.data ?? []

  // Images for both horse and marketplace targets (second query for marketplace).
  const { data: marketImages } = await supabase
    .from("listing_images")
    .select("horse_listing_id, marketplace_listing_id, image_url, is_primary, position")
    .in("marketplace_listing_id", marketIds.length ? marketIds : ["00000000-0000-0000-0000-000000000000"])
  const images = [...(imagesRes.data ?? []), ...(marketImages ?? [])]

  const sellerIds = [...new Set([...horses, ...market].map((r) => r.seller_account_id))]
  const { data: sellersData } = await supabase
    .from("seller_accounts")
    .select("id, display_name")
    .in("id", sellerIds.length ? sellerIds : ["00000000-0000-0000-0000-000000000000"])
  const sellerName = new Map((sellersData ?? []).map((s) => [s.id, s.display_name]))

  const imageFor = (key: "horse_listing_id" | "marketplace_listing_id", id: string) => {
    const matches = images.filter((img) => img[key] === id)
    const primary = matches.find((m) => m.is_primary) ?? matches[0]
    return primary?.image_url ?? PLACEHOLDER
  }

  const items: WatchlistItem[] = []

  for (const h of horses) {
    const auction = auctions.find((a) => a.horse_listing_id === h.id)
    const isAuction = h.sale_mode === "auction" && Boolean(auction)
    const price = isAuction ? auction?.current_bid ?? auction?.starting_bid ?? null : h.asking_price
    items.push({
      recordId: h.id,
      kind: "horse",
      title: h.title,
      href: isAuction ? `/auctions/${h.slug}` : `/horses/buy-now/${h.slug}`,
      image: imageFor("horse_listing_id", h.id),
      price,
      priceLabel: isAuction ? "Current bid" : "Buy now",
      location: locationOf(h),
      seller: sellerName.get(h.seller_account_id) ?? "Verified seller",
      badge: isAuction ? "Horse auction" : "Buy now horse",
      endsAt: isAuction ? auction?.ends_at ?? null : null,
    })
  }

  for (const m of market) {
    items.push({
      recordId: m.id,
      kind: "marketplace",
      title: m.title,
      href: `/marketplace/${m.slug}`,
      image: imageFor("marketplace_listing_id", m.id),
      price: m.price,
      priceLabel: "Price",
      location: locationOf(m),
      seller: sellerName.get(m.seller_account_id) ?? "Verified seller",
      badge: "Marketplace",
      endsAt: null,
    })
  }

  return items
}
