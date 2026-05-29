/**
 * Lightweight in-memory fixed-window rate limiter for server actions / routes.
 *
 * NOTE: state is per-instance (serverless = per warm lambda), so this is a
 * conservative first line of defence against accidental floods, not a strict
 * distributed limiter. For hard guarantees back it with a shared store
 * (Upstash/Redis) or a Postgres counter table — the call sites won't change.
 */

type Bucket = { count: number; resetAt: number }
const store = new Map<string, Bucket>()
let lastSweep = 0

function sweep(now: number) {
  if (now - lastSweep < 60_000) return
  lastSweep = now
  for (const [k, b] of store) if (now > b.resetAt) store.delete(k)
}

export type RateLimitResult = { allowed: boolean; retryAfterMs: number }

export function rateLimit(key: string, limit: number, windowMs: number): RateLimitResult {
  const now = Date.now()
  sweep(now)
  const b = store.get(key)
  if (!b || now > b.resetAt) {
    store.set(key, { count: 1, resetAt: now + windowMs })
    return { allowed: true, retryAfterMs: 0 }
  }
  if (b.count >= limit) return { allowed: false, retryAfterMs: b.resetAt - now }
  b.count += 1
  return { allowed: true, retryAfterMs: 0 }
}

/** Conservative defaults for common actions (per user, fixed window). */
export const LIMITS = {
  message: { limit: 20, windowMs: 60_000 },
  enquiry: { limit: 5, windowMs: 60_000 },
  listing: { limit: 10, windowMs: 60_000 },
  bid: { limit: 30, windowMs: 60_000 },
  upload: { limit: 40, windowMs: 60_000 },
  auth: { limit: 10, windowMs: 60_000 },
} as const
