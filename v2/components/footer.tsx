import Link from "next/link"
import { 
  Facebook, 
  Twitter, 
  Instagram, 
  Youtube,
  Mail,
  Phone,
  MapPin
} from "lucide-react"

const footerLinks = {
  horses: [
    { name: "Live Auctions", href: "/auctions" },
    { name: "Buy Now Horses", href: "/horses/buy-now" },
    { name: "Closing Soon", href: "/auctions?sort=ending" },
    { name: "Recently Sold", href: "/horses/sold" },
  ],
  marketplace: [
    { name: "Equipment", href: "/marketplace/equipment" },
    { name: "Vehicles & Floats", href: "/marketplace/vehicles" },
    { name: "Services", href: "/marketplace/services" },
    { name: "Property", href: "/marketplace/property" },
    { name: "Feed & Supplements", href: "/marketplace/feed" },
  ],
  sellers: [
    { name: "Sell Your Horse", href: "/sell/horse" },
    { name: "List Equipment", href: "/sell/equipment" },
    { name: "Enterprise Accounts", href: "/enterprise" },
    { name: "Pricing", href: "/pricing" },
    { name: "Seller Guide", href: "/guides/seller" },
  ],
  support: [
    { name: "Help Center", href: "/help" },
    { name: "Trust & Safety", href: "/trust" },
    { name: "Terms of Service", href: "/terms" },
    { name: "Privacy Policy", href: "/privacy" },
    { name: "Contact", href: "/contact" },
  ],
}

const socialLinks = [
  { name: "Facebook", icon: Facebook, href: "https://harnesslink.com" },
  { name: "Twitter", icon: Twitter, href: "https://harnesslink.com" },
  { name: "Instagram", icon: Instagram, href: "https://harnesslink.com" },
  { name: "YouTube", icon: Youtube, href: "https://harnesslink.com" },
]

export function Footer() {
  const homeHref = `${process.env.NEXT_PUBLIC_BASE_PATH || ""}/`

  return (
    <footer className="bg-primary text-primary-foreground">
      {/* Main Footer */}
      <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-12 lg:py-16">
        {/* Centered Logo */}
        <div className="flex justify-center mb-10">
          <a href={homeHref}>
            <span 
              className="text-3xl sm:text-4xl font-bold uppercase tracking-wide text-primary-foreground"
              style={{ fontFamily: 'var(--font-cinzel)' }}
            >
              HarnessBid
            </span>
          </a>
        </div>

        <div className="grid grid-cols-2 md:grid-cols-4 gap-8 lg:gap-12">
          {/* Horses Links */}
          <div>
            <h3 className="font-sora text-sm font-semibold uppercase tracking-wider mb-4">
              Horses
            </h3>
            <ul className="space-y-2">
              {footerLinks.horses.map((link) => (
                <li key={link.name}>
                  <Link 
                    href={link.href}
                    className="text-sm text-primary-foreground/70 hover:text-primary-foreground transition-colors"
                  >
                    {link.name}
                  </Link>
                </li>
              ))}
            </ul>
          </div>

          {/* Marketplace Links */}
          <div>
            <h3 className="font-sora text-sm font-semibold uppercase tracking-wider mb-4">
              Marketplace
            </h3>
            <ul className="space-y-2">
              {footerLinks.marketplace.map((link) => (
                <li key={link.name}>
                  <Link 
                    href={link.href}
                    className="text-sm text-primary-foreground/70 hover:text-primary-foreground transition-colors"
                  >
                    {link.name}
                  </Link>
                </li>
              ))}
            </ul>
          </div>

          {/* Sellers Links */}
          <div>
            <h3 className="font-sora text-sm font-semibold uppercase tracking-wider mb-4">
              For Sellers
            </h3>
            <ul className="space-y-2">
              {footerLinks.sellers.map((link) => (
                <li key={link.name}>
                  <Link 
                    href={link.href}
                    className="text-sm text-primary-foreground/70 hover:text-primary-foreground transition-colors"
                  >
                    {link.name}
                  </Link>
                </li>
              ))}
            </ul>
          </div>

          {/* Support Links */}
          <div>
            <h3 className="font-sora text-sm font-semibold uppercase tracking-wider mb-4">
              Support
            </h3>
            <ul className="space-y-2">
              {footerLinks.support.map((link) => (
                <li key={link.name}>
                  <Link 
                    href={link.href}
                    className="text-sm text-primary-foreground/70 hover:text-primary-foreground transition-colors"
                  >
                    {link.name}
                  </Link>
                </li>
              ))}
            </ul>
          </div>
        </div>

        {/* Social & Contact */}
        <div className="mt-12 pt-8 border-t border-primary-foreground/10">
          <div className="flex flex-col md:flex-row items-center justify-between gap-6">
            {/* Social Links */}
            <div className="flex gap-3">
              {socialLinks.map((social) => (
                <a
                  key={social.name}
                  href={social.href}
                  target="_blank"
                  rel="noopener noreferrer"
                  className="p-2 rounded-md bg-primary-foreground/10 hover:bg-primary-foreground/20 transition-colors"
                  aria-label={social.name}
                >
                  <social.icon className="h-4 w-4" />
                </a>
              ))}
            </div>
            
            {/* Contact Info */}
            <div className="flex flex-wrap gap-6 justify-center text-sm text-primary-foreground/70">
              <a href="mailto:support@harnessbid.com" className="flex items-center gap-2 hover:text-primary-foreground transition-colors">
                <Mail className="h-4 w-4" />
                support@harnessbid.com
              </a>
              <a href="tel:+1800000000" className="flex items-center gap-2 hover:text-primary-foreground transition-colors">
                <Phone className="h-4 w-4" />
                1-800-HARNESS
              </a>
              <span className="flex items-center gap-2">
                <MapPin className="h-4 w-4" />
                Global
              </span>
            </div>
          </div>
        </div>
      </div>

      {/* Bottom Bar */}
      <div className="border-t border-primary-foreground/10">
        <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-4">
          <div className="flex flex-col md:flex-row justify-between items-center gap-4 text-xs text-primary-foreground/50">
            <p>&copy; {new Date().getFullYear()} HarnessBid. All rights reserved.</p>
            <p>
              Part of the{" "}
              <a 
                href="https://harnesslink.com" 
                target="_blank" 
                rel="noopener noreferrer"
                className="text-accent hover:text-accent/80 transition-colors"
              >
                HarnessLink
              </a>{" "}
              network
            </p>
          </div>
        </div>
      </div>
    </footer>
  )
}
