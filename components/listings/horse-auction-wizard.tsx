"use client"

import { useRef, useState } from "react"
import { useActionState } from "react"
import { AuctionListingDetail } from "@/components/listing-detail"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { Textarea } from "@/components/ui/textarea"
import { ImageUploader } from "@/components/listings/image-uploader"
import { WizardFrame, StepNav, SubmitBar } from "@/components/listings/wizard"
import { createHorseAuctionAction } from "@/lib/listings/actions"
import type { ActionState } from "@/lib/auth/actions"
import type { HorseAuctionDetail } from "@/lib/supabase/queries"
import {
  HORSE_GAITS,
  HORSE_SEXES,
  buildHorsePreview,
  type SellerPreviewInfo,
} from "@/components/listings/preview-helpers"

const STEPS = ["Horse details", "Breeding", "Auction & media", "Preview"]
const initialState: ActionState = {}

export function HorseAuctionWizard({ seller }: { seller: SellerPreviewInfo }) {
  const formRef = useRef<HTMLFormElement>(null)
  const [step, setStep] = useState(0)
  const [clientError, setClientError] = useState<string | null>(null)
  const [preview, setPreview] = useState<HorseAuctionDetail | null>(null)
  const [state, formAction] = useActionState(createHorseAuctionAction, initialState)

  function data() {
    return new FormData(formRef.current!)
  }

  function validate(current: number): string | null {
    const fd = data()
    if (current === 0 && !String(fd.get("title") ?? "").trim()) {
      return "Enter the horse name."
    }
    if (current === 2) {
      const start = Number(fd.get("startingBid"))
      const reserve = Number(fd.get("reservePrice"))
      const end = String(fd.get("auctionEnd") ?? "")
      if (!start || start <= 0) return "Enter a valid starting bid."
      if (reserve && reserve < start) return "Reserve must be ≥ starting bid."
      const endDate = end ? new Date(end) : null
      if (!endDate || Number.isNaN(endDate.getTime())) return "Choose a valid auction end date."
      if (endDate.getTime() <= Date.now()) return "Auction end date must be in the future."
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
    if (step === 2) setPreview(buildHorsePreview(data(), seller, "auction"))
    setStep((s) => Math.min(s + 1, STEPS.length - 1))
  }

  function back() {
    setClientError(null)
    setStep((s) => Math.max(s - 1, 0))
  }

  return (
    <WizardFrame
      eyebrow="Sell · Horse auction"
      title="Create a horse auction"
      intro="List a Standardbred for auction with pedigree, reserve, and timing. Save a draft anytime or submit for review."
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
            <Input id="shortDescription" name="shortDescription" placeholder="One-line summary" />
          </div>
          <div className="space-y-2">
            <Label htmlFor="description">Description</Label>
            <Textarea id="description" name="description" className="min-h-28" />
          </div>
          <div className="grid gap-5 sm:grid-cols-2">
            <div className="space-y-2">
              <Label htmlFor="ageYears">Age (years) / year foaled</Label>
              <Input id="ageYears" name="ageYears" inputMode="numeric" placeholder="e.g. 3" />
            </div>
            <div className="space-y-2">
              <Label htmlFor="color">Color</Label>
              <Input id="color" name="color" placeholder="e.g. Bay" />
            </div>
          </div>
          <div className="space-y-2">
            <Label htmlFor="location">Location / shipping region</Label>
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
              <Input id="bestMile" name="bestMile" placeholder="e.g. 1:52.3" />
            </div>
          </div>
        </div>

        {/* Step 2 */}
        <div className={step === 2 ? "space-y-5" : "hidden"}>
          <div className="grid gap-5 sm:grid-cols-3">
            <div className="space-y-2">
              <Label htmlFor="startingBid">Starting bid (USD)</Label>
              <Input id="startingBid" name="startingBid" inputMode="numeric" required />
            </div>
            <div className="space-y-2">
              <Label htmlFor="reservePrice">Reserve (optional)</Label>
              <Input id="reservePrice" name="reservePrice" inputMode="numeric" />
            </div>
            <div className="space-y-2">
              <Label htmlFor="bidIncrement">Bid increment</Label>
              <Input id="bidIncrement" name="bidIncrement" inputMode="numeric" defaultValue="100" />
            </div>
          </div>
          <div className="space-y-2">
            <Label htmlFor="auctionEnd">Auction end date &amp; time</Label>
            <Input id="auctionEnd" name="auctionEnd" type="datetime-local" required />
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
            <Label>Photos &amp; pedigree images</Label>
            <ImageUploader />
          </div>
        </div>

        {/* Step 3 preview */}
        <div className={step === 3 ? "space-y-5" : "hidden"}>
          <div className="rounded-lg border border-border bg-secondary/40 px-4 py-3 text-sm text-muted-foreground">
            This is how your auction will appear publicly once approved.
          </div>
          <div className="overflow-hidden rounded-lg border border-border">
            {preview && <AuctionListingDetail listing={preview} />}
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
