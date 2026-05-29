"use client"

import { useCallback, useEffect, useState } from "react"
import { useRouter } from "next/navigation"
import Link from "next/link"
import { Bell, Check } from "lucide-react"
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu"
import { createSupabaseBrowserClient } from "@/lib/supabase/client"
import { useAuthUser } from "@/hooks/use-auth-user"
import { useNotificationsRealtime } from "@/hooks/use-notifications-realtime"
import {
  markAllNotificationsRead,
  markNotificationRead,
} from "@/lib/notifications/actions"

const hasEnv = Boolean(
  process.env.NEXT_PUBLIC_SUPABASE_URL && process.env.NEXT_PUBLIC_SUPABASE_ANON_KEY,
)

type Item = {
  id: string
  title: string
  body: string | null
  link_url: string | null
  read_at: string | null
  created_at: string
}

function timeAgo(iso: string) {
  const mins = Math.floor((Date.now() - new Date(iso).getTime()) / 60000)
  if (mins < 1) return "just now"
  if (mins < 60) return `${mins}m ago`
  const hrs = Math.floor(mins / 60)
  if (hrs < 24) return `${hrs}h ago`
  return `${Math.floor(hrs / 24)}d ago`
}

export function NotificationsMenu() {
  const { isAuthenticated } = useAuthUser()
  const router = useRouter()
  const [unread, setUnread] = useState(0)
  const [items, setItems] = useState<Item[]>([])
  const [open, setOpen] = useState(false)

  const refreshCount = useCallback(async () => {
    if (!hasEnv) return
    try {
      const supabase = createSupabaseBrowserClient()
      const {
        data: { user },
      } = await supabase.auth.getUser()
      if (!user) {
        setUnread(0)
        return
      }
      const { count } = await supabase
        .from("notifications")
        .select("id", { count: "exact", head: true })
        .eq("profile_id", user.id)
        .is("read_at", null)
      setUnread(count ?? 0)
    } catch {
      /* ignore */
    }
  }, [])

  const loadItems = useCallback(async () => {
    if (!hasEnv) return
    try {
      const supabase = createSupabaseBrowserClient()
      const {
        data: { user },
      } = await supabase.auth.getUser()
      if (!user) return
      const { data } = await supabase
        .from("notifications")
        .select("id, title, body, link_url, read_at, created_at")
        .eq("profile_id", user.id)
        .order("created_at", { ascending: false })
        .limit(12)
      setItems(data ?? [])
    } catch {
      /* ignore */
    }
  }, [])

  useEffect(() => {
    if (isAuthenticated) refreshCount()
    else {
      setUnread(0)
      setItems([])
    }
  }, [isAuthenticated, refreshCount])

  // Live unread updates (no-op if Realtime isn't enabled on the table).
  useNotificationsRealtime(useCallback(() => refreshCount(), [refreshCount]))

  useEffect(() => {
    if (open) loadItems()
  }, [open, loadItems])

  async function onItemClick(item: Item) {
    if (!item.read_at) {
      await markNotificationRead(item.id)
      setItems((prev) => prev.map((i) => (i.id === item.id ? { ...i, read_at: "now" } : i)))
      setUnread((u) => Math.max(0, u - 1))
    }
    setOpen(false)
    if (item.link_url) router.push(item.link_url)
  }

  async function onMarkAll() {
    await markAllNotificationsRead()
    setItems((prev) => prev.map((i) => ({ ...i, read_at: "now" })))
    setUnread(0)
  }

  if (!isAuthenticated) return null

  return (
    <DropdownMenu open={open} onOpenChange={setOpen}>
      <DropdownMenuTrigger asChild>
        <button
          type="button"
          aria-label="Notifications"
          className="relative flex h-9 w-9 items-center justify-center rounded-md text-primary-foreground/70 transition-colors hover:bg-primary-foreground/10 hover:text-primary-foreground"
        >
          <Bell className="h-5 w-5" />
          {unread > 0 && (
            <span className="absolute -right-0.5 -top-0.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-accent px-1 text-[10px] font-semibold text-accent-foreground">
              {unread > 9 ? "9+" : unread}
            </span>
          )}
        </button>
      </DropdownMenuTrigger>
      <DropdownMenuContent align="end" className="w-80 p-0">
        <div className="flex items-center justify-between border-b border-border px-3 py-2">
          <span className="font-sora text-sm font-semibold text-foreground">Notifications</span>
          {unread > 0 && (
            <button
              type="button"
              onClick={onMarkAll}
              className="flex items-center gap-1 text-xs font-medium text-primary hover:underline"
            >
              <Check className="h-3 w-3" />
              Mark all read
            </button>
          )}
        </div>

        <div className="max-h-80 overflow-y-auto">
          {items.length === 0 ? (
            <div className="px-4 py-8 text-center text-sm text-muted-foreground">
              You&apos;re all caught up.
            </div>
          ) : (
            items.map((item) => (
              <button
                key={item.id}
                type="button"
                onClick={() => onItemClick(item)}
                className={
                  "flex w-full flex-col items-start gap-0.5 border-b border-border px-3 py-2.5 text-left transition-colors hover:bg-secondary " +
                  (item.read_at ? "" : "bg-accent/5")
                }
              >
                <div className="flex w-full items-center gap-2">
                  {!item.read_at && <span className="h-2 w-2 shrink-0 rounded-full bg-accent" />}
                  <span className="truncate text-sm font-medium text-foreground">{item.title}</span>
                  <span className="ml-auto shrink-0 text-[10px] text-muted-foreground">
                    {timeAgo(item.created_at)}
                  </span>
                </div>
                {item.body && (
                  <span className="line-clamp-2 pl-4 text-xs text-muted-foreground">{item.body}</span>
                )}
              </button>
            ))
          )}
        </div>

        <div className="border-t border-border p-2">
          <Link
            href="/dashboard/notifications"
            onClick={() => setOpen(false)}
            className="block rounded-md px-3 py-2 text-center text-sm font-medium text-primary hover:bg-secondary"
          >
            View all notifications
          </Link>
        </div>
      </DropdownMenuContent>
    </DropdownMenu>
  )
}
