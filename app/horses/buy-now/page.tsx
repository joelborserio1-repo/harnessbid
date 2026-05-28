import { EmptyStatePage } from "@/components/static-pages"
import { BadgeCheck } from "lucide-react"

export default function BuyNowHorsesPage() {
  return (
    <EmptyStatePage
      icon={BadgeCheck}
      eyebrow="Buy now horses"
      title="Buy now horse listings are coming soon"
      description="HarnessBid is prepared for fixed-price horse listings with pedigree details, seller verification, and enquiry-first purchasing."
      primary={{ label: "Browse live auctions", href: "/auctions" }}
      secondary={{ label: "Sell your horse", href: "/sell/horse", variant: "outline" }}
      points={["Pedigree-first listings", "Verified seller profiles", "Premium horse imagery"]}
    />
  )
}
