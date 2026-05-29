import Link from "next/link"
import { redirect } from "next/navigation"
import { LayoutDashboard, LogOut } from "lucide-react"
import { PageFrame } from "@/components/static-pages"
import { Button } from "@/components/ui/button"
import { Card, CardContent } from "@/components/ui/card"
import { signOutAction } from "@/lib/auth/actions"
import {
  getCurrentProfile,
  getOwnedSellerAccount,
  getSessionUser,
} from "@/lib/supabase/auth-server"

// Auth depends on per-request cookies; never statically prerender.
export const dynamic = "force-dynamic"

const ROLE_LABELS: Record<string, string> = {
  buyer: "Buyer",
  seller: "Seller",
  enterprise_seller: "Enterprise seller",
  admin: "Administrator",
}

export default async function AccountPage() {
  const user = await getSessionUser()
  if (!user) redirect("/login?redirect=/account")

  const [profile, sellerAccount] = await Promise.all([
    getCurrentProfile(),
    getOwnedSellerAccount(),
  ])

  const rows: Array<[string, string]> = [
    ["Name", profile?.fullName || profile?.displayName || "Not provided"],
    ["Email", profile?.email || user.email || "—"],
    ["Account role", ROLE_LABELS[profile?.role ?? "buyer"] ?? "Buyer"],
    [
      "Seller account",
      sellerAccount
        ? `${sellerAccount.displayName} (${sellerAccount.verificationStatus})`
        : "Not set up yet",
    ],
  ]

  return (
    <PageFrame>
      <section className="bg-primary py-10 sm:py-12">
        <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
          <p className="text-sm font-semibold uppercase tracking-wider text-accent">Account</p>
          <h1 className="mt-3 font-sora text-3xl font-semibold tracking-wide text-primary-foreground sm:text-4xl">
            Your HarnessBid account
          </h1>
          <p className="mt-4 max-w-2xl text-primary-foreground/75">
            Manage your profile and seller status. Saved activity and enquiries connect in later
            phases.
          </p>
        </div>
      </section>

      <section className="mx-auto max-w-3xl px-4 py-10 sm:px-6 lg:px-8 lg:py-14">
        <Card className="border-border/70 bg-card shadow-sm">
          <CardContent className="p-6 sm:p-8">
            <h2 className="font-sora text-xl font-semibold text-foreground">Profile details</h2>
            <dl className="mt-5 divide-y divide-border">
              {rows.map(([label, value]) => (
                <div key={label} className="flex justify-between gap-4 py-3">
                  <dt className="text-sm text-muted-foreground">{label}</dt>
                  <dd className="text-sm font-medium text-foreground text-right">{value}</dd>
                </div>
              ))}
            </dl>

            <div className="mt-6 flex flex-col gap-3 sm:flex-row">
              <Button asChild className="bg-accent text-accent-foreground hover:bg-accent/90">
                <Link href="/dashboard">
                  <LayoutDashboard className="mr-2 h-4 w-4" />
                  Seller dashboard
                </Link>
              </Button>
              <form action={signOutAction}>
                <Button type="submit" variant="outline" className="w-full sm:w-auto">
                  <LogOut className="mr-2 h-4 w-4" />
                  Sign out
                </Button>
              </form>
            </div>
          </CardContent>
        </Card>
      </section>
    </PageFrame>
  )
}
