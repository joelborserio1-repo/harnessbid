import { redirect } from "next/navigation"
import { Header } from "@/components/header"
import { Footer } from "@/components/footer"
import { EnquiryInbox } from "@/components/enquiries/enquiry-inbox"
import { getSessionUser } from "@/lib/supabase/auth-server"
import { getUserEnquiries } from "@/lib/enquiries/queries"

// Enquiries depend on per-request auth; never statically prerender.
export const dynamic = "force-dynamic"

export default async function DashboardMessagesPage() {
  const user = await getSessionUser()
  if (!user) redirect("/login?redirect=/dashboard/messages")

  const inbox = await getUserEnquiries()

  return (
    <div className="min-h-screen flex flex-col">
      <Header />
      <main className="flex-1">
        <section className="bg-primary py-8">
          <div className="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <p className="text-sm font-semibold uppercase tracking-wider text-accent">Messages</p>
            <h1 className="mt-2 font-sora text-2xl font-semibold text-primary-foreground sm:text-3xl">
              Enquiries
            </h1>
            <p className="mt-3 max-w-2xl text-primary-foreground/75">
              Buyer enquiries you&apos;ve received and enquiries you&apos;ve sent. Threaded replies
              arrive in a later phase.
            </p>
          </div>
        </section>

        <section className="mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:px-8">
          <EnquiryInbox inbox={inbox} />
        </section>
      </main>
      <Footer />
    </div>
  )
}
