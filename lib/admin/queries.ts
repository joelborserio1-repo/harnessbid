import {
  createSupabaseServerAuthClient,
  hasSupabaseEnv,
} from "@/lib/supabase/auth-server"

async function client() {
  return createSupabaseServerAuthClient()
}

export type AdminStats = {
  totalListings: number
  activeHorseAuctions: number
  activeMarketplace: number
  pendingApprovals: number
  enterpriseApplications: number
  openReports: number
  bidsToday: number
  enquiries: number
}

export async function getAdminStats(): Promise<AdminStats> {
  if (!hasSupabaseEnv()) {
    return {
      totalListings: 0,
      activeHorseAuctions: 0,
      activeMarketplace: 0,
      pendingApprovals: 0,
      enterpriseApplications: 0,
      openReports: 0,
      bidsToday: 0,
      enquiries: 0,
    }
  }
  const supabase = await client()
  const startOfDay = new Date()
  startOfDay.setHours(0, 0, 0, 0)

  const [
    horse,
    market,
    horsePending,
    marketPending,
    activeAuctions,
    activeMarket,
    enterprise,
    reports,
    bidsToday,
    enquiries,
  ] = await Promise.all([
    supabase.from("horse_listings").select("id", { count: "exact", head: true }),
    supabase.from("marketplace_listings").select("id", { count: "exact", head: true }),
    supabase.from("horse_listings").select("id", { count: "exact", head: true }).eq("status", "pending_review"),
    supabase.from("marketplace_listings").select("id", { count: "exact", head: true }).eq("status", "pending_review"),
    supabase.from("auctions").select("id", { count: "exact", head: true }).in("status", ["live", "extended"]),
    supabase.from("marketplace_listings").select("id", { count: "exact", head: true }).eq("status", "published"),
    supabase.from("enterprise_sellers").select("id", { count: "exact", head: true }).in("onboarding_status", ["draft", "in_review"]),
    supabase.from("listing_reports").select("id", { count: "exact", head: true }).eq("status", "open"),
    supabase.from("bids").select("id", { count: "exact", head: true }).gte("placed_at", startOfDay.toISOString()),
    supabase.from("enquiries").select("id", { count: "exact", head: true }),
  ])

  return {
    totalListings: (horse.count ?? 0) + (market.count ?? 0),
    activeHorseAuctions: activeAuctions.count ?? 0,
    activeMarketplace: activeMarket.count ?? 0,
    pendingApprovals: (horsePending.count ?? 0) + (marketPending.count ?? 0),
    enterpriseApplications: enterprise.count ?? 0,
    openReports: reports.count ?? 0,
    bidsToday: bidsToday.count ?? 0,
    enquiries: enquiries.count ?? 0,
  }
}

export type QueueItem = {
  kind: "horse" | "marketplace"
  id: string
  title: string
  slug: string
  status: string
  createdAt: string
}

export async function getModerationQueue(): Promise<QueueItem[]> {
  if (!hasSupabaseEnv()) return []
  const supabase = await client()
  const states = ["pending_review", "draft"] as const
  const [horse, market] = await Promise.all([
    supabase
      .from("horse_listings")
      .select("id, title, slug, status, created_at")
      .in("status", states)
      .order("created_at", { ascending: false }),
    supabase
      .from("marketplace_listings")
      .select("id, title, slug, status, created_at")
      .in("status", states)
      .order("created_at", { ascending: false }),
  ])
  const items: QueueItem[] = [
    ...(horse.data ?? []).map((h) => ({
      kind: "horse" as const,
      id: h.id,
      title: h.title,
      slug: h.slug,
      status: h.status,
      createdAt: h.created_at,
    })),
    ...(market.data ?? []).map((m) => ({
      kind: "marketplace" as const,
      id: m.id,
      title: m.title,
      slug: m.slug,
      status: m.status,
      createdAt: m.created_at,
    })),
  ]
  return items.sort((a, b) => b.createdAt.localeCompare(a.createdAt))
}

export type EnterpriseApplication = {
  id: string
  sellerAccountId: string
  sellerName: string
  slug: string
  tradingName: string | null
  onboardingStatus: string
  verificationStatus: string
  createdAt: string
}

export async function getEnterpriseApplications(): Promise<EnterpriseApplication[]> {
  if (!hasSupabaseEnv()) return []
  const supabase = await client()
  const { data } = await supabase
    .from("enterprise_sellers")
    .select("id, seller_account_id, trading_name, onboarding_status, created_at")
    .order("created_at", { ascending: false })
  if (!data || data.length === 0) return []

  const sellerIds = data.map((e) => e.seller_account_id)
  const { data: sellers } = await supabase
    .from("seller_accounts")
    .select("id, display_name, slug, verification_status")
    .in("id", sellerIds)
  const byId = new Map((sellers ?? []).map((s) => [s.id, s]))

  return data.map((e) => {
    const s = byId.get(e.seller_account_id)
    return {
      id: e.id,
      sellerAccountId: e.seller_account_id,
      sellerName: s?.display_name ?? "Seller",
      slug: s?.slug ?? "",
      tradingName: e.trading_name,
      onboardingStatus: e.onboarding_status,
      verificationStatus: s?.verification_status ?? "unverified",
      createdAt: e.created_at,
    }
  })
}

export type ReportItem = {
  id: string
  reason: string
  details: string | null
  status: string
  createdAt: string
  listingKind: "horse" | "marketplace"
  listingTitle: string
}

export async function getReports(): Promise<ReportItem[]> {
  if (!hasSupabaseEnv()) return []
  const supabase = await client()
  const { data } = await supabase
    .from("listing_reports")
    .select("id, reason, details, status, created_at, horse_listing_id, marketplace_listing_id")
    .order("created_at", { ascending: false })
    .limit(100)
  if (!data || data.length === 0) return []

  const horseIds = data.map((r) => r.horse_listing_id).filter((v): v is string => Boolean(v))
  const marketIds = data.map((r) => r.marketplace_listing_id).filter((v): v is string => Boolean(v))
  const [horses, markets] = await Promise.all([
    horseIds.length
      ? supabase.from("horse_listings").select("id, title").in("id", horseIds)
      : Promise.resolve({ data: [] as Array<{ id: string; title: string }> }),
    marketIds.length
      ? supabase.from("marketplace_listings").select("id, title").in("id", marketIds)
      : Promise.resolve({ data: [] as Array<{ id: string; title: string }> }),
  ])
  const horseTitle = new Map((horses.data ?? []).map((h) => [h.id, h.title]))
  const marketTitle = new Map((markets.data ?? []).map((m) => [m.id, m.title]))

  return data.map((r) => ({
    id: r.id,
    reason: r.reason,
    details: r.details,
    status: r.status,
    createdAt: r.created_at,
    listingKind: r.horse_listing_id ? "horse" : "marketplace",
    listingTitle: r.horse_listing_id
      ? horseTitle.get(r.horse_listing_id) ?? "Horse listing"
      : marketTitle.get(r.marketplace_listing_id ?? "") ?? "Marketplace listing",
  }))
}

export type AdminCategory = {
  id: string
  name: string
  slug: string
  categoryType: string
  sortOrder: number
  isActive: boolean
  featured: boolean
}

export async function getAdminCategories(): Promise<AdminCategory[]> {
  if (!hasSupabaseEnv()) return []
  const supabase = await client()
  const { data } = await supabase
    .from("categories")
    .select("id, name, slug, category_type, sort_order, is_active, metadata")
    .order("category_type", { ascending: true })
    .order("sort_order", { ascending: true })
  return (data ?? []).map((c) => ({
    id: c.id,
    name: c.name,
    slug: c.slug,
    categoryType: c.category_type,
    sortOrder: c.sort_order,
    isActive: c.is_active,
    featured: Boolean((c.metadata as Record<string, unknown> | null)?.featured),
  }))
}

export type AdminSaleEvent = {
  id: string
  name: string
  slug: string
  status: string
  eventType: string
  startsAt: string | null
}

export async function getAdminSaleEvents(): Promise<AdminSaleEvent[]> {
  if (!hasSupabaseEnv()) return []
  const supabase = await client()
  const { data } = await supabase
    .from("sale_events")
    .select("id, name, slug, status, event_type, starts_at")
    .order("created_at", { ascending: false })
    .limit(100)
  return (
    (data as Array<{
      id: string
      name: string
      slug: string
      status: string
      event_type: string
      starts_at: string | null
    }> | null) ?? []
  ).map((e) => ({
    id: e.id,
    name: e.name,
    slug: e.slug,
    status: e.status,
    eventType: e.event_type,
    startsAt: e.starts_at,
  }))
}
