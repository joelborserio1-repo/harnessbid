"use client"

import { useActionState } from "react"
import { useFormStatus } from "react-dom"
import { Trash2 } from "lucide-react"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { Textarea } from "@/components/ui/textarea"
import {
  deleteListingAction,
  updateListingAction,
  type ActionState,
} from "@/lib/listings/actions"
import type { OwnedListingDetail } from "@/lib/listings/queries"

const initialState: ActionState = {}

// Direct publish stays admin-gated; sellers move between these states only.
const STATUS_OPTIONS = ["draft", "pending_review", "paused", "archived"] as const

function SaveButton() {
  const { pending } = useFormStatus()
  return (
    <Button
      type="submit"
      disabled={pending}
      className="bg-accent text-accent-foreground hover:bg-accent/90"
    >
      {pending ? "Saving…" : "Save changes"}
    </Button>
  )
}

export function ListingEditForm({ listing }: { listing: OwnedListingDetail }) {
  const [state, formAction] = useActionState(updateListingAction, initialState)
  const statuses = STATUS_OPTIONS.includes(listing.status as (typeof STATUS_OPTIONS)[number])
    ? STATUS_OPTIONS
    : ([listing.status, ...STATUS_OPTIONS] as readonly string[])

  return (
    <div className="space-y-6">
      <form action={formAction} className="space-y-5">
        <input type="hidden" name="id" value={listing.id} />
        <input type="hidden" name="kind" value={listing.kind} />

        <div className="space-y-2">
          <Label htmlFor="title">Title</Label>
          <Input id="title" name="title" defaultValue={listing.title} required />
        </div>

        <div className="space-y-2">
          <Label htmlFor="description">Description</Label>
          <Textarea
            id="description"
            name="description"
            defaultValue={listing.description ?? ""}
            className="min-h-28"
          />
        </div>

        <div className="grid gap-5 sm:grid-cols-2">
          <div className="space-y-2">
            <Label htmlFor="price">{listing.kind === "horse" ? "Asking price (USD)" : "Price (USD)"}</Label>
            <Input
              id="price"
              name="price"
              inputMode="numeric"
              defaultValue={listing.price ?? ""}
            />
          </div>
          <div className="space-y-2">
            <Label htmlFor="status">Status</Label>
            <select
              id="status"
              name="status"
              defaultValue={listing.status}
              className="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
            >
              {statuses.map((s) => (
                <option key={s} value={s}>
                  {s.replace(/_/g, " ")}
                </option>
              ))}
            </select>
          </div>
        </div>

        {state.error && (
          <p className="rounded-md border border-destructive/30 bg-destructive/10 px-3 py-2 text-sm text-destructive">
            {state.error}
          </p>
        )}

        <SaveButton />
      </form>

      <form action={deleteListingAction} className="border-t border-border pt-5">
        <input type="hidden" name="id" value={listing.id} />
        <input type="hidden" name="kind" value={listing.kind} />
        <Button type="submit" variant="ghost" className="text-destructive">
          <Trash2 className="mr-2 h-4 w-4" />
          Delete listing
        </Button>
      </form>
    </div>
  )
}
