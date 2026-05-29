"use client"

import { useActionState, useMemo, useState } from "react"
import { useFormStatus } from "react-dom"
import { useSearchParams } from "next/navigation"
import Link from "next/link"
import { BookmarkPlus, CheckCircle2 } from "lucide-react"
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
import { useAuthUser } from "@/hooks/use-auth-user"
import { createSavedSearchAction, type SavedSearchActionState } from "@/lib/saved-searches/actions"

const initialState: SavedSearchActionState = {}

function SubmitButton() {
  const { pending } = useFormStatus()
  return (
    <Button
      type="submit"
      disabled={pending}
      className="w-full bg-accent text-accent-foreground hover:bg-accent/90"
    >
      {pending ? "Saving…" : "Save search"}
    </Button>
  )
}

export function SaveSearchButton({
  searchType,
  label = "Save search",
}: {
  searchType: "horse_auction" | "buy_now_horse" | "marketplace"
  label?: string
}) {
  const { isAuthenticated } = useAuthUser()
  const params = useSearchParams()
  const [state, formAction] = useActionState(createSavedSearchAction, initialState)
  const [open, setOpen] = useState(false)

  const filters = useMemo(() => {
    const obj: Record<string, string> = {}
    params.forEach((value, key) => {
      if (value) obj[key] = value
    })
    return obj
  }, [params])

  const defaultName = useMemo(() => {
    const filterBits = Object.values(filters).slice(0, 2).join(" · ")
    const base = searchType === "marketplace" ? "Marketplace search" : "Horse search"
    return filterBits ? `${base}: ${filterBits}` : base
  }, [filters, searchType])

  if (!isAuthenticated) {
    return (
      <Button asChild variant="outline" size="sm">
        <Link href="/login?redirect=/marketplace">
          <BookmarkPlus className="mr-2 h-4 w-4" />
          Log in to save searches
        </Link>
      </Button>
    )
  }

  return (
    <Dialog open={open} onOpenChange={setOpen}>
      <DialogTrigger asChild>
        <Button variant="outline" size="sm">
          <BookmarkPlus className="mr-2 h-4 w-4" />
          {label}
        </Button>
      </DialogTrigger>
      <DialogContent className="sm:max-w-md">
        <DialogHeader>
          <DialogTitle className="font-sora">Save this search</DialogTitle>
          <DialogDescription>
            Save your current filters to revisit later from your dashboard.
          </DialogDescription>
        </DialogHeader>

        {state.success ? (
          <div className="flex flex-col items-center gap-3 py-6 text-center">
            <CheckCircle2 className="h-10 w-10 text-accent" />
            <p className="font-medium text-foreground">{state.message ?? "Search saved."}</p>
            <Button variant="outline" onClick={() => setOpen(false)}>
              Close
            </Button>
          </div>
        ) : (
          <form action={formAction} className="space-y-4">
            <input type="hidden" name="searchType" value={searchType} />
            <input type="hidden" name="filters" value={JSON.stringify(filters)} />
            <div className="space-y-2">
              <Label htmlFor="saved-search-name">Search name</Label>
              <Input id="saved-search-name" name="name" defaultValue={defaultName} required />
            </div>
            {Object.keys(filters).length > 0 && (
              <p className="text-xs text-muted-foreground">
                Filters: {Object.entries(filters).map(([k, v]) => `${k}=${v}`).join(", ")}
              </p>
            )}
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
