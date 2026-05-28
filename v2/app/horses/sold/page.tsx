import { EmptyStatePage } from "@/components/static-pages"
import { Gavel } from "lucide-react"

export default function SoldHorsesPage() {
  return (
    <EmptyStatePage
      icon={Gavel}
      eyebrow="Recently sold"
      title="Recently sold horses will appear here"
      description="Completed sale records will give buyers and sellers confidence through transparent auction results and premium listing history."
      primary={{ label: "Browse auctions", href: "/auctions" }}
      secondary={{ label: "Browse marketplace", href: "/marketplace", variant: "outline" }}
      points={["Auction outcomes", "Sale history", "Market confidence"]}
    />
  )
}
