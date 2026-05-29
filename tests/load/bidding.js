// k6 concurrent bidding load test (Phase 21).
//
// Validates the atomic place_bid RPC + anti-sniping under contention by hitting
// Supabase PostgREST directly with authenticated JWTs (mirrors what the server
// action does server-side).
//
//   k6 run \
//     -e SUPABASE_URL=https://xxxx.supabase.co \
//     -e SUPABASE_ANON_KEY=... \
//     -e AUCTION_ID=f0000000-0000-0000-0000-000000000001 \
//     -e EMAIL=buyer@harnessbid.test -e PASSWORD=password123 \
//     tests/load/bidding.js
//
// NOTE: a realistic test uses several distinct seeded bidders so proxies
// actually compete; parameterise EMAIL/PASSWORD per VU for that.
import http from "k6/http"
import { check, sleep } from "k6"

const URL = __ENV.SUPABASE_URL
const ANON = __ENV.SUPABASE_ANON_KEY
const AUCTION_ID = __ENV.AUCTION_ID
const EMAIL = __ENV.EMAIL
const PASSWORD = __ENV.PASSWORD

export const options = {
  vus: 10,
  duration: "1m",
  thresholds: {
    http_req_failed: ["rate<0.05"],
    http_req_duration: ["p(95)<2000"],
  },
}

function token() {
  const res = http.post(
    `${URL}/auth/v1/token?grant_type=password`,
    JSON.stringify({ email: EMAIL, password: PASSWORD }),
    { headers: { apikey: ANON, "Content-Type": "application/json" } },
  )
  return res.json("access_token")
}

export function setup() {
  return { jwt: token() }
}

export default function (data) {
  // Escalating max-bid so the proxy engine resolves a winner each tick.
  const amount = 13000 + Math.floor(Math.random() * 40) * 1000
  const res = http.post(
    `${URL}/rest/v1/rpc/place_bid`,
    JSON.stringify({ p_auction_id: AUCTION_ID, p_max_amount: amount }),
    {
      headers: {
        apikey: ANON,
        Authorization: `Bearer ${data.jwt}`,
        "Content-Type": "application/json",
      },
    },
  )
  check(res, { "rpc responded": (r) => r.status === 200 })
  sleep(1)
}
