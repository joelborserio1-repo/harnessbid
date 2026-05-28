import { 
  Package, 
  Search, 
  Heart, 
  MessageSquare, 
  AlertCircle,
  Gavel
} from "lucide-react"
import { Button } from "@/components/ui/button"
import Link from "next/link"

interface EmptyStateProps {
  type: "listings" | "search" | "watchlist" | "messages" | "auctions" | "error"
  title?: string
  description?: string
  action?: {
    label: string
    href: string
  }
}

const emptyStateConfig = {
  listings: {
    icon: Package,
    title: "No listings found",
    description: "There are no listings in this category yet. Check back soon or try a different filter.",
    action: { label: "Browse All Listings", href: "/marketplace" },
  },
  search: {
    icon: Search,
    title: "No results found",
    description: "We couldn't find any listings matching your search. Try adjusting your filters or search terms.",
    action: { label: "Clear Filters", href: "/marketplace" },
  },
  watchlist: {
    icon: Heart,
    title: "Your watchlist is empty",
    description: "Save listings you're interested in by clicking the heart icon. They'll appear here for easy access.",
    action: { label: "Start Browsing", href: "/marketplace" },
  },
  messages: {
    icon: MessageSquare,
    title: "No messages yet",
    description: "When you contact sellers or receive inquiries, your conversations will appear here.",
    action: { label: "Browse Listings", href: "/marketplace" },
  },
  auctions: {
    icon: Gavel,
    title: "No active auctions",
    description: "There are no live auctions at the moment. Check back soon for exciting new opportunities.",
    action: { label: "View Marketplace", href: "/marketplace" },
  },
  error: {
    icon: AlertCircle,
    title: "Something went wrong",
    description: "We couldn't load this content. Please try again or contact support if the problem persists.",
    action: { label: "Back to Home", href: "/" },
  },
}

export function EmptyState({ type, title, description, action }: EmptyStateProps) {
  const config = emptyStateConfig[type]
  const Icon = config.icon

  return (
    <div className="flex flex-col items-center justify-center py-16 px-4 text-center">
      <div className="w-16 h-16 rounded-full bg-secondary flex items-center justify-center mb-6">
        <Icon className="h-8 w-8 text-muted-foreground" />
      </div>
      <h3 className="font-sora text-xl font-semibold text-foreground mb-2">
        {title || config.title}
      </h3>
      <p className="text-muted-foreground max-w-md mb-6">
        {description || config.description}
      </p>
      {(action || config.action) && (
        <Link href={action?.href || config.action.href}>
          <Button className="bg-primary text-primary-foreground hover:bg-primary/90">
            {action?.label || config.action.label}
          </Button>
        </Link>
      )}
    </div>
  )
}

// Loading skeleton for listing cards
export function ListingCardSkeleton() {
  return (
    <div className="rounded-lg border border-border bg-card overflow-hidden animate-pulse">
      <div className="aspect-[4/3] bg-muted" />
      <div className="p-4 space-y-3">
        <div className="h-3 bg-muted rounded w-1/4" />
        <div className="h-5 bg-muted rounded w-3/4" />
        <div className="h-4 bg-muted rounded w-1/2" />
        <div className="pt-3 border-t border-border flex justify-between">
          <div className="h-6 bg-muted rounded w-1/4" />
          <div className="h-4 bg-muted rounded w-1/4" />
        </div>
      </div>
    </div>
  )
}

// Loading skeleton for the marketplace grid
export function MarketplaceGridSkeleton({ count = 8 }: { count?: number }) {
  return (
    <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
      {Array.from({ length: count }).map((_, i) => (
        <ListingCardSkeleton key={i} />
      ))}
    </div>
  )
}
