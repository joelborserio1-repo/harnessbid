/**
 * Centralised environment access + validation.
 *
 * We deliberately avoid a hard "throw on import" so builds (which run without
 * runtime secrets) never break. Instead `validateEnv()` is called from
 * `instrumentation.ts` at server startup to log clear warnings for missing
 * critical vars, and accessors expose typed, intent-revealing reads.
 *
 * SECURITY: only `NEXT_PUBLIC_*` vars are safe in the browser bundle. Server
 * secrets (SUPABASE_SERVICE_ROLE_KEY, STRIPE_SECRET_KEY, CRON_SECRET, ...) must
 * never be referenced from client components.
 */

type EnvVar = {
  name: string
  required: boolean
  /** True if this value is exposed to the browser (NEXT_PUBLIC_*). */
  public: boolean
  description: string
}

export const ENV_SPEC: EnvVar[] = [
  { name: "NEXT_PUBLIC_SUPABASE_URL", required: true, public: true, description: "Supabase project URL" },
  { name: "NEXT_PUBLIC_SUPABASE_ANON_KEY", required: true, public: true, description: "Supabase anon key (RLS-bound)" },
  { name: "SUPABASE_SERVICE_ROLE_KEY", required: false, public: false, description: "Service role key (server-only; webhooks/cron)" },
  { name: "NEXT_PUBLIC_STRIPE_PUBLISHABLE_KEY", required: false, public: true, description: "Stripe publishable key" },
  { name: "STRIPE_SECRET_KEY", required: false, public: false, description: "Stripe secret key (server-only)" },
  { name: "STRIPE_WEBHOOK_SECRET", required: false, public: false, description: "Stripe webhook signing secret" },
  { name: "CRON_SECRET", required: false, public: false, description: "Shared secret for scheduled-job routes" },
]

export type EnvReport = {
  ok: boolean
  missingRequired: string[]
  missingOptional: string[]
  leaks: string[]
}

/** Validates env without throwing; returns a structured report. */
export function checkEnv(): EnvReport {
  const missingRequired: string[] = []
  const missingOptional: string[] = []
  const leaks: string[] = []

  for (const v of ENV_SPEC) {
    const value = process.env[v.name]
    if (!value) {
      if (v.required) missingRequired.push(v.name)
      else missingOptional.push(v.name)
    }
    // Guard against a server secret accidentally being named NEXT_PUBLIC_*.
    if (!v.public && v.name.startsWith("NEXT_PUBLIC_")) leaks.push(v.name)
  }

  return {
    ok: missingRequired.length === 0 && leaks.length === 0,
    missingRequired,
    missingOptional,
    leaks,
  }
}

export const isProduction = process.env.NODE_ENV === "production"
