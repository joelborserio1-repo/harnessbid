"use client"

import { useCallback, useEffect, useRef, useState, useTransition } from "react"
import Link from "next/link"
import { Building2, Send, Paperclip, X, FileText, Loader2 } from "lucide-react"
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
  const [attachment, setAttachment] = useState<{ url: string; type: string; name: string } | null>(null)
  const [uploading, setUploading] = useState(false)
  const fileRef = useRef<HTMLInputElement>(null)
  const endRef = useRef<HTMLDivElement>(null)

  async function uploadAttachment(file: File) {
    if (!hasEnv) return
    setError(null)
    setUploading(true)
    try {
      const supabase = createSupabaseBrowserClient()
      const { data: { user } } = await supabase.auth.getUser()
      if (!user) throw new Error("Please sign in.")
      const safe = file.name.replace(/[^a-zA-Z0-9._-]/g, "-").slice(-50)
      const path = `${user.id}/${conversation.id}/${Date.now()}-${safe}`
      const { error: upErr } = await supabase.storage.from("message-attachments").upload(path, file, { upsert: false, contentType: file.type })
      if (upErr) throw upErr
      const { data } = supabase.storage.from("message-attachments").getPublicUrl(path)
      setAttachment({ url: data.publicUrl, type: file.type, name: file.name })
    } catch (e) {
      setError(e instanceof Error ? e.message : "Upload failed")
    } finally {
      setUploading(false)
    }
  }

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
    if (!value && !attachment) return
    const att = attachment
    // Optimistic append.
    const optimistic: ConversationMessage = {
      id: `tmp-${Date.now()}`,
      mine: true,
      body: value || (att?.type.startsWith("image/") ? "Shared an image" : att ? "Shared an attachment" : ""),
      createdAt: new Date().toISOString(),
      read: false,
      attachmentUrl: att?.url ?? null,
      attachmentType: att?.type ?? null,
    }
    setMessages((prev) => [...prev, optimistic])
    setBody("")
    setAttachment(null)
    startTransition(async () => {
      const result = await sendMessageAction(conversation.id, value, att ? { url: att.url, type: att.type } : null)
      if (!result.ok) {
        setError(result.error ?? "Could not send.")
        setMessages((prev) => prev.filter((m) => m.id !== optimistic.id))
        setBody(value)
        setAttachment(att)
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
                {m.attachmentUrl && (
                  m.attachmentType?.startsWith("image/") ? (
                    <a href={m.attachmentUrl} target="_blank" rel="noopener noreferrer" className="mb-1.5 block">
                      {/* eslint-disable-next-line @next/next/no-img-element */}
                      <img src={m.attachmentUrl} alt="attachment" className="max-h-48 rounded-sm border border-black/10" />
                    </a>
                  ) : (
                    <a
                      href={m.attachmentUrl}
                      target="_blank"
                      rel="noopener noreferrer"
                      className={"mb-1.5 inline-flex items-center gap-2 rounded-sm border px-2 py-1 text-xs " + (mine ? "border-primary-foreground/30" : "border-border")}
                    >
                      <FileText className="h-3.5 w-3.5" /> View attachment
                    </a>
                  )
                )}
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
        {attachment && (
          <div className="mb-2 flex items-center gap-2 rounded-sm border border-border bg-secondary/50 px-2 py-1.5 text-xs">
            <FileText className="h-3.5 w-3.5 text-accent shrink-0" />
            <span className="truncate flex-1">{attachment.name}</span>
            <button onClick={() => setAttachment(null)} aria-label="Remove attachment" className="hover:text-destructive">
              <X className="h-3.5 w-3.5" />
            </button>
          </div>
        )}
        <div className="flex items-end gap-2">
          <input
            ref={fileRef}
            type="file"
            accept="image/jpeg,image/png,image/webp,image/avif,application/pdf"
            className="hidden"
            onChange={(e) => {
              const f = e.target.files?.[0]
              if (f) uploadAttachment(f)
              e.target.value = ""
            }}
          />
          <Button
            type="button"
            variant="outline"
            size="icon"
            className="shrink-0"
            disabled={uploading}
            onClick={() => fileRef.current?.click()}
            aria-label="Attach file"
          >
            {uploading ? <Loader2 className="h-4 w-4 animate-spin" /> : <Paperclip className="h-4 w-4" />}
          </Button>
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
            disabled={pending || uploading || (!body.trim() && !attachment)}
            className="bg-accent text-accent-foreground hover:bg-accent/90"
          >
            <Send className="mr-2 h-4 w-4" />
            Send
          </Button>
        </div>
      </div>
    </div>
  )
}
