import { redirect } from "next/navigation"
import { Header } from "@/components/header"
import { Footer } from "@/components/footer"
import { NotificationsList } from "@/components/notifications/notifications-list"
import { getSessionUser } from "@/lib/supabase/auth-server"
import { getUserNotifications } from "@/lib/notifications/queries"

export const dynamic = "force-dynamic"

export default async function NotificationsPage() {
  const user = await getSessionUser()
  if (!user) redirect("/login?redirect=/dashboard/notifications")

  const notifications = await getUserNotifications(50)

  return (
    <div className="min-h-screen flex flex-col">
      <Header />
      <main className="flex-1">
        <section className="bg-primary py-8">
          <div className="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <p className="text-sm font-semibold uppercase tracking-wider text-accent">Activity</p>
            <h1 className="mt-2 font-sora text-2xl font-semibold text-primary-foreground sm:text-3xl">
              Notifications
            </h1>
          </div>
        </section>
        <section className="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
          <NotificationsList initial={notifications} />
        </section>
      </main>
      <Footer />
    </div>
  )
}
