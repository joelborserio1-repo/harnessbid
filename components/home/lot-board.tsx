"use client"

import { useEffect, useMemo, useState } from "react"
import Link from "next/link"
import { ArrowUpDown, Gavel } from "lucide-react"
import type { HorseAuctionCard } from "@/lib/supabase/queries"

/* Live countdown — ticks every second, mono numerals. */
function useCountdown(endsAt: string) {
  const [now, setNow] = useState(() => Date.now())
  useEffect(() => {
    const t = setInterval(() => setNow(Date.now()), 1000)
    return () => clearInterval(t)
  }, [])
  const ms = new Date(endsAt).getTime() - now
  if (ms <= 0) return { label: "CLOSED", urgent: false, closed: true }
  const d = Math.floor(ms / 86_400_000)
  const h = Math.floor((ms % 86_400_000) / 3_600_000)
  const m = Math.floor((ms % 3_600_000) / 60_000)
  const s = Math.floor((ms % 60_000) / 1000)
  const pad = (n: number) => String(n).padStart(2, "0")
  const label = d > 0 ? `${d}d ${pad(h)}:${pad(m)}:${pad(s)}` : `${pad(h)}:${pad(m)}:${pad(s)}`
  return { label, urgent: ms < 600_000, closed: false } // <10 min = urgent
}

function money(n: number) {
  return new Intl.NumberFormat("en-US", { style: "currency", currency: "USD", maximumFractionDigits: 0 }).format(n)
}

function pedigree(a: HorseAuctionCard) {
  if (a.sire && a.dam) return `${a.sire} × ${a.dam}`
  if (a.sire) return `${a.sire} ×`
  return a.description
}

function descriptor(a: HorseAuctionCard) {
  const parts = [
    a.age ? `${a.age}yo` : null,
    a.sex ? a.sex[0].toUpperCase() + a.sex.slice(1) : null,
    a.gait ? a.gait[0].toUpperCase() + a.gait.slice(1) : null,
  ].filter(Boolean)
  return parts.join(" · ")
}

function LotRow({ a }: { a: HorseAuctionCard }) {
  const c = useCountdown(a.endsAt)
  return (
    <Link
      href={`/auctions/${a.id}`}
      className="group grid grid-cols-[3rem_1fr_auto] sm:grid-cols-[3.5rem_1fr_8rem_9rem_7rem] items-center gap-3 px-4 sm:px-6 py-3.5 border-b border-white/8 hover:bg-white/[0.04] transition-colors"
    >
      {/* Lot number */}
      <span className="font-mono text-sm text-amber-200/70 tabular-nums">
        {a.lot ? String(a.lot).padStart(3, "0") : "—"}
      </span>

      {/* Name + pedigree */}
      <div className="min-w-0">
        <div className="flex items-center gap-2">
          <span className="font-cinzel text-[15px] sm:text-base text-white truncate group-hover:text-amber-100 transition-colors">
            {a.name}
          </span>
          {a.reserveMet && (
            <span className="hidden sm:inline text-[9px] uppercase tracking-wider text-emerald-300/80 border border-emerald-300/30 rounded-sm px-1 py-px">
              Reserve met
            </span>
          )}
        </div>
        <p className="truncate text-xs text-white/45 mt-0.5 italic">{pedigree(a)}</p>
      </div>

      {/* Descriptor (desktop) */}
      <span className="hidden sm:block text-xs text-white/55 tabular-nums">{descriptor(a) || "—"}</span>

      {/* Current bid */}
      <div className="text-right sm:text-left">
        <p className="hidden sm:block text-[9px] uppercase tracking-wider text-white/40">Current bid</p>
        <p className="font-mono text-sm sm:text-base text-amber-200 tabular-nums">{money(a.currentBid)}</p>
        <p className="sm:hidden text-[10px] text-white/40 tabular-nums">{a.bids} bids · {c.label}</p>
      </div>

      {/* Countdown (desktop) */}
      <div className="hidden sm:block text-right">
        <p className="text-[9px] uppercase tracking-wider text-white/40">{c.closed ? "" : "Closes in"}</p>
        <p className={`font-mono text-sm tabular-nums ${c.urgent ? "text-red-300" : "text-white/75"}`}>
          {c.label}
        </p>
        <p className="text-[10px] text-white/35 tabular-nums">{a.bids} bids</p>
      </div>
    </Link>
  )
}

type SortKey = "ending" | "bid" | "lot"

/**
 * Live lot board — the catalogue-as-hero. Dense, sortable, real-time countdowns.
 * Dark "catalogue terminal" surface (proposed palette evolution, navy-rooted) so
 * the brand can judge it as a slice before it's locked or applied site-wide.
 */
export function LotBoard({ lots }: { lots: HorseAuctionCard[] }) {
  const [sort, setSort] = useState<SortKey>("ending")

  const sorted = useMemo(() => {
    const copy = [...lots]
    if (sort === "ending") copy.sort((a, b) => new Date(a.endsAt).getTime() - new Date(b.endsAt).getTime())
    if (sort === "bid") copy.sort((a, b) => b.currentBid - a.currentBid)
    if (sort === "lot") copy.sort((a, b) => (a.lot ?? 9999) - (b.lot ?? 9999))
    return copy
  }, [lots, sort])

  const sorts: [SortKey, string][] = [
    ["ending", "Ending soon"],
    ["bid", "Top bids"],
    ["lot", "Lot order"],
  ]

  return (
    <section className="bg-[#0a1230] text-white">
      <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-10 lg:py-14">
        {/* Header row */}
        <div className="flex flex-wrap items-end justify-between gap-4 mb-6">
          <div>
            <div className="flex items-center gap-2 mb-1">
              <span className="relative flex h-2 w-2">
                <span className="absolute inline-flex h-full w-full rounded-full bg-amber-300/60 animate-ping" />
                <span className="relative inline-flex h-2 w-2 rounded-full bg-amber-300" />
              </span>
              <span className="font-sans text-xs uppercase tracking-[0.18em] text-amber-200/80">
                Live catalogue
              </span>
            </div>
            <h2 className="font-cinzel text-2xl sm:text-3xl text-white">Lots selling now</h2>
          </div>

          <div className="flex items-center gap-1 rounded-sm border border-white/12 p-1">
            <ArrowUpDown className="h-3.5 w-3.5 text-white/40 ml-2 mr-1" />
            {sorts.map(([key, label]) => (
              <button
                key={key}
                onClick={() => setSort(key)}
                className={`px-3 py-1.5 text-xs rounded-sm transition-colors ${
                  sort === key ? "bg-amber-300 text-[#0a1230] font-medium" : "text-white/65 hover:text-white"
                }`}
              >
                {label}
              </button>
            ))}
          </div>
        </div>

        {/* Board */}
        <div className="rounded-sm border border-white/12 overflow-hidden">
          {/* Column header (desktop) */}
          <div className="hidden sm:grid grid-cols-[3.5rem_1fr_8rem_9rem_7rem] gap-3 px-6 py-2.5 bg-white/[0.03] border-b border-white/12 text-[10px] uppercase tracking-wider text-white/40">
            <span>Lot</span>
            <span>Horse · Sire × Dam</span>
            <span>Type</span>
            <span>Current bid</span>
            <span className="text-right">Closes</span>
          </div>

          {sorted.length === 0 ? (
            <div className="px-6 py-12 text-center text-white/50">
              <Gavel className="mx-auto mb-3 h-7 w-7 text-white/30" />
              <p className="text-sm">No lots are live right now. Upcoming sales open soon.</p>
            </div>
          ) : (
            sorted.map((a) => <LotRow key={a.id} a={a} />)
          )}
        </div>

        <div className="mt-5 flex items-center justify-between">
          <p className="text-xs text-white/45">
            {sorted.length} lot{sorted.length === 1 ? "" : "s"} live · prices in USD · updated in real time
          </p>
          <Link
            href="/auctions"
            className="text-xs font-medium text-amber-200 hover:text-amber-100 flex items-center gap-1"
          >
            View full catalogue →
          </Link>
        </div>
      </div>
    </section>
  )
}
