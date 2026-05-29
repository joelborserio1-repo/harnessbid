"use client"

import { useEffect, useState } from "react"
import { Timer } from "lucide-react"

function parts(target: number) {
  const ms = Math.max(0, target - Date.now())
  return {
    d: Math.floor(ms / 86_400_000),
    h: Math.floor((ms % 86_400_000) / 3_600_000),
    m: Math.floor((ms % 3_600_000) / 60_000),
    s: Math.floor((ms % 60_000) / 1000),
    done: ms <= 0,
  }
}

export function EventCountdown({ target, label }: { target: string | null; label: string }) {
  const [now, setNow] = useState(0)
  useEffect(() => {
    setNow(Date.now())
    const id = setInterval(() => setNow(Date.now()), 1000)
    return () => clearInterval(id)
  }, [])

  if (!target) return null
  const t = parts(new Date(target).getTime())
  // Avoid hydration mismatch: render nothing until mounted (now>0).
  if (now === 0) return null
  if (t.done) return null

  const cell = (value: number, unit: string) => (
    <div className="flex flex-col items-center rounded-md bg-primary-foreground/10 px-3 py-2">
      <span className="font-sora text-xl font-bold text-primary-foreground">{String(value).padStart(2, "0")}</span>
      <span className="text-[10px] uppercase tracking-wide text-primary-foreground/60">{unit}</span>
    </div>
  )

  return (
    <div>
      <p className="mb-2 flex items-center gap-1 text-sm text-primary-foreground/70">
        <Timer className="h-4 w-4" />
        {label}
      </p>
      <div className="flex gap-2">
        {cell(t.d, "days")}
        {cell(t.h, "hrs")}
        {cell(t.m, "min")}
        {cell(t.s, "sec")}
      </div>
    </div>
  )
}
