"use client"

import Link from "next/link"
import Image from "next/image"
import { 
  Settings,
  BarChart3,
  Package,
  Heart,
  MessageSquare,
  Plus,
  Eye,
  Clock,
  TrendingUp,
  BadgeCheck,
  ChevronRight,
  Gavel,
  DollarSign
} from "lucide-react"
import { Button } from "@/components/ui/button"
import { Badge } from "@/components/ui/badge"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs"
import { EnterpriseRequestCard } from "@/components/enterprise-request-card"

export type SellerDashboardProps = {
  seller?: {
    name: string
    accountTypeLabel: string
    verified: boolean
    isEnterprise: boolean
    enterpriseRequested: boolean
  }
}

const sellerStats = {
  totalListings: 156,
  activeListings: 23,
  totalViews: 12450,
  totalSales: 89,
  revenue: 487500,
  messages: 5,
  watchers: 234,
}

const activeListings = [
  {
    id: 1,
    title: "Finntack Pro Jog Cart - Carbon Fiber",
    price: 4800,
    image: "https://images.unsplash.com/photo-1590155930633-7a2b8d7b5c8a?w=200&h=150&fit=crop",
    views: 234,
    watchers: 12,
    status: "active",
    daysListed: 5,
  },
  {
    id: 2,
    title: "Zilco Racing Hopples Complete Set",
    price: 450,
    image: "https://images.unsplash.com/photo-1591016765932-c9e00b445f66?w=200&h=150&fit=crop",
    views: 89,
    watchers: 4,
    status: "active",
    daysListed: 12,
  },
  {
    id: 3,
    title: "Premium Leather Driving Lines",
    price: 680,
    image: "https://images.unsplash.com/photo-1553284965-83fd3e82fa5a?w=200&h=150&fit=crop",
    views: 156,
    watchers: 8,
    status: "pending",
    daysListed: 1,
  },
]

const recentMessages = [
  {
    id: 1,
    from: "John D.",
    listing: "Finntack Pro Jog Cart",
    message: "Is this still available? Can you ship to Melbourne?",
    time: "2 hours ago",
    unread: true,
  },
  {
    id: 2,
    from: "Sarah M.",
    listing: "Zilco Racing Hopples",
    message: "Would you accept $400?",
    time: "5 hours ago",
    unread: true,
  },
  {
    id: 3,
    from: "Racing Pro",
    listing: "Premium Leather Lines",
    message: "Can I see more photos of the stitching?",
    time: "1 day ago",
    unread: false,
  },
]

function formatCurrency(amount: number) {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(amount)
}

export function SellerDashboard({ seller }: SellerDashboardProps = {}) {
  const sellerName = seller?.name ?? "Harness Pro Equipment"
  const sellerSubtitle = seller
    ? seller.verified
      ? `${seller.accountTypeLabel} · Verified seller`
      : `${seller.accountTypeLabel} · Verification pending`
    : "Enterprise Seller since 2019"
  const showVerifiedBadge = seller ? seller.verified : true
  const showEnterpriseRequest = seller ? !seller.isEnterprise : false

  return (
    <div className="min-h-screen bg-background">
      {/* Dashboard Header */}
      <div className="bg-primary py-8">
        <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
          <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div className="flex items-center gap-4">
              <div className="relative w-16 h-16 rounded-full overflow-hidden bg-primary-foreground/10">
                <Image
                  src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=100&h=100&fit=crop"
                  alt="Seller avatar"
                  fill
                  className="object-cover"
                />
              </div>
              <div>
                <div className="flex items-center gap-2">
                  <h1 className="font-sora text-xl font-semibold text-primary-foreground">
                    {sellerName}
                  </h1>
                  {showVerifiedBadge && <BadgeCheck className="h-5 w-5 text-accent" />}
                </div>
                <p className="text-sm text-primary-foreground/70">{sellerSubtitle}</p>
              </div>
            </div>
            <div className="flex gap-2">
              <Link href="/sell/new">
                <Button className="bg-accent text-accent-foreground hover:bg-accent/90">
                  <Plus className="mr-2 h-4 w-4" />
                  New Listing
                </Button>
              </Link>
              <Button variant="outline" className="border-primary-foreground/30 text-primary-foreground hover:bg-primary-foreground/10">
                <Settings className="mr-2 h-4 w-4" />
                Settings
              </Button>
            </div>
          </div>
        </div>
      </div>

      <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8">
        {/* Stats Grid */}
        <div className="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
          <Card>
            <CardContent className="p-4">
              <div className="flex items-center gap-3">
                <div className="w-10 h-10 rounded-full bg-accent/10 flex items-center justify-center">
                  <Package className="h-5 w-5 text-accent" />
                </div>
                <div>
                  <p className="text-2xl font-bold text-foreground">{sellerStats.activeListings}</p>
                  <p className="text-xs text-muted-foreground">Active Listings</p>
                </div>
              </div>
            </CardContent>
          </Card>
          
          <Card>
            <CardContent className="p-4">
              <div className="flex items-center gap-3">
                <div className="w-10 h-10 rounded-full bg-accent/10 flex items-center justify-center">
                  <Eye className="h-5 w-5 text-accent" />
                </div>
                <div>
                  <p className="text-2xl font-bold text-foreground">{sellerStats.totalViews.toLocaleString()}</p>
                  <p className="text-xs text-muted-foreground">Total Views</p>
                </div>
              </div>
            </CardContent>
          </Card>
          
          <Card>
            <CardContent className="p-4">
              <div className="flex items-center gap-3">
                <div className="w-10 h-10 rounded-full bg-accent/10 flex items-center justify-center">
                  <MessageSquare className="h-5 w-5 text-accent" />
                </div>
                <div>
                  <p className="text-2xl font-bold text-foreground">{sellerStats.messages}</p>
                  <p className="text-xs text-muted-foreground">New Messages</p>
                </div>
              </div>
            </CardContent>
          </Card>
          
          <Card>
            <CardContent className="p-4">
              <div className="flex items-center gap-3">
                <div className="w-10 h-10 rounded-full bg-accent/10 flex items-center justify-center">
                  <DollarSign className="h-5 w-5 text-accent" />
                </div>
                <div>
                  <p className="text-2xl font-bold text-foreground">{formatCurrency(sellerStats.revenue)}</p>
                  <p className="text-xs text-muted-foreground">Total Revenue</p>
                </div>
              </div>
            </CardContent>
          </Card>
        </div>

        {/* Main Content */}
        <div className="grid lg:grid-cols-3 gap-8">
          {/* Listings */}
          <div className="lg:col-span-2">
            <Card>
              <CardHeader className="flex flex-row items-center justify-between">
                <CardTitle className="font-sora text-lg">Your Listings</CardTitle>
                <Link href="/dashboard/listings">
                  <Button variant="ghost" size="sm">
                    View All
                    <ChevronRight className="ml-1 h-4 w-4" />
                  </Button>
                </Link>
              </CardHeader>
              <CardContent>
                <Tabs defaultValue="active">
                  <TabsList className="mb-4">
                    <TabsTrigger value="active">Active</TabsTrigger>
                    <TabsTrigger value="pending">Pending</TabsTrigger>
                    <TabsTrigger value="sold">Sold</TabsTrigger>
                  </TabsList>
                  
                  <TabsContent value="active" className="space-y-4">
                    {activeListings.filter(l => l.status === 'active').map((listing) => (
                      <div key={listing.id} className="flex gap-4 p-3 bg-secondary rounded-lg">
                        <div className="relative w-20 h-20 flex-shrink-0 rounded-md overflow-hidden">
                          <Image
                            src={listing.image}
                            alt={listing.title}
                            fill
                            className="object-cover"
                          />
                        </div>
                        <div className="flex-1 min-w-0">
                          <h3 className="font-medium text-foreground truncate">{listing.title}</h3>
                          <p className="text-lg font-semibold text-foreground">{formatCurrency(listing.price)}</p>
                          <div className="flex items-center gap-4 mt-1 text-xs text-muted-foreground">
                            <span className="flex items-center gap-1">
                              <Eye className="h-3 w-3" />
                              {listing.views} views
                            </span>
                            <span className="flex items-center gap-1">
                              <Heart className="h-3 w-3" />
                              {listing.watchers} watching
                            </span>
                            <span className="flex items-center gap-1">
                              <Clock className="h-3 w-3" />
                              {listing.daysListed}d
                            </span>
                          </div>
                        </div>
                        <div className="flex flex-col gap-1">
                          <Button variant="outline" size="sm">Edit</Button>
                          <Button variant="ghost" size="sm" className="text-destructive">Remove</Button>
                        </div>
                      </div>
                    ))}
                  </TabsContent>
                  
                  <TabsContent value="pending" className="space-y-4">
                    {activeListings.filter(l => l.status === 'pending').map((listing) => (
                      <div key={listing.id} className="flex gap-4 p-3 bg-secondary rounded-lg">
                        <div className="relative w-20 h-20 flex-shrink-0 rounded-md overflow-hidden">
                          <Image
                            src={listing.image}
                            alt={listing.title}
                            fill
                            className="object-cover"
                          />
                        </div>
                        <div className="flex-1 min-w-0">
                          <div className="flex items-center gap-2">
                            <h3 className="font-medium text-foreground truncate">{listing.title}</h3>
                            <Badge variant="secondary">Pending Review</Badge>
                          </div>
                          <p className="text-lg font-semibold text-foreground">{formatCurrency(listing.price)}</p>
                        </div>
                      </div>
                    ))}
                  </TabsContent>
                  
                  <TabsContent value="sold">
                    <div className="rounded-lg border border-border bg-secondary/50 p-6 text-center">
                      <div className="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-lg border border-accent/40 bg-accent/10">
                        <Package className="h-6 w-6 text-primary" />
                      </div>
                      <h3 className="font-sora text-lg font-semibold text-foreground">No sold listings yet</h3>
                      <p className="mx-auto mt-2 max-w-sm text-sm leading-6 text-muted-foreground">
                        Completed marketplace sales and auction results will appear here once your seller account has settled listings.
                      </p>
                      <Link href="/sell/new">
                        <Button className="mt-5 bg-accent text-accent-foreground hover:bg-accent/90">
                          Create listing
                        </Button>
                      </Link>
                    </div>
                  </TabsContent>
                </Tabs>
              </CardContent>
            </Card>
          </div>

          {/* Messages & Activity */}
          <div className="space-y-6">
            <Card>
              <CardHeader className="flex flex-row items-center justify-between">
                <CardTitle className="font-sora text-lg">Messages</CardTitle>
                <Link href="/dashboard/messages">
                  <Button variant="ghost" size="sm">
                    View All
                    <ChevronRight className="ml-1 h-4 w-4" />
                  </Button>
                </Link>
              </CardHeader>
              <CardContent className="space-y-3">
                {recentMessages.map((message) => (
                  <div 
                    key={message.id} 
                    className={`p-3 rounded-lg ${message.unread ? 'bg-accent/5 border border-accent/20' : 'bg-secondary'}`}
                  >
                    <div className="flex items-start justify-between mb-1">
                      <p className="font-medium text-foreground text-sm">{message.from}</p>
                      <span className="text-xs text-muted-foreground">{message.time}</span>
                    </div>
                    <p className="text-xs text-muted-foreground mb-1">Re: {message.listing}</p>
                    <p className="text-sm text-foreground line-clamp-2">{message.message}</p>
                  </div>
                ))}
              </CardContent>
            </Card>

            <Card>
              <CardHeader>
                <CardTitle className="font-sora text-lg">Quick Actions</CardTitle>
              </CardHeader>
              <CardContent className="space-y-2">
                <Link href="/sell/new">
                  <Button variant="outline" className="w-full justify-start">
                    <Plus className="mr-2 h-4 w-4" />
                    Create New Listing
                  </Button>
                </Link>
                <Link href="/sell/auction">
                  <Button variant="outline" className="w-full justify-start">
                    <Gavel className="mr-2 h-4 w-4" />
                    Start an Auction
                  </Button>
                </Link>
                <Link href="/dashboard/analytics">
                  <Button variant="outline" className="w-full justify-start">
                    <BarChart3 className="mr-2 h-4 w-4" />
                    View Analytics
                  </Button>
                </Link>
              </CardContent>
            </Card>

            {showEnterpriseRequest && (
              <EnterpriseRequestCard requested={seller?.enterpriseRequested ?? false} />
            )}
          </div>
        </div>
      </div>
    </div>
  )
}
