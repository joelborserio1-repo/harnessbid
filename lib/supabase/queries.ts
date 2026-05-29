import { createSupabaseServerClient, hasSupabaseEnv } from "./server"
import type { Database, Json } from "./database.types"

const FALLBACK_IMAGE = "/placeholder.jpg"

type CategoryRow = Database["public"]["Tables"]["categories"]["Row"]
type HorseRow = Database["public"]["Tables"]["horse_listings"]["Row"]
type MarketplaceRow = Database["public"]["Tables"]["marketplace_listings"]["Row"]
type SellerRow = Database["public"]["Tables"]["seller_accounts"]["Row"]
type AuctionRow = Database["public"]["Tables"]["auctions"]["Row"]
type ImageRow = Database["public"]["Tables"]["listing_images"]["Row"]

type EnterpriseSellerRow = {
  id: string
  seller_account_id: string
  legal_name: string | null
  trading_name: string | null
  tier: "standard" | "preferred" | "premier" | "strategic"
  onboarding_status: "draft" | "invited" | "in_review" | "active" | "paused" | "offboarded"
  featured_until: string | null
  brand_settings: Json
  created_at: string
  updated_at: string
}

type SaleEventRow = {
  id: string
  seller_account_id: string | null
  enterprise_seller_id: string | null
  name: string
  slug: string
  event_type: "online_auction" | "timed_auction" | "live_sale" | "private_sale" | "clearance_sale"
  status: "draft" | "scheduled" | "live" | "closed" | "settled" | "cancelled" | "archived"
  description: string | null
  hero_image_url: string | null
  timezone: string
  starts_at: string | null
  ends_at: string | null
  metadata: Json
  created_at: string
  updated_at: string
}

export type QueryResult<T> = {
  data: T
  error: string | null
  configured: boolean
}

export type MarketplaceCategory = {
  id: string
  name: string
  href: string
  count: number
}

export type MarketplaceCard = {
  id: string
  title: string
  price: number
  image: string
  location: string
  condition: string
  category: string
  seller: string
  verified: boolean
  featured: boolean
  shipping: boolean
  createdAt: string
}

export type HorseAuctionCard = {
  id: string
  listingId: string
  title: string
  name: string
  description: string
  currentBid: number
  image: string
  location: string
  seller: string
  verified: boolean
  bids: number
  endTime: string
}

export type MarketplaceDetail = MarketplaceCard & {
  description: string
  images: string[]
  sellerSlug: string
  sellerAvatar: string
  sellerMemberSince: string
  sellerTotalListings: number
  sellerRating: number
  sellerReviews: number
  sellerResponseTime: string
  sellerEnterprise: boolean
  shippingDomestic: number | null
  shippingInternational: string | null
  specs: Array<{ label: string; value: string }>
  views: number
  watchers: number
}

export type HorseAuctionDetail = HorseAuctionCard & {
  listingSlug: string
  endsAt: string
  description: string
  images: string[]
  startingBid: number
  reservePrice: number | null
  reserveMet: boolean
  nextMinimumBid: number
  bidIncrement: number
  category: string
  watchers: number
  views: number
  sellerSlug: string
  sellerAvatar: string
  sellerMemberSince: string
  sellerTotalListings: number
  sellerRating: number
  sellerReviews: number
  sellerResponseTime: string
  sellerEnterprise: boolean
  specs: Array<{ label: string; value: string }>
}

export type SellerSaleEvent = {
  id: string
  slug: string
  name: string
  description: string
  status: string
  eventType: string
  image: string
  startsAt: string | null
  endsAt: string | null
}

export type SellerStorefront = {
  seller: {
    id: string
    name: string
    slug: string
    bio: string
    logo: string
    website: string | null
    location: string
    verified: boolean
    accountType: string
    responseTime: string
    rating: number
    reviewCount: number
    totalSales: number
    totalListings: number
    memberSince: string
  }
  enterprise: {
    id: string
    tradingName: string
    legalName: string
    tier: string
    featured: boolean
  } | null
  saleEvents: SellerSaleEvent[]
  marketplaceListings: MarketplaceCard[]
  horseAuctions: HorseAuctionCard[]
}

function emptyResult<T>(data: T, error: string | null = null): QueryResult<T> {
  return { data, error, configured: hasSupabaseEnv() }
}

function getClientOrEmpty<T>(data: T): { client: ReturnType<typeof createSupabaseServerClient>; result?: QueryResult<T> } {
  if (!hasSupabaseEnv()) {
    return {
      client: null as never,
      result: emptyResult(data, "Supabase env vars are not configured."),
    }
  }

  return { client: createSupabaseServerClient() }
}

function money(value: number | null) {
  return Number(value ?? 0)
}

function locationFrom(row: Pick<MarketplaceRow | HorseRow | SellerRow, "location_text" | "city" | "region" | "country_code">) {
  if (row.location_text) return row.location_text
  return [row.city, row.region, row.country_code].filter(Boolean).join(", ") || "Location on request"
}

function timeAgo(value: string | null) {
  if (!value) return "Recently listed"
  const days = Math.max(0, Math.floor((Date.now() - new Date(value).getTime()) / 86_400_000))
  if (days === 0) return "Today"
  if (days === 1) return "1 day ago"
  return `${days} days ago`
}

function timeLeft(value: string) {
  const ms = new Date(value).getTime() - Date.now()
  if (ms <= 0) return "Closed"
  const days = Math.floor(ms / 86_400_000)
  const hours = Math.floor((ms % 86_400_000) / 3_600_000)
  if (days > 0) return `${days}d ${hours}h`
  return `${Math.max(1, hours)}h`
}

function titleName(title: string) {
  return title.split(" - ")[0] || title
}

function arraySpecs(value: Json): Array<{ label: string; value: string }> {
  if (Array.isArray(value)) {
    return value
      .map((item) => {
        if (item && typeof item === "object" && !Array.isArray(item)) {
          const label = "label" in item ? String(item.label ?? "") : ""
          const specValue = "value" in item ? String(item.value ?? "") : ""
          if (label && specValue) return { label, value: specValue }
        }
        return null
      })
      .filter(Boolean) as Array<{ label: string; value: string }>
  }

  if (value && typeof value === "object") {
    return Object.entries(value).map(([label, specValue]) => ({
      label,
      value: String(specValue ?? ""),
    }))
  }

  return []
}

async function sellerMap(ids: string[]) {
  const uniqueIds = [...new Set(ids)].filter(Boolean)
  if (uniqueIds.length === 0) return new Map<string, SellerRow>()
  const supabase = createSupabaseServerClient()
  const { data } = await supabase.from("seller_accounts").select("*").in("id", uniqueIds)
  return new Map((data ?? []).map((seller) => [seller.id, seller]))
}

async function categoryMap(ids: string[]) {
  const uniqueIds = [...new Set(ids)].filter(Boolean)
  if (uniqueIds.length === 0) return new Map<string, CategoryRow>()
  const supabase = createSupabaseServerClient()
  const { data } = await supabase.from("categories").select("*").in("id", uniqueIds)
  return new Map((data ?? []).map((category) => [category.id, category]))
}

async function imagesFor(target: "horse_listing_id" | "marketplace_listing_id", ids: string[]) {
  const uniqueIds = [...new Set(ids)].filter(Boolean)
  if (uniqueIds.length === 0) return new Map<string, ImageRow[]>()
  const supabase = createSupabaseServerClient()
  const { data } = await supabase
    .from("listing_images")
    .select("*")
    .in(target, uniqueIds)
    .order("position", { ascending: true })

  const map = new Map<string, ImageRow[]>()
  for (const image of data ?? []) {
    const id = image[target]
    if (!id) continue
    map.set(id, [...(map.get(id) ?? []), image])
  }
  return map
}

function primaryImage(images: ImageRow[] | undefined) {
  return images?.find((image) => image.is_primary)?.image_url ?? images?.[0]?.image_url ?? FALLBACK_IMAGE
}

function untypedClient() {
  return createSupabaseServerClient() as any
}

function imageUrls(images: ImageRow[] | undefined, fallback: string) {
  const urls = (images ?? [])
    .map((image) => image.image_url)
    .filter((url): url is string => Boolean(url))

  return urls.length > 0 ? urls : [fallback]
}

export async function getMarketplaceCategories(): Promise<QueryResult<MarketplaceCategory[]>> {
  const setup = getClientOrEmpty<MarketplaceCategory[]>([])
  if (setup.result) return setup.result

  const { data, error } = await setup.client
    .from("categories")
    .select("*")
    .in("category_type", ["marketplace", "service"])
    .eq("is_active", true)
    .order("sort_order", { ascending: true })

  if (error) return emptyResult([], error.message)

  return emptyResult(
    (data ?? []).map((category) => ({
      id: category.slug,
      name: category.name,
      href: `/marketplace/${category.slug}`,
      count: 0,
    })),
  )
}

export type CategoryOption = { id: string; name: string; slug: string }

export async function getMarketplaceCategoryOptions(): Promise<QueryResult<CategoryOption[]>> {
  const setup = getClientOrEmpty<CategoryOption[]>([])
  if (setup.result) return setup.result

  const { data, error } = await setup.client
    .from("categories")
    .select("id, name, slug")
    .in("category_type", ["marketplace", "service"])
    .eq("is_active", true)
    .order("sort_order", { ascending: true })

  if (error) return emptyResult([], error.message)
  return emptyResult((data ?? []).map((c) => ({ id: c.id, name: c.name, slug: c.slug })))
}

export async function getMarketplaceListings(limit = 12): Promise<QueryResult<MarketplaceCard[]>> {
  const setup = getClientOrEmpty<MarketplaceCard[]>([])
  if (setup.result) return setup.result

  const { data, error } = await setup.client
    .from("marketplace_listings")
    .select("*")
    .in("status", ["published", "under_offer", "sold"])
    .not("published_at", "is", null)
    .order("published_at", { ascending: false })
    .limit(limit)

  if (error) return emptyResult([], error.message)

  const rows = data ?? []
  const [sellers, categories, images] = await Promise.all([
    sellerMap(rows.map((row) => row.seller_account_id)),
    categoryMap(rows.map((row) => row.category_id ?? "")),
    imagesFor("marketplace_listing_id", rows.map((row) => row.id)),
  ])

  return emptyResult(
    rows.map((row) => mapMarketplaceCard(row, sellers.get(row.seller_account_id), categories.get(row.category_id ?? ""), images.get(row.id))),
  )
}

export async function getHorseAuctions(limit = 12): Promise<QueryResult<HorseAuctionCard[]>> {
  const setup = getClientOrEmpty<HorseAuctionCard[]>([])
  if (setup.result) return setup.result

  const { data, error } = await setup.client
    .from("auctions")
    .select("*")
    .in("status", ["scheduled", "live", "extended"])
    .not("horse_listing_id", "is", null)
    .order("ends_at", { ascending: true })
    .limit(limit)

  if (error) return emptyResult([], error.message)

  const auctions = data ?? []
  const listingIds = auctions.map((auction) => auction.horse_listing_id).filter(Boolean) as string[]
  const { data: horses } = await setup.client.from("horse_listings").select("*").in("id", listingIds)
  const horseMap = new Map((horses ?? []).map((horse) => [horse.id, horse]))
  const [sellers, images] = await Promise.all([
    sellerMap((horses ?? []).map((horse) => horse.seller_account_id)),
    imagesFor("horse_listing_id", listingIds),
  ])

  return emptyResult(
    auctions
      .map((auction) => {
        const horse = auction.horse_listing_id ? horseMap.get(auction.horse_listing_id) : null
        if (!horse) return null
        return mapHorseAuctionCard(auction, horse, sellers.get(horse.seller_account_id), images.get(horse.id))
      })
      .filter(Boolean) as HorseAuctionCard[],
  )
}

export async function getMarketplaceListing(identifier: string): Promise<QueryResult<MarketplaceDetail | null>> {
  const setup = getClientOrEmpty<MarketplaceDetail | null>(null)
  if (setup.result) return setup.result

  const query = setup.client.from("marketplace_listings").select("*").limit(1)
  const { data, error } = await (/^[0-9a-f-]{36}$/i.test(identifier)
    ? query.or(`id.eq.${identifier},slug.eq.${identifier}`)
    : query.eq("slug", identifier))

  if (error) return emptyResult(null, error.message)
  const row = data?.[0]
  if (!row) return emptyResult(null)

  const [sellers, categories, images] = await Promise.all([
    sellerMap([row.seller_account_id]),
    categoryMap([row.category_id ?? ""]),
    imagesFor("marketplace_listing_id", [row.id]),
  ])

  return emptyResult(mapMarketplaceDetail(row, sellers.get(row.seller_account_id), categories.get(row.category_id ?? ""), images.get(row.id)))
}

export async function getHorseAuction(identifier: string): Promise<QueryResult<HorseAuctionDetail | null>> {
  const setup = getClientOrEmpty<HorseAuctionDetail | null>(null)
  if (setup.result) return setup.result

  let auction: AuctionRow | null = null
  let horse: HorseRow | null = null

  if (/^[0-9a-f-]{36}$/i.test(identifier)) {
    const { data } = await setup.client.from("auctions").select("*").eq("id", identifier).limit(1)
    auction = data?.[0] ?? null
  }

  if (auction?.horse_listing_id) {
    const { data } = await setup.client.from("horse_listings").select("*").eq("id", auction.horse_listing_id).limit(1)
    horse = data?.[0] ?? null
  } else {
    const { data } = await setup.client.from("horse_listings").select("*").eq("slug", identifier).limit(1)
    horse = data?.[0] ?? null
    if (horse) {
      const { data: auctions } = await setup.client.from("auctions").select("*").eq("horse_listing_id", horse.id).limit(1)
      auction = auctions?.[0] ?? null
    }
  }

  if (!auction || !horse) return emptyResult(null)

  const [sellers, categories, images] = await Promise.all([
    sellerMap([horse.seller_account_id]),
    categoryMap([horse.category_id ?? ""]),
    imagesFor("horse_listing_id", [horse.id]),
  ])

  return emptyResult(
    mapHorseAuctionDetail(
      auction,
      horse,
      sellers.get(horse.seller_account_id),
      categories.get(horse.category_id ?? ""),
      images.get(horse.id),
    ),
  )
}

export async function getSellerStorefront(slug: string): Promise<QueryResult<SellerStorefront | null>> {
  const setup = getClientOrEmpty<SellerStorefront | null>(null)
  if (setup.result) return setup.result

  const { data: sellerRows, error } = await setup.client
    .from("seller_accounts")
    .select("*")
    .eq("slug", slug)
    .eq("is_active", true)
    .eq("verification_status", "verified")
    .limit(1)

  if (error) return emptyResult(null, error.message)
  const seller = sellerRows?.[0]
  if (!seller) return emptyResult(null)

  const client = untypedClient()
  const { data: enterpriseRows } = await client
    .from("enterprise_sellers")
    .select("*")
    .eq("seller_account_id", seller.id)
    .eq("onboarding_status", "active")
    .limit(1)

  const enterprise = (enterpriseRows?.[0] ?? null) as EnterpriseSellerRow | null

  const [marketplace, horses, events] = await Promise.all([
    getSellerMarketplaceListings(seller),
    getSellerHorseAuctions(seller),
    getSellerSaleEvents(seller, enterprise),
  ])

  return emptyResult({
    seller: {
      id: seller.id,
      name: seller.display_name,
      slug: seller.slug,
      bio: seller.bio ?? "Verified HarnessBid seller with public marketplace activity.",
      logo: seller.logo_url ?? "/placeholder-user.jpg",
      website: seller.website_url,
      location: locationFrom(seller),
      verified: seller.verification_status === "verified",
      accountType: seller.account_type,
      responseTime: seller.response_time_label ?? "Contact seller",
      rating: seller.rating,
      reviewCount: seller.review_count,
      totalSales: seller.total_sales,
      totalListings: seller.total_listings,
      memberSince: seller.created_at ? new Date(seller.created_at).getFullYear().toString() : "New seller",
    },
    enterprise: enterprise
      ? {
          id: enterprise.id,
          tradingName: enterprise.trading_name ?? seller.display_name,
          legalName: enterprise.legal_name ?? seller.display_name,
          tier: enterprise.tier,
          featured: Boolean(enterprise.featured_until && new Date(enterprise.featured_until).getTime() > Date.now()),
        }
      : null,
    saleEvents: events,
    marketplaceListings: marketplace,
    horseAuctions: horses,
  })
}

export async function getSaleEvents(limit = 24): Promise<QueryResult<SellerSaleEvent[]>> {
  const setup = getClientOrEmpty<SellerSaleEvent[]>([])
  if (setup.result) return setup.result

  const client = untypedClient()
  const { data, error } = await client
    .from("sale_events")
    .select("*")
    .in("status", ["scheduled", "live", "closed", "settled"])
    .order("starts_at", { ascending: true, nullsFirst: false })
    .limit(limit)

  if (error) return emptyResult([], error.message)
  return emptyResult(((data ?? []) as SaleEventRow[]).map(mapSaleEvent))
}

async function getSellerMarketplaceListings(seller: SellerRow) {
  const { data } = await createSupabaseServerClient()
    .from("marketplace_listings")
    .select("*")
    .eq("seller_account_id", seller.id)
    .in("status", ["published", "under_offer", "sold"])
    .not("published_at", "is", null)
    .order("published_at", { ascending: false })
    .limit(8)

  const rows = data ?? []
  const [categories, images] = await Promise.all([
    categoryMap(rows.map((row) => row.category_id ?? "")),
    imagesFor("marketplace_listing_id", rows.map((row) => row.id)),
  ])

  return rows.map((row) => mapMarketplaceCard(row, seller, categories.get(row.category_id ?? ""), images.get(row.id)))
}

async function getSellerHorseAuctions(seller: SellerRow) {
  const supabase = createSupabaseServerClient()
  const { data: horses } = await supabase
    .from("horse_listings")
    .select("*")
    .eq("seller_account_id", seller.id)
    .in("status", ["published", "under_offer", "sold"])
    .not("published_at", "is", null)
    .order("published_at", { ascending: false })
    .limit(8)

  const horseRows = horses ?? []
  if (horseRows.length === 0) return []

  const { data: auctions } = await supabase
    .from("auctions")
    .select("*")
    .in("horse_listing_id", horseRows.map((horse) => horse.id))
    .in("status", ["scheduled", "live", "extended", "closed", "settled"])
    .order("ends_at", { ascending: true })

  const auctionMap = new Map((auctions ?? []).map((auction) => [auction.horse_listing_id, auction]))
  const images = await imagesFor("horse_listing_id", horseRows.map((horse) => horse.id))

  return horseRows
    .map((horse) => {
      const auction = auctionMap.get(horse.id)
      if (!auction) return null
      return mapHorseAuctionCard(auction, horse, seller, images.get(horse.id))
    })
    .filter(Boolean) as HorseAuctionCard[]
}

async function getSellerSaleEvents(seller: SellerRow, enterprise: EnterpriseSellerRow | null) {
  const client = untypedClient()
  const filters = [`seller_account_id.eq.${seller.id}`]
  if (enterprise) filters.push(`enterprise_seller_id.eq.${enterprise.id}`)

  const { data } = await client
    .from("sale_events")
    .select("*")
    .or(filters.join(","))
    .in("status", ["scheduled", "live", "closed", "settled"])
    .order("starts_at", { ascending: true, nullsFirst: false })
    .limit(6)

  return ((data ?? []) as SaleEventRow[]).map(mapSaleEvent)
}

function mapMarketplaceCard(row: MarketplaceRow, seller?: SellerRow, category?: CategoryRow, images?: ImageRow[]): MarketplaceCard {
  return {
    id: row.slug,
    title: row.title,
    price: money(row.price),
    image: primaryImage(images),
    location: locationFrom(row),
    condition: row.condition === "not_applicable" ? "N/A" : row.condition.replace(/_/g, " "),
    category: category?.name ?? "Marketplace",
    seller: seller?.display_name ?? "Verified seller",
    verified: seller?.verification_status === "verified",
    featured: Boolean(row.featured_until && new Date(row.featured_until).getTime() > Date.now()),
    shipping: row.shipping_available,
    createdAt: timeAgo(row.published_at ?? row.created_at),
  }
}

function mapMarketplaceDetail(row: MarketplaceRow, seller?: SellerRow, category?: CategoryRow, images?: ImageRow[]): MarketplaceDetail {
  const card = mapMarketplaceCard(row, seller, category, images)
  return {
    ...card,
    description: row.description ?? "",
    images: imageUrls(images, card.image),
    sellerSlug: seller?.slug ?? "",
    sellerAvatar: seller?.logo_url ?? "/placeholder-user.jpg",
    sellerMemberSince: seller?.created_at ? new Date(seller.created_at).getFullYear().toString() : "New seller",
    sellerTotalListings: seller?.total_listings ?? 0,
    sellerRating: seller?.rating ?? 0,
    sellerReviews: seller?.review_count ?? 0,
    sellerResponseTime: seller?.response_time_label ?? "Contact seller",
    sellerEnterprise: seller?.account_type === "enterprise",
    shippingDomestic: row.domestic_shipping_price,
    shippingInternational: row.international_shipping_notes,
    specs: arraySpecs(row.specs),
    views: row.view_count,
    watchers: row.watcher_count,
  }
}

function mapHorseAuctionCard(auction: AuctionRow, horse: HorseRow, seller?: SellerRow, images?: ImageRow[]): HorseAuctionCard {
  return {
    id: auction.id,
    listingId: horse.slug,
    title: horse.title,
    name: titleName(horse.title),
    description: horse.short_description ?? horse.description ?? "Standardbred horse auction listing.",
    currentBid: money(auction.current_bid ?? auction.starting_bid),
    image: primaryImage(images),
    location: locationFrom(horse),
    seller: seller?.display_name ?? "Verified seller",
    verified: seller?.verification_status === "verified",
    bids: auction.bid_count,
    endTime: timeLeft(auction.ends_at),
  }
}

function mapHorseAuctionDetail(auction: AuctionRow, horse: HorseRow, seller?: SellerRow, category?: CategoryRow, images?: ImageRow[]): HorseAuctionDetail {
  const card = mapHorseAuctionCard(auction, horse, seller, images)
  return {
    ...card,
    listingSlug: horse.slug,
    endsAt: auction.ends_at,
    description: horse.description ?? card.description,
    images: imageUrls(images, card.image),
    startingBid: money(auction.starting_bid),
    reservePrice: auction.reserve_price,
    reserveMet: auction.reserve_met,
    nextMinimumBid: money((auction.current_bid ?? auction.starting_bid) + auction.bid_increment),
    bidIncrement: money(auction.bid_increment),
    category: category?.name ?? "Horse Auction",
    watchers: horse.watcher_count,
    views: horse.view_count,
    sellerSlug: seller?.slug ?? "",
    sellerAvatar: seller?.logo_url ?? "/placeholder-user.jpg",
    sellerMemberSince: seller?.created_at ? new Date(seller.created_at).getFullYear().toString() : "New seller",
    sellerTotalListings: seller?.total_listings ?? 0,
    sellerRating: seller?.rating ?? 0,
    sellerReviews: seller?.review_count ?? 0,
    sellerResponseTime: seller?.response_time_label ?? "Contact seller",
    sellerEnterprise: seller?.account_type === "enterprise",
    specs: arraySpecs(horse.specs).length
      ? arraySpecs(horse.specs)
      : [
          { label: "Age", value: horse.age_years ? `${horse.age_years} Years` : "On request" },
          { label: "Color", value: horse.color ?? "On request" },
          { label: "Sex", value: horse.sex },
          { label: "Sire", value: horse.sire ?? "On request" },
          { label: "Dam", value: horse.dam ?? "On request" },
          { label: "Best Mile", value: horse.best_mile ?? "On request" },
        ],
  }
}

function mapSaleEvent(row: SaleEventRow): SellerSaleEvent {
  return {
    id: row.id,
    slug: row.slug,
    name: row.name,
    description: row.description ?? "Premium HarnessBid sale event with verified seller participation.",
    status: row.status.replace(/_/g, " "),
    eventType: row.event_type.replace(/_/g, " "),
    image: row.hero_image_url ?? FALLBACK_IMAGE,
    startsAt: row.starts_at,
    endsAt: row.ends_at,
  }
}
