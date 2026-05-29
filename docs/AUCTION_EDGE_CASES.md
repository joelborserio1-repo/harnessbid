# Auction Edge Cases & Integrity (Phase 21)

How the bidding engine (`place_bid` / `_close_auction` /
`process_auction_transitions`, migration `202605290004`) handles the hard cases,
and the residual risks to watch in soft launch.

| Scenario | Handling | Residual risk |
|---|---|---|
| **Simultaneous bids** | `place_bid` takes `SELECT … FOR UPDATE` on the auction row, so concurrent calls serialise; the second sees the first's `current_bid`. | None expected at marketplace scale; validate with `tests/load/bidding.js`. |
| **Proxy conflicts** | eBay-style resolution: higher max wins at `min(maxA, maxB + increment)`; ties keep the earlier leader. Single `winning` row, kept in sync with `current_bid`. | Many distinct proxy bidders untested at volume — load-test with per-VU accounts. |
| **Anti-sniping** | Bid inside `soft_close_seconds` of `ends_at` extends `ends_at = now + soft_close` and sets status `extended` — server-side, no client timers. | Repeated late bids keep extending (by design); consider a max-extension cap later. |
| **Reserve transitions** | `reserve_met` recomputed each bid; `_close_auction` only assigns a winner when reserve met (or null). | UI shows reserve state from server; fine. |
| **Auction close timing** | pg_cron / Vercel Cron runs `process_auction_transitions` (idempotent, time-gated); plus lazy `close_auction_if_ended` on view. | If no scheduler runs, close happens lazily on next view — acceptable but enable cron. |
| **Duplicate / self bids** | Leader can only raise their max (not re-bid); seller blocked via listing→owner check; below-minimum rejected. | None. |
| **Stale realtime** | `useAuctionRealtime` re-reads `current_bid`/`bid_count`/`reserve_met` on every change; the panel computes min-bid from fresh state. | If Realtime disabled, state refreshes on action/navigation only — enable Realtime. |
| **Reconnect** | Supabase channel auto-reconnects; cleanup on unmount prevents stale listeners. | Brief gap on reconnect; server validation is the source of truth, so no bad bids. |
| **Timezones** | All times are `timestamptz` (UTC) compared with `now()` in SQL; UI formats locally. | Display only; integrity is UTC-correct. |
| **Idempotent retries** | Close logic is guarded (`status in (...)` checks) so re-runs are no-ops. | None. |

## Verification

- `tests/load/bidding.js` — concurrent authenticated bids on a seeded live
  auction; assert no 5xx and a single consistent `winning` bid post-run.
- Manual: two browsers bidding on the seeded `bettor-dream-lot-1` auction —
  confirm outbid notification, anti-snipe extension, reserve badge.

## Documented weaknesses / follow-ups

1. Anti-sniping has no maximum-extension ceiling.
2. Rate limiting on bids is per-instance (Phase 20) until backed by a shared store.
3. No bid retraction/admin reversal flow (intentional for now).
4. Settlement (`settled` status, payments) is deferred to the payments build.
