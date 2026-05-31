"use client"

import { useState } from "react"
import { Share2, Check } from "lucide-react"
import { Button } from "@/components/ui/button"

/**
 * Share a listing: uses the native Web Share sheet on supported devices
 * (mobile), falling back to copy-to-clipboard with brief inline confirmation.
 * Self-contained — no global toaster dependency.
 */
export function ShareButton({
  title,
  variant = "icon",
}: {
  title?: string
  variant?: "icon" | "full"
}) {
  const [copied, setCopied] = useState(false)

  async function onShare() {
    const url = typeof window !== "undefined" ? window.location.href : ""
    const shareData = { title: title ? `${title} — HarnessBid` : "HarnessBid", url }
    // Native share sheet where available (mobile / some desktop browsers).
    if (typeof navigator !== "undefined" && navigator.share) {
      try {
        await navigator.share(shareData)
        return
      } catch {
        // user cancelled or unsupported — fall through to clipboard
      }
    }
    try {
      await navigator.clipboard.writeText(url)
      setCopied(true)
      setTimeout(() => setCopied(false), 2000)
    } catch {
      // clipboard blocked — no-op
    }
  }

  if (variant === "full") {
    return (
      <Button variant="outline" onClick={onShare} className="gap-2">
        {copied ? <Check className="h-4 w-4 text-accent" /> : <Share2 className="h-4 w-4" />}
        {copied ? "Link copied" : "Share"}
      </Button>
    )
  }

  return (
    <Button variant="outline" size="icon" onClick={onShare} aria-label="Share listing">
      {copied ? <Check className="h-5 w-5 text-accent" /> : <Share2 className="h-5 w-5" />}
    </Button>
  )
}
