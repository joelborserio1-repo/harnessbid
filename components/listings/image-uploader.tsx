"use client"

import { useCallback, useRef, useState } from "react"
import { ImagePlus, Loader2, Star, X } from "lucide-react"
import { createSupabaseBrowserClient } from "@/lib/supabase/client"

const BUCKET = "listing-images"
const ACCEPTED = ["image/jpeg", "image/png", "image/webp", "image/avif"]
const MAX_BYTES = 10 * 1024 * 1024 // 10 MiB

type UploadItem = {
  id: string
  previewUrl: string
  status: "uploading" | "done" | "error"
  path?: string
  url?: string
  error?: string
}

const hasEnv = Boolean(
  process.env.NEXT_PUBLIC_SUPABASE_URL && process.env.NEXT_PUBLIC_SUPABASE_ANON_KEY,
)

function sanitize(name: string) {
  return name.toLowerCase().replace(/[^a-z0-9.]+/g, "-").replace(/^-+|-+$/g, "").slice(-60)
}

/**
 * Multi-image uploader backed by Supabase Storage (bucket: listing-images).
 * Uploads run client-side under the user's own uid prefix (storage RLS), and
 * the resulting {path, url} list is serialised into a hidden input consumed by
 * the listing server action. Supabase Storage has no native progress event, so
 * each tile shows an indeterminate uploading state.
 */
export function ImageUploader({
  name = "images",
  maxImages = 8,
}: {
  name?: string
  maxImages?: number
}) {
  const [items, setItems] = useState<UploadItem[]>([])
  const [notice, setNotice] = useState<string | null>(null)
  const [dragOver, setDragOver] = useState(false)
  const inputRef = useRef<HTMLInputElement>(null)

  const completed = items.filter((i) => i.status === "done" && i.path && i.url)
  const hiddenValue = JSON.stringify(completed.map((i) => ({ path: i.path, url: i.url })))

  const uploadFile = useCallback(async (file: File) => {
    const id = `${Date.now()}-${Math.random().toString(36).slice(2)}`
    const previewUrl = URL.createObjectURL(file)
    setItems((prev) => [...prev, { id, previewUrl, status: "uploading" }])

    try {
      const supabase = createSupabaseBrowserClient()
      const {
        data: { user },
      } = await supabase.auth.getUser()
      if (!user) throw new Error("Sign in required")

      const path = `${user.id}/${Date.now()}-${sanitize(file.name)}`
      const { error } = await supabase.storage.from(BUCKET).upload(path, file, {
        cacheControl: "3600",
        upsert: false,
        contentType: file.type,
      })
      if (error) throw error

      const { data } = supabase.storage.from(BUCKET).getPublicUrl(path)
      setItems((prev) =>
        prev.map((i) =>
          i.id === id ? { ...i, status: "done", path, url: data.publicUrl } : i,
        ),
      )
    } catch (err) {
      setItems((prev) =>
        prev.map((i) =>
          i.id === id
            ? { ...i, status: "error", error: err instanceof Error ? err.message : "Upload failed" }
            : i,
        ),
      )
    }
  }, [])

  const handleFiles = useCallback(
    (files: FileList | null) => {
      if (!files) return
      setNotice(null)
      const remaining = maxImages - items.length
      if (remaining <= 0) {
        setNotice(`You can upload up to ${maxImages} images.`)
        return
      }
      const selected = Array.from(files).slice(0, remaining)
      for (const file of selected) {
        if (!ACCEPTED.includes(file.type)) {
          setNotice("Only JPG, PNG, WEBP, or AVIF images are allowed.")
          continue
        }
        if (file.size > MAX_BYTES) {
          setNotice("Each image must be 10 MB or smaller.")
          continue
        }
        void uploadFile(file)
      }
    },
    [items.length, maxImages, uploadFile],
  )

  function removeItem(id: string) {
    setItems((prev) => {
      const target = prev.find((i) => i.id === id)
      if (target?.path) {
        // Best-effort cleanup of the orphaned storage object.
        try {
          void createSupabaseBrowserClient().storage.from(BUCKET).remove([target.path])
        } catch {
          /* ignore */
        }
      }
      return prev.filter((i) => i.id !== id)
    })
  }

  if (!hasEnv) {
    return (
      <div className="rounded-lg border border-dashed border-border bg-secondary/40 p-6 text-center text-sm text-muted-foreground">
        Image uploads require Supabase Storage configuration.
      </div>
    )
  }

  return (
    <div className="space-y-3">
      <input type="hidden" name={name} value={hiddenValue} readOnly />

      <div
        role="button"
        tabIndex={0}
        onClick={() => inputRef.current?.click()}
        onKeyDown={(e) => {
          if (e.key === "Enter" || e.key === " ") inputRef.current?.click()
        }}
        onDragOver={(e) => {
          e.preventDefault()
          setDragOver(true)
        }}
        onDragLeave={() => setDragOver(false)}
        onDrop={(e) => {
          e.preventDefault()
          setDragOver(false)
          handleFiles(e.dataTransfer.files)
        }}
        className={
          "flex cursor-pointer flex-col items-center justify-center gap-2 rounded-lg border-2 border-dashed p-6 text-center transition-colors " +
          (dragOver ? "border-accent bg-accent/10" : "border-border bg-secondary/40 hover:border-accent/50")
        }
      >
        <div className="flex h-11 w-11 items-center justify-center rounded-lg border border-accent/40 bg-accent/10">
          <ImagePlus className="h-5 w-5 text-primary" />
        </div>
        <p className="text-sm font-medium text-foreground">Drag &amp; drop images, or click to browse</p>
        <p className="text-xs text-muted-foreground">
          JPG, PNG, WEBP, AVIF · up to {maxImages} images · 10 MB each
        </p>
        <input
          ref={inputRef}
          type="file"
          accept={ACCEPTED.join(",")}
          multiple
          className="hidden"
          onChange={(e) => handleFiles(e.target.files)}
        />
      </div>

      {notice && <p className="text-sm text-destructive">{notice}</p>}

      {items.length > 0 && (
        <div className="grid grid-cols-3 gap-3 sm:grid-cols-4">
          {items.map((item, index) => (
            <div
              key={item.id}
              className="group relative aspect-square overflow-hidden rounded-lg border border-border bg-secondary"
            >
              {/* eslint-disable-next-line @next/next/no-img-element */}
              <img src={item.previewUrl} alt="" className="h-full w-full object-cover" />
              {item.status === "uploading" && (
                <div className="absolute inset-0 flex items-center justify-center bg-background/60">
                  <Loader2 className="h-5 w-5 animate-spin text-primary" />
                </div>
              )}
              {item.status === "error" && (
                <div className="absolute inset-0 flex items-center justify-center bg-destructive/20 p-1 text-center text-[10px] text-destructive">
                  {item.error ?? "Failed"}
                </div>
              )}
              {item.status === "done" && index === 0 && (
                <span className="absolute left-1 top-1 flex items-center gap-1 rounded bg-primary/90 px-1.5 py-0.5 text-[10px] font-medium text-primary-foreground">
                  <Star className="h-3 w-3" /> Primary
                </span>
              )}
              <button
                type="button"
                onClick={() => removeItem(item.id)}
                className="absolute right-1 top-1 flex h-6 w-6 items-center justify-center rounded-full bg-background/80 text-foreground opacity-0 transition-opacity group-hover:opacity-100"
                aria-label="Remove image"
              >
                <X className="h-3.5 w-3.5" />
              </button>
            </div>
          ))}
        </div>
      )}
    </div>
  )
}
