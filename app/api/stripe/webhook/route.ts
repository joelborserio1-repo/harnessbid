import { NextResponse } from "next/server"
import { hasStripeServerEnv } from "@/lib/payments/config"

/**
 * Stripe webhook endpoint (placeholder).
 *
 * Phase 17 ships the secure shape only. When Stripe is wired:
 *   1. read the raw body + `stripe-signature` header
 *   2. verify with `stripe.webhooks.constructEvent(body, sig, STRIPE_WEBHOOK_SECRET)`
 *   3. on verified events, use the SERVER-ONLY service-role client
 *      (`createSupabaseServiceClient`) to record `payment_events` and advance
 *      `payment_intents` / `invoices` / `payouts` statuses.
 *
 * Until then this never trusts unverified payloads: it returns 503 when Stripe
 * isn't configured and 501 (acknowledged, not processed) when it is, so no
 * state can change from unverified input.
 */
export async function POST() {
  if (!hasStripeServerEnv()) {
    return NextResponse.json({ error: "stripe_not_configured" }, { status: 503 })
  }
  return NextResponse.json(
    { received: true, processed: false, note: "signature verification not yet wired" },
    { status: 501 },
  )
}

export async function GET() {
  return NextResponse.json({ ok: true, endpoint: "stripe-webhook", configured: hasStripeServerEnv() })
}
