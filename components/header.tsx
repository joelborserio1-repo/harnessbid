"use client"

import { useState } from "react"
import Link from "next/link"
import { useRouter } from "next/navigation"
import {
  Search,
  User,
  Heart,
  Menu,
  X,
  ChevronDown,
  Bike,
  Shield,
  Dumbbell,
  Truck,
  Wrench,
  Building2,
  Leaf,
  Dna,
  Shirt,
  MoreHorizontal,
  Gavel,
  LayoutDashboard,
  LogOut,
  Package,
  Store,
  Bell
} from "lucide-react"
import { Button } from "@/components/ui/button"
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuTrigger,
  DropdownMenuSeparator,
} from "@/components/ui/dropdown-menu"
import { useAuthUser } from "@/hooks/use-auth-user"
import { NotificationsMenu } from "@/components/notifications/notifications-menu"

const horseCategories = [
  { name: "Live Auctions", icon: Gavel, href: "/auctions" },
  { name: "Buy Now Horses", icon: Dna, href: "/horses/buy-now" },
  { name: "Closing Soon", icon: Gavel, href: "/auctions?sort=ending" },
  { name: "Recently Sold", icon: Dna, href: "/horses/sold" },
]

const marketplaceCategories = [
  { name: "Equipment", icon: Bike, href: "/marketplace/equipment" },
  { name: "Race Bikes & Sulkies", icon: Bike, href: "/marketplace/bikes-sulkies" },
  { name: "Harness & Tack", icon: Shield, href: "/marketplace/harness-tack" },
  { name: "Helmets & Safety Gear", icon: Shield, href: "/marketplace/safety-gear" },
  { name: "Walking Machines", icon: Dumbbell, href: "/marketplace/walking-machines" },
  { name: "Joggers & Training Carts", icon: Bike, href: "/marketplace/joggers" },
]

const otherCategories = [
  { name: "Vehicles & Floats", icon: Truck, href: "/marketplace/vehicles" },
  { name: "Services", icon: Wrench, href: "/marketplace/services" },
  { name: "Property", icon: Building2, href: "/marketplace/property" },
  { name: "Feed & Supplements", icon: Leaf, href: "/marketplace/feed" },
  { name: "Memorabilia", icon: MoreHorizontal, href: "/marketplace/memorabilia" },
  { name: "Other", icon: Shirt, href: "/marketplace/other" },
]

export function Header() {
  const [isMobileMenuOpen, setIsMobileMenuOpen] = useState(false)
  const homeHref = `${process.env.NEXT_PUBLIC_BASE_PATH || ""}/`
  const { isAuthenticated, email, sellerSlug, isEnterprise } = useAuthUser()
  const router = useRouter()

  async function handleSignOut() {
    try {
      const { createSupabaseBrowserClient } = await import("@/lib/supabase/client")
      await createSupabaseBrowserClient().auth.signOut()
    } catch {
      // No-op: Supabase env may be unconfigured; fall through to refresh.
    }
    setIsMobileMenuOpen(false)
    router.refresh()
    router.push("/")
  }

  return (
    <header className="sticky top-0 z-50 w-full border-b border-border/40 bg-primary">
      <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div className="flex h-18 items-center justify-between py-3">
          {/* Desktop Left Navigation */}
          <nav className="hidden lg:flex items-center gap-1 flex-1">
            <DropdownMenu>
              <DropdownMenuTrigger asChild>
                <Button variant="ghost" className="font-medium text-primary-foreground/80 hover:text-primary-foreground hover:bg-primary-foreground/10">
                  <Gavel className="mr-2 h-4 w-4" />
                  Horses
                  <ChevronDown className="ml-1 h-4 w-4" />
                </Button>
              </DropdownMenuTrigger>
              <DropdownMenuContent className="w-[280px] p-3" align="start">
                <p className="mb-2 px-2 text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                  Horse Sales
                </p>
                {horseCategories.map((category) => (
                  <DropdownMenuItem key={category.name} asChild>
                    <Link href={category.href} className="flex items-center gap-2 cursor-pointer">
                      <category.icon className="h-4 w-4 text-muted-foreground" />
                      <span>{category.name}</span>
                    </Link>
                  </DropdownMenuItem>
                ))}
              </DropdownMenuContent>
            </DropdownMenu>
            
            <DropdownMenu>
              <DropdownMenuTrigger asChild>
                <Button variant="ghost" className="font-medium text-primary-foreground/80 hover:text-primary-foreground hover:bg-primary-foreground/10">
                  Marketplace
                  <ChevronDown className="ml-1 h-4 w-4" />
                </Button>
              </DropdownMenuTrigger>
              <DropdownMenuContent className="w-[500px] p-4" align="start">
                <div className="grid grid-cols-2 gap-2">
                  <div>
                    <p className="mb-2 px-2 text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                      Equipment
                    </p>
                    {marketplaceCategories.map((category) => (
                      <DropdownMenuItem key={category.name} asChild>
                        <Link href={category.href} className="flex items-center gap-2 cursor-pointer">
                          <category.icon className="h-4 w-4 text-muted-foreground" />
                          <span>{category.name}</span>
                        </Link>
                      </DropdownMenuItem>
                    ))}
                  </div>
                  <div>
                    <p className="mb-2 px-2 text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                      Other Categories
                    </p>
                    {otherCategories.map((category) => (
                      <DropdownMenuItem key={category.name} asChild>
                        <Link href={category.href} className="flex items-center gap-2 cursor-pointer">
                          <category.icon className="h-4 w-4 text-muted-foreground" />
                          <span>{category.name}</span>
                        </Link>
                      </DropdownMenuItem>
                    ))}
                  </div>
                </div>
                <DropdownMenuSeparator className="my-2" />
                <DropdownMenuItem asChild>
                  <Link href="/marketplace" className="flex items-center justify-center font-medium text-primary">
                    View All Marketplace Listings
                  </Link>
                </DropdownMenuItem>
              </DropdownMenuContent>
            </DropdownMenu>

            <Link href="/sell">
              <Button variant="ghost" className="font-medium text-primary-foreground/80 hover:text-primary-foreground hover:bg-primary-foreground/10">
                Sell
              </Button>
            </Link>
          </nav>

          {/* Centered Logo */}
          <a href={homeHref} className="flex items-center justify-center lg:absolute lg:left-1/2 lg:-translate-x-1/2">
            <span 
              className="text-2xl sm:text-3xl font-bold uppercase tracking-wide text-primary-foreground"
              style={{ fontFamily: 'var(--font-cinzel)' }}
            >
              HarnessBid
            </span>
          </a>

          {/* Desktop Right Actions */}
          <div className="hidden lg:flex items-center gap-2 flex-1 justify-end">
            <Button asChild variant="ghost" size="icon" className="text-primary-foreground/70 hover:text-primary-foreground hover:bg-primary-foreground/10">
              <Link href="/search">
                <Search className="h-5 w-5" />
                <span className="sr-only">Search</span>
              </Link>
            </Button>
            <Button asChild variant="ghost" size="icon" className="text-primary-foreground/70 hover:text-primary-foreground hover:bg-primary-foreground/10">
              <Link href="/watchlist">
                <Heart className="h-5 w-5" />
                <span className="sr-only">Watchlist</span>
              </Link>
            </Button>
            <NotificationsMenu />
            {isAuthenticated ? (
              <DropdownMenu>
                <DropdownMenuTrigger asChild>
                  <Button variant="ghost" className="text-primary-foreground/70 hover:text-primary-foreground hover:bg-primary-foreground/10">
                    <User className="mr-2 h-4 w-4" />
                    Account
                    <ChevronDown className="ml-1 h-4 w-4" />
                  </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="end" className="w-56">
                  {email && (
                    <>
                      <DropdownMenuLabel className="truncate font-normal text-muted-foreground">
                        {email}
                      </DropdownMenuLabel>
                      <DropdownMenuSeparator />
                    </>
                  )}
                  <DropdownMenuItem asChild>
                    <Link href="/dashboard" className="flex cursor-pointer items-center gap-2">
                      <LayoutDashboard className="h-4 w-4 text-muted-foreground" />
                      Dashboard
                    </Link>
                  </DropdownMenuItem>
                  <DropdownMenuItem asChild>
                    <Link href="/dashboard/listings" className="flex cursor-pointer items-center gap-2">
                      <Package className="h-4 w-4 text-muted-foreground" />
                      My Listings
                    </Link>
                  </DropdownMenuItem>
                  <DropdownMenuItem asChild>
                    <Link href="/watchlist" className="flex cursor-pointer items-center gap-2">
                      <Heart className="h-4 w-4 text-muted-foreground" />
                      Saved Listings
                    </Link>
                  </DropdownMenuItem>
                  <DropdownMenuItem asChild>
                    <Link href="/dashboard/saved-searches" className="flex cursor-pointer items-center gap-2">
                      <Search className="h-4 w-4 text-muted-foreground" />
                      Saved Searches
                    </Link>
                  </DropdownMenuItem>
                  <DropdownMenuItem asChild>
                    <Link href="/dashboard/bids" className="flex cursor-pointer items-center gap-2">
                      <Gavel className="h-4 w-4 text-muted-foreground" />
                      My Bids
                    </Link>
                  </DropdownMenuItem>
                  {isEnterprise && sellerSlug && (
                    <DropdownMenuItem asChild>
                      <Link href={`/seller/${sellerSlug}`} className="flex cursor-pointer items-center gap-2">
                        <Store className="h-4 w-4 text-muted-foreground" />
                        My Storefront
                      </Link>
                    </DropdownMenuItem>
                  )}
                  <DropdownMenuItem asChild>
                    <Link href="/account" className="flex cursor-pointer items-center gap-2">
                      <User className="h-4 w-4 text-muted-foreground" />
                      Account
                    </Link>
                  </DropdownMenuItem>
                  <DropdownMenuSeparator />
                  <DropdownMenuItem
                    onClick={handleSignOut}
                    className="flex cursor-pointer items-center gap-2 text-destructive focus:text-destructive"
                  >
                    <LogOut className="h-4 w-4" />
                    Logout
                  </DropdownMenuItem>
                </DropdownMenuContent>
              </DropdownMenu>
            ) : (
              <>
                <Link href="/login">
                  <Button variant="ghost" className="text-primary-foreground/70 hover:text-primary-foreground hover:bg-primary-foreground/10">
                    <User className="mr-2 h-4 w-4" />
                    Login
                  </Button>
                </Link>
                <Link href="/register">
                  <Button className="bg-accent text-accent-foreground hover:bg-accent/90">
                    Register
                  </Button>
                </Link>
              </>
            )}
          </div>

          {/* Mobile Menu Button */}
          <button
            className="lg:hidden p-2 text-primary-foreground/70 hover:text-primary-foreground"
            onClick={() => setIsMobileMenuOpen(!isMobileMenuOpen)}
            aria-label="Toggle menu"
          >
            {isMobileMenuOpen ? <X className="h-6 w-6" /> : <Menu className="h-6 w-6" />}
          </button>
        </div>

        {/* Mobile Navigation */}
        {isMobileMenuOpen && (
          <div className="lg:hidden border-t border-primary-foreground/10 py-4">
            <nav className="flex flex-col gap-2">
              <p className="px-4 py-2 text-xs font-semibold uppercase tracking-wider text-primary-foreground/50">
                Horses
              </p>
              {horseCategories.map((category) => (
                <Link 
                  key={category.name}
                  href={category.href} 
                  className="flex items-center gap-2 px-4 py-2 text-primary-foreground/80 hover:bg-primary-foreground/10 rounded-md"
                  onClick={() => setIsMobileMenuOpen(false)}
                >
                  <category.icon className="h-5 w-5" />
                  {category.name}
                </Link>
              ))}
              <div className="border-t border-primary-foreground/10 my-2" />
              <p className="px-4 py-2 text-xs font-semibold uppercase tracking-wider text-primary-foreground/50">
                Marketplace
              </p>
              <Link 
                href="/marketplace" 
                className="flex items-center gap-2 px-4 py-2 text-primary-foreground/80 hover:bg-primary-foreground/10 rounded-md"
                onClick={() => setIsMobileMenuOpen(false)}
              >
                <Search className="h-5 w-5" />
                Browse All
              </Link>
              <Link 
                href="/sell" 
                className="flex items-center gap-2 px-4 py-2 text-primary-foreground/80 hover:bg-primary-foreground/10 rounded-md"
                onClick={() => setIsMobileMenuOpen(false)}
              >
                Sell
              </Link>
              <div className="border-t border-primary-foreground/10 my-2" />
              <Link 
                href="/watchlist" 
                className="flex items-center gap-2 px-4 py-2 text-primary-foreground/80 hover:bg-primary-foreground/10 rounded-md"
                onClick={() => setIsMobileMenuOpen(false)}
              >
                <Heart className="h-5 w-5" />
                Watchlist
              </Link>
              {isAuthenticated ? (
                <>
                  <Link
                    href="/dashboard"
                    className="flex items-center gap-2 px-4 py-2 text-primary-foreground/80 hover:bg-primary-foreground/10 rounded-md"
                    onClick={() => setIsMobileMenuOpen(false)}
                  >
                    <LayoutDashboard className="h-5 w-5" />
                    Dashboard
                  </Link>
                  <Link
                    href="/dashboard/listings"
                    className="flex items-center gap-2 px-4 py-2 text-primary-foreground/80 hover:bg-primary-foreground/10 rounded-md"
                    onClick={() => setIsMobileMenuOpen(false)}
                  >
                    <Package className="h-5 w-5" />
                    My Listings
                  </Link>
                  <Link
                    href="/dashboard/notifications"
                    className="flex items-center gap-2 px-4 py-2 text-primary-foreground/80 hover:bg-primary-foreground/10 rounded-md"
                    onClick={() => setIsMobileMenuOpen(false)}
                  >
                    <Bell className="h-5 w-5" />
                    Notifications
                  </Link>
                  {isEnterprise && sellerSlug && (
                    <Link
                      href={`/seller/${sellerSlug}`}
                      className="flex items-center gap-2 px-4 py-2 text-primary-foreground/80 hover:bg-primary-foreground/10 rounded-md"
                      onClick={() => setIsMobileMenuOpen(false)}
                    >
                      <Store className="h-5 w-5" />
                      My Storefront
                    </Link>
                  )}
                  <Link
                    href="/account"
                    className="flex items-center gap-2 px-4 py-2 text-primary-foreground/80 hover:bg-primary-foreground/10 rounded-md"
                    onClick={() => setIsMobileMenuOpen(false)}
                  >
                    <User className="h-5 w-5" />
                    Account
                  </Link>
                  <button
                    type="button"
                    onClick={handleSignOut}
                    className="flex items-center gap-2 px-4 py-2 text-left text-primary-foreground/80 hover:bg-primary-foreground/10 rounded-md"
                  >
                    <LogOut className="h-5 w-5" />
                    Logout
                  </button>
                </>
              ) : (
                <>
                  <Link
                    href="/login"
                    className="flex items-center gap-2 px-4 py-2 text-primary-foreground/80 hover:bg-primary-foreground/10 rounded-md"
                    onClick={() => setIsMobileMenuOpen(false)}
                  >
                    <User className="h-5 w-5" />
                    Login
                  </Link>
                  <Link href="/register" onClick={() => setIsMobileMenuOpen(false)}>
                    <Button className="w-full mt-2 bg-accent text-accent-foreground hover:bg-accent/90">
                      Register
                    </Button>
                  </Link>
                </>
              )}
            </nav>
          </div>
        )}
      </div>
    </header>
  )
}
