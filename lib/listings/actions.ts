"use server"

import { revalidatePath } from "next/cache"
import { redirect } from "next/navigation"
import { createSupabaseServerAuthClient, hasSupabaseEnv } from "@/lib/supabase/auth-server"
import { slugCandidates } from "@/lib/seller/slug"
import type { ActionState } from "@/lib/auth/actions"

const NOT_CONFIGURED: ActionState = {
  error: "Listings are not available yet. Please try again later.",
}
const MAX_IMAGES = 8

type UploadedImage = { path: string; url: string }

type SellerCtx = {
  supabase: Awaited<ReturnType<typeof createSupabaseServerAuthClient>>
  userId: string
  sellerAccountId: string
}

async function requireSeller(): Promise<SellerCtx | { error: string }> {
  const supabase = await createSupabaseServerAuthClient()
  const {
    data: { user },
  } = await supabase.auth.getUser()
  if (!user) return { error: "You must be signed in to create a listing." }

  const { data } = await supabase
    .from("seller_accounts")
    .select("id")
    .eq("owner_profile_id", user.id)
    .limit(1)
    .maybeSingle()
  if (!data) return { error: "Complete seller onboarding before creating a listing." }

  return { supabase, userId: user.id, sellerAccountId: data.id }
}

function parseImages(raw: FormDataEntryValue | null): UploadedImage[] {
  if (typeof raw !== "string" || !raw.trim()) return []
  try {
    const parsed = JSON.parse(raw)
    if (!Array.isArray(parsed)) return []
    return parsed
      .filter((i) => i && typeof i.path === "string" && typeof i.url === "string")
      .slice(0, MAX_IMAGES)
      .map((i) => ({ path: i.path, url: i.url }))
  } catch {
    return []
  }
}

async function insertImages(
  ctx: SellerCtx,
  target: "horse_listing_id" | "marketplace_listing_id",
  listingId: string,
  images: UploadedImage[],
) {
  if (images.length === 0) return
  const rows = images.map((img, index) => ({
    horse_listing_id: target === "horse_listing_id" ? listingId : null,
    marketplace_listing_id: target === "marketplace_listing_id" ? listingId : null,
    storage_bucket: "listing-images",
    storage_path: img.path,
    image_url: img.url,
    position: index,
    is_primary: index === 0,
  }))
  await ctx.supabase.from("listing_images").insert(rows)
}

function statusFromIntent(formData: FormData) {
  return String(formData.get("intent") ?? "draft") === "publish"
    ? ("pending_review" as const) // moderation placeholder; real publish is admin-gated
    : ("draft" as const)
}

function num(value: FormDataEntryValue | null): number | null {
  const n = Number(String(value ?? "").replace(/[^0-9.]/g, ""))
  return Number.isFinite(n) && n > 0 ? n : null
}

// --- Horse auction ---------------------------------------------------------

export async function createHorseAuctionAction(
  _prev: ActionState,
  formData: FormData,
): Promise<ActionState> {
  if (!hasSupabaseEnv()) return NOT_CONFIGURED
  const ctx = await requireSeller()
  if ("error" in ctx) return ctx

  const status = statusFromIntent(formData)
  const title = String(formData.get("title") ?? "").trim()
  const startingBid = num(formData.get("startingBid"))
  const reserve = num(formData.get("reservePrice"))
  const increment = num(formData.get("bidIncrement")) ?? 100
  const endRaw = String(formData.get("auctionEnd") ?? "")

  if (!title) return { error: "Enter the horse name." }
  if (!startingBid) return { error: "Enter a valid starting bid." }
  if (reserve && reserve < startingBid) {
    return { error: "Reserve price must be greater than or equal to the starting bid." }
  }
  const endsAt = endRaw ? new Date(endRaw) : null
  if (!endsAt || Number.isNaN(endsAt.getTime())) return { error: "Choose a valid auction end date." }
  if (endsAt.getTime() <= Date.now()) return { error: "Auction end date must be in the future." }

  const images = parseImages(formData.get("images"))
  const horseId = await insertHorse(ctx, formData, { saleMode: "auction", status, price: null })
  if (typeof horseId !== "string") return horseId

  await insertImages(ctx, "horse_listing_id", horseId, images)

  const { error: auctionError } = await ctx.supabase.from("auctions").insert({
    horse_listing_id: horseId,
    status: status === "draft" ? "draft" : "scheduled",
    starts_at: new Date().toISOString(),
    ends_at: endsAt.toISOString(),
    starting_bid: startingBid,
    reserve_price: reserve,
    bid_increment: increment,
  })
  if (auctionError) return { error: `Listing saved, but the auction could not be created: ${auctionError.message}` }

  revalidatePath("/dashboard/listings")
  redirect("/dashboard/listings")
}

// --- Buy now horse ---------------------------------------------------------

export async function createBuyNowHorseAction(
  _prev: ActionState,
  formData: FormData,
): Promise<ActionState> {
  if (!hasSupabaseEnv()) return NOT_CONFIGURED
  const ctx = await requireSeller()
  if ("error" in ctx) return ctx

  const status = statusFromIntent(formData)
  const title = String(formData.get("title") ?? "").trim()
  const price = num(formData.get("price"))

  if (!title) return { error: "Enter the horse name." }
  if (!price) return { error: "Enter a valid fixed price." }

  const images = parseImages(formData.get("images"))
  const horseId = await insertHorse(ctx, formData, { saleMode: "buy_now", status, price })
  if (typeof horseId !== "string") return horseId

  await insertImages(ctx, "horse_listing_id", horseId, images)

  revalidatePath("/dashboard/listings")
  redirect("/dashboard/listings")
}

async function insertHorse(
  ctx: SellerCtx,
  formData: FormData,
  opts: { saleMode: "auction" | "buy_now"; status: "draft" | "pending_review"; price: number | null },
): Promise<string | ActionState> {
  const title = String(formData.get("title") ?? "").trim()
  const breed = String(formData.get("breed") ?? "").trim() || "Standardbred"
  const sex = String(formData.get("sex") ?? "unknown")
  const gait = String(formData.get("gait") ?? "unknown")
  const ageYears = num(formData.get("ageYears"))
  const base = {
    seller_account_id: ctx.sellerAccountId,
    title,
    status: opts.status,
    sale_mode: opts.saleMode,
    currency: "USD",
    asking_price: opts.price,
    short_description: String(formData.get("shortDescription") ?? "").trim() || null,
    description: String(formData.get("description") ?? "").trim() || null,
    location_text: String(formData.get("location") ?? "").trim() || null,
    breed,
    sex: sex as never,
    gait: gait as never,
    age_years: ageYears,
    color: String(formData.get("color") ?? "").trim() || null,
    sire: String(formData.get("sire") ?? "").trim() || null,
    dam: String(formData.get("dam") ?? "").trim() || null,
    dam_sire: String(formData.get("damSire") ?? "").trim() || null,
    best_mile: String(formData.get("bestMile") ?? "").trim() || null,
    metadata: {
      seller_notes: String(formData.get("sellerNotes") ?? "").trim() || null,
      video_url: String(formData.get("videoUrl") ?? "").trim() || null,
    },
  }

  for (const slug of slugCandidates(title, 4)) {
    const { data, error } = await ctx.supabase
      .from("horse_listings")
      .insert({ ...base, slug })
      .select("id")
      .single()
    if (!error && data) return data.id
    if (error?.code !== "23505") {
      return { error: `Could not save the horse listing: ${error?.message ?? "unknown error"}` }
    }
  }
  return { error: "Could not generate a unique listing URL. Please adjust the horse name." }
}

// --- Marketplace listing ---------------------------------------------------

export async function createMarketplaceListingAction(
  _prev: ActionState,
  formData: FormData,
): Promise<ActionState> {
  if (!hasSupabaseEnv()) return NOT_CONFIGURED
  const ctx = await requireSeller()
  if ("error" in ctx) return ctx

  const status = statusFromIntent(formData)
  const title = String(formData.get("title") ?? "").trim()
  const price = num(formData.get("price"))
  const categoryId = String(formData.get("categoryId") ?? "").trim()
  const condition = String(formData.get("condition") ?? "used")

  if (!title) return { error: "Enter a listing title." }
  if (!categoryId) return { error: "Select a category." }
  if (!price) return { error: "Enter a valid price." }

  const images = parseImages(formData.get("images"))
  const shippingAvailable = formData.get("shippingAvailable") === "on"

  const base = {
    seller_account_id: ctx.sellerAccountId,
    category_id: categoryId,
    title,
    status,
    sale_mode: "buy_now" as const,
    description: String(formData.get("description") ?? "").trim() || null,
    condition: condition as never,
    currency: "USD",
    price,
    accepts_offers: formData.get("acceptsOffers") === "on",
    shipping_available: shippingAvailable,
    location_text: String(formData.get("location") ?? "").trim() || null,
    metadata: {
      featured_requested: formData.get("featured") === "on",
      seller_notes: String(formData.get("sellerNotes") ?? "").trim() || null,
    },
  }

  let marketplaceId: string | null = null
  let lastError: string | null = null
  for (const slug of slugCandidates(title, 4)) {
    const { data, error } = await ctx.supabase
      .from("marketplace_listings")
      .insert({ ...base, slug })
      .select("id")
      .single()
    if (!error && data) {
      marketplaceId = data.id
      break
    }
    lastError = error?.message ?? null
    if (error?.code !== "23505") break
  }
  if (!marketplaceId) {
    return { error: lastError ? `Could not save the listing: ${lastError}` : "Could not save the listing." }
  }

  await insertImages(ctx, "marketplace_listing_id", marketplaceId, images)

  revalidatePath("/dashboard/listings")
  redirect("/dashboard/listings")
}

// --- Edit / delete ---------------------------------------------------------

export async function updateListingAction(
  _prev: ActionState,
  formData: FormData,
): Promise<ActionState> {
  if (!hasSupabaseEnv()) return NOT_CONFIGURED
  const ctx = await requireSeller()
  if ("error" in ctx) return ctx

  const id = String(formData.get("id") ?? "")
  const kind = String(formData.get("kind") ?? "")
  const table = kind === "horse" ? "horse_listings" : "marketplace_listings"
  const title = String(formData.get("title") ?? "").trim()
  const description = String(formData.get("description") ?? "").trim() || null
  const status = String(formData.get("status") ?? "draft")
  const price = num(formData.get("price"))

  if (!id || (kind !== "horse" && kind !== "marketplace")) {
    return { error: "Invalid listing." }
  }
  if (!title) return { error: "Title is required." }

  const patch: Record<string, unknown> = {
    title,
    description,
    status,
  }
  if (kind === "horse") patch.asking_price = price
  else patch.price = price

  // RLS scopes updates to owned rows; the seller-account filter is belt-and-braces.
  const { error } = await ctx.supabase
    .from(table)
    .update(patch as never)
    .eq("id", id)
    .eq("seller_account_id", ctx.sellerAccountId)

  if (error) return { error: `Could not update the listing: ${error.message}` }

  revalidatePath("/dashboard/listings")
  redirect("/dashboard/listings")
}

export async function deleteListingAction(formData: FormData): Promise<void> {
  if (!hasSupabaseEnv()) return
  const ctx = await requireSeller()
  if ("error" in ctx) return

  const id = String(formData.get("id") ?? "")
  const kind = String(formData.get("kind") ?? "")
  const table = kind === "horse" ? "horse_listings" : "marketplace_listings"
  if (!id || (kind !== "horse" && kind !== "marketplace")) return

  await ctx.supabase.from(table).delete().eq("id", id).eq("seller_account_id", ctx.sellerAccountId)

  revalidatePath("/dashboard/listings")
  redirect("/dashboard/listings")
}
