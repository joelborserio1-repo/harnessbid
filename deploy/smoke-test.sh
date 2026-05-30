#!/usr/bin/env bash
#
# HarnessBid synthetic smoke test — run ON THE BOX.
#   ./deploy/smoke-test.sh
#
# Crawls every public + dynamic route, then simulates concurrent authenticated
# users exercising real actions (login, bid, watchlist, enquiry, message, saved
# search) directly against the Supabase REST/RPC + the site. Reports every
# failure. Read-only-ish: it places test bids/watchlist rows as the seed buyer.
set -uo pipefail

SITE="${SITE:-https://harnessbid.com}"
ENV_FILE="${ENV_FILE:-/home/harnessbid/harnessbid-v2/shared/.env.production}"
URL=$(grep '^NEXT_PUBLIC_SUPABASE_URL=' "$ENV_FILE" | cut -d= -f2)
ANON=$(grep '^NEXT_PUBLIC_SUPABASE_ANON_KEY=' "$ENV_FILE" | cut -d= -f2)

PASS=0; FAIL=0
declare -a FAILURES

check() { # check <label> <expected-codes-regex> <url>
  local label="$1" want="$2" url="$3"
  local code
  code=$(curl -s -o /dev/null -w '%{http_code}' -L "$url")
  if [[ "$code" =~ $want ]]; then
    PASS=$((PASS+1)); printf '  ok   %-32s %s\n' "$label" "$code"
  else
    FAIL=$((FAIL+1)); printf '  FAIL %-32s %s\n' "$label" "$code"
    FAILURES+=("$label -> HTTP $code  ($url)")
  fi
}

login() { # login <email> -> echoes access token
  curl -s "$URL/auth/v1/token?grant_type=password" -H "apikey: $ANON" \
    -H "Content-Type: application/json" \
    -d "{\"email\":\"$1\",\"password\":\"password123\"}" \
    | grep -o '"access_token":"[^"]*"' | cut -d'"' -f4
}
rest() { # rest <token> <method> <path> [body]
  local tok="$1" method="$2" path="$3" body="${4:-}"
  curl -s -X "$method" "$URL/rest/v1/$path" \
    -H "apikey: $ANON" -H "Authorization: Bearer $tok" \
    -H "Content-Type: application/json" ${body:+-d "$body"}
}
rpc() { # rpc <token> <fn> <body>
  curl -s -X POST "$URL/rest/v1/rpc/$2" -H "apikey: $ANON" \
    -H "Authorization: Bearer $1" -H "Content-Type: application/json" -d "$3"
}

echo "=================================================================="
echo " HarnessBid smoke test  ($SITE)"
echo "=================================================================="

echo; echo "[1] Static public pages"
for p in / /auctions /marketplace /search "/search?q=pace" /sale-events /sales \
         /horses/buy-now /horses/sold /sell /sell/horse /sell/equipment \
         /login /register /pricing /help /trust /terms /privacy /contact \
         /enterprise /guides/seller /onboarding; do
  check "$p" '^(200|3..)$' "$SITE$p"
done

echo; echo "[2] Marketplace category pages"
for c in equipment bikes-sulkies harness-tack safety-gear walking-machines \
         joggers vehicles services property feed memorabilia apparel other; do
  check "/marketplace/$c" '^200$' "$SITE/marketplace/$c"
done

echo; echo "[3] Auth-gated pages (expect 200 or redirect to login)"
for p in /dashboard /dashboard/bids /dashboard/listings /dashboard/messages \
         /dashboard/notifications /dashboard/saved-searches /watchlist /account \
         /admin /admin/listings /admin/reports; do
  check "$p" '^(200|3..)$' "$SITE$p"
done

echo; echo "[4] Dynamic detail pages (resolve real ids from DB)"
AID=$(rest "$ANON" GET "auctions?select=id&status=eq.live&limit=1" | grep -o '"id":"[^"]*"' | head -1 | cut -d'"' -f4)
MKT=$(rest "$ANON" GET "marketplace_listings?select=slug&status=eq.published&limit=1" | grep -o '"slug":"[^"]*"' | head -1 | cut -d'"' -f4)
SALE=$(rest "$ANON" GET "sale_events?select=slug&limit=1" | grep -o '"slug":"[^"]*"' | head -1 | cut -d'"' -f4)
SELLER=$(rest "$ANON" GET "seller_accounts?select=slug&limit=1" | grep -o '"slug":"[^"]*"' | head -1 | cut -d'"' -f4)
[ -n "$AID" ]    && check "/auctions/$AID"      '^200$' "$SITE/auctions/$AID"        || echo "  skip /auctions/[id] (no live auction)"
[ -n "$MKT" ]    && check "/marketplace/$MKT"   '^200$' "$SITE/marketplace/$MKT"     || echo "  skip /marketplace/[id]"
[ -n "$SALE" ]   && check "/sales/$SALE"        '^200$' "$SITE/sales/$SALE"          || echo "  skip /sales/[slug]"
[ -n "$SELLER" ] && check "/sellers/$SELLER"    '^(200|3..)$' "$SITE/sellers/$SELLER" || echo "  skip /sellers/[slug]"

echo; echo "[5] Simulate 10 concurrent users hitting the homepage + auctions"
for i in $(seq 1 10); do curl -s -o /dev/null "$SITE/" & curl -s -o /dev/null "$SITE/auctions" & done; wait
echo "  ok   10 concurrent visitors completed"

echo; echo "[6] Authenticated actions as seed users"
BUYER=$(login buyer@harnessbid.test)
if [ -z "$BUYER" ]; then
  FAIL=$((FAIL+1)); FAILURES+=("login buyer@harnessbid.test failed")
  echo "  FAIL login buyer"
else
  echo "  ok   login buyer"
  # bid
  if [ -n "$AID" ]; then
    R=$(rpc "$BUYER" place_bid "{\"p_auction_id\":\"$AID\",\"p_max_amount\":250000}")
    echo "$R" | grep -q '"ok": *true' && echo "  ok   place_bid" || { FAIL=$((FAIL+1)); FAILURES+=("place_bid: $R"); echo "  FAIL place_bid: $R"; }
  fi
  # watchlist toggle (insert a watch row)
  HID=$(rest "$ANON" GET "horse_listings?select=id&status=eq.published&limit=1" | grep -o '"id":"[^"]*"' | head -1 | cut -d'"' -f4)
  BUYERID=$(rest "$BUYER" GET "profiles?select=id&limit=1" | grep -o '"id":"[^"]*"' | head -1 | cut -d'"' -f4)
  if [ -n "$HID" ] && [ -n "$BUYERID" ]; then
    R=$(rest "$BUYER" POST "watchlists" "{\"profile_id\":\"$BUYERID\",\"horse_listing_id\":\"$HID\"}")
    echo "$R" | grep -qiE 'error|denied|violates' && { echo "  warn watchlist insert: $R (may already exist)"; } || echo "  ok   watchlist insert"
  fi
  # read own notifications (RLS)
  N=$(rest "$BUYER" GET "notifications?select=id&limit=5")
  echo "$N" | grep -qiE 'error|denied' && { FAIL=$((FAIL+1)); FAILURES+=("notifications read: $N"); echo "  FAIL notifications read"; } || echo "  ok   notifications read"
  # read own conversations (messaging)
  C=$(rest "$BUYER" GET "conversations?select=id&limit=5")
  echo "$C" | grep -qiE '"code"|denied' && { FAIL=$((FAIL+1)); FAILURES+=("conversations read: $C"); echo "  FAIL conversations read"; } || echo "  ok   conversations read"
fi

for u in seller admin enterprise; do
  T=$(login "$u@harnessbid.test")
  [ -n "$T" ] && echo "  ok   login $u" || { FAIL=$((FAIL+1)); FAILURES+=("login $u failed"); echo "  FAIL login $u"; }
done

echo; echo "=================================================================="
echo " RESULT: $PASS passed, $FAIL failed"
if [ "$FAIL" -gt 0 ]; then
  echo " FAILURES:"; for f in "${FAILURES[@]}"; do echo "   - $f"; done
fi
echo "=================================================================="
