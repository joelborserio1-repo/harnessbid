import Link from "next/link"
import { Building2, MessageSquare } from "lucide-react"
import { Badge } from "@/components/ui/badge"
import { Card, CardContent } from "@/components/ui/card"
import type { ConversationListItem } from "@/lib/conversations/queries"

function timeAgo(iso: string) {
  const mins = Math.floor((Date.now() - new Date(iso).getTime()) / 60000)
  if (mins < 1) return "just now"
  if (mins < 60) return `${mins}m ago`
  const hrs = Math.floor(mins / 60)
  if (hrs < 24) return `${hrs}h ago`
  return `${Math.floor(hrs / 24)}d ago`
}

export function ConversationList({ items }: { items: ConversationListItem[] }) {
  if (items.length === 0) {
    return (
      <Card className="border-border/70">
        <CardContent className="flex flex-col items-center gap-4 p-10 text-center">
          <div className="flex h-14 w-14 items-center justify-center rounded-lg border border-accent/40 bg-accent/10">
            <MessageSquare className="h-7 w-7 text-primary" />
          </div>
          <h2 className="font-sora text-xl font-semibold text-foreground">No conversations yet</h2>
          <p className="max-w-md text-sm leading-6 text-muted-foreground">
            When you contact a seller — or a buyer enquires about your listing — the conversation
            appears here.
          </p>
        </CardContent>
      </Card>
    )
  }

  return (
    <div className="space-y-2">
      {items.map((c) => (
        <Link key={c.id} href={`/dashboard/messages/${c.id}`}>
          <Card className={"border-border/70 transition-colors hover:border-accent " + (c.unread > 0 ? "bg-accent/5" : "")}>
            <CardContent className="flex items-center gap-3 p-4">
              <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-accent/10">
                {c.enterprise ? (
                  <Building2 className="h-5 w-5 text-primary" />
                ) : (
                  <MessageSquare className="h-5 w-5 text-primary" />
                )}
              </div>
              <div className="min-w-0 flex-1">
                <div className="flex items-center gap-2">
                  <span className="truncate font-medium text-foreground">{c.counterpartName}</span>
                  <Badge variant="secondary" className="shrink-0 text-[10px]">
                    {c.role === "buyer" ? "Buying" : "Selling"}
                  </Badge>
                  {c.unread > 0 && (
                    <span className="ml-auto flex h-5 min-w-5 items-center justify-center rounded-full bg-accent px-1.5 text-xs font-semibold text-accent-foreground">
                      {c.unread}
                    </span>
                  )}
                </div>
                {c.listingTitle && (
                  <p className="truncate text-xs text-muted-foreground">Re: {c.listingTitle}</p>
                )}
                <p className={"truncate text-sm " + (c.unread > 0 ? "font-medium text-foreground" : "text-muted-foreground")}>
                  {c.lastBody || "No messages yet"}
                </p>
              </div>
              <span className="shrink-0 self-start text-[10px] text-muted-foreground">{timeAgo(c.lastAt)}</span>
            </CardContent>
          </Card>
        </Link>
      ))}
    </div>
  )
}
