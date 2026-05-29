import { redirect } from "next/navigation"
import { Header } from "@/components/header"
import { Footer } from "@/components/footer"
import { ConversationList } from "@/components/conversations/conversation-list"
import { getSessionUser } from "@/lib/supabase/auth-server"
import { getConversations } from "@/lib/conversations/queries"

export const dynamic = "force-dynamic"

export default async function DashboardMessagesPage() {
  const user = await getSessionUser()
  if (!user) redirect("/login?redirect=/dashboard/messages")

  const conversations = await getConversations()

  return (
    <div className="min-h-screen flex flex-col">
      <Header />
      <main className="flex-1">
        <section className="bg-primary py-8">
          <div className="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <p className="text-sm font-semibold uppercase tracking-wider text-accent">Messages</p>
            <h1 className="mt-2 font-sora text-2xl font-semibold text-primary-foreground sm:text-3xl">
              Conversations
            </h1>
            <p className="mt-3 max-w-2xl text-primary-foreground/75">
              Your buyer and seller conversations, sorted by latest activity.
            </p>
          </div>
        </section>
        <section className="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8">
          <ConversationList items={conversations} />
        </section>
      </main>
      <Footer />
    </div>
  )
}
