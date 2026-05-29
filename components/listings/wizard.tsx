"use client"

import { ArrowLeft, ArrowRight, Check } from "lucide-react"
import { useFormStatus } from "react-dom"
import { Button } from "@/components/ui/button"
import { Card, CardContent } from "@/components/ui/card"

export function WizardFrame({
  eyebrow,
  title,
  intro,
  stepTitles,
  currentStep,
  children,
}: {
  eyebrow: string
  title: string
  intro: string
  stepTitles: string[]
  currentStep: number
  children: React.ReactNode
}) {
  return (
    <>
      <section className="bg-primary py-10 sm:py-12">
        <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
          <p className="text-sm font-semibold uppercase tracking-wider text-accent">{eyebrow}</p>
          <h1 className="mt-3 font-sora text-3xl font-semibold tracking-wide text-primary-foreground sm:text-4xl">
            {title}
          </h1>
          <p className="mt-4 max-w-2xl text-primary-foreground/75">{intro}</p>

          <ol className="mt-6 flex flex-wrap gap-3">
            {stepTitles.map((stepTitle, index) => {
              const stateLabel =
                index < currentStep ? "done" : index === currentStep ? "current" : "upcoming"
              return (
                <li key={stepTitle} className="flex items-center gap-2">
                  <span
                    className={
                      "flex h-7 w-7 items-center justify-center rounded-full text-xs font-semibold " +
                      (stateLabel === "done"
                        ? "bg-accent text-accent-foreground"
                        : stateLabel === "current"
                          ? "bg-primary-foreground text-primary"
                          : "bg-primary-foreground/15 text-primary-foreground/70")
                    }
                  >
                    {index < currentStep ? <Check className="h-4 w-4" /> : index + 1}
                  </span>
                  <span
                    className={
                      "text-sm " +
                      (stateLabel === "upcoming"
                        ? "text-primary-foreground/60"
                        : "text-primary-foreground")
                    }
                  >
                    {stepTitle}
                  </span>
                </li>
              )
            })}
          </ol>
        </div>
      </section>

      <section className="mx-auto max-w-3xl px-4 py-10 sm:px-6 lg:px-8 lg:py-14">
        <Card className="border-border/70 bg-card shadow-sm">
          <CardContent className="p-6 sm:p-8">{children}</CardContent>
        </Card>
      </section>
    </>
  )
}

export function StepNav({
  step,
  canContinue = true,
  onBack,
  onNext,
}: {
  step: number
  canContinue?: boolean
  onBack: () => void
  onNext: () => void
}) {
  return (
    <div className="flex items-center justify-between gap-3 pt-2">
      {step > 0 ? (
        <Button type="button" variant="outline" onClick={onBack}>
          <ArrowLeft className="mr-2 h-4 w-4" />
          Back
        </Button>
      ) : (
        <span />
      )}
      <Button
        type="button"
        onClick={onNext}
        disabled={!canContinue}
        className="bg-accent text-accent-foreground hover:bg-accent/90"
      >
        Continue
        <ArrowRight className="ml-2 h-4 w-4" />
      </Button>
    </div>
  )
}

export function SubmitBar({ onBack }: { onBack: () => void }) {
  const { pending } = useFormStatus()
  return (
    <div className="flex flex-col gap-3 pt-2 sm:flex-row sm:items-center sm:justify-between">
      <Button type="button" variant="outline" onClick={onBack} disabled={pending}>
        <ArrowLeft className="mr-2 h-4 w-4" />
        Back
      </Button>
      <div className="flex flex-col gap-2 sm:flex-row">
        <Button type="submit" name="intent" value="draft" variant="outline" disabled={pending}>
          {pending ? "Saving…" : "Save as draft"}
        </Button>
        <Button
          type="submit"
          name="intent"
          value="publish"
          disabled={pending}
          className="bg-accent text-accent-foreground hover:bg-accent/90"
        >
          {pending ? "Submitting…" : "Submit for review"}
        </Button>
      </div>
    </div>
  )
}
