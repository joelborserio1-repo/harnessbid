/**
 * Production-safe structured logging.
 *
 * Emits single-line JSON in production (friendly for Logtail / Vercel log
 * drains) and readable lines in development. Integration points for Sentry /
 * PostHog are marked with TODOs — wire the SDKs here so call sites never change.
 */

type Level = "debug" | "info" | "warn" | "error"

const isProd = process.env.NODE_ENV === "production"

function emit(level: Level, message: string, context?: Record<string, unknown>) {
  if (level === "debug" && isProd) return
  const entry = { level, message, ...context, ts: new Date().toISOString() }

  const sink = level === "error" ? console.error : level === "warn" ? console.warn : console.log
  sink(isProd ? JSON.stringify(entry) : `[${level}] ${message}`, isProd ? "" : context ?? "")

  // Forward errors to Sentry when a DSN is configured (no-op otherwise).
  if (level === "error") {
    // Lazy import to avoid pulling Sentry into the edge/client bundles.
    import("./sentry")
      .then((m) => m.captureError(message, context))
      .catch(() => {})
  }
}

export const logger = {
  debug: (m: string, c?: Record<string, unknown>) => emit("debug", m, c),
  info: (m: string, c?: Record<string, unknown>) => emit("info", m, c),
  warn: (m: string, c?: Record<string, unknown>) => emit("warn", m, c),
  error: (m: string, c?: Record<string, unknown>) => emit("error", m, c),
}

/**
 * Wraps a server action so unexpected errors are logged with context and a
 * safe message is returned instead of leaking internals. `redirect()` /
 * `notFound()` control-flow errors are re-thrown so Next can handle them.
 */
export async function withActionLogging<T>(
  name: string,
  fn: () => Promise<T>,
  fallback: T,
): Promise<T> {
  try {
    return await fn()
  } catch (err) {
    // Next.js navigation signals must propagate.
    if (err && typeof err === "object" && "digest" in err) {
      const digest = String((err as { digest?: string }).digest ?? "")
      if (digest.startsWith("NEXT_REDIRECT") || digest === "NEXT_NOT_FOUND") throw err
    }
    logger.error(`action_failed:${name}`, {
      error: err instanceof Error ? err.message : String(err),
    })
    return fallback
  }
}
