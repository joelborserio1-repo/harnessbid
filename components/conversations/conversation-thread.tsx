"use client"

import { useCallback, useEffect, useRef, useState, useTransition } from "react"
import Link from "next/link"
import { Building2, Send } from "lucide-react"
import { Badge } from "@/components/ui/badge"
import { Button } from "@/components/ui/button"
import { Textarea } from "@/components/ui/textarea"
import { createSupabaseBrowserClient } from "@/lib/supabase/client"
import { useConversationRealtime } from "@/hooks/use-conversation-realtime"
import {
  markConversationReadAction,
  sendMessageAction,
} from "@/lib/conversations/actions"
import type { ConversationDetail, ConversationMessage } from "@/lib/conversations/queries"

const hasEnv = Boolean(
  process.env.NEXT_PUBLIC_SUPABASE_URL && process.env.NEXT_PUBLIC_SUPABASE_ANON_KEY,
)

function time(iso: string) {
  return new Intl.DateTimeFormat("en", { dateStyle: "medium", timeStyle: "short" }).format(new Date(iso))
}

export function ConversationThread({ conversation }: { conversation: ConversationDetail }) {
  const [messages, setMessages] = useState<ConversationMessage[]>(conversation.messages)
  const [body, setBody] = useState("")
  const [error, setError] = useState<string | null>(null)
  const [pending, startTransition] = useTransition()
  const endRef = useRef<HTMLDivElement>(null)

  const scrollToEnd = useCallback(() => {
    endRef.current?.scrollIntoView({ behavior: "smooth", block: "end" })
  }, [])

  const refresh = useCallback(async () => {
    if (!hasEnv) return
    try {
      const supabase = createSupabaseBrowserClient()
      const {
        data: { user },
      } = await supabase.auth.getUser()
      const { data } = await supabase
        .from("messages")
        .select("id, sender_profile_id, body, read_at, created_at")
        .eq("conversation_id", conversation.id)
        .order("created_at", { ascending: true })
      if (data) {
        setMessages(
          data.map((m) => ({
            id: m.id,
            mine: user ? m.sender_profile_id === user.id : false,
            body: m.body,
            createdAt: m.created_at,
            read: m.read_at !== null,
          })),
        )
      }
      await markConversationReadAction(conversation.id)
    } catch {
      /* ignore */
    }
  }, [conversation.id])

  useEffect(() => {
    // Mark read on open.
    markConversationReadAction(conversation.id)
  }, [conversation.id])

  useConversationRealtime(
    conversation.id,
    useCallback(() => refresh(), [refresh]),
  )

  useEffect(() => {
    scrollToEnd()
  }, [messages, scrollToEnd])

  function send() {
    setError(null)
    const value = body.trim()
    if (!value) return
    // Optimistic append.
    const optimistic: ConversationMessage = {
      id: `tmp-${Date.now()}`,
      mine: true,
      body: value,
      createdAt: new Date().toISOString(),
      read: false,
    }
    setMessages((prev) => [...prev, optimistic])
    setBody("")
    startTransition(async () => {
      const result = await sendMessageAction(conversation.id, value)
      if (!result.ok) {
        setError(result.error ?? "Could not send.")
        setMessages((prev) => prev.filter((m) => m.id !== optimistic.id))
        setBody(value)
        return
      }
      refresh()
    })
  }

  return (
    <div className="flex h-[70vh] flex-col rounded-lg border border-border bg-card">
      {/* Header */}
      <div className="flex items-center justify-between gap-3 border-b border-border p-4">
        <div className="min-w-0">
          <div className="flex items-center gap-2">
            <h2 className="truncate font-sora font-semibold text-foreground">
              {conversation.counterpartName}
            </h2>
            {conversation.enterprise && (
              <Badge className="bg-accent text-accent-foreground">
                <Building2 className="mr-1 h-3 w-3" />
                Enterprise
              </Badge>
            )}
            <Badge variant="secondary" className="capitalize">
              {conversation.role === "buyer" ? "You: buyer" : "You: seller"}
            </Badge>
          </div>
          {conversation.listingTitle && (
            <p className="truncate text-xs text-muted-foreground">
              {conversation.listingHref ? (
                <Link href={conversation.listingHref} className="hover:text-primary">
                  {conversation.listingTitle}
                </Link>
              ) : (
                conversation.listingTitle
              )}
            </p>
          )}
        </div>
      </div>

      {/* Messages */}
      <div className="flex-1 space-y-3 overflow-y-auto p-4">
        {messages.map((m) => {
          const mine = m.mine
          return (
            <div key={m.id} className={"flex " + (mine ? "justify-end" : "justify-start")}>
              <div
                className={
                  "max-w-[80%] rounded-lg px-3 py-2 text-sm " +
                  (mine
                    ? "bg-primary text-primary-foreground"
                    : "bg-secondary text-foreground")
                }
              >
                <p className="whitespace-pre-line">{m.body}</p>
                <p className={"mt-1 text-[10px] " + (mine ? "text-primary-foreground/60" : "text-muted-foreground")}>
                  {time(m.createdAt)}
                </p>
              </div>
            </div>
          )
        })}
        <div ref={endRef} />
      </div>

      {/* Composer */}
      <div className="border-t border-border p-3">
        {error && <p className="mb-2 text-sm text-destructive">{error}</p>}
        <div className="flex items-end gap-2">
          <Textarea
            value={body}
            onChange={(e) => setBody(e.target.value)}
            placeholder="Write a reply…"
            className="min-h-11 flex-1 resize-none"
            onKeyDown={(e) => {
              if (e.key === "Enter" && !e.shiftKey) {
                e.preventDefault()
                send()
              }
            }}
          />
          <Button
            onClick={send}
            disabled={pending || !body.trim()}
            className="bg-accent text-accent-foreground hover:bg-accent/90"
          >
            <Send className="mr-2 h-4 w-4" />
            Send
          </Button>
        </div>
        {/* TODO: image/document attachment placeholders (schema fields exist:
            messages.attachment_url / attachment_type). */}
      </div>
    </div>
  )
}
