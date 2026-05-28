import { ContentPage } from "@/components/static-pages"
import { BadgeCheck, Gavel, Package } from "lucide-react"

export default function PricingPage() {
  return (
    <ContentPage
      page={{
        eyebrow: "Seller pricing",
        title: "Pricing designed for premium listings",
        description: "HarnessBid pricing is presented as a front-end information page while commercial terms and payment connections remain unconnected.",
        sections: [
          { icon: Package, title: "Marketplace listings", body: "Equipment, vehicles, services, property, feed, and apparel can use polished marketplace pages and seller enquiries." },
          { icon: Gavel, title: "Horse auctions", body: "Auction presentation supports reserve language, bid history UI, sale timing, and premium horse detail pages." },
          { icon: BadgeCheck, title: "Enterprise accounts", body: "High-volume and managed sellers can be reviewed for sale events, featured placement, and brand profile support." },
        ],
        cta: {
          title: "Discuss seller options",
          description: "Commercial plans should be confirmed directly with HarnessBid before payment or settlement flows are enabled.",
          primary: { label: "Contact sales", href: "/contact" },
          secondary: { label: "Seller guide", href: "/guides/seller", variant: "outline" },
        },
      }}
    />
  )
}
