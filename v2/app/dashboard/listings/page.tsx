import { DashboardEmptyPage } from "@/components/static-pages"
import { Package } from "lucide-react"

export default function DashboardListingsPage() {
  return (
    <DashboardEmptyPage
      icon={Package}
      title="No seller listings yet"
      description="Your seller listing workspace is ready for active, pending, paused, sold, and archived HarnessBid listings."
    />
  )
}
