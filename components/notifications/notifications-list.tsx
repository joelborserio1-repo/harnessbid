"use client"

import { useState, useTransition } from "react"
import { useRouter } from "next/navigation"
import { Bell, Check } from "lucide-react"
import { Button } from "@/components/ui/button"
import { Card, CardContent } from "@/components/ui/card"
import {
  markAllNotificationsRead,
  markNotificationRead,
} from "@/lib/notifications/actions"
import type { NotificationView } from "@/lib/notifications/queries"

function formatDate(iso: string) {
  return new Intl.DateTimeFormat("en", { dateStyle: "medium", timeStyle: "short" }).format(
    new Date(iso),
  )
}

export function NotificationsList({ initial }: { initial: NotificationView[] }) {
  const [items, setItems] = useState(initial)
  const [, startTransition] = useTransition()
  const router = useRouter()
  const hasUnread = items.some((i) => !i.read)

  function open(item: NotificationView) {
    if (!item.read) {
      setItems((prev) => prev.map((i) => (i.id === item.id ? { ...i, read: true } : i)))
      startTransition(async () => {
        await markNotificationRead(item.id)
      })
    }
    if (item.linkUrl) router.push(item.linkUrl)
  }

  function markAll() {
    setItems((prev) => prev.map((i) => ({ ...i, read: true })))
    startTransition(async () => {
      await markAllNotificationsRead()
    })
  }

  if (items.length === 0) {
    return (
      <Card className="border-border/70">
        <CardContent className="flex flex-col items-center gap-4 p-10 text-center">
          <div className="flex h-14 w-14 items-center justify-center rounded-lg border border-accent/40 bg-accent/10">
            <Bell className="h-7 w-7 text-primary" />
          </div>
          <h2 className="font-sora text-xl font-semibold text-foreground">No notifications yet</h2>
          <p className="max-w-md text-sm leading-6 text-muted-foreground">
            Enquiries, saved-search alerts, and account updates will appear here.
          </p>
        </CardContent>
      </Card>
    )
  }

  return (
    <div className="space-y-3">
      {hasUnread && (
        <div className="flex justify-end">
          <Button variant="outline" size="sm" onClick={markAll}>
            <Check className="mr-1 h-3.5 w-3.5" />
            Mark all read
          </Button>
        </div>
      )}
      {items.map((item) => (
        <button
          key={item.id}
          type="button"
          onClick={() => open(item)}
          className={
            "flex w-full flex-col items-start gap-1 rounded-lg border p-4 text-left transition-colors hover:bg-secondary " +
            (item.read ? "border-border bg-card" : "border-accent/30 bg-accent/5")
          }
        >
          <div className="flex w-full items-center gap-2">
            {!item.read && <span className="h-2 w-2 shrink-0 rounded-full bg-accent" />}
            <span className="font-sora font-medium text-foreground">{item.title}</span>
            <span className="ml-auto shrink-0 text-xs text-muted-foreground">
              {formatDate(item.createdAt)}
            </span>
          </div>
          {item.body && <p className="text-sm leading-6 text-muted-foreground">{item.body}</p>}
        </button>
      ))}
    </div>
  )
}
