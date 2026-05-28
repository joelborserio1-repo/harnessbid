import { ContentPage } from "@/components/static-pages"
import { BadgeCheck, BarChart3, Building2 } from "lucide-react"

export default function EnterprisePage() {
  return (
    <ContentPage
      page={{
        eyebrow: "Enterprise sellers",
        title: "Built for premium racing sellers",
        description: "HarnessBid supports sale companies, studs, equipment suppliers, and professional service providers who need a trusted marketplace presence inside the HarnessLink ecosystem.",
        sections: [
          { icon: Building2, title: "Enterprise profiles", body: "Create a stronger seller presence with brand details, verification signals, sale events, and public listing history." },
          { icon: BarChart3, title: "High-volume readiness", body: "The front-end is prepared for seller dashboards, active listings, enquiries, watchers, and revenue summaries." },
          { icon: BadgeCheck, title: "Premium buyer confidence", body: "Verified seller language, gold premium accents, and clear listing pages help serious buyers move faster." },
        ],
        cta: {
          title: "Apply for enterprise access",
          description: "Tell HarnessBid about your sale program, inventory, or marketplace category and the team can review the right account path.",
          primary: { label: "Contact HarnessBid", href: "/contact" },
          secondary: { label: "View pricing", href: "/pricing", variant: "outline" },
        },
      }}
    />
  )
}
