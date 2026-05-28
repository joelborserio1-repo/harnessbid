import Link from "next/link"
import {
  AlertTriangle,
  BadgeCheck,
  BarChart3,
  BookOpen,
  Building2,
  CheckCircle2,
  Clock,
  CreditCard,
  FileText,
  Gavel,
  Heart,
  HelpCircle,
  Home,
  Lock,
  Mail,
  MapPin,
  MessageSquare,
  Package,
  Plus,
  Search,
  Shield,
  Sparkles,
  Truck,
  User,
  Wrench,
} from "lucide-react"
import { Header } from "@/components/header"
import { Footer } from "@/components/footer"
import { Button } from "@/components/ui/button"
import { Card, CardContent } from "@/components/ui/card"
import { Input } from "@/components/ui/input"
import { Textarea } from "@/components/ui/textarea"

type Icon = typeof Search

type Action = {
  label: string
  href: string
  variant?: "default" | "outline" | "secondary"
}

type EmptyPageProps = {
  icon: Icon
  eyebrow?: string
  title: string
  description: string
  primary: Action
  secondary?: Action
  points?: string[]
}

type ContentPageProps = {
  eyebrow: string
  title: string
  description: string
  sections: Array<{
    title: string
    body: string
    icon?: Icon
  }>
  cta?: {
    title: string
    description: string
    primary: Action
    secondary?: Action
  }
}

type SellerOption = {
  icon: Icon
  title: string
  body: string
  href: string
}

export const categoryPages: Record<string, EmptyPageProps> = {
  equipment: {
    icon: Package,
    eyebrow: "Marketplace category",
    title: "Equipment listings are being curated",
    description: "HarnessBid is preparing a cleaner equipment experience for sulkies, training gear, stable equipment, and professional racing supplies.",
    primary: { label: "Browse marketplace", href: "/marketplace" },
    secondary: { label: "List equipment", href: "/sell/equipment", variant: "outline" },
    points: ["Verified sellers", "Premium listing cards", "Category filters coming online"],
  },
  "bikes-sulkies": {
    icon: Gavel,
    eyebrow: "Race bikes and sulkies",
    title: "No race bikes or sulkies match this view yet",
    description: "New carts and sulkies will appear here as sellers publish listings. You can keep browsing the full marketplace while this category fills out.",
    primary: { label: "Browse all marketplace", href: "/marketplace" },
    secondary: { label: "Sell a sulky", href: "/sell/equipment", variant: "outline" },
  },
  "harness-tack": {
    icon: Shield,
    eyebrow: "Harness and tack",
    title: "Harness and tack listings are coming soon",
    description: "This category is reserved for racing hopples, bridles, lines, pads, and stable essentials from trusted industry sellers.",
    primary: { label: "Browse marketplace", href: "/marketplace" },
    secondary: { label: "Sell tack", href: "/sell/equipment", variant: "outline" },
  },
  "safety-gear": {
    icon: Shield,
    eyebrow: "Safety gear",
    title: "No helmets or safety gear are listed yet",
    description: "Safety products will be grouped here with condition, location, and shipping clarity once listings are approved.",
    primary: { label: "Browse marketplace", href: "/marketplace" },
    secondary: { label: "Back to homepage", href: "/", variant: "outline" },
  },
  "walking-machines": {
    icon: Wrench,
    eyebrow: "Walking machines",
    title: "Walking machine listings are quiet right now",
    description: "Large equipment needs strong seller details and transport notes. This page is ready for those high-value listings.",
    primary: { label: "Browse marketplace", href: "/marketplace" },
    secondary: { label: "List equipment", href: "/sell/equipment", variant: "outline" },
  },
  joggers: {
    icon: Truck,
    eyebrow: "Joggers and training carts",
    title: "No joggers match this category view",
    description: "Training carts and joggers will be presented here with condition, brand, location, and shipping information.",
    primary: { label: "Browse marketplace", href: "/marketplace" },
    secondary: { label: "Clear filters", href: "/marketplace", variant: "outline" },
  },
  vehicles: {
    icon: Truck,
    eyebrow: "Vehicles and floats",
    title: "Vehicle and float listings are not live yet",
    description: "Floats, trucks, and transport equipment will use a more detailed listing format for location, dimensions, and inspection notes.",
    primary: { label: "Browse marketplace", href: "/marketplace" },
    secondary: { label: "Sell a vehicle", href: "/sell/equipment", variant: "outline" },
  },
  services: {
    icon: Wrench,
    eyebrow: "Professional services",
    title: "Service providers are being onboarded",
    description: "HarnessBid will support trusted racing services across breeding, transport, training, and advisory partners.",
    primary: { label: "Contact HarnessBid", href: "/contact" },
    secondary: { label: "Browse marketplace", href: "/marketplace", variant: "outline" },
  },
  property: {
    icon: Building2,
    eyebrow: "Property",
    title: "No racing properties are listed yet",
    description: "Training facilities, agistment properties, and racing real estate will appear here as enterprise listings are approved.",
    primary: { label: "Browse marketplace", href: "/marketplace" },
    secondary: { label: "Enterprise accounts", href: "/enterprise", variant: "outline" },
  },
  feed: {
    icon: Package,
    eyebrow: "Feed and supplements",
    title: "Feed and supplement listings are coming soon",
    description: "This category will help racing operations source feed, supplements, and recurring stable supplies from verified sellers.",
    primary: { label: "Browse marketplace", href: "/marketplace" },
    secondary: { label: "Sell an item", href: "/sell/equipment", variant: "outline" },
  },
  memorabilia: {
    icon: Sparkles,
    eyebrow: "Memorabilia",
    title: "No memorabilia listings are available yet",
    description: "Collectables and racing history items will sit here with premium imagery and provenance notes.",
    primary: { label: "Browse marketplace", href: "/marketplace" },
    secondary: { label: "Back to homepage", href: "/", variant: "outline" },
  },
  breeding: {
    icon: BadgeCheck,
    eyebrow: "Breeding",
    title: "Breeding listings are being prepared",
    description: "Future breeding pages will connect stallion services, bloodstock opportunities, and enterprise sale events.",
    primary: { label: "Browse auctions", href: "/auctions" },
    secondary: { label: "Contact us", href: "/contact", variant: "outline" },
  },
  apparel: {
    icon: Package,
    eyebrow: "Apparel",
    title: "Apparel listings are not live yet",
    description: "Stablewear, branded racing apparel, and team gear will appear here when sellers publish their first listings.",
    primary: { label: "Browse marketplace", href: "/marketplace" },
    secondary: { label: "Sell apparel", href: "/sell/equipment", variant: "outline" },
  },
  other: {
    icon: Search,
    eyebrow: "Marketplace category",
    title: "Nothing has landed in other yet",
    description: "Listings that do not fit the main racing categories will appear here after review.",
    primary: { label: "Browse marketplace", href: "/marketplace" },
    secondary: { label: "Sell an item", href: "/sell/equipment", variant: "outline" },
  },
}

export const supportPages: Record<string, ContentPageProps> = {
  help: {
    eyebrow: "Support",
    title: "Help Center",
    description: "Guidance for buying, selling, watching listings, and working with HarnessBid sale events across the HarnessLink ecosystem.",
    sections: [
      { icon: Search, title: "Find listings", body: "Use marketplace categories, auction timing, seller verification, and listing detail pages to compare opportunities before making contact." },
      { icon: MessageSquare, title: "Contact sellers", body: "Enquiries are designed to keep seller communication clear while payment and bidding writes remain intentionally disconnected at this stage." },
      { icon: Shield, title: "Stay protected", body: "Look for verified seller signals, detailed listing notes, clear shipping information, and HarnessBid support pathways for unusual requests." },
    ],
  },
  trust: {
    eyebrow: "Safety",
    title: "Trust & Safety",
    description: "HarnessBid is being built as a premium racing marketplace where identity, listing quality, and buyer confidence matter.",
    sections: [
      { icon: BadgeCheck, title: "Verified sellers", body: "Seller accounts and enterprise profiles are structured so buyers can understand who they are dealing with before making an enquiry." },
      { icon: FileText, title: "Clear listing records", body: "Horse listings, marketplace items, images, sale events, enquiries, and watchlists each have their own clean place in the platform." },
      { icon: Lock, title: "Payments not connected yet", body: "Payment tables are placeholders only. No checkout, escrow, or payout flow is active until the product is ready." },
    ],
  },
  terms: {
    eyebrow: "Legal",
    title: "Terms of Service",
    description: "A clean front-end terms page for the HarnessBid platform while final legal copy is prepared.",
    sections: [
      { icon: CheckCircle2, title: "Marketplace use", body: "HarnessBid provides listing, browsing, enquiry, and auction presentation tools for harness racing participants and industry sellers." },
      { icon: Gavel, title: "Auction presentation", body: "Auction pages shown in this interface are front-end experiences only until bidding writes and settlement flows are connected." },
      { icon: Shield, title: "Responsible participation", body: "Users should provide accurate listing information, respect seller communications, and follow applicable racing and consumer regulations." },
    ],
  },
  privacy: {
    eyebrow: "Legal",
    title: "Privacy Policy",
    description: "HarnessBid is designed around account profiles, seller accounts, enquiries, watchlists, and future payment placeholders.",
    sections: [
      { icon: User, title: "Account information", body: "Profiles may store contact details, display names, seller status, and preferences needed to operate the marketplace." },
      { icon: Heart, title: "Marketplace activity", body: "Watchlists, enquiries, listing views, and seller dashboards help personalize the product and improve marketplace quality." },
      { icon: Lock, title: "Protected workflows", body: "Row-level security is planned so users only access the buyer, seller, and admin data appropriate to their role." },
    ],
  },
  contact: {
    eyebrow: "Contact",
    title: "Talk to HarnessBid",
    description: "Reach the team about seller onboarding, enterprise sale events, listing support, or HarnessLink marketplace partnerships.",
    sections: [
      { icon: Mail, title: "Support", body: "Email support@harnessbid.com for account, listing, or marketplace questions." },
      { icon: Building2, title: "Enterprise sellers", body: "Sale companies, studs, equipment suppliers, and service providers can request an enterprise account review." },
      { icon: MapPin, title: "Global racing network", body: "HarnessBid is positioned for international harness racing communities while keeping the product focused and premium." },
    ],
    cta: {
      title: "Send an enquiry",
      description: "Tell us what you are trying to sell, source, or organize and the team will route it to the right place.",
      primary: { label: "Email support", href: "mailto:support@harnessbid.com" },
      secondary: { label: "Enterprise accounts", href: "/enterprise", variant: "outline" },
    },
  },
}

export function PageFrame({ children }: { children: React.ReactNode }) {
  return (
    <div className="min-h-screen flex flex-col">
      <Header />
      <main className="flex-1">{children}</main>
      <Footer />
    </div>
  )
}

function PageHero({ eyebrow, title, description }: { eyebrow?: string; title: string; description: string }) {
  return (
    <section className="bg-primary py-10 sm:py-12 lg:py-16">
      <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        {eyebrow && <p className="text-sm font-semibold uppercase tracking-wider text-accent">{eyebrow}</p>}
        <h1 className="mt-3 font-sora text-3xl font-semibold tracking-wide text-primary-foreground sm:text-4xl lg:text-5xl">
          {title}
        </h1>
        <p className="mt-4 max-w-3xl text-base leading-7 text-primary-foreground/75 sm:text-lg">{description}</p>
      </div>
    </section>
  )
}

function ActionButtons({ primary, secondary }: { primary: Action; secondary?: Action }) {
  return (
    <div className="flex flex-col gap-3 sm:flex-row">
      <Button asChild className="bg-accent text-accent-foreground hover:bg-accent/90">
        <Link href={primary.href}>{primary.label}</Link>
      </Button>
      {secondary && (
        <Button asChild variant={secondary.variant || "outline"}>
          <Link href={secondary.href}>{secondary.label}</Link>
        </Button>
      )}
    </div>
  )
}

export function EmptyStatePage(props: EmptyPageProps) {
  const Icon = props.icon

  return (
    <PageFrame>
      <PageHero eyebrow={props.eyebrow} title={props.title} description={props.description} />
      <section className="mx-auto max-w-4xl px-4 py-10 sm:px-6 lg:px-8 lg:py-14">
        <Card className="border-border/70 bg-card shadow-sm">
          <CardContent className="p-6 sm:p-8 lg:p-10">
            <div className="flex flex-col gap-6 sm:flex-row sm:items-start">
              <div className="flex h-14 w-14 shrink-0 items-center justify-center rounded-lg border border-accent/40 bg-accent/10">
                <Icon className="h-7 w-7 text-primary" />
              </div>
              <div className="min-w-0 flex-1">
                <h2 className="font-sora text-2xl font-semibold text-foreground">Keep moving with HarnessBid</h2>
                <p className="mt-3 max-w-2xl text-sm leading-6 text-muted-foreground">
                  {props.description}
                </p>
                {props.points && (
                  <div className="mt-5 grid gap-3 sm:grid-cols-3">
                    {props.points.map((point) => (
                      <div key={point} className="rounded-lg border border-border bg-secondary/50 p-3 text-sm text-foreground">
                        {point}
                      </div>
                    ))}
                  </div>
                )}
                <div className="mt-6">
                  <ActionButtons primary={props.primary} secondary={props.secondary} />
                </div>
              </div>
            </div>
          </CardContent>
        </Card>
      </section>
    </PageFrame>
  )
}

export function ContentPage({ page }: { page: ContentPageProps }) {
  return (
    <PageFrame>
      <PageHero eyebrow={page.eyebrow} title={page.title} description={page.description} />
      <section className="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8 lg:py-14">
        <div className="grid gap-5 md:grid-cols-3">
          {page.sections.map((section) => {
            const Icon = section.icon || FileText
            return (
              <Card key={section.title} className="border-border/70 bg-card">
                <CardContent className="p-6">
                  <div className="mb-5 flex h-11 w-11 items-center justify-center rounded-lg border border-accent/40 bg-accent/10">
                    <Icon className="h-5 w-5 text-primary" />
                  </div>
                  <h2 className="font-sora text-xl font-semibold text-foreground">{section.title}</h2>
                  <p className="mt-3 text-sm leading-6 text-muted-foreground">{section.body}</p>
                </CardContent>
              </Card>
            )
          })}
        </div>
        {page.cta && (
          <Card className="mt-8 border-border/70 bg-card">
            <CardContent className="grid gap-6 p-6 sm:p-8 lg:grid-cols-[1fr_auto] lg:items-center">
              <div>
                <h2 className="font-sora text-2xl font-semibold text-foreground">{page.cta.title}</h2>
                <p className="mt-2 max-w-2xl text-sm leading-6 text-muted-foreground">{page.cta.description}</p>
              </div>
              <ActionButtons primary={page.cta.primary} secondary={page.cta.secondary} />
            </CardContent>
          </Card>
        )}
      </section>
    </PageFrame>
  )
}

export function AuthPage({ mode }: { mode: "login" | "register" }) {
  const isRegister = mode === "register"
  return (
    <PageFrame>
      <section className="bg-primary py-10 sm:py-12">
        <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
          <p className="text-sm font-semibold uppercase tracking-wider text-accent">Account access</p>
          <h1 className="mt-3 font-sora text-3xl font-semibold tracking-wide text-primary-foreground sm:text-4xl">
            {isRegister ? "Create your HarnessBid account" : "Login to HarnessBid"}
          </h1>
          <p className="mt-4 max-w-2xl text-primary-foreground/75">
            {isRegister
              ? "Join the premium marketplace for harness racing auctions, equipment, services, and enterprise sale events."
              : "Access your watchlist, seller dashboard, enquiries, and saved marketplace activity."}
          </p>
        </div>
      </section>
      <section className="mx-auto grid max-w-6xl gap-8 px-4 py-10 sm:px-6 lg:grid-cols-[1fr_420px] lg:px-8 lg:py-14">
        <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-1">
          {[
            ["Verified marketplace", "Follow trusted sellers and high-quality listings across the racing ecosystem."],
            ["Watchlists and enquiries", "Save horses, equipment, and sale events before you contact sellers."],
            ["Seller-ready structure", "Move from browsing to selling when your account is approved."],
          ].map(([title, body]) => (
            <Card key={title} className="border-border/70 bg-card">
              <CardContent className="p-5">
                <BadgeCheck className="mb-3 h-5 w-5 text-accent" />
                <h2 className="font-sora text-lg font-semibold">{title}</h2>
                <p className="mt-2 text-sm leading-6 text-muted-foreground">{body}</p>
              </CardContent>
            </Card>
          ))}
        </div>
        <Card className="border-border/70 bg-card">
          <CardContent className="p-6 sm:p-8">
            <div className="space-y-4">
              {isRegister && <Input placeholder="Full name" />}
              <Input type="email" placeholder="Email address" />
              <Input type="password" placeholder="Password" />
              {isRegister && <Input placeholder="Country or racing region" />}
              <Button className="w-full bg-accent text-accent-foreground hover:bg-accent/90">
                {isRegister ? "Create account" : "Login"}
              </Button>
              <p className="text-center text-sm text-muted-foreground">
                {isRegister ? "Already registered?" : "New to HarnessBid?"}{" "}
                <Link className="font-medium text-primary hover:underline" href={isRegister ? "/login" : "/register"}>
                  {isRegister ? "Login" : "Create an account"}
                </Link>
              </p>
            </div>
          </CardContent>
        </Card>
      </section>
    </PageFrame>
  )
}

export function ContactFormPage() {
  return (
    <ContentPage page={supportPages.contact} />
  )
}

export function SellerActionPage({
  type,
}: {
  type: "sell" | "horse" | "equipment" | "new" | "auction"
}) {
  const copy = {
    sell: ["Sell on HarnessBid", "Choose the right selling path for horses, equipment, services, or enterprise sale events."],
    horse: ["Sell your horse", "Prepare a premium horse listing with pedigree, race record, veterinary notes, images, and sale format."],
    equipment: ["List equipment", "Create a polished marketplace listing for gear, carts, floats, services, feed, or stable equipment."],
    new: ["Create a new listing", "Start with the listing type that best fits your item and move through the seller approval flow."],
    auction: ["Create an auction", "Prepare the front-end auction experience for a horse, sale event, or premium marketplace item."],
  }[type]
  const sellerOptions: SellerOption[] = [
    {
      icon: Gavel,
      title: "Horse auction",
      body: "Pedigree, race record, images, reserve details, and auction timing.",
      href: "/sell/horse",
    },
    {
      icon: Package,
      title: "Marketplace listing",
      body: "Equipment, floats, services, feed, apparel, and racing supplies.",
      href: "/sell/equipment",
    },
    {
      icon: Building2,
      title: "Enterprise sale event",
      body: "Managed sale events for farms, vendors, and industry partners.",
      href: "/enterprise",
    },
  ]

  return (
    <PageFrame>
      <PageHero eyebrow="Seller workspace" title={copy[0]} description={copy[1]} />
      <section className="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8 lg:py-14">
        <div className="grid gap-5 lg:grid-cols-3">
          {sellerOptions.map(({ icon: ItemIcon, title, body, href }) => {
            return (
              <Card key={title} className="border-border/70 bg-card">
                <CardContent className="p-6">
                  <ItemIcon className="mb-5 h-7 w-7 text-primary" />
                  <h2 className="font-sora text-xl font-semibold">{title}</h2>
                  <p className="mt-3 text-sm leading-6 text-muted-foreground">{body}</p>
                  <Button asChild variant="outline" className="mt-6 w-full">
                    <Link href={href}>Select</Link>
                  </Button>
                </CardContent>
              </Card>
            )
          })}
        </div>
      </section>
    </PageFrame>
  )
}

export function DashboardEmptyPage({
  title,
  description,
  icon: Icon,
}: {
  title: string
  description: string
  icon: Icon
}) {
  return (
    <EmptyStatePage
      icon={Icon}
      eyebrow="Account"
      title={title}
      description={description}
      primary={{ label: "Create listing", href: "/sell/new" }}
      secondary={{ label: "Back to dashboard", href: "/dashboard", variant: "outline" }}
      points={["Seller tools", "Listing analytics", "Buyer enquiries"]}
    />
  )
}

export function SellerProfilePage({ slug }: { slug: string }) {
  const name = slug.split("-").map((part) => part.charAt(0).toUpperCase() + part.slice(1)).join(" ")
  return (
    <EmptyStatePage
      icon={Building2}
      eyebrow="Seller profile"
      title={`${name} has no public listings yet`}
      description="This seller profile is ready for verified details, active listings, sale events, response times, and HarnessBid trust signals."
      primary={{ label: "Browse marketplace", href: "/marketplace" }}
      secondary={{ label: "Contact HarnessBid", href: "/contact", variant: "outline" }}
      points={["Verified profile shell", "Enterprise-ready layout", "No seller listings yet"]}
    />
  )
}

export function NotFoundPageContent() {
  return (
    <EmptyStatePage
      icon={AlertTriangle}
      eyebrow="Page not found"
      title="This HarnessBid page is not available"
      description="The listing, category, seller, or support page may have moved, expired, or not been published yet."
      primary={{ label: "Back to homepage", href: "/" }}
      secondary={{ label: "Browse marketplace", href: "/marketplace", variant: "outline" }}
    />
  )
}

export function LoadingPageContent() {
  return (
    <PageFrame>
      <section className="bg-primary py-10 sm:py-12">
        <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
          <div className="h-4 w-32 rounded bg-primary-foreground/20" />
          <div className="mt-5 h-10 max-w-xl rounded bg-primary-foreground/20" />
          <div className="mt-4 h-5 max-w-2xl rounded bg-primary-foreground/10" />
        </div>
      </section>
      <section className="mx-auto grid max-w-7xl gap-5 px-4 py-10 sm:grid-cols-2 sm:px-6 lg:grid-cols-3 lg:px-8">
        {[0, 1, 2].map((item) => (
          <Card key={item} className="border-border/70">
            <CardContent className="p-6">
              <div className="h-12 w-12 rounded-lg bg-secondary" />
              <div className="mt-6 h-6 w-3/4 rounded bg-secondary" />
              <div className="mt-4 h-4 rounded bg-secondary" />
              <div className="mt-2 h-4 w-5/6 rounded bg-secondary" />
            </CardContent>
          </Card>
        ))}
      </section>
    </PageFrame>
  )
}

export function ErrorPageContent() {
  return (
    <EmptyStatePage
      icon={AlertTriangle}
      eyebrow="Something went wrong"
      title="HarnessBid could not load this page"
      description="The page shell is still available, but the requested content did not load cleanly. Try returning to a stable marketplace view."
      primary={{ label: "Browse marketplace", href: "/marketplace" }}
      secondary={{ label: "Back to homepage", href: "/", variant: "outline" }}
    />
  )
}

export function EnquiryPanel() {
  return (
    <Card className="border-border/70 bg-card">
      <CardContent className="grid gap-4 p-6 sm:p-8">
        <Input placeholder="Name" />
        <Input type="email" placeholder="Email" />
        <Textarea placeholder="How can HarnessBid help?" className="min-h-28" />
        <Button className="bg-accent text-accent-foreground hover:bg-accent/90">Send enquiry</Button>
      </CardContent>
    </Card>
  )
}

export const iconMap = {
  BarChart3,
  BookOpen,
  Clock,
  CreditCard,
  FileText,
  Heart,
  HelpCircle,
  Home,
  MessageSquare,
  Package,
  Plus,
  Search,
}
