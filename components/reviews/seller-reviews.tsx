"use client"

import { useActionState, useState } from "react"
import { useFormStatus } from "react-dom"
import { Star, Loader2 } from "lucide-react"
import { Button } from "@/components/ui/button"
import { Textarea } from "@/components/ui/textarea"
import { submitReviewAction, type ReviewState } from "@/lib/reviews/actions"
import type { SellerReview } from "@/lib/reviews/queries"

const initialState: ReviewState = {}

function Stars({ value, className = "" }: { value: number; className?: string }) {
  return (
    <span className={`inline-flex ${className}`}>
      {[1, 2, 3, 4, 5].map((n) => (
        <Star key={n} className={`h-4 w-4 ${n <= Math.round(value) ? "fill-accent text-accent" : "text-muted-foreground/40"}`} />
      ))}
    </span>
  )
}

function SubmitButton() {
  const { pending } = useFormStatus()
  return (
    <Button type="submit" disabled={pending} className="bg-primary text-primary-foreground hover:bg-primary/90">
      {pending ? <Loader2 className="mr-2 h-4 w-4 animate-spin" /> : null}
      Post review
    </Button>
  )
}

export function SellerReviews({
  sellerAccountId,
  sellerSlug,
  rating,
  reviewCount,
  reviews,
  canReview,
}: {
  sellerAccountId: string
  sellerSlug: string
  rating: number
  reviewCount: number
  reviews: SellerReview[]
  canReview: boolean
}) {
  const [state, formAction] = useActionState(submitReviewAction, initialState)
  const mine = reviews.find((r) => r.isMine)
  const [stars, setStars] = useState<number>(mine?.rating ?? 0)
  const [hover, setHover] = useState(0)

  return (
    <section className="rounded-lg border border-border bg-card p-6">
      <div className="flex items-center justify-between mb-5">
        <h2 className="font-cinzel text-xl font-medium text-foreground">Reviews</h2>
        <div className="flex items-center gap-2 text-sm">
          <Stars value={rating} />
          <span className="font-medium text-foreground">{rating.toFixed(1)}</span>
          <span className="text-muted-foreground">({reviewCount})</span>
        </div>
      </div>

      {/* Leave a review */}
      {canReview && (
        <form action={formAction} className="mb-6 rounded-sm border border-border bg-secondary/40 p-4">
          <input type="hidden" name="sellerAccountId" value={sellerAccountId} />
          <input type="hidden" name="sellerSlug" value={sellerSlug} />
          <input type="hidden" name="rating" value={stars} />
          <p className="text-sm font-medium text-foreground mb-2">{mine ? "Update your review" : "Leave a review"}</p>
          <div className="flex items-center gap-1 mb-3" onMouseLeave={() => setHover(0)}>
            {[1, 2, 3, 4, 5].map((n) => (
              <button
                key={n}
                type="button"
                onClick={() => setStars(n)}
                onMouseEnter={() => setHover(n)}
                aria-label={`${n} star${n > 1 ? "s" : ""}`}
                className="p-0.5"
              >
                <Star className={`h-6 w-6 transition-colors ${n <= (hover || stars) ? "fill-accent text-accent" : "text-muted-foreground/40"}`} />
              </button>
            ))}
          </div>
          <Textarea
            name="comment"
            defaultValue={mine?.comment ?? ""}
            placeholder="Share your experience dealing with this seller…"
            className="min-h-20 bg-card"
          />
          {state.error && <p className="mt-2 text-sm text-destructive">{state.error}</p>}
          {state.success && <p className="mt-2 text-sm text-accent">{state.message}</p>}
          <div className="mt-3"><SubmitButton /></div>
        </form>
      )}

      {/* Review list */}
      {reviews.length === 0 ? (
        <p className="text-sm text-muted-foreground">No reviews yet. Be the first to review this seller.</p>
      ) : (
        <ul className="space-y-4">
          {reviews.map((r) => (
            <li key={r.id} className="border-b border-border pb-4 last:border-b-0 last:pb-0">
              <div className="flex items-center justify-between mb-1">
                <span className="text-sm font-medium text-foreground">
                  {r.reviewerName}{r.isMine ? " (you)" : ""}
                </span>
                <Stars value={r.rating} />
              </div>
              {r.comment && <p className="text-sm text-muted-foreground leading-relaxed">{r.comment}</p>}
              <p className="text-xs text-muted-foreground/70 mt-1">{new Date(r.createdAt).toLocaleDateString()}</p>
            </li>
          ))}
        </ul>
      )}
    </section>
  )
}
