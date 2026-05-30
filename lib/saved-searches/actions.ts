"use server"

import { revalidatePath } from "next/cache"
import {
  createSupabaseServerAuthClient,
  hasSupabaseEnv,
} from "@/lib/supabase/auth-server"

export type SavedSearchActionState = { error?: string; success?: boolean; message?: string }

const NOT_CONFIGURED: SavedSearchActionState = {
  error: "Saved searches are not available yet. Please try again later.",
}

const ALLOWED_TYPES = ["horse_auction", "buy_now_horse", "marketplace"]

function parseFilters(raw: FormDataEntryValue | null): Record<string, unknown> {
  if (typeof raw !== "string" || !raw.trim()) return {}
  try {
    const parsed = JSON.parse(raw)
    return parsed && typeof parsed === "object" && !Array.isArray(parsed) ? parsed : {}
  } catch {
    return {}
  }
}

export async function createSavedSearchAction(
  _prev: SavedSearchActionState,
  formData: FormData,
): Promise<SavedSearchActionState> {
  if (!hasSupabaseEnv()) return NOT_CONFIGURED
  const supabase = await createSupabaseServerAuthClient()
  const {
    data: { user },
  } = await supabase.auth.getUser()
  if (!user) return { error: "Please sign in to save a search." }

  const name = String(formData.get("name") ?? "").trim()
  const searchType = String(formData.get("searchType") ?? "")
  const filters = parseFilters(formData.get("filters"))

  if (!ALLOWED_TYPES.includes(searchType)) return { error: "Invalid search type." }
  if (!name) return { error: "Give your saved search a name." }

  // Prevent obvious duplicates: same type + identical filters for this user.
  const { data: existing } = await supabase
    .from("saved_searches")
    .select("id, filters")
    .eq("profile_id", user.id)
    .eq("search_type", searchType)
  const filterKey = JSON.stringify(filters)
  if ((existing ?? []).some((row) => JSON.stringify(row.filters ?? {}) === filterKey)) {
    return { success: true, message: "You've already saved this search." }
  }

  const { error } = await supabase.from("saved_searches").insert({
    profile_id: user.id,
    name,
    search_type: searchType,
    filters: filters as never,
  })
  if (error) return { error: "Could not save your search. Please try again." }

  revalidatePath("/dashboard/saved-searches")
  return { success: true, message: "Search saved." }
}

export async function deleteSavedSearchAction(formData: FormData): Promise<void> {
  if (!hasSupabaseEnv()) return
  const supabase = await createSupabaseServerAuthClient()
  const {
    data: { user },
  } = await supabase.auth.getUser()
  if (!user) return

  const id = String(formData.get("id") ?? "")
  if (!id) return
  await supabase.from("saved_searches").delete().eq("id", id).eq("profile_id", user.id)
  revalidatePath("/dashboard/saved-searches")
}

export async function toggleSavedSearchAlerts(formData: FormData): Promise<void> {
  if (!hasSupabaseEnv()) return
  const supabase = await createSupabaseServerAuthClient()
  const {
    data: { user },
  } = await supabase.auth.getUser()
  if (!user) return

  const id = String(formData.get("id") ?? "")
  const enabled = formData.get("enabled") === "true"
  if (!id) return
  // Alert delivery: the /api/cron/saved-searches sweep matches enabled searches
  // against new listings and creates notifications, which /api/cron/emails then
  // emails. Toggling this flag opts the search in/out of that sweep.
  await supabase
    .from("saved_searches")
    .update({ alert_enabled: enabled })
    .eq("id", id)
    .eq("profile_id", user.id)
  revalidatePath("/dashboard/saved-searches")
}
