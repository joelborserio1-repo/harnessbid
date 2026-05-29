// k6 mixed marketplace load test (Phase 21).
//
// Simulates blended public traffic: homepage, marketplace browse/search,
// auctions, sales catalogue, and seller storefronts — the read path real
// visitors hit during a live sale. Pair with tests/load/bidding.js for the
// authenticated write path.
//
//   k6 run -e K6_BASE_URL=https://staging.harnessbid.com tests/load/mixed.js
import http from "k6/http"
import { check, sleep } from "k6"
import { Rate } from "k6/metrics"

const BASE = __ENV.K6_BASE_URL || "http://localhost:3000"
const errors = new Rate("page_errors")

export const options = {
  scenarios: {
    visitors: {
      executor: "ramping-vus",
      startVUs: 0,
      stages: [
        { duration: "30s", target: 20 },
        { duration: "2m", target: 20 },
        { duration: "30s", target: 0 },
      ],
    },
  },
  thresholds: {
    http_req_failed: ["rate<0.02"],
    http_req_duration: ["p(95)<1800"],
    page_errors: ["rate<0.02"],
  },
}

const PATHS = [
  "/",
  "/marketplace",
  "/marketplace?search=bike",
  "/auctions",
  "/sales",
  "/sales/apg-yearling-sale-2026",
  "/horses/buy-now",
]

export default function () {
  const path = PATHS[Math.floor(Math.random() * PATHS.length)]
  const res = http.get(`${BASE}${path}`)
  const ok = check(res, { "status 200": (r) => r.status === 200 })
  errors.add(!ok)
  sleep(Math.random() * 2 + 0.5)
}
