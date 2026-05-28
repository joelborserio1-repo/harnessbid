import { DashboardEmptyPage } from "@/components/static-pages"
import { MessageSquare } from "lucide-react"

export default function DashboardMessagesPage() {
  return (
    <DashboardEmptyPage
      icon={MessageSquare}
      title="No enquiries yet"
      description="Buyer enquiries and seller replies will appear here when someone contacts you about a listing or sale event."
    />
  )
}
