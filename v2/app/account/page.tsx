import { EmptyStatePage } from "@/components/static-pages"
import { User } from "lucide-react"

export default function AccountPage() {
  return (
    <EmptyStatePage
      icon={User}
      eyebrow="Account"
      title="Your account workspace is ready"
      description="Profile details, saved listings, seller settings, enquiries, and future payment placeholders will live here once account data is connected."
      primary={{ label: "Browse marketplace", href: "/marketplace" }}
      secondary={{ label: "Seller dashboard", href: "/dashboard", variant: "outline" }}
      points={["Profile details", "Saved activity", "Seller settings"]}
    />
  )
}
