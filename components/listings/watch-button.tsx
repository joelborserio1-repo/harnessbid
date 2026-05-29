"use client"

import { useEffect, useState, useTransition } from "react"
import { useRouter } from "next/navigation"
import { Heart } from "lucide-react"
import { Button } from "@/components/ui/button"
import { createSupabaseBrowserClient } from "@/lib/supabase/client"
import { toggleWatchlistAction, type WatchKind } from "@/lib/listings/watchlist"

const hasEnv = Boolean(
  process.env.NEXT_PUBLIC_SUPABASE_URL && process.env.NEXT_PUBLIC_SUPABASE_ANON_KEY,
)

type WatchedSets = { horse: Set<string>; marketplace: Set<string> }
let watchedCache: Promise<WatchedSets> | null = null

function loadWatchedSets(): Promise<WatchedSets> {
  if (!watchedCache) {
    watchedCache = (async () => {
      const empty: WatchedSets = { horse: new Set(), marketplace: new Set() }
      if (!hasEnv) return empty
      try {
        const supabase = createSupabaseBrowserClient()
        const {
          data: { user },
        } = await supabase.auth.getUser()
        if (!user) return empty
        const { data } = await supabase
          .from("watchlists")
          .select("horse_listing_id, marketplace_listing_id")
          .eq("profile_id", user.id)
        for (const row of data ?? []) {
          if (row.horse_listing_id) empty.horse.add(row.horse_listing_id)
          if (row.marketplace_listing_id) empty.marketplace.add(row.marketplace_listing_id)
        }
        return empty
      } catch {
        return empty
      }
    })()
  }
  return watchedCache
}

function updateCache(kind: WatchKind, id: string, watched: boolean) {
  if (!watchedCache) return
  watchedCache.then((sets) => {
    if (watched) sets[kind].add(id)
    else sets[kind].delete(id)
  })
}

export function WatchButton({
  listingId,
  kind,
  initialWatched,
  variant = "icon",
  className,
}: {
  listingId: string
  kind: WatchKind
  initialWatched?: boolean
  variant?: "icon" | "full"
  className?: string
}) {
  const router = useRouter()
  const [watched, setWatched] = useState(Boolean(initialWatched))
  const [pending, startTransition] = useTransition()

  useEffect(() => {
    if (initialWatched !== undefined) return
    let active = true
    loadWatchedSets().then((sets) => {
      if (active) setWatched(sets[kind].has(listingId))
    })
    return () => {
      active = false
    }
  }, [initialWatched, kind, listingId])

  function onClick(e: React.MouseEvent) {
    // Allow use inside clickable cards/links without triggering navigation.
    e.preventDefault()
    e.stopPropagation()
    const next = !watched
    setWatched(next) // optimistic
    startTransition(async () => {
      const result = await toggleWatchlistAction(listingId, kind)
      if (result.error === "auth") {
        setWatched(false)
        const redirect = typeof window !== "undefined" ? window.location.pathname : "/watchlist"
        router.push(`/login?redirect=${encodeURIComponent(redirect)}`)
        return
      }
      if (result.error) {
        setWatched(!next) // revert
        return
      }
      setWatched(result.watched)
      updateCache(kind, listingId, result.watched)
    })
  }

  if (variant === "full") {
    return (
      <Button
        type="button"
        variant="outline"
        onClick={onClick}
        disabled={pending}
        className={className}
        aria-pressed={watched}
      >
        <Heart className={"mr-2 h-4 w-4 " + (watched ? "fill-accent text-accent" : "")} />
        {watched ? "Saved" : "Save"}
      </Button>
    )
  }

  return (
    <button
      type="button"
      onClick={onClick}
      disabled={pending}
      aria-label={watched ? "Remove from watchlist" : "Add to watchlist"}
      aria-pressed={watched}
      className={
        "flex h-9 w-9 items-center justify-center rounded-full border border-border bg-card/90 text-foreground transition-colors hover:bg-card " +
        (className ?? "")
      }
    >
      <Heart className={"h-4 w-4 " + (watched ? "fill-accent text-accent" : "")} />
    </button>
  )
}
