import Link from "next/link"
import { BarChart3, Building2, FileWarning, FolderTree, Gavel, LayoutGrid, ShieldCheck } from "lucide-react"
import { Header } from "@/components/header"
import { Footer } from "@/components/footer"
import { requireStaff } from "@/lib/admin/guard"

export const dynamic = "force-dynamic"

const NAV = [
  { href: "/admin", label: "Overview", icon: BarChart3 },
  { href: "/admin/listings", label: "Listings", icon: LayoutGrid },
  { href: "/admin/enterprise", label: "Enterprise", icon: Building2 },
  { href: "/admin/reports", label: "Reports", icon: FileWarning },
  { href: "/admin/sale-events", label: "Sale events", icon: Gavel },
  { href: "/admin/categories", label: "Categories", icon: FolderTree },
]

export default async function AdminLayout({ children }: { children: React.ReactNode }) {
  // Server-side staff gate (defense-in-depth alongside the proxy auth check).
  const ctx = await requireStaff()

  return (
    <div className="min-h-screen flex flex-col">
      <Header />
      <div className="bg-primary">
        <div className="mx-auto flex max-w-7xl flex-col gap-3 px-4 py-4 sm:px-6 lg:px-8">
          <div className="flex items-center gap-2 text-primary-foreground">
            <ShieldCheck className="h-5 w-5 text-accent" />
            <span className="font-sora text-lg font-semibold">Admin Console</span>
            <span className="rounded bg-primary-foreground/15 px-2 py-0.5 text-xs uppercase tracking-wide text-primary-foreground/80">
              {ctx.role}
            </span>
          </div>
          <nav className="flex flex-wrap gap-1">
            {NAV.map(({ href, label, icon: Icon }) => (
              <Link
                key={href}
                href={href}
                className="flex items-center gap-2 rounded-md px-3 py-1.5 text-sm text-primary-foreground/80 hover:bg-primary-foreground/10 hover:text-primary-foreground"
              >
                <Icon className="h-4 w-4" />
                {label}
              </Link>
            ))}
          </nav>
        </div>
      </div>
      <main className="flex-1 bg-background">
        <div className="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">{children}</div>
      </main>
      <Footer />
    </div>
  )
}
