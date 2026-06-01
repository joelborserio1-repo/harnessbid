"use client"

import { useRouter, useSearchParams, usePathname } from "next/navigation"
import { useCallback } from "react"
import { Search, X } from "lucide-react"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { Button } from "@/components/ui/button"
import { Badge } from "@/components/ui/badge"
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select"
import type { MarketplaceCategory } from "@/lib/supabase/queries"

const CONDITIONS = ["new", "excellent", "good", "used", "fair"]

/** Reads/writes marketplace filters to the URL so they persist + drive the server query. */
function useUrlFilters() {
  const router = useRouter()
  const pathname = usePathname()
  const params = useSearchParams()

  const setParam = useCallback(
    (updates: Record<string, string | null>) => {
      const next = new URLSearchParams(params.toString())
      for (const [k, v] of Object.entries(updates)) {
        if (v === null || v === "") next.delete(k)
        else next.set(k, v)
      }
      // Any filter change resets to page 1.
      if (!("page" in updates)) next.delete("page")
      router.push(`${pathname}?${next.toString()}`)
    },
    [params, pathname, router],
  )

  return { params, setParam }
}

export function MarketplaceFilters({ categories }: { categories: MarketplaceCategory[] }) {
  const { params, setParam } = useUrlFilters()
  const activeCat = params.get("category") ?? ""
  const activeCond = params.get("condition") ?? ""

  return (
    <div className="space-y-6">
      <div>
        <Label className="text-sm font-medium mb-2 block">Search</Label>
        <form
          onSubmit={(e) => {
            e.preventDefault()
            const v = new FormData(e.currentTarget).get("q")
            setParam({ q: String(v ?? "") || null })
          }}
          className="relative"
        >
          <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
          <Input name="q" type="search" defaultValue={params.get("q") ?? ""} placeholder="Search listings..." className="pl-9" />
        </form>
      </div>

      <div>
        <Label className="text-sm font-medium mb-2 block">Category</Label>
        <div className="space-y-1.5">
          {categories.map((c) => (
            <button
              key={c.id}
              onClick={() => setParam({ category: activeCat === c.id ? null : c.id })}
              className={`flex w-full items-center justify-between rounded-sm px-2 py-1.5 text-sm transition-colors ${
                activeCat === c.id ? "bg-primary text-primary-foreground" : "hover:bg-secondary text-foreground"
              }`}
            >
              <span>{c.name}</span>
              <span className={activeCat === c.id ? "text-primary-foreground/70" : "text-muted-foreground"}>{c.count}</span>
            </button>
          ))}
        </div>
      </div>

      <div>
        <Label className="text-sm font-medium mb-2 block">Condition</Label>
        <div className="flex flex-wrap gap-1.5">
          {CONDITIONS.map((cond) => (
            <button
              key={cond}
              onClick={() => setParam({ condition: activeCond === cond ? null : cond })}
              className={`rounded-sm border px-2.5 py-1 text-xs capitalize transition-colors ${
                activeCond === cond ? "border-primary bg-primary text-primary-foreground" : "border-border hover:border-accent text-foreground"
              }`}
            >
              {cond}
            </button>
          ))}
        </div>
      </div>

      <div>
        <Label className="text-sm font-medium mb-2 block">Price range (USD)</Label>
        <form
          onSubmit={(e) => {
            e.preventDefault()
            const fd = new FormData(e.currentTarget)
            setParam({ min: String(fd.get("min") ?? "") || null, max: String(fd.get("max") ?? "") || null })
          }}
          className="flex items-center gap-2"
        >
          <Input name="min" type="number" defaultValue={params.get("min") ?? ""} placeholder="Min" className="w-full text-sm" />
          <span className="text-muted-foreground">-</span>
          <Input name="max" type="number" defaultValue={params.get("max") ?? ""} placeholder="Max" className="w-full text-sm" />
          <Button type="submit" size="sm" variant="outline">Go</Button>
        </form>
      </div>
    </div>
  )
}

export function MarketplaceSort() {
  const { params, setParam } = useUrlFilters()
  return (
    <Select value={params.get("sort") ?? "newest"} onValueChange={(v) => setParam({ sort: v === "newest" ? null : v })}>
      <SelectTrigger className="w-[180px]"><SelectValue placeholder="Sort by" /></SelectTrigger>
      <SelectContent>
        <SelectItem value="newest">Newest First</SelectItem>
        <SelectItem value="price-low">Price: Low to High</SelectItem>
        <SelectItem value="price-high">Price: High to Low</SelectItem>
      </SelectContent>
    </Select>
  )
}

export function MarketplaceActiveFilters({ categoryName }: { categoryName?: string }) {
  const { params, setParam } = useUrlFilters()
  const chips: { key: string; label: string }[] = []
  if (params.get("q")) chips.push({ key: "q", label: `"${params.get("q")}"` })
  if (params.get("category")) chips.push({ key: "category", label: categoryName ?? "Category" })
  if (params.get("condition")) chips.push({ key: "condition", label: params.get("condition")! })
  if (params.get("min") || params.get("max")) chips.push({ key: "price", label: `$${params.get("min") ?? "0"}–${params.get("max") ?? "∞"}` })
  if (chips.length === 0) return null
  return (
    <div className="flex flex-wrap items-center gap-2 mb-6">
      {chips.map((c) => (
        <Badge key={c.key} variant="secondary" className="flex items-center gap-1 capitalize">
          {c.label}
          <button
            className="ml-1 hover:text-destructive"
            aria-label="Remove filter"
            onClick={() => setParam(c.key === "price" ? { min: null, max: null } : { [c.key]: null })}
          >
            <X className="h-3 w-3" />
          </button>
        </Badge>
      ))}
      <button onClick={() => setParam({ q: null, category: null, condition: null, min: null, max: null })} className="text-xs text-muted-foreground hover:text-foreground underline">
        Clear all
      </button>
    </div>
  )
}

export function MarketplacePagination({ page, totalPages }: { page: number; totalPages: number }) {
  const { setParam } = useUrlFilters()
  if (totalPages <= 1) return null
  const go = (p: number) => setParam({ page: p <= 1 ? null : String(p) })
  // Compact window of page numbers around the current page.
  const nums = Array.from({ length: totalPages }, (_, i) => i + 1).filter(
    (n) => n === 1 || n === totalPages || Math.abs(n - page) <= 1,
  )
  const out: (number | "…")[] = []
  nums.forEach((n, i) => {
    if (i > 0 && n - nums[i - 1] > 1) out.push("…")
    out.push(n)
  })
  return (
    <div className="flex items-center justify-center gap-2 mt-12">
      <Button variant="outline" disabled={page <= 1} onClick={() => go(page - 1)}>Previous</Button>
      {out.map((n, i) =>
        n === "…" ? (
          <span key={`e${i}`} className="px-2 text-muted-foreground">…</span>
        ) : (
          <Button
            key={n}
            variant="outline"
            onClick={() => go(n)}
            className={n === page ? "bg-primary text-primary-foreground hover:bg-primary/90" : ""}
          >
            {n}
          </Button>
        ),
      )}
      <Button variant="outline" disabled={page >= totalPages} onClick={() => go(page + 1)}>Next</Button>
    </div>
  )
}
