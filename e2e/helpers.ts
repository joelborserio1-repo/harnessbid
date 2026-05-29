import { type Page, expect } from "@playwright/test"

/** Seeded test accounts (supabase/seed.sql). Password is shared. */
export const ACCOUNTS = {
  admin: "admin@harnessbid.test",
  seller: "seller@harnessbid.test",
  enterprise: "enterprise@harnessbid.test",
  buyer: "buyer@harnessbid.test",
} as const

export const TEST_PASSWORD = "password123"

/** Logs in via the real login form and waits for redirect away from /login. */
export async function login(page: Page, email: string, password = TEST_PASSWORD) {
  await page.goto("/login")
  await page.getByLabel(/email/i).fill(email)
  await page.getByLabel(/password/i).fill(password)
  await page.getByRole("button", { name: /login/i }).click()
  await expect(page).not.toHaveURL(/\/login/)
}

/** Asserts no obvious horizontal overflow at the current viewport (mobile QA). */
export async function expectNoHorizontalScroll(page: Page) {
  const overflow = await page.evaluate(
    () => document.documentElement.scrollWidth - document.documentElement.clientWidth,
  )
  expect(overflow).toBeLessThanOrEqual(2)
}
