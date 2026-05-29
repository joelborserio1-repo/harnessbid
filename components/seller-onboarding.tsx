"use client"

import { useActionState } from "react"
import { useFormStatus } from "react-dom"
import { Store } from "lucide-react"
import { Button } from "@/components/ui/button"
import { Card, CardContent } from "@/components/ui/card"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { Textarea } from "@/components/ui/textarea"
import { createSellerAccountAction, type ActionState } from "@/lib/auth/actions"

const initialState: ActionState = {}

function SubmitButton() {
  const { pending } = useFormStatus()
  return (
    <Button
      type="submit"
      disabled={pending}
      className="bg-accent text-accent-foreground hover:bg-accent/90"
    >
      {pending ? "Creating…" : "Create seller account"}
    </Button>
  )
}

export function SellerOnboarding({ defaultName }: { defaultName?: string }) {
  const [state, formAction] = useActionState(createSellerAccountAction, initialState)

  return (
    <>
      <section className="bg-primary py-10 sm:py-12">
        <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
          <p className="text-sm font-semibold uppercase tracking-wider text-accent">Seller workspace</p>
          <h1 className="mt-3 font-sora text-3xl font-semibold tracking-wide text-primary-foreground sm:text-4xl">
            Set up your seller account
          </h1>
          <p className="mt-4 max-w-2xl text-primary-foreground/75">
            Create your HarnessBid seller profile to unlock the seller dashboard. Listing creation
            opens once your account is verified.
          </p>
        </div>
      </section>

      <section className="mx-auto max-w-3xl px-4 py-10 sm:px-6 lg:px-8 lg:py-14">
        <Card className="border-border/70 bg-card shadow-sm">
          <CardContent className="p-6 sm:p-8">
            <div className="mb-6 flex items-center gap-4">
              <div className="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg border border-accent/40 bg-accent/10">
                <Store className="h-6 w-6 text-primary" />
              </div>
              <div>
                <h2 className="font-sora text-xl font-semibold text-foreground">Seller details</h2>
                <p className="text-sm text-muted-foreground">
                  You can refine these later from your dashboard.
                </p>
              </div>
            </div>

            <form action={formAction} className="space-y-5">
              <div className="space-y-2">
                <Label htmlFor="displayName">Seller or business name</Label>
                <Input id="displayName" name="displayName" defaultValue={defaultName} required />
              </div>

              <div className="space-y-2">
                <Label htmlFor="accountType">Account type</Label>
                <select
                  id="accountType"
                  name="accountType"
                  defaultValue="individual"
                  className="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                >
                  <option value="individual">Individual</option>
                  <option value="business">Business</option>
                </select>
              </div>

              <div className="space-y-2">
                <Label htmlFor="location">Location</Label>
                <Input id="location" name="location" placeholder="City, region or country" />
              </div>

              <div className="space-y-2">
                <Label htmlFor="contactEmail">Contact email</Label>
                <Input id="contactEmail" name="contactEmail" type="email" placeholder="Optional — defaults to your account email" />
              </div>

              <div className="space-y-2">
                <Label htmlFor="bio">About your operation</Label>
                <Textarea id="bio" name="bio" className="min-h-24" placeholder="Tell buyers about your stable, business, or services." />
              </div>

              {state.error && (
                <p className="rounded-md border border-destructive/30 bg-destructive/10 px-3 py-2 text-sm text-destructive">
                  {state.error}
                </p>
              )}

              <div className="flex flex-col gap-3 sm:flex-row">
                <SubmitButton />
              </div>

              <p className="text-xs text-muted-foreground">
                New seller accounts start unverified. Listing creation, bidding, and payouts are
                enabled in later phases.
              </p>
            </form>
          </CardContent>
        </Card>
      </section>
    </>
  )
}
