"use client"

import Link from "next/link"
import Image from "next/image"
import { 
  Search, 
  MapPin, 
  Heart,
  ChevronRight,
  ArrowRight,
  Shield,
  Truck,
  CreditCard,
  Headphones,
  Gavel,
  BadgeCheck,
  ShoppingBag
} from "lucide-react"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { Badge } from "@/components/ui/badge"
import { Card, CardContent } from "@/components/ui/card"
import type { HorseAuctionCard, MarketplaceCard, MarketplaceCategory } from "@/lib/supabase/queries"

const assetPath = (path: string) => `${process.env.NEXT_PUBLIC_BASE_PATH || ""}${path}`

const enterpriseSellers = [
  { name: "APG", logo: assetPath("/placeholder.svg?text=APG"), href: "/sellers/apg" },
  { name: "Nutrien", logo: assetPath("/placeholder.svg?text=Nutrien"), href: "/sellers/nutrien" },
  { name: "Tattersalls", logo: assetPath("/placeholder.svg?text=Tattersalls"), href: "/sellers/tattersalls" },
  { name: "Lexington Selected", logo: assetPath("/placeholder.svg?text=LEX"), href: "/sellers/lexington-selected" },
]

function formatCurrency(amount: number) {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(amount)
}

export function HeroSection() {
  return (
    <section className="relative overflow-hidden">
      {/* Background Image */}
      <div className="absolute inset-0">
        <Image
          src={assetPath("/thekingman.jpg")}
          alt="Harness Racing"
          fill
          className="object-cover"
          priority
        />
        <div className="absolute inset-0 bg-primary opacity-30" />
      </div>

      <div className="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-20 lg:py-32">
        <div className="text-center max-w-4xl mx-auto">
          <Badge className="mb-6 bg-accent/20 text-accent hover:bg-accent/20 border-accent/30 font-sans text-xs uppercase tracking-wider">
            The Global Harness Racing Marketplace
          </Badge>
          
          <h1 className="font-sora text-4xl sm:text-5xl lg:text-7xl font-semibold tracking-tight text-primary-foreground mb-6 text-balance">
            Where Champions
            <span className="block text-accent mt-2">Change Hands</span>
          </h1>
          
          <p className="text-lg sm:text-xl text-primary-foreground/80 mb-8 max-w-2xl mx-auto leading-relaxed">
            Premium auctions for standardbred horses and a marketplace for racing equipment, vehicles, and professional services. Trusted by industry leaders worldwide.
          </p>

          {/* Search Bar */}
          <div className="max-w-2xl mx-auto mb-8">
            <div className="relative flex gap-2 bg-card rounded-lg p-2 shadow-2xl">
              <div className="relative flex-1">
                <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-muted-foreground" />
                <Input 
                  type="search" 
                  placeholder="Search horses, equipment, services..." 
                  className="pl-10 border-0 bg-transparent focus-visible:ring-0 focus-visible:ring-offset-0 text-foreground placeholder:text-muted-foreground"
                />
              </div>
              <Button className="bg-primary text-primary-foreground hover:bg-primary/90 px-6">
                Search
              </Button>
            </div>
          </div>

          {/* Quick Links */}
          <div className="flex flex-wrap justify-center gap-6 text-sm">
            <Link href="/auctions" className="font-medium text-primary-foreground/85 hover:text-accent transition-colors flex items-center gap-2">
              <Gavel className="h-4 w-4" />
              Horse Auctions
            </Link>
            <Link href="/marketplace" className="font-medium text-primary-foreground/85 hover:text-accent transition-colors flex items-center gap-2">
              <ShoppingBag className="h-4 w-4" />
              Equipment Marketplace
            </Link>
            <Link href="/sell" className="font-medium text-primary-foreground/85 hover:text-accent transition-colors flex items-center gap-2">
              <ArrowRight className="h-4 w-4" />
              Start Selling
            </Link>
          </div>
        </div>
      </div>
    </section>
  )
}

export function FeaturedHorseAuctions({ auctions }: { auctions: HorseAuctionCard[] }) {
  return (
    <section className="py-16 lg:py-20 bg-background">
      <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div className="flex items-end justify-between mb-10">
          <div>
            <Badge className="mb-3 bg-accent/10 text-accent border-accent/20 font-sans text-xs uppercase tracking-wider">
              <Gavel className="mr-1 h-3 w-3" />
              Horses
            </Badge>
            <h2 className="font-sora text-2xl sm:text-3xl font-semibold tracking-wide text-foreground">
              Featured Horse Auctions
            </h2>
            <p className="mt-2 text-muted-foreground">
              Premium standardbred bloodstock ending soon
            </p>
          </div>
          <Link href="/auctions">
            <Button variant="ghost" className="hidden sm:flex text-primary hover:text-primary/80">
              View All Horse Auctions
              <ChevronRight className="ml-1 h-4 w-4" />
            </Button>
          </Link>
        </div>

        <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
          {auctions.length === 0 && (
            <div className="col-span-full rounded-lg border border-border bg-card p-8 text-center">
              <Gavel className="mx-auto mb-4 h-8 w-8 text-primary" />
              <h3 className="font-sora text-xl font-semibold text-foreground">No horse auctions are live yet</h3>
              <p className="mx-auto mt-2 max-w-xl text-sm leading-6 text-muted-foreground">
                Published horse auctions from Supabase will appear here once listings, images, sellers, and auction timing are connected.
              </p>
              <Link href="/auctions">
                <Button className="mt-5 bg-accent text-accent-foreground hover:bg-accent/90">
                  Browse auctions
                </Button>
              </Link>
            </div>
          )}
          {auctions.map((auction) => (
            <Link key={auction.id} href={`/auctions/${auction.id}`}>
              <Card className="group overflow-hidden border-border/50 bg-card card-hover cursor-pointer">
                <div className="relative aspect-[4/3] overflow-hidden">
                  <Image
                    src={auction.image}
                    alt={auction.name}
                    fill
                    className="object-cover transition-transform duration-500 group-hover:scale-105"
                  />
                  <div className="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent" />
                  
                  {/* Auction Badge */}
                  <Badge className="absolute top-4 left-4 bg-accent text-accent-foreground font-semibold">
                    <Gavel className="mr-1 h-3 w-3" />
                    Live Auction
                  </Badge>
                  
                  {/* Watchlist Button */}
                  <button 
                    className="absolute top-4 right-4 p-2 rounded-full bg-card/80 hover:bg-card text-foreground transition-colors"
                    onClick={(e) => {
                      e.preventDefault()
                    }}
                    aria-label="Add to watchlist"
                  >
                    <Heart className="h-4 w-4" />
                  </button>
                  
                  {/* Timer */}
                  <div className="absolute bottom-4 left-4 right-4">
                    <div className="bg-primary/90 backdrop-blur-sm rounded-md px-3 py-2">
                      <span className="text-sm font-semibold text-primary-foreground">{auction.endTime}</span>
                    </div>
                  </div>
                </div>
                
                <CardContent className="p-4">
                  <div className="flex items-start justify-between gap-2 mb-2">
                    <div>
                      <h3 className="font-sora text-lg font-semibold text-foreground tracking-wide">
                        {auction.name}
                      </h3>
                      <p className="text-sm text-muted-foreground">{auction.description}</p>
                    </div>
                  </div>
                  
                  <div className="flex items-center gap-2 text-sm text-muted-foreground mb-3">
                    <MapPin className="h-4 w-4" />
                    {auction.location}
                  </div>
                  
                  <div className="flex items-center justify-between pt-3 border-t border-border">
                    <div>
                      <p className="text-xs text-muted-foreground uppercase tracking-wider">Current Bid</p>
                      <p className="text-xl font-semibold text-foreground">{formatCurrency(auction.currentBid)}</p>
                    </div>
                    <div className="text-right">
                      <div className="flex items-center gap-1 text-sm text-muted-foreground">
                        {auction.verified && <BadgeCheck className="h-4 w-4 text-accent" />}
                        <span>{auction.seller}</span>
                      </div>
                      <p className="text-xs text-muted-foreground">{auction.bids} bids</p>
                    </div>
                  </div>
                </CardContent>
              </Card>
            </Link>
          ))}
        </div>

        <Link href="/auctions" className="sm:hidden">
          <Button variant="outline" className="w-full mt-6">
            View All Horse Auctions
            <ChevronRight className="ml-1 h-4 w-4" />
          </Button>
        </Link>
      </div>
    </section>
  )
}

export function MarketplaceCategoryStrip({ categories }: { categories: MarketplaceCategory[] }) {
  return (
    <section className="py-12 bg-secondary border-y border-border">
      <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div className="text-center mb-8">
          <Badge className="mb-3 bg-primary/10 text-primary border-primary/20 font-sans text-xs uppercase tracking-wider">
            <ShoppingBag className="mr-1 h-3 w-3" />
            Marketplace
          </Badge>
          <h2 className="font-sora text-xl sm:text-2xl font-semibold tracking-wide text-foreground">
            Browse by Category
          </h2>
        </div>
        <div className="grid grid-cols-3 md:grid-cols-6 gap-4 lg:gap-6">
          {categories.length === 0 && (
            <div className="col-span-full rounded-lg border border-border bg-card p-8 text-center">
              <ShoppingBag className="mx-auto mb-4 h-8 w-8 text-primary" />
              <h3 className="font-sora text-xl font-semibold text-foreground">No marketplace categories loaded</h3>
              <p className="mx-auto mt-2 max-w-xl text-sm leading-6 text-muted-foreground">
                Seeded Supabase categories will appear here after migrations run and environment variables are configured.
              </p>
            </div>
          )}
          {categories.map((category) => (
            <Link 
              key={category.id} 
              href={category.href}
              className="group flex flex-col items-center text-center"
            >
              <div className="w-12 h-12 lg:w-14 lg:h-14 rounded-full bg-card border border-border flex items-center justify-center mb-2 group-hover:border-primary group-hover:bg-primary/5 transition-colors">
                <ShoppingBag className="h-5 w-5 lg:h-6 lg:w-6 text-muted-foreground group-hover:text-primary transition-colors" />
              </div>
              <span className="text-xs lg:text-sm text-foreground font-medium leading-tight">
                {category.name}
              </span>
              <span className="text-xs text-muted-foreground mt-0.5">
                {category.count}
              </span>
            </Link>
          ))}
        </div>
      </div>
    </section>
  )
}

export function LatestMarketplaceListings({ listings }: { listings: MarketplaceCard[] }) {
  return (
    <section className="py-16 lg:py-20 bg-background">
      <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div className="flex items-end justify-between mb-10">
          <div>
            <Badge className="mb-3 bg-primary/10 text-primary border-primary/20 font-sans text-xs uppercase tracking-wider">
              <ShoppingBag className="mr-1 h-3 w-3" />
              Marketplace
            </Badge>
            <h2 className="font-sora text-2xl sm:text-3xl font-semibold tracking-wide text-foreground">
              Latest Marketplace Listings
            </h2>
            <p className="mt-2 text-muted-foreground">
              Equipment, vehicles, and services
            </p>
          </div>
          <Link href="/marketplace">
            <Button variant="ghost" className="hidden sm:flex text-primary hover:text-primary/80">
              View All Listings
              <ChevronRight className="ml-1 h-4 w-4" />
            </Button>
          </Link>
        </div>

        <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
          {listings.length === 0 && (
            <div className="col-span-full rounded-lg border border-border bg-card p-8 text-center">
              <ShoppingBag className="mx-auto mb-4 h-8 w-8 text-primary" />
              <h3 className="font-sora text-xl font-semibold text-foreground">No marketplace listings are published yet</h3>
              <p className="mx-auto mt-2 max-w-xl text-sm leading-6 text-muted-foreground">
                Published marketplace listings from Supabase will appear here with seller, category, price, and image data.
              </p>
              <Link href="/marketplace">
                <Button className="mt-5 bg-accent text-accent-foreground hover:bg-accent/90">
                  Browse marketplace
                </Button>
              </Link>
            </div>
          )}
          {listings.map((listing) => (
            <Link key={listing.id} href={`/marketplace/${listing.id}`}>
              <Card className="group overflow-hidden border-border/50 bg-card card-hover cursor-pointer">
                <div className="relative aspect-[4/3] overflow-hidden">
                  <Image
                    src={listing.image}
                    alt={listing.title}
                    fill
                    className="object-cover transition-transform duration-500 group-hover:scale-105"
                  />
                  <Badge className="absolute top-3 left-3 bg-card/90 text-foreground text-xs">
                    {listing.condition}
                  </Badge>
                </div>
                
                <CardContent className="p-4">
                  <p className="text-xs text-muted-foreground uppercase tracking-wider mb-1">
                    {listing.category}
                  </p>
                  <h3 className="font-medium text-foreground mb-2 line-clamp-1">
                    {listing.title}
                  </h3>
                  <div className="flex items-center justify-between">
                    <p className="text-lg font-semibold text-foreground">
                      {formatCurrency(listing.price)}
                    </p>
                    <div className="flex items-center gap-1 text-sm text-muted-foreground">
                      <MapPin className="h-3 w-3" />
                      {listing.location}
                    </div>
                  </div>
                </CardContent>
              </Card>
            </Link>
          ))}
        </div>

        <Link href="/marketplace" className="sm:hidden">
          <Button variant="outline" className="w-full mt-6">
            View All Listings
            <ChevronRight className="ml-1 h-4 w-4" />
          </Button>
        </Link>
      </div>
    </section>
  )
}

export function EnterpriseSellers() {
  return (
    <section className="py-16 lg:py-20 bg-primary">
      <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div className="text-center mb-12">
          <h2 className="font-sora text-2xl sm:text-3xl font-semibold tracking-wide text-primary-foreground">
            Trusted by Industry Leaders
          </h2>
          <p className="mt-2 text-primary-foreground/70">
            Professional sales companies and bloodstock agents worldwide
          </p>
        </div>

        <div className="grid grid-cols-2 md:grid-cols-4 gap-6 lg:gap-8 mb-12">
          {enterpriseSellers.map((seller) => (
            <Link 
              key={seller.name} 
              href={seller.href}
              className="flex items-center justify-center p-6 lg:p-8 bg-primary-foreground/5 rounded-lg border border-primary-foreground/10 hover:border-accent/50 transition-colors"
            >
              <span className="text-lg font-semibold text-primary-foreground/80 hover:text-primary-foreground">
                {seller.name}
              </span>
            </Link>
          ))}
        </div>

        <div className="text-center">
          <Link href="/enterprise">
            <Button variant="outline" className="border-primary-foreground/30 text-primary-foreground hover:bg-primary-foreground/10">
              Learn About Enterprise Accounts
              <ArrowRight className="ml-2 h-4 w-4" />
            </Button>
          </Link>
        </div>
      </div>
    </section>
  )
}

export function TrustStrip() {
  const trustItems = [
    { icon: Shield, title: "Verified Sellers", description: "All sellers verified" },
    { icon: CreditCard, title: "Payment Ready", description: "Checkout planned" },
    { icon: Truck, title: "Global Shipping", description: "Worldwide logistics" },
    { icon: Headphones, title: "24/7 Support", description: "Expert assistance" },
  ]

  return (
    <section className="py-12 bg-secondary border-y border-border">
      <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div className="grid grid-cols-2 lg:grid-cols-4 gap-6 lg:gap-8">
          {trustItems.map((item) => (
            <div key={item.title} className="flex items-center gap-3">
              <div className="w-12 h-12 rounded-full bg-card border border-border flex items-center justify-center flex-shrink-0">
                <item.icon className="h-5 w-5 text-accent" />
              </div>
              <div>
                <h3 className="font-medium text-foreground text-sm">{item.title}</h3>
                <p className="text-xs text-muted-foreground">{item.description}</p>
              </div>
            </div>
          ))}
        </div>
      </div>
    </section>
  )
}

export function SellerCTA() {
  return (
    <section className="py-16 lg:py-20 bg-background">
      <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div className="relative overflow-hidden rounded-2xl bg-gradient-to-br from-primary via-primary to-primary/90 p-8 lg:p-12">
          {/* Background Pattern */}
          <div className="absolute inset-0 opacity-10">
            <div className="absolute inset-0" style={{ 
              backgroundImage: `url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='1'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E")` 
            }} />
          </div>
          
          <div className="relative grid lg:grid-cols-2 gap-8 items-center">
            <div>
              <h2 className="font-sora text-2xl sm:text-3xl lg:text-4xl font-semibold text-primary-foreground mb-4 tracking-wide">
                Ready to Sell?
              </h2>
              <p className="text-primary-foreground/70 mb-6 text-lg leading-relaxed">
                Join thousands of trainers, breeders, and industry professionals selling on HarnessBid. List your horses, equipment, or services today.
              </p>
              <div className="flex flex-wrap gap-4">
                <Link href="/sell/horse">
                  <Button size="lg" className="bg-accent text-accent-foreground hover:bg-accent/90">
                    <Gavel className="mr-2 h-4 w-4" />
                    Sell a Horse
                  </Button>
                </Link>
                <Link href="/sell/equipment">
                  <Button size="lg" variant="outline" className="border-primary-foreground/30 text-primary-foreground hover:bg-primary-foreground/10">
                    <ShoppingBag className="mr-2 h-4 w-4" />
                    List Equipment
                  </Button>
                </Link>
              </div>
            </div>
            
            <div className="grid grid-cols-2 gap-4">
              <div className="bg-primary-foreground/10 rounded-lg p-6 text-center">
                <p className="text-3xl lg:text-4xl font-semibold text-accent mb-1">$2.4M+</p>
                <p className="text-sm text-primary-foreground/70">Total Sales</p>
              </div>
              <div className="bg-primary-foreground/10 rounded-lg p-6 text-center">
                <p className="text-3xl lg:text-4xl font-semibold text-accent mb-1">1,200+</p>
                <p className="text-sm text-primary-foreground/70">Active Listings</p>
              </div>
              <div className="bg-primary-foreground/10 rounded-lg p-6 text-center">
                <p className="text-3xl lg:text-4xl font-semibold text-accent mb-1">45+</p>
                <p className="text-sm text-primary-foreground/70">Countries</p>
              </div>
              <div className="bg-primary-foreground/10 rounded-lg p-6 text-center">
                <p className="text-3xl lg:text-4xl font-semibold text-accent mb-1">98%</p>
                <p className="text-sm text-primary-foreground/70">Satisfaction</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  )
}

// Legacy exports for backward compatibility
export { FeaturedHorseAuctions as FeaturedAuctions }
export { MarketplaceCategoryStrip as CategoryStrip }
export { LatestMarketplaceListings as LatestListings }
