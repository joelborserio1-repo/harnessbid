/**
 * Next.js instrumentation: runs once when the server starts. Used here for a
 * non-fatal environment validation check so misconfiguration is visible in
 * logs immediately on boot (without crashing the process).
 */
export async function register() {
  // Only run in the Node.js server runtime (not edge/browser).
  if (process.env.NEXT_RUNTIME !== "nodejs") return

  const { checkEnv } = await import("@/lib/env")
  const { logger } = await import("@/lib/logger")

  const report = checkEnv()
  if (report.leaks.length > 0) {
    logger.error("env_secret_leak", { vars: report.leaks })
  }
  if (report.missingRequired.length > 0) {
    logger.error("env_missing_required", { vars: report.missingRequired })
  } else if (report.missingOptional.length > 0) {
    logger.warn("env_missing_optional", { vars: report.missingOptional })
  } else {
    logger.info("env_ok")
  }
}
