import { test, expect } from "@playwright/test"

/**
 * End-to-end seller + buyer journey against a SEEDED staging deployment.
 * Marked skip by default; enable once E2E_BASE_URL points at an environment
 * loaded with supabase/seed.sql.
 *
 * Coverage target (Phase 21):
 *  - buyer logs in, opens the live APG auction, places a bid
 *  - buyer sees their bid under /dashboard/bids
 *  - buyer enquires on the buy-now filly -> conversation created
 *  - seller logs in, sees the enquiry conversation, replies
 *  - admin logs in, opens /admin moderation surfaces
 */

const PW = "password123"

test.describe.skip("seeded buyer/seller journey", () => {
  test("buyer places a bid on the live auction", async ({ page }) => {
    await page.goto("/login")
    await page.getByLabel(/email/i).fill("buyer@harnessbid.test")
    await page.getByLabel(/password/i).fill(PW)
    await page.getByRole("button", { name: /login/i }).click()

    await page.goto("/auctions/bettor-dream-lot-1")
    await expect(page.getByText(/current bid/i)).toBeVisible()
    await page.getByPlaceholder(/\d/).first().fill("13000")
    await page.getByRole("button", { name: /place bid/i }).click()
    await expect(page.getByText(/highest bidder|outbid/i)).toBeVisible()
  })

  test("admin can open the moderation console", async ({ page }) => {
    await page.goto("/login")
    await page.getByLabel(/email/i).fill("admin@harnessbid.test")
    await page.getByLabel(/password/i).fill(PW)
    await page.getByRole("button", { name: /login/i }).click()
    await page.goto("/admin")
    await expect(page.getByText(/platform overview/i)).toBeVisible()
  })
})
