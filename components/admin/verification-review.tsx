"use client"

import { useState } from "react"
import { Eye, Loader2 } from "lucide-react"
import { Button } from "@/components/ui/button"
import { getVerificationDocUrls } from "@/lib/verification/actions"

/**
 * Staff-only: reveal short-lived signed URLs for an ID document on demand
 * (documents are never embedded directly, so they aren't logged in HTML or
 * cached). Opens each in a new tab.
 */
export function ViewDocsButton({ frontPath, backPath }: { frontPath: string; backPath: string | null }) {
  const [loading, setLoading] = useState(false)

  async function reveal() {
    setLoading(true)
    try {
      const urls = await getVerificationDocUrls(frontPath, backPath)
      if (urls.front) window.open(urls.front, "_blank", "noopener,noreferrer")
      if (urls.back) window.open(urls.back, "_blank", "noopener,noreferrer")
    } finally {
      setLoading(false)
    }
  }

  return (
    <Button type="button" size="sm" variant="outline" onClick={reveal} disabled={loading}>
      {loading ? <Loader2 className="mr-2 h-4 w-4 animate-spin" /> : <Eye className="mr-2 h-4 w-4" />}
      View ID documents
    </Button>
  )
}
