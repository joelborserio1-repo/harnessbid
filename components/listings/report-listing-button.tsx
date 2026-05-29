"use client"

import { useActionState, useState } from "react"
import { useFormStatus } from "react-dom"
import { CheckCircle2, Flag } from "lucide-react"
import { Button } from "@/components/ui/button"
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from "@/components/ui/dialog"
import { Label } from "@/components/ui/label"
import { Textarea } from "@/components/ui/textarea"
import { createReportAction, type ReportActionState } from "@/lib/listings/report"

const initialState: ReportActionState = {}
const REASONS = ["Prohibited or fraudulent", "Misleading details", "Wrong category", "Spam", "Other"]

function SubmitButton() {
  const { pending } = useFormStatus()
  return (
    <Button type="submit" disabled={pending} variant="outline" className="w-full">
      {pending ? "Submitting…" : "Submit report"}
    </Button>
  )
}

export function ReportListingButton({
  listingId,
  kind,
}: {
  listingId: string
  kind: "horse" | "marketplace"
}) {
  const [state, formAction] = useActionState(createReportAction, initialState)
  const [open, setOpen] = useState(false)

  return (
    <Dialog open={open} onOpenChange={setOpen}>
      <DialogTrigger asChild>
        <Button type="button" variant="ghost" size="sm" className="text-muted-foreground">
          <Flag className="mr-2 h-4 w-4" />
          Report
        </Button>
      </DialogTrigger>
      <DialogContent className="sm:max-w-md">
        <DialogHeader>
          <DialogTitle className="font-sora">Report this listing</DialogTitle>
          <DialogDescription>Flag this listing for the HarnessBid moderation team.</DialogDescription>
        </DialogHeader>

        {state.success ? (
          <div className="flex flex-col items-center gap-3 py-6 text-center">
            <CheckCircle2 className="h-10 w-10 text-accent" />
            <p className="font-medium text-foreground">{state.message ?? "Report submitted."}</p>
            <Button variant="outline" onClick={() => setOpen(false)}>
              Close
            </Button>
          </div>
        ) : (
          <form action={formAction} className="space-y-4">
            <input type="hidden" name="listingId" value={listingId} />
            <input type="hidden" name="kind" value={kind} />
            <div className="space-y-2">
              <Label htmlFor="reason">Reason</Label>
              <select
                id="reason"
                name="reason"
                defaultValue=""
                className="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
              >
                <option value="" disabled>
                  Select a reason
                </option>
                {REASONS.map((r) => (
                  <option key={r} value={r}>
                    {r}
                  </option>
                ))}
              </select>
            </div>
            <div className="space-y-2">
              <Label htmlFor="details">Details (optional)</Label>
              <Textarea id="details" name="details" className="min-h-24" />
            </div>
            {state.error && (
              <p className="rounded-md border border-destructive/30 bg-destructive/10 px-3 py-2 text-sm text-destructive">
                {state.error}
              </p>
            )}
            <SubmitButton />
          </form>
        )}
      </DialogContent>
    </Dialog>
  )
}
