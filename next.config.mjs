/** @type {import('next').NextConfig} */
const basePath = process.env.NEXT_PUBLIC_BASE_PATH || ""

const nextConfig = {
  basePath,
  assetPrefix: basePath || undefined,
  trailingSlash: Boolean(basePath),
  typescript: {
    ignoreBuildErrors: true,
  },
  eslint: {
    // Lint is run separately via `npm run lint`; keep production builds
    // independent of lint results.
    ignoreDuringBuilds: true,
  },
  images: {
    unoptimized: true,
  },
}

export default nextConfig
