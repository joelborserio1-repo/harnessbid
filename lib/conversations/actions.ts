"use server"

import { revalidatePath } from "next/cache"
import {
  createSupabaseServerAuthClient,
  hasSupabaseEnv,
} from "@/lib/supabase/auth-server"
import { LIMITS, rateLimit } from "@/lib/rate-limit"

export type MessageActionResult = { ok: boolean; error?: string }

/** Sends a reply in a conversation. Participant-only (enforced by RLS). */
export async function sendMessageAction(
  conversationId: string,
  body: string,
  attachment?: { url: string; type: string } | null,
): Promise<MessageActionResult> {
  if (!hasSupabaseEnv()) return { ok: false, error: "Messaging is not available yet." }
  const trimmed = body.trim()
  if (!conversationId) return { ok: false, error: "Invalid conversation." }
  if (trimmed.length === 0 && !attachment) return { ok: false, error: "Enter a message." }
  if (trimmed.length > 4000) return { ok: false, error: "Message is too long." }

  const supabase = await createSupabaseServerAuthClient()
  const {
    data: { user },
  } = await supabase.auth.getUser()
  if (!user) return { ok: false, error: "Please sign in." }

  const limited = rateLimit(`msg:${user.id}`, LIMITS.message.limit, LIMITS.message.windowMs)
  if (!limited.allowed) {
    return { ok: false, error: "You're sending messages too quickly. Please slow down." }
  }

  // Confirm participation (RLS only returns the row to participants).
  const { data: conv } = await supabase
    .from("conversations")
    .select("id")
    .eq("id", conversationId)
    .maybeSingle()
  if (!conv) return { ok: false, error: "Conversation not found." }

  // Validate the attachment URL belongs to our bucket (defense-in-depth).
  let attachmentUrl: string | null = null
  let attachmentType: string | null = null
  if (attachment?.url) {
    if (!attachment.url.includes("/message-attachments/")) {
      return { ok: false, error: "Invalid attachment." }
    }
    attachmentUrl = attachment.url
    attachmentType = attachment.type || null
  }

  const { error } = await supabase.from("messages").insert({
    conversation_id: conversationId,
    sender_profile_id: user.id,
    body: trimmed || (attachmentType?.startsWith("image/") ? "Shared an image" : "Shared an attachment"),
    attachment_url: attachmentUrl,
    attachment_type: attachmentType,
  } as never)
  if (error) return { ok: false, error: "Could not send your message. Please try again." }

  revalidatePath(`/dashboard/messages/${conversationId}`)
  revalidatePath("/dashboard/messages")
  return { ok: true }
}

/** Marks all messages from the other participant as read. */
export async function markConversationReadAction(conversationId: string): Promise<MessageActionResult> {
  if (!hasSupabaseEnv()) return { ok: false, error: "not_configured" }
  const supabase = await createSupabaseServerAuthClient()
  const {
    data: { user },
  } = await supabase.auth.getUser()
  if (!user) return { ok: false, error: "auth" }

  const { error } = await supabase
    .from("messages")
    .update({ read_at: new Date().toISOString() })
    .eq("conversation_id", conversationId)
    .neq("sender_profile_id", user.id)
    .is("read_at", null)
  if (error) return { ok: false, error: "failed" }

  revalidatePath("/dashboard/messages")
  return { ok: true }
}
