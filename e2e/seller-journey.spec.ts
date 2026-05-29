import { test, expect } from "@playwright/test"
import { ACCOUNTS, login } from "./helpers"

/**
 * Seller + buyer + enterprise + admin journeys against a SEEDED deployment.
 * Skipped by default — enable when E2E_BASE_URL targets a seeded env.
 */
test.describe.skip("seeded journeys", () => {
  test("seller reaches dashboard tools", async ({ page }) => {
    await login(page, ACCOUNTS.seller)
    await page.goto("/dashboard")
    await expect(page.getByText(/your listings|your auctions/i).first()).toBeVisible()
    await page.goto("/dashboard/listings")
    await expect(page).toHaveURL(/\/dashboard\/listings/)
  })

  test("buyer watchlist + enquiry + messages", async ({ page }) => {
    await login(page, ACCOUNTS.buyer)
    await page.goto("/watchlist")
    await expect(page.getByRole("heading", { name: /saved listings/i })).toBeVisible()
    await page.goto("/dashboard/messages")
    await expect(page.getByRole("heading", { name: /conversations/i })).toBeVisible()
  })

  test("enterprise storefront is reachable", async ({ page }) => {
    await page.goto("/sales/apg-yearling-sale-2026")
    await expect(page.getByText(/catalogue/i)).toBeVisible()
  })

  test("admin moderation surfaces load", async ({ page }) => {
    await login(page, ACCOUNTS.admin)
    await page.goto("/admin/listings")
    await expect(page.getByRole("heading", { name: /listing moderation/i })).toBeVisible()
    await page.goto("/admin/enterprise")
    await expect(page.getByRole("heading", { name: /enterprise sellers/i })).toBeVisible()
  })
})
