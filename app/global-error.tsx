"use client"

import { useEffect } from "react"

/**
 * Root error boundary — catches errors in the root layout itself. Must render
 * its own <html>/<body>. Kept dependency-free so it works even if app chrome
 * fails. Per-route errors are handled by the branded app/error.tsx.
 */
export default function GlobalError({
  error,
  reset,
}: {
  error: Error & { digest?: string }
  reset: () => void
}) {
  useEffect(() => {
    // TODO(observability): report to Sentry here once wired.
    console.error("global_error", error?.digest ?? error?.message)
  }, [error])

  return (
    <html lang="en">
      <body
        style={{
          margin: 0,
          minHeight: "100vh",
          display: "flex",
          alignItems: "center",
          justifyContent: "center",
          background: "#00205F",
          color: "#fff",
          fontFamily: "system-ui, sans-serif",
          textAlign: "center",
          padding: "2rem",
        }}
      >
        <div style={{ maxWidth: 440 }}>
          <h1 style={{ fontSize: "1.5rem", fontWeight: 700, marginBottom: "0.75rem" }}>
            HarnessBid hit an unexpected error
          </h1>
          <p style={{ opacity: 0.8, lineHeight: 1.6, marginBottom: "1.5rem" }}>
            Something went wrong loading this page. Please try again — if it keeps happening, our
            team has been notified.
          </p>
          <button
            onClick={() => reset()}
            style={{
              background: "#C9A24B",
              color: "#00205F",
              border: "none",
              borderRadius: 8,
              padding: "0.65rem 1.25rem",
              fontWeight: 600,
              cursor: "pointer",
            }}
          >
            Try again
          </button>
        </div>
      </body>
    </html>
  )
}
