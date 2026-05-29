"use client"

import { useActionState, useState } from "react"
import { useFormStatus } from "react-dom"
import Link from "next/link"
import { CheckCircle2, MessageSquare } from "lucide-react"
import { Button } from "@/components/ui/button"
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from "@/components/ui/dialog"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { Textarea } from "@/components/ui/textarea"
import { createEnquiryAction, type ActionState } from "@/lib/enquiries/actions"

export type EnquiryTarget = { id: string; kind: "horse" | "marketplace"; label: string }

const initialState: ActionState = {}

function SubmitButton() {
  const { pending } = useFormStatus()
  return (
    <Button
      type="submit"
      disabled={pending}
      className="w-full bg-accent text-accent-foreground hover:bg-accent/90"
    >
      {pending ? "Sending…" : "Send enquiry"}
    </Button>
  )
}

export function EnquiryDialog({
  targets,
  triggerLabel = "Contact seller",
  triggerClassName = "bg-accent text-accent-foreground hover:bg-accent/90",
  triggerVariant,
  isOwner = false,
}: {
  targets: EnquiryTarget[]
  triggerLabel?: string
  triggerClassName?: string
  triggerVariant?: "outline" | "ghost" | "secondary" | "default"
  isOwner?: boolean
}) {
  const [state, formAction] = useActionState(createEnquiryAction, initialState)
  const [open, setOpen] = useState(false)
  const [selected, setSelected] = useState(targets[0])

  const disabled = targets.length === 0 || isOwner

  return (
    <Dialog open={open} onOpenChange={setOpen}>
      <DialogTrigger asChild>
        <Button
          type="button"
          variant={triggerVariant}
          className={triggerVariant ? undefined : triggerClassName}
          disabled={disabled}
        >
          <MessageSquare className="mr-2 h-4 w-4" />
          {isOwner ? "Your listing" : triggerLabel}
        </Button>
      </DialogTrigger>
      <DialogContent className="sm:max-w-md">
        <DialogHeader>
          <DialogTitle className="font-sora">Contact seller</DialogTitle>
          <DialogDescription>
            Send a message about this listing. The seller will see your enquiry in their dashboard.
          </DialogDescription>
        </DialogHeader>

        {state.success ? (
          <div className="flex flex-col items-center gap-3 py-6 text-center">
            <CheckCircle2 className="h-10 w-10 text-accent" />
            <p className="font-medium text-foreground">{state.message ?? "Enquiry sent."}</p>
            <Button variant="outline" onClick={() => setOpen(false)}>
              Close
            </Button>
          </div>
        ) : (
          <form action={formAction} className="space-y-4">
            {targets.length > 1 ? (
              <div className="space-y-2">
                <Label htmlFor="target">Listing</Label>
                <select
                  id="target"
                  value={`${selected.kind}:${selected.id}`}
                  onChange={(e) => {
                    const [kind, id] = e.target.value.split(":")
                    setSelected({ kind: kind as "horse" | "marketplace", id, label: "" })
                  }}
                  className="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                >
                  {targets.map((t) => (
                    <option key={`${t.kind}:${t.id}`} value={`${t.kind}:${t.id}`}>
                      {t.label}
                    </option>
                  ))}
                </select>
              </div>
            ) : null}

            <input type="hidden" name="listingId" value={selected?.id ?? ""} />
            <input type="hidden" name="kind" value={selected?.kind ?? ""} />

            <div className="space-y-2">
              <Label htmlFor="message">Message</Label>
              <Textarea
                id="message"
                name="message"
                required
                minLength={10}
                className="min-h-28"
                placeholder="Ask about availability, shipping, pedigree, condition…"
              />
            </div>

            <div className="grid gap-4 sm:grid-cols-2">
              <div className="space-y-2">
                <Label htmlFor="contactEmail">Email (optional)</Label>
                <Input id="contactEmail" name="contactEmail" type="email" />
              </div>
              <div className="space-y-2">
                <Label htmlFor="contactPhone">Phone (optional)</Label>
                <Input id="contactPhone" name="contactPhone" />
              </div>
            </div>

            <div className="space-y-2">
              <Label htmlFor="contactPreference">Preferred contact method</Label>
              <select
                id="contactPreference"
                name="contactPreference"
                defaultValue="either"
                className="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
              >
                <option value="either">Either</option>
                <option value="email">Email</option>
                <option value="phone">Phone</option>
              </select>
            </div>

            {state.error && (
              <div className="space-y-2">
                <p className="rounded-md border border-destructive/30 bg-destructive/10 px-3 py-2 text-sm text-destructive">
                  {state.error}
                </p>
                {state.error.toLowerCase().includes("sign in") && (
                  <Link
                    href="/login"
                    className="text-sm font-medium text-primary hover:underline"
                  >
                    Go to login
                  </Link>
                )}
              </div>
            )}

            <SubmitButton />
          </form>
        )}
      </DialogContent>
    </Dialog>
  )
}
