"use client"

import { useTransition } from "react"
import { useRouter } from "next/navigation"
import { Mail, MessageSquare, Phone } from "lucide-react"
import { Badge } from "@/components/ui/badge"
import { Button } from "@/components/ui/button"
import { Card, CardContent } from "@/components/ui/card"
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs"
import { updateEnquiryStatusAction } from "@/lib/enquiries/actions"
import type { EnquiryInbox as Inbox, EnquiryView } from "@/lib/enquiries/queries"

function formatDate(iso: string) {
  return new Intl.DateTimeFormat("en", { dateStyle: "medium" }).format(new Date(iso))
}

function StatusButton({ id, status, label }: { id: string; status: string; label: string }) {
  const router = useRouter()
  const [pending, startTransition] = useTransition()
  return (
    <Button
      type="button"
      size="sm"
      variant="outline"
      disabled={pending}
      onClick={() =>
        startTransition(async () => {
          const fd = new FormData()
          fd.set("id", id)
          fd.set("status", status)
          await updateEnquiryStatusAction({}, fd)
          router.refresh()
        })
      }
    >
      {label}
    </Button>
  )
}

function StatusBadge({ status }: { status: string }) {
  const tone =
    status === "replied"
      ? "bg-accent text-accent-foreground"
      : status === "open"
        ? "bg-primary text-primary-foreground"
        : undefined
  return <Badge className={tone} variant={tone ? undefined : "secondary"}>{status}</Badge>
}

function EnquiryCard({ enquiry }: { enquiry: EnquiryView }) {
  const received = enquiry.direction === "received"
  return (
    <Card className="border-border/70">
      <CardContent className="space-y-3 p-4 sm:p-5">
        <div className="flex flex-wrap items-start justify-between gap-2">
          <div>
            <p className="font-sora font-semibold text-foreground">{enquiry.listingTitle}</p>
            <p className="text-xs text-muted-foreground">
              {received ? "From" : "To"} {enquiry.counterpart} · {formatDate(enquiry.createdAt)}
            </p>
          </div>
          <StatusBadge status={enquiry.status} />
        </div>

        <p className="whitespace-pre-line text-sm leading-6 text-foreground">{enquiry.message}</p>

        {received && (enquiry.contactEmail || enquiry.contactPhone) && (
          <div className="flex flex-wrap gap-4 text-xs text-muted-foreground">
            {enquiry.contactEmail && (
              <span className="flex items-center gap-1">
                <Mail className="h-3.5 w-3.5" />
                {enquiry.contactEmail}
              </span>
            )}
            {enquiry.contactPhone && (
              <span className="flex items-center gap-1">
                <Phone className="h-3.5 w-3.5" />
                {enquiry.contactPhone}
              </span>
            )}
          </div>
        )}

        {received && (
          <div className="flex gap-2 pt-1">
            <StatusButton id={enquiry.id} status="replied" label="Mark replied" />
            <StatusButton id={enquiry.id} status="closed" label="Close" />
          </div>
        )}
      </CardContent>
    </Card>
  )
}

function EmptyState({ label }: { label: string }) {
  return (
    <div className="rounded-lg border border-border bg-secondary/50 p-8 text-center">
      <div className="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-lg border border-accent/40 bg-accent/10">
        <MessageSquare className="h-6 w-6 text-primary" />
      </div>
      <p className="font-sora text-lg font-semibold text-foreground">{label}</p>
    </div>
  )
}

export function EnquiryInbox({ inbox }: { inbox: Inbox }) {
  return (
    <Tabs defaultValue="received">
      <TabsList className="mb-4">
        <TabsTrigger value="received">Received ({inbox.received.length})</TabsTrigger>
        <TabsTrigger value="sent">Sent ({inbox.sent.length})</TabsTrigger>
      </TabsList>
      <TabsContent value="received" className="space-y-3">
        {inbox.received.length === 0 ? (
          <EmptyState label="No enquiries received yet" />
        ) : (
          inbox.received.map((e) => <EnquiryCard key={e.id} enquiry={e} />)
        )}
      </TabsContent>
      <TabsContent value="sent" className="space-y-3">
        {inbox.sent.length === 0 ? (
          <EmptyState label="You haven't sent any enquiries yet" />
        ) : (
          inbox.sent.map((e) => <EnquiryCard key={e.id} enquiry={e} />)
        )}
      </TabsContent>
    </Tabs>
  )
}
