/**
 * Minimal Sentry error forwarding via the Store API — no @sentry/* SDK.
 *
 * Enabled only when NEXT_PUBLIC_SENTRY_DSN is set; otherwise every call is a
 * no-op. Fire-and-forget: never awaited on the request path, never throws.
 * Parses the DSN to build the ingest URL + auth header.
 */
type Dsn = { url: string; key: string }

let cached: Dsn | null | undefined

function parseDsn(): Dsn | null {
  if (cached !== undefined) return cached
  const dsn = process.env.NEXT_PUBLIC_SENTRY_DSN
  if (!dsn) return (cached = null)
  try {
    // https://<key>@<host>/<project>
    const u = new URL(dsn)
    const projectId = u.pathname.replace(/^\//, "")
    const host = u.host
    cached = {
      url: `https://${host}/api/${projectId}/store/`,
      key: u.username,
    }
    return cached
  } catch {
    return (cached = null)
  }
}

export function sentryEnabled(): boolean {
  return parseDsn() !== null
}

export function captureError(message: string, context?: Record<string, unknown>) {
  const dsn = parseDsn()
  if (!dsn) return
  const payload = {
    event_id: crypto.randomUUID().replace(/-/g, ""),
    timestamp: new Date().toISOString(),
    platform: "node",
    level: "error",
    logger: "harnessbid",
    environment: process.env.NODE_ENV ?? "production",
    message: { formatted: message },
    extra: context ?? {},
  }
  // Fire-and-forget; swallow all failures so logging never breaks a request.
  void fetch(dsn.url, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      "X-Sentry-Auth": `Sentry sentry_version=7, sentry_key=${dsn.key}, sentry_client=harnessbid/1.0`,
    },
    body: JSON.stringify(payload),
  }).catch(() => {})
}
