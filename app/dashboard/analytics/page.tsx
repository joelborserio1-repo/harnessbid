import { DashboardEmptyPage } from "@/components/static-pages"
import { BarChart3 } from "lucide-react"

export default function DashboardAnalyticsPage() {
  return (
    <DashboardEmptyPage
      icon={BarChart3}
      title="Analytics will appear after listings go live"
      description="Views, watchers, enquiries, active listings, sale performance, and marketplace activity will be summarized here."
    />
  )
}
