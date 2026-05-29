// k6 load test placeholder (Phase 20). Install k6 (https://k6.io) then:
//   K6_BASE_URL=https://staging.harnessbid.com k6 run tests/load/auctions.js
//
// Exercises read-heavy public paths to validate behaviour under concurrent
// load. Phase 21 extends this with authenticated bidding bursts against a
// seeded auction to validate the place_bid RPC + anti-sniping under contention.
import http from "k6/http"
import { check, sleep } from "k6"

const BASE = __ENV.K6_BASE_URL || "http://localhost:3000"

export const options = {
  scenarios: {
    browse: {
      executor: "ramping-vus",
      startVUs: 0,
      stages: [
        { duration: "30s", target: 50 },
        { duration: "1m", target: 50 },
        { duration: "30s", target: 0 },
      ],
    },
  },
  thresholds: {
    http_req_failed: ["rate<0.02"],
    http_req_duration: ["p(95)<1500"],
  },
}

export default function () {
  const paths = ["/", "/marketplace", "/auctions", "/sales", "/horses/buy-now"]
  for (const p of paths) {
    const res = http.get(`${BASE}${p}`)
    check(res, { "status 200": (r) => r.status === 200 })
    sleep(0.5)
  }
}
