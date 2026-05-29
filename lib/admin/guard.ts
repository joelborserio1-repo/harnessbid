import { redirect } from "next/navigation"
import {
  createSupabaseServerAuthClient,
  getCurrentProfile,
  hasSupabaseEnv,
} from "@/lib/supabase/auth-server"
import type { Database } from "@/lib/supabase/database.types"

type Role = Database["public"]["Enums"]["app_role"]

export type AdminContext = {
  userId: string
  role: Role
  isAdmin: boolean
  isModerator: boolean
  isStaff: boolean
}

/** Returns staff context, or null if the current user is not admin/moderator. */
export async function getAdminContext(): Promise<AdminContext | null> {
  if (!hasSupabaseEnv()) return null
  const supabase = await createSupabaseServerAuthClient()
  const {
    data: { user },
  } = await supabase.auth.getUser()
  if (!user) return null

  const profile = await getCurrentProfile()
  const role = (profile?.role ?? "buyer") as Role
  const isAdmin = role === "admin"
  const isModerator = role === "moderator"
  const isStaff = isAdmin || isModerator
  if (!isStaff) return null

  return { userId: user.id, role, isAdmin, isModerator, isStaff }
}

/**
 * Server-side guard for admin routes/actions. Redirects non-staff users away
 * (never relies on client-side checks). Returns the staff context on success.
 */
export async function requireStaff(): Promise<AdminContext> {
  const ctx = await getAdminContext()
  if (!ctx) redirect("/")
  return ctx
}

/** Writes a moderation log row (audit placeholder). Best-effort. */
export async function logModeration(
  action: string,
  entityType: string,
  entityId: string | null,
  notes?: string,
): Promise<void> {
  if (!hasSupabaseEnv()) return
  const supabase = await createSupabaseServerAuthClient()
  const {
    data: { user },
  } = await supabase.auth.getUser()
  if (!user) return
  await supabase.from("moderation_logs").insert({
    actor_profile_id: user.id,
    action,
    entity_type: entityType,
    entity_id: entityId,
    notes: notes ?? null,
  })
}
