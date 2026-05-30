---
name: UJ junction-table rebuild over the dev proxy
description: Why uj_build_joins_internal must never be run as-is through the Replit dev DB proxy, and the safe fast path.
---

# Urban Jungle junction rebuild — proxy danger

`uj_build_joins_internal()` does `DELETE FROM` on all 7 UJ junction tables, then
INSERTs one row per `cg_exec` call. In the Replit dev environment every DB call is
an HTTP round-trip to the WordPress proxy (`cg-db-proxy.php`), and **that proxy
points at the LIVE production database** — dev and live share one DB.

**Why this is dangerous:** running the full rebuild over the proxy takes 10+ min
(hundreds of round-trips) and frequently gets killed (workflow restart, timeout)
*after* the truncate but *before* the re-inserts land — leaving the production
junction tables empty and the builder broken for everyone.

**How to apply:** Never trigger `uj_build_joins` / `uj_build_joins_internal` from
the dev environment expecting it to finish. Use `tools/uj-rebuild-joins.php`
instead — it replicates the same resolution logic but batches each junction table
into a single multi-row `INSERT IGNORE` (~20 proxy calls total, ~20s). On the live
cPanel server (direct PDO) the original per-row function is fine.
