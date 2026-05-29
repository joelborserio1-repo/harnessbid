import { test, expect } from "@playwright/test"

/**
 * Auth surface tests. Run against a deployment (E2E_BASE_URL). The invalid-login
 * test does not require seeded data; the seeded-login test uses the Phase 21
 * seed accounts (password "password123").
 */

test("register page shows the signup form", async ({ page }) => {
  await page.goto("/register")
  await expect(page.getByLabel(/full name/i)).toBeVisible()
  await expect(page.getByLabel(/email/i)).toBeVisible()
  await expect(page.getByLabel(/password/i)).toBeVisible()
})

test("invalid login shows an error", async ({ page }) => {
  await page.goto("/login")
  await page.getByLabel(/email/i).fill("nobody@example.com")
  await page.getByLabel(/password/i).fill("wrongpassword")
  await page.getByRole("button", { name: /login/i }).click()
  await expect(page.getByText(/invalid email or password/i)).toBeVisible()
})

// Requires the seed data to be loaded.
test.skip("seeded buyer can log in and reach the dashboard area", async ({ page }) => {
  await page.goto("/login")
  await page.getByLabel(/email/i).fill("buyer@harnessbid.test")
  await page.getByLabel(/password/i).fill("password123")
  await page.getByRole("button", { name: /login/i }).click()
  await page.waitForURL(/\/(dashboard|onboarding)/)
})
