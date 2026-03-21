# Migration 002: Schema Normalisation

**Status: Planned — SQL files not yet written.**
This migration will be developed as part of Task #8 (Database schema normalisation), which begins after Tasks #4, #5, and #6 are complete.

---

## Overview

Normalise the core CustomTables tables using an **expand → migrate → contract** pattern so the live site is never broken during the work. Each phase is a separate SQL file that can be applied, tested, and rolled back independently.

## Scope

| Table area | Problem | Fix |
|---|---|---|
| `gifts` | `requires_1..19` repeated columns | Move to rows in `gift_requirements` |
| `gifts` | `type_1..8` repeated columns | Move to `gift_type_map` join table |
| `careers` | `career_skill_one/two/three`, `career_gift_one/two/three` repeated columns | Move to `career_skills`, `career_gifts` mapping tables |
| `species` | Long text trait fields | Extract to `species_traits` child table |
| All canonical tables | No audit timestamps | Add `created_at`, `updated_at` columns |
| All FK columns | No foreign key constraints | Add FK constraints and indexes |

## Phases (per table area)

### Phase 1 — Expand
Add new tables/columns alongside existing ones. Do not remove anything. The app continues to read old columns.

### Phase 2 — Migrate
Backfill new tables/columns from old data. Run idempotent (safe to re-run). Verify row counts and spot-check data before proceeding.

### Phase 3 — Contract
Update all app queries to read from new tables/columns. Drop old columns. Verify app still functions correctly.

## Files (to be added during Task #8)

```
01-expand-gifts.sql
02-migrate-gifts.sql
03-contract-gifts.sql
04-expand-careers.sql
05-migrate-careers.sql
06-contract-careers.sql
07-expand-species.sql
08-migrate-species.sql
09-contract-species.sql
10-audit-fields.sql
11-fk-constraints-and-indexes.sql
```

## Rollback strategy

- Expand phase: rollback by dropping the new tables/columns (no data loss — old columns still exist)
- Migrate phase: rollback by truncating the new tables/columns (no data loss — old data untouched)
- Contract phase: **no automatic rollback** — old columns are dropped. Restore from the phpMyAdmin dump taken before Phase 3 if needed.

Always take a fresh phpMyAdmin dump immediately before running any Phase 3 (contract) SQL.
