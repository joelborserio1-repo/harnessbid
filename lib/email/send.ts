import "server-only"
import { logger } from "@/lib/logger"

/**
 * Transactional email via the Resend REST API (no SDK dependency).
 *
 * Safe no-op when RESEND_API_KEY is absent: returns { ok: false, skipped: true }
 * and logs at debug, so the app runs fine in dev / before email is configured.
 * Never throws — email failures must not break the request that triggered them.
 */
const RESEND_ENDPOINT = "https://api.resend.com/emails"

export function emailConfigured(): boolean {
  return Boolean(process.env.RESEND_API_KEY)
}

function fromAddress(): string {
  // EMAIL_FROM e.g. "HarnessBid <noreply@harnessbid.com>". Falls back to a
  // resend.dev sender so it still sends in testing before a domain is verified.
  return process.env.EMAIL_FROM || "HarnessBid <onboarding@resend.dev>"
}

export type SendResult = { ok: boolean; skipped?: boolean; id?: string; error?: string }

export async function sendEmail(params: {
  to: string | string[]
  subject: string
  html: string
  text?: string
  replyTo?: string
}): Promise<SendResult> {
  const key = process.env.RESEND_API_KEY
  if (!key) {
    logger.debug("email_skipped_no_key", { subject: params.subject })
    return { ok: false, skipped: true }
  }

  try {
    const res = await fetch(RESEND_ENDPOINT, {
      method: "POST",
      headers: {
        Authorization: `Bearer ${key}`,
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        from: fromAddress(),
        to: Array.isArray(params.to) ? params.to : [params.to],
        subject: params.subject,
        html: params.html,
        text: params.text,
        reply_to: params.replyTo,
      }),
    })

    if (!res.ok) {
      const body = await res.text().catch(() => "")
      logger.error("email_send_failed", { status: res.status, body: body.slice(0, 300) })
      return { ok: false, error: `resend_${res.status}` }
    }

    const data = (await res.json().catch(() => ({}))) as { id?: string }
    logger.info("email_sent", { subject: params.subject, id: data.id })
    return { ok: true, id: data.id }
  } catch (err) {
    logger.error("email_exception", { error: err instanceof Error ? err.message : String(err) })
    return { ok: false, error: "exception" }
  }
}
