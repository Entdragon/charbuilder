# Migration 002: Schema Normalisation

**Status: SQL files written — ready to apply to production.**

Apply phases in order via phpMyAdmin → SQL tab. Verify spot-check output after each phase before proceeding.

---

## Overview

Normalise the core CustomTables tables using an **expand → migrate → contract** pattern so the live site is never broken during the work. Each phase is a separate SQL file that can be applied, tested, and rolled back independently.

## Scope

| Table area | Problem | Fix |
|---|---|---|
| `gifts` | `requires_1..19` repeated columns | Move to rows in `gift_requirements` |
| `gifts` | `type_1..8` repeated columns | Move to `gift_type_map` join table |
| `careers` | `career_skill_one/two/three`, `career_gift_one/two/three` repeated columns | Move to `career_skills`, `career_gifts` mapping tables |
| `species` | Inline FK trait columns | Extract to `species_traits` child table |
| All canonical tables | No audit timestamps | Add `created_at`, `updated_at` columns |
| All FK columns | No foreign key constraints | Add FK constraints and indexes |

## Files

```
01-expand-gifts.sql       — Create gift_type_map table; verify gift_requirements exists
02-migrate-gifts.sql      — Backfill gift_type_map from type_1..8; safety-net backfill gift_requirements
03-contract-gifts.sql     — Drop requires_1..19, type_1..8, requires_special_* columns from gifts
04-expand-careers.sql     — Create career_skills and career_gifts tables
05-migrate-careers.sql    — Backfill career_skills/career_gifts from inline columns
06-contract-careers.sql   — Drop career_skill_*/career_gift_* inline columns from careers
07-expand-species.sql     — Create species_traits child table
08-migrate-species.sql    — Backfill species_traits from inline FK columns on species
09-contract-species.sql   — Drop inline trait columns from species
10-audit-fields.sql       — Add created_at/updated_at to all canonical tables
11-fk-constraints-and-indexes.sql — Add FK constraints and indexes (run once; not idempotent)
```

## Node.js server query behaviour

The server code in `server/routes/actions/` uses the new normalised schema as the **primary query path**. Each endpoint retains a legacy fallback that reads old inline columns in the event the new mapping tables do not yet exist — this allows the app to be deployed before the expand phases are run without breaking the live site.

The fallback is triggered automatically when the query raises a MySQL "table doesn't exist" error. Once all contract phases have been applied and the old columns are dropped, the fallback SQL will simply never be reached.

Specifically:
- `gifts.js` — `cg_get_free_gifts` reads from `gift_requirements` (already normalised); legacy requires_* columns are no longer referenced
- `career.js` — primary query reads from `career_skills`/`career_gifts`; fallback reads `career_skill_one/two/three`/`career_gift_one/two/three`
- `species.js` — primary query reads from `species_traits`; fallback reads inline `ct_species_gift_*`/`ct_species_skill_*` etc.

## Apply order

### Gifts (safe to deploy server code first — gifts.js already reads gift_requirements)
1. Run `01-expand-gifts.sql` — confirm `gift_type_map` created
2. Run `02-migrate-gifts.sql` — check spot-check counts; fix any orphans
3. Run `03-contract-gifts.sql` — confirm 0 legacy columns remaining

### Careers
4. Run `04-expand-careers.sql` — confirm new tables created
5. Run `05-migrate-careers.sql` — check "careers skill_one unresolved = 0"
6. Deploy updated Node.js code (career.js) if not already done — fallback path keeps site live until step 7
7. Run `06-contract-careers.sql` — confirm 0 inline columns remaining

### Species
8. Run `07-expand-species.sql` — confirm `species_traits` created
9. Run `08-migrate-species.sql` — check trait counts per key
10. Deploy updated Node.js code (species.js) if not already done — fallback path keeps site live until step 11
11. Run `09-contract-species.sql` — confirm 0 inline columns remaining

### Final steps
12. Run `10-audit-fields.sql` — adds audit timestamps to all tables
13. Run `11-fk-constraints-and-indexes.sql` — adds FK constraints and indexes (run once; will error on re-run if constraints already exist)

## Rollback strategy

- Expand phase: rollback by dropping the new tables/columns (no data loss — old columns still exist)
- Migrate phase: rollback by truncating the new tables/columns (no data loss — old data untouched)
- Contract phase: **no automatic rollback** — old columns are dropped. Restore from the phpMyAdmin dump taken before Phase 3 if needed.

Always take a fresh phpMyAdmin dump immediately before running any Phase 3 (contract) SQL.
