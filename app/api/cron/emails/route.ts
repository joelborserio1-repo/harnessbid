import { NextResponse, type NextRequest } from "next/server"
import { createSupabaseServiceClient, hasServiceRoleEnv } from "@/lib/supabase/admin"
import { checkCronAuth } from "@/lib/cron-auth"
import { sendEmail, emailConfigured } from "@/lib/email/send"
import { logger } from "@/lib/logger"

/**
 * Email outbox sweep. Delivers transactional emails for notifications that have
 * not yet been emailed (emailed_at is null), then stamps them sent. Works for
 * outbid / auction-won / enquiry / saved-search alerts regardless of whether the
 * notification row was created by app code or a SQL function — a single, robust
 * integration point.
 *
 * Secured by CRON_SECRET. Idempotent: rows are claimed by setting emailed_at, so
 * a re-run won't double-send. Safe no-op when RESEND_API_KEY is unset.
 */
const BATCH = 50

function subjectFor(title: string): string {
  return `${title} — HarnessBid`
}

function htmlFor(n: { title: string; body: string | null; link_url: string | null }, base: string): string {
  const link = n.link_url ? `${base}${n.link_url}` : base
  return `<!doctype html><html><body style="margin:0;background:#F1ECE3;font-family:-apple-system,Segoe UI,Roboto,Arial,sans-serif;color:#1B2B47;">
<table width="100%" cellpadding="0" cellspacing="0" style="padding:24px 0;"><tr><td align="center">
<table width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;">
<tr><td style="padding:8px 24px 18px;"><a href="${base}" style="text-decoration:none;color:#14294A;font-size:22px;font-weight:700;">HarnessBid</a></td></tr>
<tr><td style="background:#fff;border:1px solid #DFD8CB;border-radius:6px;padding:26px;">
<h1 style="margin:0 0 12px;font-size:19px;color:#14294A;">${n.title}</h1>
${n.body ? `<p style="margin:0 0 18px;font-size:15px;line-height:1.6;">${n.body}</p>` : ""}
<a href="${link}" style="display:inline-block;background:#E0A33C;color:#14294A;text-decoration:none;font-weight:600;font-size:14px;padding:11px 22px;border-radius:4px;">View on HarnessBid</a>
</td></tr>
<tr><td style="padding:16px 24px;color:#5B6478;font-size:12px;">You're receiving this because you have a HarnessBid account. <a href="${base}/dashboard/notifications" style="color:#14294A;">Manage preferences</a>.</td></tr>
</table></td></tr></table></body></html>`
}

async function runSweep(request: NextRequest) {
  const authReason = checkCronAuth(request)
  if (authReason === "cron_not_configured") return NextResponse.json({ error: authReason }, { status: 503 })
  if (authReason === "unauthorized") return NextResponse.json({ error: authReason }, { status: 401 })
  if (!hasServiceRoleEnv()) return NextResponse.json({ error: "service_role_not_configured" }, { status: 503 })

  // No email provider -> claim nothing, report skipped (keeps the route healthy).
  if (!emailConfigured()) {
    return NextResponse.json({ ok: true, skipped: "email_not_configured", sent: 0 })
  }

  const base = process.env.NEXT_PUBLIC_SITE_URL || "https://harnessbid.com"
  const supabase = createSupabaseServiceClient()

  try {
    // Pending: un-emailed, unread, recent (avoid backfilling old rows on first run).
    const { data: pending, error } = await supabase
      .from("notifications")
      .select("id, profile_id, type, title, body, link_url, created_at")
      .is("emailed_at", null)
      .gte("created_at", new Date(Date.now() - 24 * 3600 * 1000).toISOString())
      .order("created_at", { ascending: true })
      .limit(BATCH)

    if (error) {
      logger.error("cron_emails_query_failed", { error: error.message })
      return NextResponse.json({ ok: false, error: error.message }, { status: 500 })
    }

    let sent = 0
    let failed = 0
    for (const n of pending ?? []) {
      // Resolve recipient email via the auth admin API (service role).
      const { data: u } = await supabase.auth.admin.getUserById(n.profile_id)
      const to = u?.user?.email
      // Always stamp emailed_at so a missing/failed address doesn't loop forever.
      const stamp = supabase.from("notifications").update({ emailed_at: new Date().toISOString() }).eq("id", n.id)

      if (!to) {
        await stamp
        continue
      }

      const result = await sendEmail({
        to,
        subject: subjectFor(n.title),
        html: htmlFor(n, base),
        text: `${n.title}\n${n.body ?? ""}\n${n.link_url ? base + n.link_url : base}`,
      })
      await stamp
      if (result.ok) sent++
      else failed++
    }

    logger.info("cron_emails_ok", { considered: pending?.length ?? 0, sent, failed })
    return NextResponse.json({ ok: true, considered: pending?.length ?? 0, sent, failed })
  } catch (err) {
    logger.error("cron_emails_exception", { error: err instanceof Error ? err.message : String(err) })
    return NextResponse.json({ ok: false }, { status: 500 })
  }
}

export async function POST(request: NextRequest) {
  return runSweep(request)
}

export async function GET(request: NextRequest) {
  return runSweep(request)
}
