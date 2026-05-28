import { Header } from "@/components/header"
import { Footer } from "@/components/footer"
import { SellerDashboard } from "@/components/seller-dashboard"

export default function DashboardPage() {
  return (
    <div className="min-h-screen flex flex-col">
      <Header />
      <main className="flex-1">
        <SellerDashboard />
      </main>
      <Footer />
    </div>
  )
}
