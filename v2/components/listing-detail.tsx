"use client"

import { useState, useEffect } from "react"
import Link from "next/link"
import Image from "next/image"
import { 
  ChevronDown,
  ChevronLeft,
  ChevronRight,
  Heart,
  Share2,
  MapPin,
  BadgeCheck,
  Truck,
  Shield,
  Clock,
  Gavel,
  MessageSquare,
  Phone,
  Mail,
  ExternalLink,
  Timer,
  Users
} from "lucide-react"
import { Button } from "@/components/ui/button"
import { Badge } from "@/components/ui/badge"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Separator } from "@/components/ui/separator"
import { Input } from "@/components/ui/input"
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs"
import type { HorseAuctionDetail, MarketplaceDetail } from "@/lib/supabase/queries"

function formatCurrency(amount: number) {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(amount)
}

function CountdownTimer({ endTime }: { endTime: Date }) {
  const [timeLeft, setTimeLeft] = useState({ days: 0, hours: 0, minutes: 0, seconds: 0 })

  useEffect(() => {
    const timer = setInterval(() => {
      const now = new Date().getTime()
      const distance = endTime.getTime() - now

      if (distance < 0) {
        clearInterval(timer)
        setTimeLeft({ days: 0, hours: 0, minutes: 0, seconds: 0 })
        return
      }

      setTimeLeft({
        days: Math.floor(distance / (1000 * 60 * 60 * 24)),
        hours: Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60)),
        minutes: Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60)),
        seconds: Math.floor((distance % (1000 * 60)) / 1000),
      })
    }, 1000)

    return () => clearInterval(timer)
  }, [endTime])

  return (
    <div className="grid grid-cols-4 gap-2 text-center">
      {[
        { value: timeLeft.days, label: "Days" },
        { value: timeLeft.hours, label: "Hours" },
        { value: timeLeft.minutes, label: "Min" },
        { value: timeLeft.seconds, label: "Sec" },
      ].map((item) => (
        <div key={item.label} className="bg-primary/10 rounded-md p-2">
          <p className="text-2xl font-bold text-primary font-mono">{item.value.toString().padStart(2, '0')}</p>
          <p className="text-xs text-muted-foreground uppercase">{item.label}</p>
        </div>
      ))}
    </div>
  )
}

function ImageGallery({ images }: { images: string[] }) {
  const [activeIndex, setActiveIndex] = useState(0)

  return (
    <div className="space-y-4">
      {/* Main Image */}
      <div className="relative aspect-[4/3] overflow-hidden rounded-lg bg-muted">
        <Image
          src={images[activeIndex]}
          alt="Listing image"
          fill
          className="object-cover"
          priority
        />
        
        {/* Navigation Arrows */}
        {images.length > 1 && (
          <>
            <button
              onClick={() => setActiveIndex((prev) => (prev === 0 ? images.length - 1 : prev - 1))}
              className="absolute left-4 top-1/2 -translate-y-1/2 p-2 rounded-full bg-card/80 hover:bg-card text-foreground transition-colors"
              aria-label="Previous image"
            >
              <ChevronLeft className="h-5 w-5" />
            </button>
            <button
              onClick={() => setActiveIndex((prev) => (prev === images.length - 1 ? 0 : prev + 1))}
              className="absolute right-4 top-1/2 -translate-y-1/2 p-2 rounded-full bg-card/80 hover:bg-card text-foreground transition-colors"
              aria-label="Next image"
            >
              <ChevronRight className="h-5 w-5" />
            </button>
          </>
        )}

        {/* Image Counter */}
        <div className="absolute bottom-4 right-4 px-3 py-1 rounded-full bg-card/80 text-sm text-foreground">
          {activeIndex + 1} / {images.length}
        </div>
      </div>

      {/* Thumbnails */}
      {images.length > 1 && (
        <div className="flex gap-2 overflow-x-auto pb-2">
          {images.map((image, index) => (
            <button
              key={index}
              onClick={() => setActiveIndex(index)}
              className={`relative w-20 h-20 flex-shrink-0 rounded-md overflow-hidden border-2 transition-colors ${
                index === activeIndex ? "border-accent" : "border-transparent hover:border-border"
              }`}
            >
              <Image
                src={image}
                alt={`Thumbnail ${index + 1}`}
                fill
                className="object-cover"
              />
            </button>
          ))}
        </div>
      )}
    </div>
  )
}

function toEquipmentListingView(data: MarketplaceDetail) {
  return {
    id: data.id,
    title: data.title,
    description: data.description,
    price: data.price,
    images: data.images.length > 0 ? data.images : [data.image],
    location: data.location,
    condition: data.condition,
    category: data.category,
    seller: {
      name: data.seller,
      verified: data.verified,
      enterprise: data.sellerEnterprise,
      memberSince: data.sellerMemberSince,
      totalListings: data.sellerTotalListings,
      rating: data.sellerRating,
      reviews: data.sellerReviews,
      responseTime: data.sellerResponseTime,
      avatar: data.sellerAvatar,
    },
    shipping: {
      available: data.shipping,
      domestic: data.shippingDomestic ?? 0,
      international: data.shippingInternational ?? "Contact seller for quote",
    },
    specs: data.specs,
    createdAt: data.createdAt,
    views: data.views,
    watchers: data.watchers,
  }
}

function toAuctionListingView(data: HorseAuctionDetail) {
  return {
    id: data.id,
    title: data.title,
    description: data.description,
    currentBid: data.currentBid,
    startingBid: data.startingBid,
    reservePrice: data.reservePrice ?? 0,
    reserveMet: data.reserveMet,
    nextMinimumBid: data.nextMinimumBid,
    bidIncrement: data.bidIncrement,
    images: data.images.length > 0 ? data.images : [data.image],
    location: data.location,
    category: data.category,
    endTime: new Date(data.endsAt),
    bids: data.bids,
    watchers: data.watchers,
    views: data.views,
    seller: {
      name: data.seller,
      verified: data.verified,
      enterprise: data.sellerEnterprise,
      memberSince: data.sellerMemberSince,
      totalListings: data.sellerTotalListings,
      rating: data.sellerRating,
      reviews: data.sellerReviews,
      responseTime: data.sellerResponseTime,
      avatar: data.sellerAvatar,
    },
    bidHistory: [] as Array<{ bidder: string; time: string; amount: number }>,
    specs: data.specs,
  }
}

export function EquipmentListingDetail({ listing: listingData }: { listing: MarketplaceDetail }) {
  const listing = toEquipmentListingView(listingData)

  return (
    <div className="min-h-screen bg-background">
      {/* Breadcrumb */}
      <div className="bg-secondary border-b border-border">
        <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-3">
          <nav className="flex items-center gap-2 text-sm text-muted-foreground">
            <Link href="/" className="hover:text-foreground">Home</Link>
            <ChevronDown className="h-4 w-4 rotate-[-90deg]" />
            <Link href="/marketplace" className="hover:text-foreground">Marketplace</Link>
            <ChevronDown className="h-4 w-4 rotate-[-90deg]" />
            <Link href="/marketplace/joggers" className="hover:text-foreground">{listing.category}</Link>
            <ChevronDown className="h-4 w-4 rotate-[-90deg]" />
            <span className="text-foreground truncate max-w-[200px]">{listing.title}</span>
          </nav>
        </div>
      </div>

      <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8">
        <div className="grid lg:grid-cols-3 gap-8">
          {/* Left Column - Images & Details */}
          <div className="lg:col-span-2 space-y-8">
            <ImageGallery images={listing.images} />

            {/* Listing Info - Mobile */}
            <div className="lg:hidden">
              <ListingInfoCard listing={listing} />
            </div>

            {/* Description */}
            <Card>
              <CardHeader>
                <CardTitle className="font-sora text-lg">Description</CardTitle>
              </CardHeader>
              <CardContent>
                <div className="prose prose-sm max-w-none text-muted-foreground whitespace-pre-line">
                  {listing.description}
                </div>
              </CardContent>
            </Card>

            {/* Specifications */}
            <Card>
              <CardHeader>
                <CardTitle className="font-sora text-lg">Specifications</CardTitle>
              </CardHeader>
              <CardContent>
                <div className="grid grid-cols-2 gap-4">
                  {listing.specs.map((spec) => (
                    <div key={spec.label} className="flex justify-between py-2 border-b border-border last:border-0">
                      <span className="text-muted-foreground">{spec.label}</span>
                      <span className="font-medium text-foreground">{spec.value}</span>
                    </div>
                  ))}
                </div>
              </CardContent>
            </Card>

            {/* Shipping */}
            <Card>
              <CardHeader>
                <CardTitle className="font-sora text-lg">Shipping & Pickup</CardTitle>
              </CardHeader>
              <CardContent className="space-y-4">
                <div className="flex items-start gap-3">
                  <Truck className="h-5 w-5 text-accent mt-0.5" />
                  <div>
                    <p className="font-medium text-foreground">Domestic Shipping</p>
                    <p className="text-sm text-muted-foreground">{formatCurrency(listing.shipping.domestic)} within Australia</p>
                  </div>
                </div>
                <div className="flex items-start gap-3">
                  <ExternalLink className="h-5 w-5 text-muted-foreground mt-0.5" />
                  <div>
                    <p className="font-medium text-foreground">International Shipping</p>
                    <p className="text-sm text-muted-foreground">{listing.shipping.international}</p>
                  </div>
                </div>
                <div className="flex items-start gap-3">
                  <MapPin className="h-5 w-5 text-muted-foreground mt-0.5" />
                  <div>
                    <p className="font-medium text-foreground">Local Pickup Available</p>
                    <p className="text-sm text-muted-foreground">{listing.location}</p>
                  </div>
                </div>
              </CardContent>
            </Card>
          </div>

          {/* Right Column - Sticky Sidebar */}
          <div className="hidden lg:block">
            <div className="sticky top-20 space-y-6">
              <ListingInfoCard listing={listing} />
              <SellerCard seller={listing.seller} />
            </div>
          </div>
        </div>

        {/* Seller Card - Mobile */}
        <div className="lg:hidden mt-8">
          <SellerCard seller={listing.seller} />
        </div>
      </div>

      {/* Mobile Sticky CTA */}
      <div className="lg:hidden fixed bottom-0 left-0 right-0 bg-card border-t border-border p-4 z-40">
        <div className="flex items-center justify-between gap-4">
          <div>
            <p className="text-sm text-muted-foreground">Price</p>
            <p className="text-2xl font-bold text-foreground">{formatCurrency(listing.price)}</p>
          </div>
          <div className="flex gap-2">
            <Button variant="outline" size="icon">
              <Heart className="h-5 w-5" />
            </Button>
            <Button className="bg-accent text-accent-foreground hover:bg-accent/90 px-6" disabled>
              Enquiries Disabled
            </Button>
          </div>
        </div>
      </div>
    </div>
  )
}

function ListingInfoCard({ listing }: { listing: ReturnType<typeof toEquipmentListingView> }) {
  return (
    <Card>
      <CardContent className="p-6">
        <div className="space-y-4">
          <div className="flex items-start justify-between">
            <div>
              <Badge variant="secondary" className="mb-2">{listing.condition}</Badge>
              <h1 className="font-sora text-xl lg:text-2xl font-semibold text-foreground">
                {listing.title}
              </h1>
            </div>
          </div>

          <div className="flex items-center gap-4 text-sm text-muted-foreground">
            <span className="flex items-center gap-1">
              <MapPin className="h-4 w-4" />
              {listing.location}
            </span>
            <span className="flex items-center gap-1">
              <Clock className="h-4 w-4" />
              {listing.createdAt}
            </span>
          </div>

          <Separator />

          <div>
            <p className="text-sm text-muted-foreground mb-1">Price</p>
            <p className="text-3xl font-bold text-foreground">{formatCurrency(listing.price)}</p>
          </div>

          <div className="flex gap-2">
            <Button className="flex-1 bg-accent text-accent-foreground hover:bg-accent/90" disabled>
              <MessageSquare className="mr-2 h-4 w-4" />
              Enquiries Disabled
            </Button>
            <Button variant="outline" size="icon">
              <Heart className="h-5 w-5" />
            </Button>
            <Button variant="outline" size="icon">
              <Share2 className="h-5 w-5" />
            </Button>
          </div>

          <div className="flex items-center gap-4 text-sm text-muted-foreground">
            <span>{listing.views} views</span>
            <span>{listing.watchers} watching</span>
          </div>

          <div className="flex items-center gap-2 p-3 bg-secondary rounded-md">
            <Shield className="h-5 w-5 text-accent" />
            <div className="text-sm">
              <p className="font-medium text-foreground">Buyer Protection</p>
              <p className="text-muted-foreground">Verified sellers; payments not connected yet</p>
            </div>
          </div>
        </div>
      </CardContent>
    </Card>
  )
}

function SellerCard({ seller }: { seller: ReturnType<typeof toEquipmentListingView>["seller"] }) {
  return (
    <Card>
      <CardContent className="p-6">
        <div className="flex items-center gap-4 mb-4">
          <div className="relative w-16 h-16 rounded-full overflow-hidden bg-muted">
            <Image
              src={seller.avatar}
              alt={seller.name}
              fill
              className="object-cover"
            />
          </div>
          <div>
            <div className="flex items-center gap-2">
              <h3 className="font-semibold text-foreground">{seller.name}</h3>
              {seller.verified && <BadgeCheck className="h-5 w-5 text-accent" />}
            </div>
            {seller.enterprise && (
              <Badge variant="secondary" className="mt-1">Enterprise Seller</Badge>
            )}
          </div>
        </div>

        <div className="grid grid-cols-2 gap-4 mb-4 text-sm">
          <div>
            <p className="text-muted-foreground">Member Since</p>
            <p className="font-medium text-foreground">{seller.memberSince}</p>
          </div>
          <div>
            <p className="text-muted-foreground">Total Listings</p>
            <p className="font-medium text-foreground">{seller.totalListings}</p>
          </div>
          <div>
            <p className="text-muted-foreground">Rating</p>
            <p className="font-medium text-foreground">{seller.rating} ({seller.reviews} reviews)</p>
          </div>
          <div>
            <p className="text-muted-foreground">Response Time</p>
            <p className="font-medium text-foreground">{seller.responseTime}</p>
          </div>
        </div>

        <Separator className="my-4" />

        <div className="space-y-2">
          <Button variant="outline" className="w-full justify-start" disabled>
            <MessageSquare className="mr-2 h-4 w-4" />
            Messaging Disabled
          </Button>
          <Button variant="outline" className="w-full justify-start" disabled>
            <Phone className="mr-2 h-4 w-4" />
            Phone Request Disabled
          </Button>
          <Link href={`/sellers/${seller.name.toLowerCase().replace(/\s+/g, '-')}`}>
            <Button variant="ghost" className="w-full justify-start text-primary">
              View All Listings
              <ChevronRight className="ml-auto h-4 w-4" />
            </Button>
          </Link>
        </div>
      </CardContent>
    </Card>
  )
}

export function AuctionListingDetail({ listing: listingData }: { listing: HorseAuctionDetail }) {
  const listing = toAuctionListingView(listingData)
  const [bidAmount, setBidAmount] = useState(listing.nextMinimumBid.toString())

  return (
    <div className="min-h-screen bg-background">
      {/* Breadcrumb */}
      <div className="bg-secondary border-b border-border">
        <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-3">
          <nav className="flex items-center gap-2 text-sm text-muted-foreground">
            <Link href="/" className="hover:text-foreground">Home</Link>
            <ChevronDown className="h-4 w-4 rotate-[-90deg]" />
            <Link href="/auctions" className="hover:text-foreground">Auctions</Link>
            <ChevronDown className="h-4 w-4 rotate-[-90deg]" />
            <span className="text-foreground truncate max-w-[200px]">{listing.title}</span>
          </nav>
        </div>
      </div>

      <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8">
        <div className="grid lg:grid-cols-3 gap-8">
          {/* Left Column - Images & Details */}
          <div className="lg:col-span-2 space-y-8">
            <ImageGallery images={listing.images} />

            {/* Auction Info - Mobile */}
            <div className="lg:hidden">
              <AuctionInfoCard listing={listing} bidAmount={bidAmount} setBidAmount={setBidAmount} />
            </div>

            {/* Description */}
            <Card>
              <CardHeader>
                <CardTitle className="font-sora text-lg">Description</CardTitle>
              </CardHeader>
              <CardContent>
                <div className="prose prose-sm max-w-none text-muted-foreground whitespace-pre-line">
                  {listing.description}
                </div>
              </CardContent>
            </Card>

            {/* Horse Details */}
            <Card>
              <CardHeader>
                <CardTitle className="font-sora text-lg">Horse Details</CardTitle>
              </CardHeader>
              <CardContent>
                <div className="grid grid-cols-2 gap-4">
                  {listing.specs.map((spec) => (
                    <div key={spec.label} className="flex justify-between py-2 border-b border-border last:border-0">
                      <span className="text-muted-foreground">{spec.label}</span>
                      <span className="font-medium text-foreground">{spec.value}</span>
                    </div>
                  ))}
                </div>
              </CardContent>
            </Card>

            {/* Bid History */}
            <Card>
              <CardHeader>
                <CardTitle className="font-sora text-lg">Bid History</CardTitle>
              </CardHeader>
              <CardContent>
                <div className="space-y-3">
                  {listing.bidHistory.length === 0 && (
                    <div className="rounded-lg border border-border bg-secondary/50 p-5 text-center">
                      <p className="font-medium text-foreground">Bid history is not public yet</p>
                      <p className="mt-1 text-sm text-muted-foreground">
                        Bidding writes and history display remain disabled until the live auction workflow is connected.
                      </p>
                    </div>
                  )}
                  {listing.bidHistory.map((bid, index) => (
                    <div key={index} className="flex items-center justify-between py-2 border-b border-border last:border-0">
                      <div className="flex items-center gap-3">
                        <div className="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center">
                          <Users className="h-4 w-4 text-primary" />
                        </div>
                        <div>
                          <p className="font-medium text-foreground">{bid.bidder}</p>
                          <p className="text-xs text-muted-foreground">{bid.time}</p>
                        </div>
                      </div>
                      <p className="font-semibold text-foreground">{formatCurrency(bid.amount)}</p>
                    </div>
                  ))}
                </div>
              </CardContent>
            </Card>
          </div>

          {/* Right Column - Sticky Sidebar */}
          <div className="hidden lg:block">
            <div className="sticky top-20 space-y-6">
              <AuctionInfoCard listing={listing} bidAmount={bidAmount} setBidAmount={setBidAmount} />
              <SellerCard seller={listing.seller} />
            </div>
          </div>
        </div>

        {/* Seller Card - Mobile */}
        <div className="lg:hidden mt-8">
          <SellerCard seller={listing.seller} />
        </div>
      </div>

      {/* Mobile Sticky CTA */}
      <div className="lg:hidden fixed bottom-0 left-0 right-0 bg-card border-t border-border p-4 z-40">
        <div className="flex items-center justify-between gap-4">
          <div>
            <p className="text-sm text-muted-foreground">Current Bid</p>
            <p className="text-2xl font-bold text-foreground">{formatCurrency(listing.currentBid)}</p>
          </div>
          <div className="flex gap-2">
            <Button variant="outline" size="icon">
              <Heart className="h-5 w-5" />
            </Button>
            <Button className="bg-accent text-accent-foreground hover:bg-accent/90 px-6">
              Bidding Disabled
            </Button>
          </div>
        </div>
      </div>
    </div>
  )
}

function AuctionInfoCard({ 
  listing, 
  bidAmount, 
  setBidAmount 
}: { 
  listing: ReturnType<typeof toAuctionListingView>
  bidAmount: string
  setBidAmount: (value: string) => void
}) {
  return (
    <Card className="overflow-hidden">
      {/* Auction Header */}
      <div className="bg-primary p-4">
        <div className="flex items-center justify-between mb-2">
          <Badge className="bg-accent text-accent-foreground">
            <Gavel className="mr-1 h-3 w-3" />
            Live Auction
          </Badge>
          {listing.reserveMet && (
            <Badge variant="secondary" className="bg-green-500/20 text-green-600">
              Reserve Met
            </Badge>
          )}
        </div>
        <h1 className="font-sora text-xl font-semibold text-primary-foreground">
          {listing.title}
        </h1>
      </div>

      <CardContent className="p-6">
        <div className="space-y-6">
          {/* Countdown */}
          <div>
            <p className="text-sm text-muted-foreground mb-2 flex items-center gap-1">
              <Timer className="h-4 w-4" />
              Auction Ends In
            </p>
            <CountdownTimer endTime={listing.endTime} />
          </div>

          <Separator />

          {/* Current Bid */}
          <div className="text-center">
            <p className="text-sm text-muted-foreground mb-1">Current Bid</p>
            <p className="text-4xl font-bold text-foreground">{formatCurrency(listing.currentBid)}</p>
            <p className="text-sm text-muted-foreground mt-1">{listing.bids} bids</p>
          </div>

          <Separator />

          {/* Bidding placeholder */}
          <div>
            <p className="text-sm text-muted-foreground mb-2">
              Enter {formatCurrency(listing.nextMinimumBid)} or more
            </p>
            <div className="flex gap-2">
              <div className="relative flex-1">
                <span className="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground">$</span>
                <Input
                  type="number"
                  value={bidAmount}
                  onChange={(e) => setBidAmount(e.target.value)}
                  className="pl-7"
                  disabled
                />
              </div>
              <Button className="bg-accent text-accent-foreground hover:bg-accent/90" disabled>
                Bidding Disabled
              </Button>
            </div>
            <p className="text-xs text-muted-foreground mt-2">
              Bid increment: {formatCurrency(listing.bidIncrement)}
            </p>
          </div>

          <div className="flex gap-2">
            <Button variant="outline" className="flex-1">
              <Heart className="mr-2 h-4 w-4" />
              Watch
            </Button>
            <Button variant="outline" size="icon">
              <Share2 className="h-5 w-5" />
            </Button>
          </div>

          <div className="flex items-center gap-4 text-sm text-muted-foreground">
            <span>{listing.views} views</span>
            <span>{listing.watchers} watching</span>
          </div>

          <div className="flex items-center gap-2 p-3 bg-secondary rounded-md">
            <Shield className="h-5 w-5 text-accent" />
            <div className="text-sm">
              <p className="font-medium text-foreground">Buyer Protection</p>
              <p className="text-muted-foreground">Escrow payments & verification</p>
            </div>
          </div>
        </div>
      </CardContent>
    </Card>
  )
}
