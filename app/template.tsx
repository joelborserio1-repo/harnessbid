"use client"

import { useEffect } from "react"
import { usePathname } from "next/navigation"

/**
 * A template re-mounts on every navigation. We use it to reset scroll position
 * to the top on route changes — the App Router otherwise preserves scroll,
 * which feels wrong on a catalogue site (landing mid-page after navigating).
 */
export default function Template({ children }: { children: React.ReactNode }) {
  const pathname = usePathname()

  useEffect(() => {
    window.scrollTo(0, 0)
  }, [pathname])

  return <>{children}</>
}
