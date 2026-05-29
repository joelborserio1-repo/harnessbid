import {
  createSupabaseServerAuthClient,
  hasSupabaseEnv,
} from "@/lib/supabase/auth-server"

export type NotificationView = {
  id: string
  type: string
  title: string
  body: string | null
  linkUrl: string | null
  read: boolean
  createdAt: string
}

/** Returns the current user's notifications, newest first. */
export async function getUserNotifications(limit = 30): Promise<NotificationView[]> {
  if (!hasSupabaseEnv()) return []
  const supabase = await createSupabaseServerAuthClient()
  const {
    data: { user },
  } = await supabase.auth.getUser()
  if (!user) return []

  const { data } = await supabase
    .from("notifications")
    .select("id, type, title, body, link_url, read_at, created_at")
    .eq("profile_id", user.id)
    .order("created_at", { ascending: false })
    .limit(limit)

  return (data ?? []).map((n) => ({
    id: n.id,
    type: n.type,
    title: n.title,
    body: n.body,
    linkUrl: n.link_url,
    read: n.read_at !== null,
    createdAt: n.created_at,
  }))
}

/** Returns the count of unread notifications for the current user. */
export async function getUnreadNotificationCount(): Promise<number> {
  if (!hasSupabaseEnv()) return 0
  const supabase = await createSupabaseServerAuthClient()
  const {
    data: { user },
  } = await supabase.auth.getUser()
  if (!user) return 0

  const { count } = await supabase
    .from("notifications")
    .select("id", { count: "exact", head: true })
    .eq("profile_id", user.id)
    .is("read_at", null)

  return count ?? 0
}
