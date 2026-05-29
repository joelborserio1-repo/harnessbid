import { cache } from "react"
import type { Metadata } from "next"
import { SellerStorefrontPage } from "@/components/seller-storefront"
import { EmptyStatePage } from "@/components/static-pages"
import { getSellerStorefront } from "@/lib/supabase/queries"
import { viewerOwnsSeller } from "@/lib/enquiries/queries"
import { Building2, Search } from "lucide-react"

const loadStorefront = cache(getSellerStorefront)

export async function generateMetadata({
  params,
}: {
  params: Promise<{ slug: string }>
}): Promise<Metadata> {
  const { slug } = await params
  const result = await loadStorefront(slug)
  const s = result.data?.seller
  if (!s) return { title: "Seller | HarnessBid" }
  return {
    title: `${s.name} | HarnessBid Seller`,
    description: s.bio?.slice(0, 160),
    openGraph: { title: s.name, description: s.bio?.slice(0, 160), type: "profile" },
  }
}

export default async function SellerStorefrontRoute({
  params,
}: {
  params: Promise<{ slug: string }>
}) {
  const { slug } = await params
  const storefront = await loadStorefront(slug)

  if (storefront.error) {
    return (
      <EmptyStatePage
        icon={Search}
        eyebrow="Seller unavailable"
        title="Seller profile could not load"
        description="The seller storefront is available, but live seller data did not load cleanly. Try browsing the marketplace or check this profile again shortly."
        primary={{ label: "Browse marketplace", href: "/marketplace" }}
        secondary={{ label: "Back to homepage", href: "/", variant: "outline" }}
      />
    )
  }

  if (!storefront.data) {
    const name = slug
      .split("-")
      .map((part) => part.charAt(0).toUpperCase() + part.slice(1))
      .join(" ")
    return (
      <EmptyStatePage
        icon={Building2}
        eyebrow="Seller profile"
        title={`${name} is not public yet`}
        description="Seller storefronts appear after the account is active, verified, and available through Supabase public read policies."
        primary={{ label: "Browse marketplace", href: "/marketplace" }}
        secondary={{ label: "Contact HarnessBid", href: "/contact", variant: "outline" }}
      />
    )
  }

  const isOwner = await viewerOwnsSeller(storefront.data.seller.id)
  return <SellerStorefrontPage storefront={storefront.data} isOwner={isOwner} />
}
