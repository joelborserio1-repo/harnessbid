"use client"

import { useState } from "react"
import Link from "next/link"
import Image from "next/image"
import { WatchButton } from "@/components/listings/watch-button"
import { SaveSearchButton } from "@/components/saved-searches/save-search-button"
import {
  Search,
  MapPin,
  Heart,
  ChevronDown,
  Grid3X3,
  List,
  SlidersHorizontal,
  X,
  BadgeCheck,
} from "lucide-react"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { Badge } from "@/components/ui/badge"
import { Card, CardContent } from "@/components/ui/card"
import { Checkbox } from "@/components/ui/checkbox"
import { Label } from "@/components/ui/label"
import { Slider } from "@/components/ui/slider"
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select"
import {
  Sheet,
  SheetContent,
  SheetHeader,
  SheetTitle,
  SheetTrigger,
} from "@/components/ui/sheet"
import {
  Accordion,
  AccordionContent,
  AccordionItem,
  AccordionTrigger,
} from "@/components/ui/accordion"
import type { MarketplaceCard, MarketplaceCategory } from "@/lib/supabase/queries"

const conditions = [
  { id: "new", name: "New", count: 234 },
  { id: "excellent", name: "Excellent", count: 156 },
  { id: "good", name: "Good", count: 189 },
  { id: "fair", name: "Fair", count: 67 },
]

const locations = [
  { id: "usa", name: "United States", count: 456 },
  { id: "australia", name: "Australia", count: 234 },
  { id: "nz", name: "New Zealand", count: 123 },
  { id: "europe", name: "Europe", count: 189 },
  { id: "canada", name: "Canada", count: 78 },
]

function formatCurrency(amount: number) {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(amount)
}

function FilterSidebar({ categories, className = "" }: { categories: MarketplaceCategory[]; className?: string }) {
  const [priceRange, setPriceRange] = useState([0, 50000])
  const [selectedCategories, setSelectedCategories] = useState<string[]>([])
  const [selectedConditions, setSelectedConditions] = useState<string[]>([])
  const [selectedLocations, setSelectedLocations] = useState<string[]>([])

  return (
    <div className={`space-y-6 ${className}`}>
      {/* Search */}
      <div>
        <Label className="text-sm font-medium mb-2 block">Search</Label>
        <div className="relative">
          <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
          <Input 
            type="search" 
            placeholder="Search listings..." 
            className="pl-9"
          />
        </div>
      </div>

      <Accordion type="multiple" defaultValue={["category", "price", "condition"]} className="w-full">
        {/* Categories */}
        <AccordionItem value="category">
          <AccordionTrigger className="text-sm font-medium">Category</AccordionTrigger>
          <AccordionContent>
            <div className="space-y-2">
              {categories.map((category) => (
                <div key={category.id} className="flex items-center justify-between">
                  <div className="flex items-center gap-2">
                    <Checkbox 
                      id={category.id}
                      checked={selectedCategories.includes(category.id)}
                      onCheckedChange={(checked) => {
                        if (checked) {
                          setSelectedCategories([...selectedCategories, category.id])
                        } else {
                          setSelectedCategories(selectedCategories.filter(c => c !== category.id))
                        }
                      }}
                    />
                    <Label htmlFor={category.id} className="text-sm cursor-pointer">
                      {category.name}
                    </Label>
                  </div>
                  <span className="text-xs text-muted-foreground">{category.count}</span>
                </div>
              ))}
            </div>
          </AccordionContent>
        </AccordionItem>

        {/* Price Range */}
        <AccordionItem value="price">
          <AccordionTrigger className="text-sm font-medium">Price Range</AccordionTrigger>
          <AccordionContent>
            <div className="space-y-4">
              <Slider
                value={priceRange}
                onValueChange={setPriceRange}
                max={50000}
                step={100}
                className="w-full"
              />
              <div className="flex items-center gap-2">
                <Input 
                  type="number" 
                  value={priceRange[0]} 
                  onChange={(e) => setPriceRange([Number(e.target.value), priceRange[1]])}
                  className="w-full text-sm"
                  placeholder="Min"
                />
                <span className="text-muted-foreground">-</span>
                <Input 
                  type="number" 
                  value={priceRange[1]} 
                  onChange={(e) => setPriceRange([priceRange[0], Number(e.target.value)])}
                  className="w-full text-sm"
                  placeholder="Max"
                />
              </div>
            </div>
          </AccordionContent>
        </AccordionItem>

        {/* Condition */}
        <AccordionItem value="condition">
          <AccordionTrigger className="text-sm font-medium">Condition</AccordionTrigger>
          <AccordionContent>
            <div className="space-y-2">
              {conditions.map((condition) => (
                <div key={condition.id} className="flex items-center justify-between">
                  <div className="flex items-center gap-2">
                    <Checkbox 
                      id={condition.id}
                      checked={selectedConditions.includes(condition.id)}
                      onCheckedChange={(checked) => {
                        if (checked) {
                          setSelectedConditions([...selectedConditions, condition.id])
                        } else {
                          setSelectedConditions(selectedConditions.filter(c => c !== condition.id))
                        }
                      }}
                    />
                    <Label htmlFor={condition.id} className="text-sm cursor-pointer">
                      {condition.name}
                    </Label>
                  </div>
                  <span className="text-xs text-muted-foreground">{condition.count}</span>
                </div>
              ))}
            </div>
          </AccordionContent>
        </AccordionItem>

        {/* Location */}
        <AccordionItem value="location">
          <AccordionTrigger className="text-sm font-medium">Location</AccordionTrigger>
          <AccordionContent>
            <div className="space-y-2">
              {locations.map((location) => (
                <div key={location.id} className="flex items-center justify-between">
                  <div className="flex items-center gap-2">
                    <Checkbox 
                      id={location.id}
                      checked={selectedLocations.includes(location.id)}
                      onCheckedChange={(checked) => {
                        if (checked) {
                          setSelectedLocations([...selectedLocations, location.id])
                        } else {
                          setSelectedLocations(selectedLocations.filter(l => l !== location.id))
                        }
                      }}
                    />
                    <Label htmlFor={location.id} className="text-sm cursor-pointer">
                      {location.name}
                    </Label>
                  </div>
                  <span className="text-xs text-muted-foreground">{location.count}</span>
                </div>
              ))}
            </div>
          </AccordionContent>
        </AccordionItem>

        {/* Seller */}
        <AccordionItem value="seller">
          <AccordionTrigger className="text-sm font-medium">Seller Type</AccordionTrigger>
          <AccordionContent>
            <div className="space-y-2">
              <div className="flex items-center gap-2">
                <Checkbox id="verified" />
                <Label htmlFor="verified" className="text-sm cursor-pointer flex items-center gap-1">
                  <BadgeCheck className="h-4 w-4 text-accent" />
                  Verified Sellers Only
                </Label>
              </div>
              <div className="flex items-center gap-2">
                <Checkbox id="enterprise" />
                <Label htmlFor="enterprise" className="text-sm cursor-pointer">
                  Enterprise Sellers
                </Label>
              </div>
            </div>
          </AccordionContent>
        </AccordionItem>
      </Accordion>

      <Button className="w-full" variant="outline">
        Clear All Filters
      </Button>
    </div>
  )
}

export function ListingCard({ listing, view = "grid" }: { listing: MarketplaceCard, view?: "grid" | "list" }) {
  if (view === "list") {
    return (
      <Link href={`/marketplace/${listing.id}`}>
        <Card className="group overflow-hidden border-border/50 bg-card card-hover cursor-pointer">
          <div className="flex flex-col sm:flex-row">
            <div className="relative w-full sm:w-48 md:w-64 aspect-[4/3] sm:aspect-square overflow-hidden flex-shrink-0">
              <Image
                src={listing.image}
                alt={listing.title}
                fill
                className="object-cover transition-transform duration-500 group-hover:scale-105"
              />
              {listing.featured && (
                <Badge className="absolute top-3 left-3 bg-accent text-accent-foreground font-semibold">
                  Featured
                </Badge>
              )}
            </div>
            
            <CardContent className="flex-1 p-4">
              <div className="flex flex-col h-full justify-between">
                <div>
                    <p className="text-xs text-muted-foreground uppercase tracking-wider mb-1">
                      {listing.category}
                    </p>
                  <h3 className="font-medium text-foreground mb-2">
                    {listing.title}
                  </h3>
                  <div className="flex items-center gap-4 text-sm text-muted-foreground mb-2">
                    <span className="flex items-center gap-1">
                      <MapPin className="h-3 w-3" />
                      {listing.location}
                    </span>
                    <Badge variant="secondary" className="text-xs">
                      {listing.condition}
                    </Badge>
                  </div>
                </div>
                
                <div className="flex items-center justify-between pt-3 border-t border-border">
                  <div>
                    <p className="text-xl font-semibold text-foreground">{formatCurrency(listing.price)}</p>
                  </div>
                  <div className="flex items-center gap-2 text-sm text-muted-foreground">
                    {listing.verified && <BadgeCheck className="h-4 w-4 text-accent" />}
                    <span>{listing.seller}</span>
                  </div>
                </div>
              </div>
            </CardContent>
          </div>
        </Card>
      </Link>
    )
  }

  return (
    <Link href={`/marketplace/${listing.id}`}>
      <Card className="group overflow-hidden border-border/50 bg-card card-hover cursor-pointer h-full">
        <div className="relative aspect-[4/3] overflow-hidden">
          <Image
            src={listing.image}
            alt={listing.title}
            fill
            className="object-cover transition-transform duration-500 group-hover:scale-105"
          />
          
          {listing.featured && (
            <Badge className="absolute top-3 left-3 bg-accent text-accent-foreground font-semibold">
              Featured
            </Badge>
          )}
          <Badge className="absolute top-3 right-3 bg-card/90 text-foreground text-xs">
            {listing.condition}
          </Badge>
          
          {/* Watchlist Button */}
          <div className="absolute bottom-3 right-3 opacity-0 transition-opacity group-hover:opacity-100">
            <WatchButton listingId={listing.recordId} kind="marketplace" variant="icon" />
          </div>

        </div>
        
        <CardContent className="p-4">
          <p className="text-xs text-muted-foreground uppercase tracking-wider mb-1">
            {listing.category}
          </p>
          <h3 className="font-medium text-foreground mb-2 line-clamp-2">
            {listing.title}
          </h3>
          <div className="flex items-center gap-1 text-sm text-muted-foreground mb-3">
            <MapPin className="h-3 w-3" />
            {listing.location}
          </div>
          
          <div className="flex items-center justify-between pt-3 border-t border-border">
            <div>
              <p className="text-lg font-semibold text-foreground">{formatCurrency(listing.price)}</p>
            </div>
            <div className="flex items-center gap-1">
              {listing.verified && <BadgeCheck className="h-4 w-4 text-accent" />}
            </div>
          </div>
        </CardContent>
      </Card>
    </Link>
  )
}

export function MarketplaceBrowse({ listings, categories }: { listings: MarketplaceCard[]; categories: MarketplaceCategory[] }) {
  const [view, setView] = useState<"grid" | "list">("grid")
  const [isFilterOpen, setIsFilterOpen] = useState(false)

  const allListings = listings

  return (
    <div className="min-h-screen bg-background">
      {/* Page Header */}
      <div className="bg-primary py-8 lg:py-12">
        <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
          <nav className="flex items-center gap-2 text-sm text-primary-foreground/60 mb-4">
            <Link href="/" className="hover:text-primary-foreground">Home</Link>
            <ChevronDown className="h-4 w-4 rotate-[-90deg]" />
            <span className="text-primary-foreground">Marketplace</span>
          </nav>
          <h1 className="font-sora text-3xl lg:text-4xl font-semibold tracking-wide text-primary-foreground">
            MARKETPLACE
          </h1>
          <p className="mt-2 text-primary-foreground/70">
            {allListings.length.toLocaleString()} listings available
          </p>
        </div>
      </div>

      {/* Main Content */}
      <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8">
        <div className="flex flex-col lg:flex-row gap-8">
          {/* Desktop Sidebar */}
          <aside className="hidden lg:block w-64 flex-shrink-0">
            <div className="sticky top-20">
              <FilterSidebar categories={categories} />
            </div>
          </aside>

          {/* Listings */}
          <div className="flex-1">
            {/* Controls */}
            <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 pb-6 border-b border-border">
              <div className="flex items-center gap-4">
                {/* Mobile Filter Button */}
                <Sheet open={isFilterOpen} onOpenChange={setIsFilterOpen}>
                  <SheetTrigger asChild>
                    <Button variant="outline" className="lg:hidden">
                      <SlidersHorizontal className="mr-2 h-4 w-4" />
                      Filters
                    </Button>
                  </SheetTrigger>
                  <SheetContent side="left" className="w-80 overflow-y-auto">
                    <SheetHeader>
                      <SheetTitle>Filters</SheetTitle>
                    </SheetHeader>
                    <div className="mt-6">
                      <FilterSidebar categories={categories} />
                    </div>
                  </SheetContent>
                </Sheet>

                {/* Sort */}
                <Select defaultValue="newest">
                  <SelectTrigger className="w-[180px]">
                    <SelectValue placeholder="Sort by" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="newest">Newest First</SelectItem>
                    <SelectItem value="price-low">Price: Low to High</SelectItem>
                    <SelectItem value="price-high">Price: High to Low</SelectItem>
                    <SelectItem value="ending-soon">Ending Soon</SelectItem>
                  </SelectContent>
                </Select>

                <SaveSearchButton searchType="marketplace" />
              </div>

              {/* View Toggle */}
              <div className="flex items-center gap-2">
                <span className="text-sm text-muted-foreground hidden sm:inline">View:</span>
                <div className="flex border border-border rounded-md overflow-hidden">
                  <button
                    onClick={() => setView("grid")}
                    className={`p-2 ${view === "grid" ? "bg-primary text-primary-foreground" : "bg-card text-muted-foreground hover:text-foreground"}`}
                    aria-label="Grid view"
                  >
                    <Grid3X3 className="h-4 w-4" />
                  </button>
                  <button
                    onClick={() => setView("list")}
                    className={`p-2 ${view === "list" ? "bg-primary text-primary-foreground" : "bg-card text-muted-foreground hover:text-foreground"}`}
                    aria-label="List view"
                  >
                    <List className="h-4 w-4" />
                  </button>
                </div>
              </div>
            </div>

            {/* Active Filters */}
            <div className="flex flex-wrap gap-2 mb-6">
              <Badge variant="secondary" className="flex items-center gap-1">
                Verified Sellers
                <button className="ml-1 hover:text-destructive" aria-label="Remove filter">
                  <X className="h-3 w-3" />
                </button>
              </Badge>
              <Badge variant="secondary" className="flex items-center gap-1">
                New Condition
                <button className="ml-1 hover:text-destructive" aria-label="Remove filter">
                  <X className="h-3 w-3" />
                </button>
              </Badge>
            </div>

            {/* Listings Grid/List */}
            {allListings.length === 0 ? (
              <div className="rounded-lg border border-border bg-card p-8 text-center">
                <Search className="mx-auto mb-4 h-8 w-8 text-primary" />
                <h2 className="font-sora text-xl font-semibold text-foreground">No marketplace listings are published yet</h2>
                <p className="mx-auto mt-2 max-w-xl text-sm leading-6 text-muted-foreground">
                  Supabase marketplace rows with published status, seller data, category, and images will appear here.
                </p>
                <Link href="/sell/equipment">
                  <Button className="mt-5 bg-accent text-accent-foreground hover:bg-accent/90">
                    Sell an item
                  </Button>
                </Link>
              </div>
            ) : (
            <div className={view === "grid" 
              ? "grid gap-6 sm:grid-cols-2 xl:grid-cols-3" 
              : "flex flex-col gap-4"
            }>
              {allListings.map((listing) => (
                <ListingCard key={listing.id} listing={listing} view={view} />
              ))}
            </div>
            )}

            {/* Pagination */}
            <div className="flex items-center justify-center gap-2 mt-12">
              <Button variant="outline" disabled>Previous</Button>
              <Button variant="outline" className="bg-primary text-primary-foreground hover:bg-primary/90">1</Button>
              <Button variant="outline">2</Button>
              <Button variant="outline">3</Button>
              <span className="px-2 text-muted-foreground">...</span>
              <Button variant="outline">12</Button>
              <Button variant="outline">Next</Button>
            </div>
          </div>
        </div>
      </div>
    </div>
  )
}
