import type { MetadataRoute } from "next"

const SITE_URL = process.env.NEXT_PUBLIC_SITE_URL || "https://harnessbid.com"

/**
 * Core static routes. TODO(SEO): extend with dynamic entries (published
 * listings, live auctions, sale events, verified storefronts) by querying
 * Supabase here once the catalogue volume warrants it.
 */
export default function sitemap(): MetadataRoute.Sitemap {
  const now = new Date()
  const routes = [
    "",
    "/marketplace",
    "/auctions",
    "/sales",
    "/horses/buy-now",
    "/pricing",
    "/enterprise",
    "/help",
    "/trust",
    "/contact",
  ]
  return routes.map((path) => ({
    url: `${SITE_URL}${path}`,
    lastModified: now,
    changeFrequency: path === "" ? "daily" : "weekly",
    priority: path === "" ? 1 : 0.7,
  }))
}
