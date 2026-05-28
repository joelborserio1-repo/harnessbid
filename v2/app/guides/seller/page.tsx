import { ContentPage } from "@/components/static-pages"
import { BookOpen, FileText, Shield } from "lucide-react"

export default function SellerGuidePage() {
  return (
    <ContentPage
      page={{
        eyebrow: "Seller guide",
        title: "Prepare a stronger HarnessBid listing",
        description: "A polished seller guide for the front-end experience, covering listing readiness, buyer confidence, and enterprise marketplace standards.",
        sections: [
          { icon: FileText, title: "Write useful detail", body: "Include brand, condition, age, location, shipping notes, or horse pedigree and racing records where relevant." },
          { icon: Shield, title: "Build trust", body: "Use clear images, accurate descriptions, prompt responses, and verified seller information to reduce buyer hesitation." },
          { icon: BookOpen, title: "Choose the right format", body: "Use marketplace listings for equipment and services, horse pages for bloodstock, and sale events for managed campaigns." },
        ],
        cta: {
          title: "Start selling",
          description: "Choose the listing path that fits your item or contact HarnessBid about enterprise onboarding.",
          primary: { label: "Create listing", href: "/sell/new" },
          secondary: { label: "Enterprise accounts", href: "/enterprise", variant: "outline" },
        },
      }}
    />
  )
}
