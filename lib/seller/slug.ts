/**
 * Centralised, URL-safe slug helpers for seller and enterprise accounts.
 * The database enforces uniqueness (`seller_accounts.slug` UNIQUE + a format
 * CHECK), so callers should treat these as the first line of defence and rely
 * on the unique-constraint retry in the seller onboarding action for final
 * collision handling.
 */

// Mirrors the DB CHECK: ^[a-z0-9]+(?:-[a-z0-9]+)*$
const SLUG_PATTERN = /^[a-z0-9]+(?:-[a-z0-9]+)*$/

// Combining diacritical marks (U+0300–U+036F) left over after NFKD normalize.
const DIACRITICS = /[̀-ͯ]/g

export function slugify(value: string): string {
  return value
    .toLowerCase()
    .normalize("NFKD")
    .replace(DIACRITICS, "")
    .replace(/[^a-z0-9]+/g, "-")
    .replace(/^-+|-+$/g, "")
    .slice(0, 48)
}

export function isValidSlug(slug: string): boolean {
  return slug.length >= 3 && slug.length <= 48 && SLUG_PATTERN.test(slug)
}

export function randomSlugSuffix(): string {
  return Math.random().toString(36).slice(2, 6)
}

/**
 * Produces an ordered list of slug candidates: the requested slug first, then
 * suffixed variants for collision retries.
 */
export function slugCandidates(base: string, attempts = 4): string[] {
  const cleaned = slugify(base) || `seller-${randomSlugSuffix()}`
  const candidates = [cleaned]
  for (let i = 1; i < attempts; i++) {
    candidates.push(`${cleaned}-${randomSlugSuffix()}`)
  }
  return candidates
}
