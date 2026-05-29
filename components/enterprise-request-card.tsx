"use client"

import { useActionState } from "react"
import { useFormStatus } from "react-dom"
import { Building2 } from "lucide-react"
import { Button } from "@/components/ui/button"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { requestEnterpriseAction, type ActionState } from "@/lib/auth/actions"

const initialState: ActionState = {}

function SubmitButton({ requested }: { requested: boolean }) {
  const { pending } = useFormStatus()
  return (
    <Button
      type="submit"
      variant="outline"
      disabled={pending || requested}
      className="w-full justify-start"
    >
      <Building2 className="mr-2 h-4 w-4" />
      {requested ? "Enterprise request submitted" : pending ? "Submitting…" : "Request enterprise access"}
    </Button>
  )
}

export function EnterpriseRequestCard({ requested }: { requested: boolean }) {
  const [state, formAction] = useActionState(requestEnterpriseAction, initialState)
  const alreadyRequested = requested || Boolean(state.success)

  return (
    <Card>
      <CardHeader>
        <CardTitle className="font-sora text-lg">Enterprise selling</CardTitle>
      </CardHeader>
      <CardContent className="space-y-3">
        <p className="text-sm text-muted-foreground">
          Sale companies, studs, and high-volume vendors can request managed enterprise onboarding,
          branded storefronts, and sale events.
        </p>
        <form action={formAction}>
          <SubmitButton requested={alreadyRequested} />
        </form>
        {state.error && <p className="text-sm text-destructive">{state.error}</p>}
        {alreadyRequested && (
          <p className="text-sm text-muted-foreground">
            {state.message ?? "Our team will be in touch to begin onboarding."}
          </p>
        )}
      </CardContent>
    </Card>
  )
}
