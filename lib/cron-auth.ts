import type { NextRequest } from "next/server"

/**
 * Shared secret check for scheduled-job routes (/api/cron/*). Accepts the
 * `Authorization: Bearer <CRON_SECRET>` header, an `x-cron-secret` header, or a
 * `?secret=` query param. Returns null if authorized, or a string reason if not.
 */
export function checkCronAuth(request: NextRequest): "cron_not_configured" | "unauthorized" | null {
  const secret = process.env.CRON_SECRET
  if (!secret) return "cron_not_configured"
  const auth = request.headers.get("authorization")
  const provided =
    (auth?.startsWith("Bearer ") ? auth.slice(7) : null) ??
    request.headers.get("x-cron-secret") ??
    request.nextUrl.searchParams.get("secret")
  return provided === secret ? null : "unauthorized"
}
