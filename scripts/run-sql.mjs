#!/usr/bin/env node
/**
 * Run a .sql file against the database directly (no copy-paste, no web editor).
 *
 *   DATABASE_URL="postgresql://...":  Supabase → Settings → Database →
 *   Connection string → URI (use the "Session"/direct or pooler string).
 *
 * Usage (on the box):
 *   DATABASE_URL="postgresql://postgres:[PWD]@db.<ref>.supabase.co:5432/postgres" \
 *     node scripts/run-sql.mjs supabase/seed_extra.sql
 *
 * Runs the whole file in one round-trip exactly as written — byte-for-byte —
 * so editor/paste corruption is impossible.
 */
import { readFileSync } from "node:fs"
import { Client } from "pg"

const file = process.argv[2]
if (!file) {
  console.error("usage: node scripts/run-sql.mjs <path-to.sql>")
  process.exit(2)
}
const url = process.env.DATABASE_URL
if (!url) {
  console.error("Set DATABASE_URL (Supabase → Settings → Database → Connection string → URI)")
  process.exit(2)
}

const sql = readFileSync(file, "utf8")
const client = new Client({ connectionString: url, ssl: { rejectUnauthorized: false } })

try {
  await client.connect()
  console.log(`[run-sql] connected; executing ${file} (${sql.length} bytes)…`)
  const res = await client.query(sql)
  // Print the last result set (e.g. a summary SELECT at the end of a seed).
  const last = Array.isArray(res) ? res[res.length - 1] : res
  if (last?.rows?.length) console.table(last.rows)
  console.log("[run-sql] ✓ success")
} catch (err) {
  console.error("[run-sql] ✗ FAILED:", err.message)
  if (err.position) {
    const pos = Number(err.position)
    console.error("[run-sql] near:", JSON.stringify(sql.slice(Math.max(0, pos - 60), pos + 60)))
  }
  process.exitCode = 1
} finally {
  await client.end()
}
