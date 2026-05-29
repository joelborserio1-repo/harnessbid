"use client"

import { useMemo, useState } from "react"
import { useActionState } from "react"
import { useFormStatus } from "react-dom"
import {
  ArrowLeft,
  ArrowRight,
  Building2,
  Check,
  ImageIcon,
  ShieldCheck,
  User,
} from "lucide-react"
import { Button } from "@/components/ui/button"
import { Card, CardContent } from "@/components/ui/card"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { Textarea } from "@/components/ui/textarea"
import { Badge } from "@/components/ui/badge"
import { completeOnboardingAction, type ActionState } from "@/lib/auth/actions"
import { isValidSlug, slugify } from "@/lib/seller/slug"

type AccountType = "individual" | "enterprise"

const EMAIL_PATTERN = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
const initialState: ActionState = {}
const STEP_TITLES = ["Account type", "Profile details", "Review & submit"]

function SubmitButton() {
  const { pending } = useFormStatus()
  return (
    <Button
      type="submit"
      disabled={pending}
      className="bg-accent text-accent-foreground hover:bg-accent/90"
    >
      {pending ? "Submitting…" : "Complete onboarding"}
    </Button>
  )
}

function PlaceholderUpload({ label, hint }: { label: string; hint: string }) {
  return (
    <div className="flex items-center gap-3 rounded-lg border border-dashed border-border bg-secondary/40 p-4">
      <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-md border border-accent/40 bg-accent/10">
        <ImageIcon className="h-5 w-5 text-primary" />
      </div>
      <div>
        <p className="text-sm font-medium text-foreground">{label}</p>
        <p className="text-xs text-muted-foreground">{hint}</p>
      </div>
      <Badge variant="secondary" className="ml-auto">
        Coming soon
      </Badge>
    </div>
  )
}

export function OnboardingWizard({ defaultName }: { defaultName?: string }) {
  const [state, formAction] = useActionState(completeOnboardingAction, initialState)

  const [step, setStep] = useState(0)
  const [accountType, setAccountType] = useState<AccountType>("individual")
  const [name, setName] = useState(defaultName ?? "")
  const [slug, setSlug] = useState(defaultName ? slugify(defaultName) : "")
  const [slugTouched, setSlugTouched] = useState(false)
  const [location, setLocation] = useState("")
  const [bio, setBio] = useState("")
  const [contactEmail, setContactEmail] = useState("")
  const [contactPhone, setContactPhone] = useState("")
  const [website, setWebsite] = useState("")
  const [abn, setAbn] = useState("")
  const [clientError, setClientError] = useState<string | null>(null)

  const isEnterprise = accountType === "enterprise"
  const nameLabel = isEnterprise ? "Company name" : "Seller name"

  function onNameChange(value: string) {
    setName(value)
    if (!slugTouched) setSlug(slugify(value))
  }

  function validateDetails(): string | null {
    if (!name.trim()) return isEnterprise ? "Enter your company name." : "Enter your seller name."
    if (slug && !isValidSlug(slug)) {
      return "Username must be 3–48 characters: lowercase letters, numbers, and hyphens."
    }
    if (!location.trim()) return "Enter your location or region."
    if (contactEmail && !EMAIL_PATTERN.test(contactEmail)) {
      return "Enter a valid contact email address."
    }
    if (isEnterprise) {
      if (!contactEmail.trim()) return "Enter a business email address."
      if (!contactPhone.trim()) return "Enter a contact phone number."
    }
    return null
  }

  function goNext() {
    setClientError(null)
    if (step === 1) {
      const err = validateDetails()
      if (err) {
        setClientError(err)
        return
      }
    }
    setStep((s) => Math.min(s + 1, 2))
  }

  function goBack() {
    setClientError(null)
    setStep((s) => Math.max(s - 1, 0))
  }

  const reviewRows = useMemo(() => {
    const rows: Array<[string, string]> = [
      ["Account type", isEnterprise ? "Enterprise seller (approval required)" : "Individual seller"],
      [nameLabel, name || "—"],
      ["Storefront URL", `/seller/${slug || slugify(name) || "your-name"}`],
      ["Location", location || "—"],
    ]
    if (isEnterprise) {
      rows.push(
        ["Business email", contactEmail || "—"],
        ["Contact phone", contactPhone || "—"],
        ["Website", website || "Not provided"],
        ["Company identifier", abn || "Not provided"],
      )
    }
    return rows
  }, [isEnterprise, nameLabel, name, slug, location, contactEmail, contactPhone, website, abn])

  return (
    <>
      <section className="bg-primary py-10 sm:py-12">
        <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
          <p className="text-sm font-semibold uppercase tracking-wider text-accent">Seller onboarding</p>
          <h1 className="mt-3 font-sora text-3xl font-semibold tracking-wide text-primary-foreground sm:text-4xl">
            Set up your HarnessBid seller account
          </h1>
          <p className="mt-4 max-w-2xl text-primary-foreground/75">
            Complete these steps to unlock your seller dashboard. Listing creation opens after your
            account is set up and verified.
          </p>

          <ol className="mt-6 flex flex-wrap gap-3">
            {STEP_TITLES.map((title, index) => {
              const stateLabel = index < step ? "done" : index === step ? "current" : "upcoming"
              return (
                <li key={title} className="flex items-center gap-2">
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
                    {index < step ? <Check className="h-4 w-4" /> : index + 1}
                  </span>
                  <span
                    className={
                      "text-sm " +
                      (stateLabel === "upcoming"
                        ? "text-primary-foreground/60"
                        : "text-primary-foreground")
                    }
                  >
                    {title}
                  </span>
                </li>
              )
            })}
          </ol>
        </div>
      </section>

      <section className="mx-auto max-w-3xl px-4 py-10 sm:px-6 lg:px-8 lg:py-14">
        <Card className="border-border/70 bg-card shadow-sm">
          <CardContent className="p-6 sm:p-8">
            <form action={formAction} className="space-y-6">
              <input type="hidden" name="accountType" value={accountType} />

              {/* Step 1: account type */}
              <div className={step === 0 ? "space-y-4" : "hidden"}>
                <h2 className="font-sora text-xl font-semibold text-foreground">
                  Choose your account type
                </h2>
                <p className="text-sm text-muted-foreground">
                  You can request an enterprise upgrade later from your dashboard.
                </p>
                <div className="grid gap-4 sm:grid-cols-2">
                  {(
                    [
                      {
                        type: "individual" as const,
                        icon: User,
                        title: "Individual seller",
                        body: "Sell horses and equipment under your own name.",
                      },
                      {
                        type: "enterprise" as const,
                        icon: Building2,
                        title: "Enterprise seller",
                        body: "Branded storefront and sale events for businesses. Approval required.",
                      },
                    ]
                  ).map(({ type, icon: Icon, title, body }) => {
                    const selected = accountType === type
                    return (
                      <button
                        type="button"
                        key={type}
                        onClick={() => setAccountType(type)}
                        className={
                          "rounded-lg border p-5 text-left transition-colors " +
                          (selected
                            ? "border-accent bg-accent/10 ring-1 ring-accent"
                            : "border-border bg-card hover:border-accent/50")
                        }
                        aria-pressed={selected}
                      >
                        <Icon className="mb-3 h-6 w-6 text-primary" />
                        <p className="font-sora font-semibold text-foreground">{title}</p>
                        <p className="mt-1 text-sm leading-6 text-muted-foreground">{body}</p>
                      </button>
                    )
                  })}
                </div>
              </div>

              {/* Step 2: details */}
              <div className={step === 1 ? "space-y-5" : "hidden"}>
                <h2 className="font-sora text-xl font-semibold text-foreground">
                  {isEnterprise ? "Company details" : "Profile details"}
                </h2>

                <div className="space-y-2">
                  <Label htmlFor="name">{nameLabel}</Label>
                  <Input
                    id="name"
                    name="name"
                    value={name}
                    onChange={(e) => onNameChange(e.target.value)}
                    required
                  />
                </div>

                <div className="space-y-2">
                  <Label htmlFor="slug">{isEnterprise ? "Company username" : "Username"}</Label>
                  <div className="flex items-center gap-2">
                    <span className="text-sm text-muted-foreground">/seller/</span>
                    <Input
                      id="slug"
                      name="slug"
                      value={slug}
                      onChange={(e) => {
                        setSlugTouched(true)
                        setSlug(slugify(e.target.value))
                      }}
                      placeholder="your-name"
                    />
                  </div>
                  <p className="text-xs text-muted-foreground">
                    Lowercase letters, numbers, and hyphens. Must be unique.
                  </p>
                </div>

                <div className="space-y-2">
                  <Label htmlFor="location">Location / region</Label>
                  <Input
                    id="location"
                    name="location"
                    value={location}
                    onChange={(e) => setLocation(e.target.value)}
                    placeholder="City, region or country"
                    required
                  />
                </div>

                {isEnterprise && (
                  <div className="grid gap-5 sm:grid-cols-2">
                    <div className="space-y-2">
                      <Label htmlFor="contactEmail">Business email</Label>
                      <Input
                        id="contactEmail"
                        name="contactEmail"
                        type="email"
                        value={contactEmail}
                        onChange={(e) => setContactEmail(e.target.value)}
                        required
                      />
                    </div>
                    <div className="space-y-2">
                      <Label htmlFor="contactPhone">Contact phone</Label>
                      <Input
                        id="contactPhone"
                        name="contactPhone"
                        value={contactPhone}
                        onChange={(e) => setContactPhone(e.target.value)}
                        required
                      />
                    </div>
                    <div className="space-y-2">
                      <Label htmlFor="website">Website</Label>
                      <Input
                        id="website"
                        name="website"
                        value={website}
                        onChange={(e) => setWebsite(e.target.value)}
                        placeholder="https:// (optional)"
                      />
                    </div>
                    <div className="space-y-2">
                      <Label htmlFor="abn">ABN / company identifier</Label>
                      <Input
                        id="abn"
                        name="abn"
                        value={abn}
                        onChange={(e) => setAbn(e.target.value)}
                        placeholder="Optional"
                      />
                    </div>
                  </div>
                )}

                {!isEnterprise && (
                  <div className="space-y-2">
                    <Label htmlFor="contactEmail">Contact email</Label>
                    <Input
                      id="contactEmail"
                      name="contactEmail"
                      type="email"
                      value={contactEmail}
                      onChange={(e) => setContactEmail(e.target.value)}
                      placeholder="Optional — defaults to your account email"
                    />
                  </div>
                )}

                <div className="space-y-2">
                  <Label htmlFor="bio">{isEnterprise ? "About your business" : "About you"}</Label>
                  <Textarea
                    id="bio"
                    name="bio"
                    value={bio}
                    onChange={(e) => setBio(e.target.value)}
                    className="min-h-24"
                    placeholder="Tell buyers about your stable, business, or services."
                  />
                </div>

                <div className="space-y-3">
                  <PlaceholderUpload
                    label={isEnterprise ? "Company logo" : "Profile avatar"}
                    hint="Image uploads connect in a later phase."
                  />
                  <PlaceholderUpload
                    label="Storefront banner"
                    hint="A branded banner will appear on your public storefront."
                  />
                </div>
              </div>

              {/* Step 3: review */}
              <div className={step === 2 ? "space-y-5" : "hidden"}>
                <h2 className="font-sora text-xl font-semibold text-foreground">Review your details</h2>

                {isEnterprise && (
                  <div className="flex items-start gap-3 rounded-lg border border-accent/30 bg-accent/10 p-4">
                    <ShieldCheck className="mt-0.5 h-5 w-5 text-primary" />
                    <p className="text-sm text-foreground">
                      Enterprise accounts require approval. Your application will be submitted with an
                      <span className="font-medium"> in review</span> status. You can still access your
                      dashboard while approval is pending.
                    </p>
                  </div>
                )}

                <dl className="divide-y divide-border rounded-lg border border-border">
                  {reviewRows.map(([label, value]) => (
                    <div key={label} className="flex justify-between gap-4 px-4 py-3">
                      <dt className="text-sm text-muted-foreground">{label}</dt>
                      <dd className="text-right text-sm font-medium text-foreground">{value}</dd>
                    </div>
                  ))}
                </dl>
              </div>

              {(clientError || state.error) && (
                <p className="rounded-md border border-destructive/30 bg-destructive/10 px-3 py-2 text-sm text-destructive">
                  {clientError ?? state.error}
                </p>
              )}

              <div className="flex items-center justify-between gap-3 pt-2">
                {step > 0 ? (
                  <Button type="button" variant="outline" onClick={goBack}>
                    <ArrowLeft className="mr-2 h-4 w-4" />
                    Back
                  </Button>
                ) : (
                  <span />
                )}

                {step < 2 ? (
                  <Button
                    type="button"
                    onClick={goNext}
                    className="bg-accent text-accent-foreground hover:bg-accent/90"
                  >
                    Continue
                    <ArrowRight className="ml-2 h-4 w-4" />
                  </Button>
                ) : (
                  <SubmitButton />
                )}
              </div>
            </form>
          </CardContent>
        </Card>
      </section>
    </>
  )
}
