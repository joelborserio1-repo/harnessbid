"use server"

import { revalidatePath } from "next/cache"
import {
  createSupabaseServerAuthClient,
  hasSupabaseEnv,
} from "@/lib/supabase/auth-server"

export type NotificationActionResult = { ok: boolean; error?: string }

/** Known notification types. `type` is stored as text for forward-compat. */
export type NotificationType =
  | "enquiry_received"
  | "enquiry_replied"
  | "watchlist"
  | "saved_search"
  | "system"

export async function markNotificationRead(id: string): Promise<NotificationActionResult> {
  if (!hasSupabaseEnv()) return { ok: false, error: "not_configured" }
  const supabase = await createSupabaseServerAuthClient()
  const {
    data: { user },
  } = await supabase.auth.getUser()
  if (!user) return { ok: false, error: "auth" }

  // RLS scopes the update to the owner; the profile filter is belt-and-braces.
  const { error } = await supabase
    .from("notifications")
    .update({ read_at: new Date().toISOString() })
    .eq("id", id)
    .eq("profile_id", user.id)
    .is("read_at", null)

  if (error) return { ok: false, error: "failed" }
  revalidatePath("/dashboard/notifications")
  return { ok: true }
}

export async function markAllNotificationsRead(): Promise<NotificationActionResult> {
  if (!hasSupabaseEnv()) return { ok: false, error: "not_configured" }
  const supabase = await createSupabaseServerAuthClient()
  const {
    data: { user },
  } = await supabase.auth.getUser()
  if (!user) return { ok: false, error: "auth" }

  const { error } = await supabase
    .from("notifications")
    .update({ read_at: new Date().toISOString() })
    .eq("profile_id", user.id)
    .is("read_at", null)

  if (error) return { ok: false, error: "failed" }
  revalidatePath("/dashboard/notifications")
  return { ok: true }
}

export type CreateNotificationInput = {
  profileId: string
  type: NotificationType
  title: string
  body?: string | null
  linkUrl?: string | null
  relatedEntityType?: string | null
  relatedEntityId?: string | null
}

/**
 * Server-only notification creation. Intended to be called from server code
 * (actions / route handlers), never the client.
 *
 * NOTE: this inserts via the request-scoped (anon-key + user session) client,
 * so RLS only permits creating notifications for the current user (or admins).
 * Cross-user notifications (e.g. enquiry -> seller) are produced by the
 * SECURITY DEFINER `notify_seller_of_enquiry` trigger so that no service-role
 * key is ever exposed to the client.
 *
 * TODO: when a server-side service-role client is introduced (server-only),
 * route arbitrary cross-user notifications through it here.
 */
export async function createNotificationServerOnly(
  input: CreateNotificationInput,
): Promise<NotificationActionResult> {
  if (!hasSupabaseEnv()) return { ok: false, error: "not_configured" }
  const supabase = await createSupabaseServerAuthClient()
  const {
    data: { user },
  } = await supabase.auth.getUser()
  if (!user) return { ok: false, error: "auth" }

  const { error } = await supabase.from("notifications").insert({
    profile_id: input.profileId,
    type: input.type,
    title: input.title,
    body: input.body ?? null,
    link_url: input.linkUrl ?? null,
    related_entity_type: input.relatedEntityType ?? null,
    related_entity_id: input.relatedEntityId ?? null,
  })

  if (error) return { ok: false, error: "failed" }
  return { ok: true }
}
