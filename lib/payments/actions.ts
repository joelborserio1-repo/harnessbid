"use server"

import { paymentProvider, type CheckoutInput, type CheckoutResult } from "@/lib/payments/service"

/**
 * Checkout entry point (placeholder). Routes through the payment provider
 * abstraction; returns a typed result so callers handle free / not-configured /
 * not-implemented states explicitly. No checkout proceeds unpaid.
 */
export async function createCheckoutAction(input: CheckoutInput): Promise<CheckoutResult> {
  return paymentProvider.createCheckoutSession(input)
}
