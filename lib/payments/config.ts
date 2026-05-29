/**
 * Commercial configuration. Fee amounts are USD whole-dollar defaults and can
 * be overridden via env without code changes. Listings are FREE by default
 * (fee = 0) — paid listings are enabled by setting a non-zero fee. This keeps
 * "free vs paid" a config decision, not a code change.
 */

export const STRIPE_PUBLISHABLE_KEY = process.env.NEXT_PUBLIC_STRIPE_PUBLISHABLE_KEY ?? ""

/** Server-only: true when the Stripe secret + webhook secret are configured. */
export function hasStripeServerEnv(): boolean {
  return Boolean(process.env.STRIPE_SECRET_KEY && process.env.STRIPE_WEBHOOK_SECRET)
}

function feeFromEnv(name: string, fallback: number): number {
  const v = Number(process.env[name])
  return Number.isFinite(v) && v >= 0 ? v : fallback
}

export const FEES = {
  currency: "USD" as const,
  horseListing: feeFromEnv("FEE_HORSE_LISTING_USD", 0),
  marketplaceListing: feeFromEnv("FEE_MARKETPLACE_LISTING_USD", 0),
  featuredUpgrade: feeFromEnv("FEE_FEATURED_UPGRADE_USD", 49),
}

export type BillingMode = "per_listing" | "invoiced" | "exempt"

export type FeeQuote = {
  amount: number
  currency: "USD"
  required: boolean
  /** none | per_listing | featured | exempt | invoiced */
  reason: string
}

/** Computes the listing fee for a seller given their billing model. */
export function quoteListingFee(
  kind: "horse" | "marketplace",
  seller: { billingMode: string; feeExempt: boolean },
): FeeQuote {
  if (seller.feeExempt) {
    return { amount: 0, currency: "USD", required: false, reason: "exempt" }
  }
  if (seller.billingMode !== "per_listing") {
    // Enterprise / invoiced sellers are not charged per listing.
    return { amount: 0, currency: "USD", required: false, reason: "invoiced" }
  }
  const amount = kind === "horse" ? FEES.horseListing : FEES.marketplaceListing
  return {
    amount,
    currency: "USD",
    required: amount > 0,
    reason: amount > 0 ? "per_listing" : "none",
  }
}

export function quoteFeaturedUpgrade(seller: { feeExempt: boolean }): FeeQuote {
  if (seller.feeExempt) return { amount: 0, currency: "USD", required: false, reason: "exempt" }
  const amount = FEES.featuredUpgrade
  return { amount, currency: "USD", required: amount > 0, reason: amount > 0 ? "featured" : "none" }
}
