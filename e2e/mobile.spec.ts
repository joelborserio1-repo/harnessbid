import { test } from "@playwright/test"
import { expectNoHorizontalScroll } from "./helpers"

/**
 * Mobile-viewport sanity: public pages render without horizontal overflow.
 * Run with the "mobile" project (iPhone 13) from playwright.config.ts.
 */
const PAGES = ["/", "/marketplace", "/auctions", "/sales", "/horses/buy-now", "/login", "/register"]

for (const path of PAGES) {
  test(`no horizontal overflow on ${path}`, async ({ page }) => {
    await page.goto(path)
    await expectNoHorizontalScroll(page)
  })
}
