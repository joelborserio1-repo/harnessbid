"use client"

import { useActionState } from "react"
import { useFormStatus } from "react-dom"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { createSaleEventAction, type AdminActionState } from "@/lib/admin/actions"

const initialState: AdminActionState = {}
const EVENT_TYPES = ["online_auction", "timed_auction", "live_sale", "private_sale", "clearance_sale"]

function SubmitButton() {
  const { pending } = useFormStatus()
  return (
    <Button type="submit" disabled={pending} className="bg-accent text-accent-foreground hover:bg-accent/90">
      {pending ? "Creating…" : "Create event"}
    </Button>
  )
}

export function CreateSaleEventForm() {
  const [state, formAction] = useActionState(createSaleEventAction, initialState)

  return (
    <form action={formAction} className="flex flex-col gap-3 sm:flex-row sm:items-end">
      <div className="flex-1 space-y-2">
        <Label htmlFor="name">Event name</Label>
        <Input id="name" name="name" placeholder="e.g. APG Yearling Sale 2026" required />
      </div>
      <div className="space-y-2">
        <Label htmlFor="eventType">Type</Label>
        <select
          id="eventType"
          name="eventType"
          defaultValue="online_auction"
          className="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm sm:w-44"
        >
          {EVENT_TYPES.map((t) => (
            <option key={t} value={t}>
              {t.replace(/_/g, " ")}
            </option>
          ))}
        </select>
      </div>
      <SubmitButton />
      {state.error && <p className="text-sm text-destructive">{state.error}</p>}
      {state.success && <p className="text-sm text-muted-foreground">{state.message}</p>}
    </form>
  )
}
