"use client"

import { useActionState, useRef, useState } from "react"
import { useFormStatus } from "react-dom"
import { UploadCloud, Check, Loader2, ShieldCheck } from "lucide-react"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { createSupabaseBrowserClient } from "@/lib/supabase/client"
import { submitVerificationAction, type VerificationState } from "@/lib/verification/actions"

const BUCKET = "seller-id-documents"
const initialState: VerificationState = {}

function sanitize(name: string) {
  return name.replace(/[^a-zA-Z0-9._-]/g, "-").slice(-60)
}

type Side = "front" | "back"

function SubmitButton({ disabled }: { disabled: boolean }) {
  const { pending } = useFormStatus()
  return (
    <Button type="submit" disabled={pending || disabled} className="bg-accent text-accent-foreground hover:bg-accent/90">
      {pending ? <Loader2 className="mr-2 h-4 w-4 animate-spin" /> : <ShieldCheck className="mr-2 h-4 w-4" />}
      Submit for verification
    </Button>
  )
}

export function VerificationForm({ currentStatus }: { currentStatus: string }) {
  const [state, formAction] = useActionState(submitVerificationAction, initialState)
  const [docType, setDocType] = useState("drivers_license")
  const [paths, setPaths] = useState<{ front?: string; back?: string }>({})
  const [uploading, setUploading] = useState<Side | null>(null)
  const [uploadError, setUploadError] = useState<string | null>(null)
  const frontRef = useRef<HTMLInputElement>(null)
  const backRef = useRef<HTMLInputElement>(null)

  async function upload(side: Side, file: File) {
    setUploadError(null)
    setUploading(side)
    try {
      const supabase = createSupabaseBrowserClient()
      const { data: { user } } = await supabase.auth.getUser()
      if (!user) throw new Error("Please sign in.")
      const path = `${user.id}/${side}-${Date.now()}-${sanitize(file.name)}`
      const { error } = await supabase.storage.from(BUCKET).upload(path, file, { upsert: true, contentType: file.type })
      if (error) throw error
      setPaths((p) => ({ ...p, [side]: path }))
    } catch (err) {
      setUploadError(err instanceof Error ? err.message : "Upload failed")
    } finally {
      setUploading(null)
    }
  }

  if (currentStatus === "verified") {
    return (
      <div className="rounded-sm border border-accent/40 bg-accent/10 p-4 flex items-center gap-3">
        <ShieldCheck className="h-5 w-5 text-accent" />
        <div>
          <p className="font-medium text-foreground">Your seller account is verified</p>
          <p className="text-sm text-muted-foreground">You can list and sell on HarnessBid.</p>
        </div>
      </div>
    )
  }

  if (currentStatus === "pending") {
    return (
      <div className="rounded-sm border border-border bg-secondary p-4 flex items-center gap-3">
        <Loader2 className="h-5 w-5 text-accent animate-spin" />
        <div>
          <p className="font-medium text-foreground">Verification under review</p>
          <p className="text-sm text-muted-foreground">We've received your documents and will be in touch shortly.</p>
        </div>
      </div>
    )
  }

  const backRequired = docType !== "passport"
  const ready = Boolean(paths.front) && (!backRequired || Boolean(paths.back))

  return (
    <form action={formAction} className="space-y-5 rounded-sm border border-border bg-card p-5">
      <div>
        <h3 className="font-cinzel text-lg font-semibold text-foreground">Verify your seller account</h3>
        <p className="text-sm text-muted-foreground mt-1">
          Upload a government-issued ID to become a verified seller. Documents are stored securely
          and reviewed by our team — they are never shown publicly.
        </p>
        {currentStatus === "rejected" && (
          <p className="mt-2 text-sm text-destructive">Your previous submission was not approved. Please re-upload clear, valid documents.</p>
        )}
      </div>

      <input type="hidden" name="docType" value={docType} />
      <input type="hidden" name="frontPath" value={paths.front ?? ""} />
      <input type="hidden" name="backPath" value={paths.back ?? ""} />

      <div className="grid sm:grid-cols-2 gap-4">
        <div>
          <Label htmlFor="docType">Document type</Label>
          <select
            id="docType"
            value={docType}
            onChange={(e) => setDocType(e.target.value)}
            className="mt-1 w-full h-10 rounded-sm border border-input bg-background px-3 text-sm"
          >
            <option value="drivers_license">Driver&apos;s licence</option>
            <option value="passport">Passport</option>
            <option value="national_id">National ID</option>
            <option value="other">Other</option>
          </select>
        </div>
        <div>
          <Label htmlFor="fullLegalName">Full legal name (as on ID)</Label>
          <Input id="fullLegalName" name="fullLegalName" required className="mt-1" placeholder="e.g. Jordan A. Buyer" />
        </div>
      </div>

      <div className="grid sm:grid-cols-2 gap-4">
        {(["front", "back"] as Side[]).map((side) => {
          if (side === "back" && !backRequired) return null
          const done = Boolean(paths[side])
          const ref = side === "front" ? frontRef : backRef
          return (
            <div key={side}>
              <Label className="capitalize">{side} of ID</Label>
              <button
                type="button"
                onClick={() => ref.current?.click()}
                className={`mt-1 w-full flex items-center justify-center gap-2 rounded-sm border border-dashed p-6 text-sm transition-colors ${
                  done ? "border-accent bg-accent/5 text-foreground" : "border-input text-muted-foreground hover:border-accent"
                }`}
              >
                {uploading === side ? (
                  <><Loader2 className="h-4 w-4 animate-spin" /> Uploading…</>
                ) : done ? (
                  <><Check className="h-4 w-4 text-accent" /> Uploaded — replace</>
                ) : (
                  <><UploadCloud className="h-4 w-4" /> Upload {side}</>
                )}
              </button>
              <input
                ref={ref}
                type="file"
                accept="image/jpeg,image/png,image/webp,application/pdf"
                className="hidden"
                onChange={(e) => {
                  const f = e.target.files?.[0]
                  if (f) upload(side, f)
                }}
              />
            </div>
          )
        })}
      </div>

      {uploadError && <p className="text-sm text-destructive">{uploadError}</p>}
      {state.error && <p className="text-sm text-destructive">{state.error}</p>}
      {state.success && <p className="text-sm text-accent">{state.message}</p>}

      <SubmitButton disabled={!ready} />
    </form>
  )
}
