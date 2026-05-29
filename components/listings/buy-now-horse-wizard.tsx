"use client"

import { useRef, useState } from "react"
import { useActionState } from "react"
import { EquipmentListingDetail } from "@/components/listing-detail"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { Textarea } from "@/components/ui/textarea"
import { ImageUploader } from "@/components/listings/image-uploader"
import { WizardFrame, StepNav, SubmitBar } from "@/components/listings/wizard"
import { createBuyNowHorseAction, type ActionState } from "@/lib/listings/actions"
import type { MarketplaceDetail } from "@/lib/supabase/queries"
import {
  HORSE_GAITS,
  HORSE_SEXES,
  buildBuyNowHorsePreview,
  type SellerPreviewInfo,
} from "@/components/listings/preview-helpers"

const STEPS = ["Horse details", "Breeding", "Pricing & media", "Preview"]
const initialState: ActionState = {}

export function BuyNowHorseWizard({ seller }: { seller: SellerPreviewInfo }) {
  const formRef = useRef<HTMLFormElement>(null)
  const [step, setStep] = useState(0)
  const [clientError, setClientError] = useState<string | null>(null)
  const [preview, setPreview] = useState<MarketplaceDetail | null>(null)
  const [state, formAction] = useActionState(createBuyNowHorseAction, initialState)

  const data = () => new FormData(formRef.current!)

  function validate(current: number): string | null {
    const fd = data()
    if (current === 0 && !String(fd.get("title") ?? "").trim()) return "Enter the horse name."
    if (current === 2) {
      const price = Number(fd.get("price"))
      if (!price || price <= 0) return "Enter a valid fixed price."
    }
    return null
  }

  function next() {
    const err = validate(step)
    if (err) {
      setClientError(err)
      return
    }
    setClientError(null)
    if (step === 2) setPreview(buildBuyNowHorsePreview(data(), seller))
    setStep((s) => Math.min(s + 1, STEPS.length - 1))
  }

  function back() {
    setClientError(null)
    setStep((s) => Math.max(s - 1, 0))
  }

  return (
    <WizardFrame
      eyebrow="Sell · Buy now horse"
      title="Create a buy now horse listing"
      intro="List a Standardbred at a fixed price with pedigree and photos. Save a draft anytime or submit for review."
      stepTitles={STEPS}
      currentStep={step}
    >
      <form ref={formRef} action={formAction} className="space-y-6">
        {/* Step 0 */}
        <div className={step === 0 ? "space-y-5" : "hidden"}>
          <div className="space-y-2">
            <Label htmlFor="title">Horse name</Label>
            <Input id="title" name="title" required />
          </div>
          <div className="space-y-2">
            <Label htmlFor="shortDescription">Short tagline</Label>
            <Input id="shortDescription" name="shortDescription" />
          </div>
          <div className="space-y-2">
            <Label htmlFor="description">Description</Label>
            <Textarea id="description" name="description" className="min-h-28" />
          </div>
          <div className="grid gap-5 sm:grid-cols-2">
            <div className="space-y-2">
              <Label htmlFor="ageYears">Age (years)</Label>
              <Input id="ageYears" name="ageYears" inputMode="numeric" />
            </div>
            <div className="space-y-2">
              <Label htmlFor="color">Color</Label>
              <Input id="color" name="color" />
            </div>
          </div>
          <div className="space-y-2">
            <Label htmlFor="location">Location</Label>
            <Input id="location" name="location" placeholder="City, region or country" />
          </div>
        </div>

        {/* Step 1 */}
        <div className={step === 1 ? "space-y-5" : "hidden"}>
          <div className="grid gap-5 sm:grid-cols-2">
            <div className="space-y-2">
              <Label htmlFor="sex">Sex</Label>
              <select id="sex" name="sex" defaultValue="unknown" className="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm">
                {HORSE_SEXES.map((s) => (
                  <option key={s} value={s}>{s}</option>
                ))}
              </select>
            </div>
            <div className="space-y-2">
              <Label htmlFor="gait">Gait</Label>
              <select id="gait" name="gait" defaultValue="unknown" className="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm">
                {HORSE_GAITS.map((g) => (
                  <option key={g} value={g}>{g}</option>
                ))}
              </select>
            </div>
          </div>
          <div className="space-y-2">
            <Label htmlFor="breed">Breed</Label>
            <Input id="breed" name="breed" defaultValue="Standardbred" />
          </div>
          <div className="grid gap-5 sm:grid-cols-2">
            <div className="space-y-2">
              <Label htmlFor="sire">Sire</Label>
              <Input id="sire" name="sire" />
            </div>
            <div className="space-y-2">
              <Label htmlFor="dam">Dam</Label>
              <Input id="dam" name="dam" />
            </div>
            <div className="space-y-2">
              <Label htmlFor="damSire">Dam sire</Label>
              <Input id="damSire" name="damSire" />
            </div>
            <div className="space-y-2">
              <Label htmlFor="bestMile">Best mile</Label>
              <Input id="bestMile" name="bestMile" />
            </div>
          </div>
        </div>

        {/* Step 2 */}
        <div className={step === 2 ? "space-y-5" : "hidden"}>
          <div className="space-y-2">
            <Label htmlFor="price">Fixed price (USD)</Label>
            <Input id="price" name="price" inputMode="numeric" required />
          </div>
          <div className="space-y-2">
            <Label htmlFor="videoUrl">Video URL (placeholder)</Label>
            <Input id="videoUrl" name="videoUrl" placeholder="https:// (optional)" />
          </div>
          <div className="space-y-2">
            <Label htmlFor="sellerNotes">Seller notes</Label>
            <Textarea id="sellerNotes" name="sellerNotes" className="min-h-20" />
          </div>
          <div className="space-y-2">
            <Label>Photos</Label>
            <ImageUploader />
          </div>
        </div>

        {/* Step 3 preview */}
        <div className={step === 3 ? "space-y-5" : "hidden"}>
          <div className="rounded-lg border border-border bg-secondary/40 px-4 py-3 text-sm text-muted-foreground">
            This is how your listing will appear publicly once approved.
          </div>
          <div className="overflow-hidden rounded-lg border border-border">
            {preview && <EquipmentListingDetail listing={preview} />}
          </div>
        </div>

        {(clientError || state.error) && (
          <p className="rounded-md border border-destructive/30 bg-destructive/10 px-3 py-2 text-sm text-destructive">
            {clientError ?? state.error}
          </p>
        )}

        {step < STEPS.length - 1 ? (
          <StepNav step={step} onBack={back} onNext={next} />
        ) : (
          <SubmitBar onBack={back} />
        )}
      </form>
    </WizardFrame>
  )
}
