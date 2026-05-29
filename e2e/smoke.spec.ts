import { test, expect } from "@playwright/test"

/**
 * Smoke tests — public routes render and core nav works. These are the
 * foundation for the Phase 21 E2E suite (auth, onboarding, listing creation,
 * bidding, messaging). Run against a deployment via E2E_BASE_URL.
 */

test("homepage renders", async ({ page }) => {
  await page.goto("/")
  await expect(page.getByRole("link", { name: /harnessbid/i }).first()).toBeVisible()
})

test("marketplace browse renders", async ({ page }) => {
  await page.goto("/marketplace")
  await expect(page).toHaveTitle(/HarnessBid/i)
})

test("auctions browse renders", async ({ page }) => {
  await page.goto("/auctions")
  await expect(page).toHaveTitle(/HarnessBid/i)
})

test("sales/events landing renders", async ({ page }) => {
  await page.goto("/sales")
  await expect(page.getByRole("heading", { name: /sales/i }).first()).toBeVisible()
})

test("login page is reachable", async ({ page }) => {
  await page.goto("/login")
  await expect(page.getByLabel(/email/i)).toBeVisible()
})

test("admin is protected for anonymous users", async ({ page }) => {
  await page.goto("/admin")
  // proxy + layout guard redirect non-staff away from /admin.
  await expect(page).not.toHaveURL(/\/admin$/)
})
