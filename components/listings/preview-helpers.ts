import type { HorseAuctionDetail, MarketplaceDetail } from "@/lib/supabase/queries"

export const HORSE_SEXES = [
  "colt",
  "filly",
  "gelding",
  "mare",
  "stallion",
  "ridgling",
  "unknown",
] as const

export const HORSE_GAITS = ["pacer", "trotter", "dual_gaited", "unknown"] as const

export const MARKETPLACE_CONDITIONS = [
  "new",
  "excellent",
  "good",
  "fair",
  "used",
  "for_parts",
  "not_applicable",
] as const

export type SellerPreviewInfo = {
  name: string
  slug: string
  verified: boolean
  isEnterprise: boolean
  avatar: string
  memberSince: string
}

const PLACEHOLDER = "/placeholder.jpg"

function str(fd: FormData, key: string): string {
  return String(fd.get(key) ?? "").trim()
}

function money(fd: FormData, key: string): number {
  const n = Number(str(fd, key).replace(/[^0-9.]/g, ""))
  return Number.isFinite(n) ? n : 0
}

function imageUrls(fd: FormData): string[] {
  try {
    const parsed = JSON.parse(str(fd, "images") || "[]")
    if (Array.isArray(parsed)) {
      const urls = parsed.map((i) => i?.url).filter((u): u is string => typeof u === "string")
      if (urls.length > 0) return urls
    }
  } catch {
    /* ignore */
  }
  return [PLACEHOLDER]
}

function timeLeft(iso: string): string {
  const ms = new Date(iso).getTime() - Date.now()
  if (!Number.isFinite(ms) || ms <= 0) return "Closed"
  const days = Math.floor(ms / 86_400_000)
  const hours = Math.floor((ms % 86_400_000) / 3_600_000)
  return days > 0 ? `${days}d ${hours}h` : `${Math.max(1, hours)}h`
}

function horseSpecs(fd: FormData): Array<{ label: string; value: string }> {
  return [
    ["Age", str(fd, "ageYears") ? `${str(fd, "ageYears")} Years` : "On request"],
    ["Color", str(fd, "color") || "On request"],
    ["Sex", str(fd, "sex") || "unknown"],
    ["Gait", str(fd, "gait") || "unknown"],
    ["Sire", str(fd, "sire") || "On request"],
    ["Dam", str(fd, "dam") || "On request"],
    ["Best Mile", str(fd, "bestMile") || "On request"],
  ].map(([label, value]) => ({ label, value }))
}

export function buildHorsePreview(
  fd: FormData,
  seller: SellerPreviewInfo,
  _mode: "auction",
): HorseAuctionDetail {
  const images = imageUrls(fd)
  const title = str(fd, "title") || "Untitled horse"
  const startingBid = money(fd, "startingBid")
  const reserve = money(fd, "reservePrice")
  const increment = money(fd, "bidIncrement") || 100
  const endsAt = str(fd, "auctionEnd")
    ? new Date(str(fd, "auctionEnd")).toISOString()
    : new Date(Date.now() + 7 * 86_400_000).toISOString()

  return {
    id: "preview",
    recordId: "preview",
    listingId: "preview",
    title,
    name: title,
    description: str(fd, "description") || str(fd, "shortDescription") || "Standardbred auction listing.",
    currentBid: startingBid,
    image: images[0],
    location: str(fd, "location") || "Location on request",
    seller: seller.name,
    verified: seller.verified,
    bids: 0,
    endTime: timeLeft(endsAt),
    lot: null,
    sire: str(fd, "sire") || null,
    dam: str(fd, "dam") || null,
    gait: str(fd, "gait") || null,
    sex: str(fd, "sex") || null,
    age: Number(str(fd, "age")) || null,
    status: "live",
    listingSlug: "preview",
    endsAt,
    images,
    startingBid,
    reservePrice: reserve || null,
    reserveMet: false,
    nextMinimumBid: startingBid + increment,
    bidIncrement: increment,
    category: "Horse Auction",
    watchers: 0,
    views: 0,
    sellerSlug: seller.slug,
    sellerAvatar: seller.avatar,
    sellerMemberSince: seller.memberSince,
    sellerTotalListings: 0,
    sellerRating: 0,
    sellerReviews: 0,
    sellerResponseTime: "Contact seller",
    sellerEnterprise: seller.isEnterprise,
    specs: horseSpecs(fd),
  }
}

function baseMarketplacePreview(
  fd: FormData,
  seller: SellerPreviewInfo,
  opts: { category: string; condition: string; specs: Array<{ label: string; value: string }> },
): MarketplaceDetail {
  const images = imageUrls(fd)
  return {
    id: "preview",
    recordId: "preview",
    title: str(fd, "title") || "Untitled listing",
    price: money(fd, "price"),
    image: images[0],
    location: str(fd, "location") || "Location on request",
    condition: opts.condition,
    category: opts.category,
    seller: seller.name,
    verified: seller.verified,
    featured: false,
    shipping: fd.get("shippingAvailable") === "on",
    createdAt: "Just now",
    description: str(fd, "description") || "",
    images,
    sellerSlug: seller.slug,
    sellerAvatar: seller.avatar,
    sellerMemberSince: seller.memberSince,
    sellerTotalListings: 0,
    sellerRating: 0,
    sellerReviews: 0,
    sellerResponseTime: "Contact seller",
    sellerEnterprise: seller.isEnterprise,
    shippingDomestic: null,
    shippingInternational: null,
    specs: opts.specs,
    views: 0,
    watchers: 0,
  }
}

export function buildBuyNowHorsePreview(
  fd: FormData,
  seller: SellerPreviewInfo,
): MarketplaceDetail {
  return baseMarketplacePreview(fd, seller, {
    category: "Buy Now Horse",
    condition: "Standardbred",
    specs: horseSpecs(fd),
  })
}

export function buildMarketplacePreview(
  fd: FormData,
  seller: SellerPreviewInfo,
  categoryName: string,
): MarketplaceDetail {
  const condition = str(fd, "condition") || "used"
  return baseMarketplacePreview(fd, seller, {
    category: categoryName || "Marketplace",
    condition: condition === "not_applicable" ? "N/A" : condition.replace(/_/g, " "),
    specs: [],
  })
}
