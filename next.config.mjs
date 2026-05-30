/** @type {import('next').NextConfig} */
const basePath = process.env.NEXT_PUBLIC_BASE_PATH || ""

// When the app runs behind a reverse proxy (e.g. the xCloud PHP proxy), the
// upstream sees x-forwarded-host=127.0.0.1:3001 while the browser Origin is the
// public domain. Next.js Server Actions reject that mismatch as CSRF unless the
// public origin is explicitly allowed. Derive it from NEXT_PUBLIC_SITE_URL.
const siteHost = (() => {
  try {
    return process.env.NEXT_PUBLIC_SITE_URL
      ? new URL(process.env.NEXT_PUBLIC_SITE_URL).host
      : undefined
  } catch {
    return undefined
  }
})()
const allowedOrigins = [siteHost, "harnessbid.com", "www.harnessbid.com"].filter(Boolean)

const securityHeaders = [
  { key: "X-Content-Type-Options", value: "nosniff" },
  { key: "X-Frame-Options", value: "SAMEORIGIN" },
  { key: "Referrer-Policy", value: "strict-origin-when-cross-origin" },
  { key: "X-DNS-Prefetch-Control", value: "on" },
  { key: "Permissions-Policy", value: "camera=(), microphone=(), geolocation=()" },
  // HSTS is enforced at the edge (Cloudflare/Vercel) in production; harmless here.
  { key: "Strict-Transport-Security", value: "max-age=63072000; includeSubDomains; preload" },
]

const nextConfig = {
  basePath,
  assetPrefix: basePath || undefined,
  trailingSlash: Boolean(basePath),
  poweredByHeader: false,
  reactStrictMode: true,
  experimental: {
    serverActions: {
      allowedOrigins,
    },
  },
  typescript: {
    ignoreBuildErrors: true,
  },
  images: {
    // TODO(perf): enable the optimizer with an allowed remote pattern for the
    // Supabase Storage public URL once a CDN/loader is chosen.
    unoptimized: true,
  },
  async headers() {
    return [{ source: "/:path*", headers: securityHeaders }]
  },
}

export default nextConfig
