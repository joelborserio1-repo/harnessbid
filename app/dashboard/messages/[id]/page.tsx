import Link from "next/link"
import { redirect } from "next/navigation"
import { ArrowLeft, MessageSquare } from "lucide-react"
import { Header } from "@/components/header"
import { Footer } from "@/components/footer"
import { EmptyStatePage } from "@/components/static-pages"
import { ConversationThread } from "@/components/conversations/conversation-thread"
import { getSessionUser } from "@/lib/supabase/auth-server"
import { getConversation } from "@/lib/conversations/queries"

export const dynamic = "force-dynamic"

export default async function ConversationPage({
  params,
}: {
  params: Promise<{ id: string }>
}) {
  const { id } = await params
  const user = await getSessionUser()
  if (!user) redirect(`/login?redirect=/dashboard/messages/${id}`)

  const conversation = await getConversation(id)
  if (!conversation) {
    return (
      <EmptyStatePage
        icon={MessageSquare}
        eyebrow="Conversation"
        title="This conversation is not available"
        description="It may have been removed, or it does not belong to your account."
        primary={{ label: "Back to messages", href: "/dashboard/messages" }}
        secondary={{ label: "Dashboard", href: "/dashboard", variant: "outline" }}
      />
    )
  }

  return (
    <div className="min-h-screen flex flex-col">
      <Header />
      <main className="flex-1">
        <section className="bg-primary py-6">
          <div className="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <Link
              href="/dashboard/messages"
              className="inline-flex items-center gap-1 text-sm text-primary-foreground/80 hover:text-primary-foreground"
            >
              <ArrowLeft className="h-4 w-4" />
              All messages
            </Link>
          </div>
        </section>
        <section className="mx-auto max-w-3xl px-4 py-6 sm:px-6 lg:px-8">
          <ConversationThread conversation={conversation} />
        </section>
      </main>
      <Footer />
    </div>
  )
}
