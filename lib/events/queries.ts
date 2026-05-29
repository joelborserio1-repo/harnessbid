import {
  createSupabaseServerAuthClient,
  hasSupabaseEnv,
} from "@/lib/supabase/auth-server"

const FALLBACK_IMAGE = "/placeholder.jpg"
const VISIBLE_EVENT_STATUSES = ["scheduled", "live", "closed", "settled"] as const

export type SaleEventSummary = {
  id: string
  slug: string
  name: string
  description: string
  status: string
  eventType: string
  image: string
  startsAt: string | null
  endsAt: string | null
  featured: boolean
}

export type SaleEventGroups = {
  featured: SaleEventSummary[]
  live: SaleEventSummary[]
  upcoming: SaleEventSummary[]
  completed: SaleEventSummary[]
}

function mapEvent(row: {
  id: string
  slug: string
  name: string
  description: string | null
  status: string
  event_type: string
  hero_image_url: string | null
  starts_at: string | null
  ends_at: string | null
  featured: boolean
}): SaleEventSummary {
  return {
    id: row.id,
    slug: row.slug,
    name: row.name,
    description: row.description ?? "Premium HarnessBid sale event.",
    status: row.status,
    eventType: row.event_type.replace(/_/g, " "),
    image: row.hero_image_url ?? FALLBACK_IMAGE,
    startsAt: row.starts_at,
    endsAt: row.ends_at,
    featured: row.featured,
  }
}

/** Public sale events grouped for the /sales landing page. */
export async function getPublicSaleEvents(): Promise<SaleEventGroups> {
  const empty: SaleEventGroups = { featured: [], live: [], upcoming: [], completed: [] }
  if (!hasSupabaseEnv()) return empty
  const supabase = await createSupabaseServerAuthClient()
  const { data } = await supabase
    .from("sale_events")
    .select("id, slug, name, description, status, event_type, hero_image_url, starts_at, ends_at, featured, sort_order")
    .in("status", VISIBLE_EVENT_STATUSES)
    .order("featured", { ascending: false })
    .order("sort_order", { ascending: true })
    .order("starts_at", { ascending: true, nullsFirst: false })

  const events = (data ?? []).map(mapEvent)
  return {
    featured: events.filter((e) => e.featured),
    live: events.filter((e) => e.status === "live"),
    upcoming: events.filter((e) => e.status === "scheduled"),
    completed: events.filter((e) => e.status === "closed" || e.status === "settled"),
  }
}

export async function getFeaturedSaleEvents(limit = 3): Promise<SaleEventSummary[]> {
  const groups = await getPublicSaleEvents()
  const ordered = [...groups.featured, ...groups.live, ...groups.upcoming]
  const seen = new Set<string>()
  const result: SaleEventSummary[] = []
  for (const e of ordered) {
    if (seen.has(e.id)) continue
    seen.add(e.id)
    result.push(e)
    if (result.length >= limit) break
  }
  return result
}

export type EnterpriseBranding = {
  tradingName: string
  legalName: string | null
  tier: string
} | null

export type SaleEventDetail = SaleEventSummary & { enterprise: EnterpriseBranding }

export async function getSaleEventBySlug(slug: string): Promise<SaleEventDetail | null> {
  if (!hasSupabaseEnv()) return null
  const supabase = await createSupabaseServerAuthClient()
  const { data } = await supabase
    .from("sale_events")
    .select(
      "id, slug, name, description, status, event_type, hero_image_url, starts_at, ends_at, featured, enterprise_seller_id",
    )
    .eq("slug", slug)
    .in("status", VISIBLE_EVENT_STATUSES)
    .maybeSingle()
  if (!data) return null

  let enterprise: EnterpriseBranding = null
  if (data.enterprise_seller_id) {
    const { data: ent } = await supabase
      .from("enterprise_sellers")
      .select("trading_name, legal_name, tier")
      .eq("id", data.enterprise_seller_id)
      .maybeSingle()
    if (ent) {
      enterprise = {
        tradingName: ent.trading_name ?? "Enterprise seller",
        legalName: ent.legal_name,
        tier: ent.tier,
      }
    }
  }

  return { ...mapEvent(data), enterprise }
}

export type CatalogueLot = {
  recordId: string
  kind: "horse" | "marketplace"
  lotNumber: string | null
  title: string
  href: string
  image: string
  priceLabel: string
  price: number
  sellerId: string
  sellerName: string
  sellerSlug: string
  gait: string | null
  sex: string | null
  ageYears: number | null
  sire: string | null
  dam: string | null
  isAuction: boolean
  auctionStatus: string | null
  reserveMet: boolean
  endsAt: string | null
}

export type ConsignorSummary = {
  id: string
  name: string
  slug: string
  lotCount: number
  enterprise: boolean
}

export type LotFilters = {
  consignor?: string
  gait?: string
  sex?: string
  status?: string
  search?: string
}

export type EventCatalogue = {
  lots: CatalogueLot[]
  consignors: ConsignorSummary[]
}

/** Lots assigned to an event (horse + marketplace), with consignors. */
export async function getEventCatalogue(
  eventId: string,
  filters: LotFilters = {},
): Promise<EventCatalogue> {
  if (!hasSupabaseEnv()) return { lots: [], consignors: [] }
  const supabase = await createSupabaseServerAuthClient()

  let horseQuery = supabase
    .from("horse_listings")
    .select(
      "id, title, slug, sale_mode, asking_price, lot_number, lot_order, seller_account_id, gait, sex, age_years, sire, dam",
    )
    .eq("sale_event_id", eventId)
    .in("status", ["published", "under_offer", "sold"])
    .not("published_at", "is", null)
    .limit(300)

  if (filters.consignor) horseQuery = horseQuery.eq("seller_account_id", filters.consignor)
  if (filters.gait) horseQuery = horseQuery.eq("gait", filters.gait as never)
  if (filters.sex) horseQuery = horseQuery.eq("sex", filters.sex as never)
  if (filters.search) horseQuery = horseQuery.ilike("title", `%${filters.search}%`)

  let marketQuery = supabase
    .from("marketplace_listings")
    .select("id, title, slug, price, lot_number, lot_order, seller_account_id")
    .eq("sale_event_id", eventId)
    .in("status", ["published", "under_offer", "sold"])
    .not("published_at", "is", null)
    .limit(300)
  if (filters.consignor) marketQuery = marketQuery.eq("seller_account_id", filters.consignor)
  if (filters.search) marketQuery = marketQuery.ilike("title", `%${filters.search}%`)
  // Horse-specific filters exclude marketplace lots.
  const skipMarket = Boolean(filters.gait || filters.sex)

  const [horseRes, marketRes] = await Promise.all([
    horseQuery,
    skipMarket ? Promise.resolve({ data: [] as never[] }) : marketQuery,
  ])

  const horses = horseRes.data ?? []
  const market = (marketRes.data ?? []) as Array<{
    id: string
    title: string
    slug: string
    price: number | null
    lot_number: string | null
    lot_order: number | null
    seller_account_id: string
  }>

  const horseIds = horses.map((h) => h.id)
  const sellerIds = [...new Set([...horses, ...market].map((r) => r.seller_account_id))]

  const [auctionsRes, imagesRes, sellersRes] = await Promise.all([
    horseIds.length
      ? supabase
          .from("auctions")
          .select("horse_listing_id, current_bid, starting_bid, status, reserve_met, ends_at")
          .in("horse_listing_id", horseIds)
      : Promise.resolve({ data: [] as never[] }),
    supabase
      .from("listing_images")
      .select("horse_listing_id, marketplace_listing_id, image_url, is_primary, position")
      .order("position", { ascending: true }),
    sellerIds.length
      ? supabase.from("seller_accounts").select("id, display_name, slug, account_type").in("id", sellerIds)
      : Promise.resolve({ data: [] as never[] }),
  ])

  const auctions = (auctionsRes.data ?? []) as Array<{
    horse_listing_id: string
    current_bid: number | null
    starting_bid: number | null
    status: string
    reserve_met: boolean
    ends_at: string
  }>
  const images = (imagesRes.data ?? []) as Array<{
    horse_listing_id: string | null
    marketplace_listing_id: string | null
    image_url: string | null
    is_primary: boolean
  }>
  const sellers = (sellersRes.data ?? []) as Array<{
    id: string
    display_name: string
    slug: string
    account_type: string
  }>
  const sellerById = new Map(sellers.map((s) => [s.id, s]))

  const imageFor = (key: "horse_listing_id" | "marketplace_listing_id", id: string) => {
    const matches = images.filter((img) => img[key] === id)
    return (matches.find((m) => m.is_primary) ?? matches[0])?.image_url ?? FALLBACK_IMAGE
  }

  const lots: CatalogueLot[] = []

  for (const h of horses) {
    const auction = auctions.find((a) => a.horse_listing_id === h.id)
    const isAuction = h.sale_mode === "auction" && Boolean(auction)
    if (filters.status && (auction?.status ?? "none") !== filters.status) continue
    const seller = sellerById.get(h.seller_account_id)
    lots.push({
      recordId: h.id,
      kind: "horse",
      lotNumber: h.lot_number,
      title: h.title,
      href: isAuction ? `/auctions/${h.slug}` : `/horses/buy-now/${h.slug}`,
      image: imageFor("horse_listing_id", h.id),
      priceLabel: isAuction ? "Current bid" : "Buy now",
      price: isAuction ? Number(auction?.current_bid ?? auction?.starting_bid ?? 0) : Number(h.asking_price ?? 0),
      sellerId: h.seller_account_id,
      sellerName: seller?.display_name ?? "Consignor",
      sellerSlug: seller?.slug ?? "",
      gait: h.gait,
      sex: h.sex,
      ageYears: h.age_years,
      sire: h.sire,
      dam: h.dam,
      isAuction,
      auctionStatus: auction?.status ?? null,
      reserveMet: auction?.reserve_met ?? false,
      endsAt: isAuction ? auction?.ends_at ?? null : null,
    })
  }

  if (!filters.status) {
    for (const m of market) {
      const seller = sellerById.get(m.seller_account_id)
      lots.push({
        recordId: m.id,
        kind: "marketplace",
        lotNumber: m.lot_number,
        title: m.title,
        href: `/marketplace/${m.slug}`,
        image: imageFor("marketplace_listing_id", m.id),
        priceLabel: "Price",
        price: Number(m.price ?? 0),
        sellerId: m.seller_account_id,
        sellerName: seller?.display_name ?? "Consignor",
        sellerSlug: seller?.slug ?? "",
        gait: null,
        sex: null,
        ageYears: null,
        sire: null,
        dam: null,
        isAuction: false,
        auctionStatus: null,
        reserveMet: false,
        endsAt: null,
      })
    }
  }

  // Order by lot number (numeric-aware), unnumbered lots last.
  lots.sort((a, b) => {
    const an = a.lotNumber ? Number(a.lotNumber.replace(/\D/g, "")) || Number.MAX_SAFE_INTEGER : Number.MAX_SAFE_INTEGER
    const bn = b.lotNumber ? Number(b.lotNumber.replace(/\D/g, "")) || Number.MAX_SAFE_INTEGER : Number.MAX_SAFE_INTEGER
    if (an !== bn) return an - bn
    return a.title.localeCompare(b.title)
  })

  // Derive consignors from the (unfiltered-by-consignor) lot set.
  const consignorMap = new Map<string, ConsignorSummary>()
  for (const lot of lots) {
    const existing = consignorMap.get(lot.sellerId)
    if (existing) existing.lotCount += 1
    else {
      const seller = sellerById.get(lot.sellerId)
      consignorMap.set(lot.sellerId, {
        id: lot.sellerId,
        name: lot.sellerName,
        slug: lot.sellerSlug,
        lotCount: 1,
        enterprise: seller?.account_type === "enterprise",
      })
    }
  }

  return {
    lots,
    consignors: [...consignorMap.values()].sort((a, b) => b.lotCount - a.lotCount),
  }
}
