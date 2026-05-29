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

// Listeners let every mounted WatchButton re-resolve when auth changes.
const listeners = new Set<() => void>()
let authSubInitialised = false

function ensureAuthSubscription() {
  if (authSubInitialised || !hasEnv) return
  authSubInitialised = true
  try {
    createSupabaseBrowserClient().auth.onAuthStateChange(() => {
      // Invalidate the cached watched-set on login/logout/token refresh and
      // notify mounted buttons so stale states don't persist across sessions.
      watchedCache = null
      listeners.forEach((fn) => fn())
    })
  } catch {
    /* env not configured */
  }
}

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
    ensureAuthSubscription()
    let active = true

    const resolve = () => {
      loadWatchedSets().then((sets) => {
        if (active) setWatched(sets[kind].has(listingId))
      })
    }

    // Resolve initial state from the cache when not provided by the server.
    if (initialWatched === undefined) resolve()

    // Re-resolve on auth changes (login/logout) for every button, including
    // server-seeded ones, so watched state never goes stale across sessions.
    listeners.add(resolve)
    return () => {
      active = false
      listeners.delete(resolve)
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
