import { NextResponse, type NextRequest } from "next/server"
import { createSupabaseServiceClient, hasServiceRoleEnv } from "@/lib/supabase/admin"
import { checkCronAuth } from "@/lib/cron-auth"
import { logger } from "@/lib/logger"

/**
 * Scheduled auction state-transition sweep.
 *
 * Drives scheduled -> live and closes ended auctions (winner assignment +
 * notifications) by calling the `process_auction_transitions` RPC with the
 * service role. Idempotent and time-gated in SQL, so duplicate/retry runs are
 * safe.
 *
 * Invoke from Vercel Cron, Supabase scheduled function, or any external
 * scheduler with the `x-cron-secret` header. pg_cron remains the recommended
 * primary; this route is the app-triggered alternative.
 */
async function runSweep(request: NextRequest) {
  const authReason = checkCronAuth(request)
  if (authReason === "cron_not_configured") return NextResponse.json({ error: authReason }, { status: 503 })
  if (authReason === "unauthorized") return NextResponse.json({ error: authReason }, { status: 401 })
  if (!hasServiceRoleEnv()) {
    return NextResponse.json({ error: "service_role_not_configured" }, { status: 503 })
  }

  try {
    const supabase = createSupabaseServiceClient()
    const { error } = await supabase.rpc("process_auction_transitions")
    if (error) {
      logger.error("cron_auctions_failed", { error: error.message })
      return NextResponse.json({ ok: false, error: error.message }, { status: 500 })
    }
    logger.info("cron_auctions_ok")
    return NextResponse.json({ ok: true, ranAt: new Date().toISOString() })
  } catch (err) {
    logger.error("cron_auctions_exception", { error: err instanceof Error ? err.message : String(err) })
    return NextResponse.json({ ok: false }, { status: 500 })
  }
}

export async function POST(request: NextRequest) {
  return runSweep(request)
}

// Allow GET so platform cron schedulers that only issue GET still work.
export async function GET(request: NextRequest) {
  return runSweep(request)
}
