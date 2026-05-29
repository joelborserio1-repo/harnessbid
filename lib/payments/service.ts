import "server-only"

import { hasStripeServerEnv } from "@/lib/payments/config"

/**
 * Payment provider abstraction. The platform talks to this interface, never a
 * concrete SDK, so providers (Stripe today; others later) are swappable and so
 * the rest of the app has no payment-secret surface.
 *
 * The Stripe SDK is intentionally NOT a dependency yet (Phase 17 is
 * preparation). `createCheckoutSession` returns a typed "not configured" /
 * "not implemented" result until keys + the SDK are wired, so nothing breaks
 * and no checkout can silently proceed unpaid.
 */
export type CheckoutInput = {
  purpose: "listing_fee" | "featured_upgrade" | "auction_deposit" | "auction_settlement"
  amount: number
  currency: string
  description: string
  successUrl: string
  cancelUrl: string
  metadata?: Record<string, string>
}

export type CheckoutResult =
  | { ok: true; url: string }
  | { ok: false; reason: "not_configured" | "not_implemented" | "free"; message: string }

export interface PaymentProvider {
  readonly name: string
  readonly configured: boolean
  createCheckoutSession(input: CheckoutInput): Promise<CheckoutResult>
}

class StripeProvider implements PaymentProvider {
  readonly name = "stripe"
  get configured() {
    return hasStripeServerEnv()
  }

  async createCheckoutSession(input: CheckoutInput): Promise<CheckoutResult> {
    if (input.amount <= 0) {
      return { ok: false, reason: "free", message: "No payment required." }
    }
    if (!this.configured) {
      return {
        ok: false,
        reason: "not_configured",
        message: "Payments are not configured yet.",
      }
    }
    // TODO(Phase 17 follow-up): add `stripe` SDK, create a Checkout Session
    // (mode: 'payment'), and return session.url. Until then we never proceed.
    return {
      ok: false,
      reason: "not_implemented",
      message: "Checkout is being finalised. Please try again later.",
    }
  }
}

export const paymentProvider: PaymentProvider = new StripeProvider()
