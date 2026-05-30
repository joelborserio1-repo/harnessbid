import { NextResponse, type NextRequest } from "next/server"
import { createSupabaseServiceClient, hasServiceRoleEnv } from "@/lib/supabase/admin"
import { checkCronAuth } from "@/lib/cron-auth"
import { logger } from "@/lib/logger"

/**
 * Saved-search alert sweep. For each alert-enabled saved search, finds listings
 * matching its filters that were published since the last run window, and
 * creates one summary notification per search with new matches. The email
 * outbox (/api/cron/emails) then delivers those notifications. Secured by
 * CRON_SECRET; idempotent within the window via a recency cutoff.
 */
const WINDOW_HOURS = 24

type Filters = Record<string, string | undefined>

async function runSweep(request: NextRequest) {
  const authReason = checkCronAuth(request)
  if (authReason === "cron_not_configured") return NextResponse.json({ error: authReason }, { status: 503 })
  if (authReason === "unauthorized") return NextResponse.json({ error: authReason }, { status: 401 })
  if (!hasServiceRoleEnv()) return NextResponse.json({ error: "service_role_not_configured" }, { status: 503 })

  const supabase = createSupabaseServiceClient()
  const since = new Date(Date.now() - WINDOW_HOURS * 3600 * 1000).toISOString()

  try {
    const { data: searches, error } = await supabase
      .from("saved_searches")
      .select("id, profile_id, name, search_type, filters")
      .eq("alert_enabled", true)
      .limit(500)

    if (error) {
      logger.error("cron_saved_searches_query_failed", { error: error.message })
      return NextResponse.json({ ok: false, error: error.message }, { status: 500 })
    }

    let created = 0
    for (const s of searches ?? []) {
      const filters = (s.filters ?? {}) as Filters
      let matchCount = 0

      if (s.search_type === "horse_auction" || s.search_type === "buy_now_horse") {
        let q = supabase
          .from("horse_listings")
          .select("id", { count: "exact", head: true })
          .eq("status", "published")
          .gte("published_at", since)
          .eq("sale_mode", s.search_type === "horse_auction" ? "auction" : "buy_now")
        if (filters.gait) q = q.eq("gait", filters.gait as never)
        if (filters.sex) q = q.eq("sex", filters.sex as never)
        const { count } = await q
        matchCount = count ?? 0
      } else if (s.search_type === "marketplace") {
        let q = supabase
          .from("marketplace_listings")
          .select("id", { count: "exact", head: true })
          .eq("status", "published")
          .gte("published_at", since)
        if (filters.category) {
          const { data: cat } = await supabase.from("categories").select("id").eq("slug", filters.category).limit(1)
          const catId = cat?.[0]?.id
          if (catId) q = q.eq("category_id", catId)
        }
        const { count } = await q
        matchCount = count ?? 0
      }

      if (matchCount > 0) {
        // De-dupe: skip if we already notified this search within the window.
        const { count: recent } = await supabase
          .from("notifications")
          .select("id", { count: "exact", head: true })
          .eq("profile_id", s.profile_id)
          .eq("type", "saved_search")
          .eq("related_entity_id", s.id)
          .gte("created_at", since)

        if ((recent ?? 0) === 0) {
          await supabase.from("notifications").insert({
            profile_id: s.profile_id,
            type: "saved_search",
            title: `New matches for "${s.name}"`,
            body: `${matchCount} new listing${matchCount === 1 ? "" : "s"} match your saved search.`,
            link_url: "/search",
            related_entity_type: "saved_search",
            related_entity_id: s.id,
          })
          created++
        }
      }
    }

    logger.info("cron_saved_searches_ok", { searches: searches?.length ?? 0, created })
    return NextResponse.json({ ok: true, searches: searches?.length ?? 0, created })
  } catch (err) {
    logger.error("cron_saved_searches_exception", { error: err instanceof Error ? err.message : String(err) })
    return NextResponse.json({ ok: false }, { status: 500 })
  }
}

export async function POST(request: NextRequest) {
  return runSweep(request)
}

export async function GET(request: NextRequest) {
  return runSweep(request)
}
