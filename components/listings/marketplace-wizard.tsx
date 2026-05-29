"use client"

import { useRef, useState } from "react"
import { useActionState } from "react"
import { EquipmentListingDetail } from "@/components/listing-detail"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { Textarea } from "@/components/ui/textarea"
import { ImageUploader } from "@/components/listings/image-uploader"
import { WizardFrame, StepNav, SubmitBar } from "@/components/listings/wizard"
import { createMarketplaceListingAction, type ActionState } from "@/lib/listings/actions"
import type { CategoryOption, MarketplaceDetail } from "@/lib/supabase/queries"
import {
  MARKETPLACE_CONDITIONS,
  buildMarketplacePreview,
  type SellerPreviewInfo,
} from "@/components/listings/preview-helpers"

const STEPS = ["Item details", "Pricing & shipping", "Media", "Preview"]
const initialState: ActionState = {}

export function MarketplaceWizard({
  seller,
  categories,
}: {
  seller: SellerPreviewInfo
  categories: CategoryOption[]
}) {
  const formRef = useRef<HTMLFormElement>(null)
  const [step, setStep] = useState(0)
  const [clientError, setClientError] = useState<string | null>(null)
  const [preview, setPreview] = useState<MarketplaceDetail | null>(null)
  const [state, formAction] = useActionState(createMarketplaceListingAction, initialState)

  const data = () => new FormData(formRef.current!)

  function validate(current: number): string | null {
    const fd = data()
    if (current === 0) {
      if (!String(fd.get("title") ?? "").trim()) return "Enter a listing title."
      if (!String(fd.get("categoryId") ?? "").trim()) return "Select a category."
    }
    if (current === 1) {
      const price = Number(fd.get("price"))
      if (!price || price <= 0) return "Enter a valid price."
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
    if (step === 2) {
      const fd = data()
      const cat = categories.find((c) => c.id === String(fd.get("categoryId") ?? ""))
      setPreview(buildMarketplacePreview(fd, seller, cat?.name ?? "Marketplace"))
    }
    setStep((s) => Math.min(s + 1, STEPS.length - 1))
  }

  function back() {
    setClientError(null)
    setStep((s) => Math.max(s - 1, 0))
  }

  return (
    <WizardFrame
      eyebrow="Sell · Marketplace listing"
      title="Create a marketplace listing"
      intro="List equipment, tack, vehicles, services, and more. Save a draft anytime or submit for review."
      stepTitles={STEPS}
      currentStep={step}
    >
      <form ref={formRef} action={formAction} className="space-y-6">
        {/* Step 0 */}
        <div className={step === 0 ? "space-y-5" : "hidden"}>
          <div className="space-y-2">
            <Label htmlFor="categoryId">Category</Label>
            <select
              id="categoryId"
              name="categoryId"
              defaultValue=""
              className="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
            >
              <option value="" disabled>
                {categories.length ? "Select a category" : "No categories available"}
              </option>
              {categories.map((c) => (
                <option key={c.id} value={c.id}>
                  {c.name}
                </option>
              ))}
            </select>
          </div>
          <div className="space-y-2">
            <Label htmlFor="title">Title</Label>
            <Input id="title" name="title" required />
          </div>
          <div className="space-y-2">
            <Label htmlFor="condition">Condition</Label>
            <select
              id="condition"
              name="condition"
              defaultValue="used"
              className="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
            >
              {MARKETPLACE_CONDITIONS.map((c) => (
                <option key={c} value={c}>
                  {c.replace(/_/g, " ")}
                </option>
              ))}
            </select>
          </div>
          <div className="space-y-2">
            <Label htmlFor="description">Description</Label>
            <Textarea id="description" name="description" className="min-h-28" />
          </div>
        </div>

        {/* Step 1 */}
        <div className={step === 1 ? "space-y-5" : "hidden"}>
          <div className="space-y-2">
            <Label htmlFor="price">Price (USD)</Label>
            <Input id="price" name="price" inputMode="numeric" required />
          </div>
          <div className="space-y-2">
            <Label htmlFor="location">Location</Label>
            <Input id="location" name="location" placeholder="City, region or country" />
          </div>
          <label className="flex items-center gap-3 text-sm text-foreground">
            <input type="checkbox" name="shippingAvailable" className="h-4 w-4 rounded border-input" />
            Shipping available
          </label>
          <label className="flex items-center gap-3 text-sm text-foreground">
            <input type="checkbox" name="acceptsOffers" className="h-4 w-4 rounded border-input" />
            Accept offers
          </label>
          <label className="flex items-center gap-3 text-sm text-foreground">
            <input type="checkbox" name="featured" className="h-4 w-4 rounded border-input" />
            Request featured placement (placeholder)
          </label>
          <div className="space-y-2">
            <Label htmlFor="sellerNotes">Seller notes</Label>
            <Textarea id="sellerNotes" name="sellerNotes" className="min-h-20" />
          </div>
        </div>

        {/* Step 2 */}
        <div className={step === 2 ? "space-y-5" : "hidden"}>
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
