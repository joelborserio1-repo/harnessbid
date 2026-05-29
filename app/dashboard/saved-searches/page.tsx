import { redirect } from "next/navigation"
import { Header } from "@/components/header"
import { Footer } from "@/components/footer"
import { SavedSearchesList } from "@/components/saved-searches/saved-searches-list"
import { getSessionUser } from "@/lib/supabase/auth-server"
import { getUserSavedSearches } from "@/lib/saved-searches/queries"

export const dynamic = "force-dynamic"

export default async function SavedSearchesPage() {
  const user = await getSessionUser()
  if (!user) redirect("/login?redirect=/dashboard/saved-searches")

  const searches = await getUserSavedSearches()

  return (
    <div className="min-h-screen flex flex-col">
      <Header />
      <main className="flex-1">
        <section className="bg-primary py-8">
          <div className="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <p className="text-sm font-semibold uppercase tracking-wider text-accent">Activity</p>
            <h1 className="mt-2 font-sora text-2xl font-semibold text-primary-foreground sm:text-3xl">
              Saved searches
            </h1>
            <p className="mt-3 max-w-2xl text-primary-foreground/75">
              Quickly return to filtered views. Alert delivery arrives in a later phase.
            </p>
          </div>
        </section>
        <section className="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
          <SavedSearchesList items={searches} />
        </section>
      </main>
      <Footer />
    </div>
  )
}
