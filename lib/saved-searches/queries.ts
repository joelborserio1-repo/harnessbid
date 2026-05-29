import {
  createSupabaseServerAuthClient,
  hasSupabaseEnv,
} from "@/lib/supabase/auth-server"

export type SavedSearchView = {
  id: string
  name: string
  searchType: string
  searchTypeLabel: string
  filters: Record<string, unknown>
  alertEnabled: boolean
  href: string
  createdAt: string
}

const TYPE_LABELS: Record<string, string> = {
  horse_auction: "Horse auctions",
  buy_now_horse: "Buy now horses",
  marketplace: "Marketplace",
}

const TYPE_BASE_PATH: Record<string, string> = {
  horse_auction: "/auctions",
  buy_now_horse: "/horses/buy-now",
  marketplace: "/marketplace",
}

function buildHref(searchType: string, filters: Record<string, unknown>): string {
  const base = TYPE_BASE_PATH[searchType] ?? "/marketplace"
  const params = new URLSearchParams()
  for (const [key, value] of Object.entries(filters)) {
    if (value == null || value === "") continue
    params.set(key, String(value))
  }
  const qs = params.toString()
  return qs ? `${base}?${qs}` : base
}

/** Returns the current user's saved searches, newest first. */
export async function getUserSavedSearches(): Promise<SavedSearchView[]> {
  if (!hasSupabaseEnv()) return []
  const supabase = await createSupabaseServerAuthClient()
  const {
    data: { user },
  } = await supabase.auth.getUser()
  if (!user) return []

  const { data } = await supabase
    .from("saved_searches")
    .select("id, name, search_type, filters, alert_enabled, created_at")
    .eq("profile_id", user.id)
    .order("created_at", { ascending: false })

  return (data ?? []).map((s) => {
    const filters = (s.filters ?? {}) as Record<string, unknown>
    return {
      id: s.id,
      name: s.name,
      searchType: s.search_type,
      searchTypeLabel: TYPE_LABELS[s.search_type] ?? "Search",
      filters,
      alertEnabled: s.alert_enabled,
      href: buildHref(s.search_type, filters),
      createdAt: s.created_at,
    }
  })
}
