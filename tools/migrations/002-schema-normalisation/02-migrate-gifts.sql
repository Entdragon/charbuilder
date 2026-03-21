-- ============================================================
-- Migration 002 / Phase 02: Migrate — Gifts
-- Database: libraryo_wp_ainng  Prefix: DcVnchxg4_
--
-- Backfills:
--   • gift_type_map  ← ct_gift_type … ct_gift_type_eight on gifts
--   • gift_requirements should already be populated; this
--     script verifies coverage and inserts any missing rows
--     from requires … requires_nineteen as a safety net.
--
-- Safe to re-run: uses INSERT IGNORE throughout.
-- ============================================================

SET NAMES utf8mb4;

-- ============================================================
-- PART A: Backfill gift_type_map from ct_gift_type … ct_gift_type_eight
-- Actual column names differ from the original estimate:
--   ct_gift_type, ct_gift_type_two … ct_gift_type_eight
-- ============================================================

INSERT IGNORE INTO `DcVnchxg4_customtables_table_gift_type_map`
  (gift_id, type_id, sort)
SELECT ct_id, ct_gift_type,       1 FROM `DcVnchxg4_customtables_table_gifts` WHERE ct_gift_type       IS NOT NULL AND ct_gift_type       != 0;

INSERT IGNORE INTO `DcVnchxg4_customtables_table_gift_type_map`
  (gift_id, type_id, sort)
SELECT ct_id, ct_gift_type_two,   2 FROM `DcVnchxg4_customtables_table_gifts` WHERE ct_gift_type_two   IS NOT NULL AND ct_gift_type_two   != 0;

INSERT IGNORE INTO `DcVnchxg4_customtables_table_gift_type_map`
  (gift_id, type_id, sort)
SELECT ct_id, ct_gift_type_three, 3 FROM `DcVnchxg4_customtables_table_gifts` WHERE ct_gift_type_three IS NOT NULL AND ct_gift_type_three != 0;

INSERT IGNORE INTO `DcVnchxg4_customtables_table_gift_type_map`
  (gift_id, type_id, sort)
SELECT ct_id, ct_gift_type_four,  4 FROM `DcVnchxg4_customtables_table_gifts` WHERE ct_gift_type_four  IS NOT NULL AND ct_gift_type_four  != 0;

INSERT IGNORE INTO `DcVnchxg4_customtables_table_gift_type_map`
  (gift_id, type_id, sort)
SELECT ct_id, ct_gift_type_five,  5 FROM `DcVnchxg4_customtables_table_gifts` WHERE ct_gift_type_five  IS NOT NULL AND ct_gift_type_five  != 0;

INSERT IGNORE INTO `DcVnchxg4_customtables_table_gift_type_map`
  (gift_id, type_id, sort)
SELECT ct_id, ct_gift_type_six,   6 FROM `DcVnchxg4_customtables_table_gifts` WHERE ct_gift_type_six   IS NOT NULL AND ct_gift_type_six   != 0;

INSERT IGNORE INTO `DcVnchxg4_customtables_table_gift_type_map`
  (gift_id, type_id, sort)
SELECT ct_id, ct_gift_type_seven, 7 FROM `DcVnchxg4_customtables_table_gifts` WHERE ct_gift_type_seven IS NOT NULL AND ct_gift_type_seven != 0;

INSERT IGNORE INTO `DcVnchxg4_customtables_table_gift_type_map`
  (gift_id, type_id, sort)
SELECT ct_id, ct_gift_type_eight, 8 FROM `DcVnchxg4_customtables_table_gifts` WHERE ct_gift_type_eight IS NOT NULL AND ct_gift_type_eight != 0;

-- Spot-check: show type_map row count
SELECT 'gift_type_map total rows' AS label, COUNT(*) AS cnt
FROM `DcVnchxg4_customtables_table_gift_type_map`;

-- ============================================================
-- PART B: Safety-net backfill of gift_requirements
--         from requires … requires_nineteen (int FKs)
--
-- Only inserts rows that do not already exist (by gift_id + sort).
-- ============================================================

INSERT IGNORE INTO `DcVnchxg4_customtables_table_gift_requirements`
  (ct_gift_id, ct_sort, ct_req_kind, ct_req_text)
SELECT ct_id,  1, 'legacy_text', ct_gifts_requires
FROM `DcVnchxg4_customtables_table_gifts`
WHERE ct_gifts_requires IS NOT NULL AND ct_gifts_requires != 0
  AND NOT EXISTS (
    SELECT 1 FROM `DcVnchxg4_customtables_table_gift_requirements` r
    WHERE r.ct_gift_id = ct_id AND r.ct_sort = 1
  );

INSERT IGNORE INTO `DcVnchxg4_customtables_table_gift_requirements`
  (ct_gift_id, ct_sort, ct_req_kind, ct_req_text)
SELECT ct_id,  2, 'legacy_text', ct_gifts_requires_two
FROM `DcVnchxg4_customtables_table_gifts`
WHERE ct_gifts_requires_two IS NOT NULL AND ct_gifts_requires_two != 0
  AND NOT EXISTS (
    SELECT 1 FROM `DcVnchxg4_customtables_table_gift_requirements` r
    WHERE r.ct_gift_id = ct_id AND r.ct_sort = 2
  );

INSERT IGNORE INTO `DcVnchxg4_customtables_table_gift_requirements`
  (ct_gift_id, ct_sort, ct_req_kind, ct_req_text)
SELECT ct_id,  3, 'legacy_text', ct_gifts_requires_three
FROM `DcVnchxg4_customtables_table_gifts`
WHERE ct_gifts_requires_three IS NOT NULL AND ct_gifts_requires_three != 0
  AND NOT EXISTS (
    SELECT 1 FROM `DcVnchxg4_customtables_table_gift_requirements` r
    WHERE r.ct_gift_id = ct_id AND r.ct_sort = 3
  );

INSERT IGNORE INTO `DcVnchxg4_customtables_table_gift_requirements`
  (ct_gift_id, ct_sort, ct_req_kind, ct_req_text)
SELECT ct_id,  4, 'legacy_text', ct_gifts_requires_four
FROM `DcVnchxg4_customtables_table_gifts`
WHERE ct_gifts_requires_four IS NOT NULL AND ct_gifts_requires_four != 0
  AND NOT EXISTS (
    SELECT 1 FROM `DcVnchxg4_customtables_table_gift_requirements` r
    WHERE r.ct_gift_id = ct_id AND r.ct_sort = 4
  );

INSERT IGNORE INTO `DcVnchxg4_customtables_table_gift_requirements`
  (ct_gift_id, ct_sort, ct_req_kind, ct_req_text)
SELECT ct_id,  5, 'legacy_text', ct_gifts_requires_five
FROM `DcVnchxg4_customtables_table_gifts`
WHERE ct_gifts_requires_five IS NOT NULL AND ct_gifts_requires_five != 0
  AND NOT EXISTS (
    SELECT 1 FROM `DcVnchxg4_customtables_table_gift_requirements` r
    WHERE r.ct_gift_id = ct_id AND r.ct_sort = 5
  );

INSERT IGNORE INTO `DcVnchxg4_customtables_table_gift_requirements`
  (ct_gift_id, ct_sort, ct_req_kind, ct_req_text)
SELECT ct_id,  6, 'legacy_text', ct_gifts_requires_six
FROM `DcVnchxg4_customtables_table_gifts`
WHERE ct_gifts_requires_six IS NOT NULL AND ct_gifts_requires_six != 0
  AND NOT EXISTS (
    SELECT 1 FROM `DcVnchxg4_customtables_table_gift_requirements` r
    WHERE r.ct_gift_id = ct_id AND r.ct_sort = 6
  );

INSERT IGNORE INTO `DcVnchxg4_customtables_table_gift_requirements`
  (ct_gift_id, ct_sort, ct_req_kind, ct_req_text)
SELECT ct_id,  7, 'legacy_text', ct_gifts_requires_seven
FROM `DcVnchxg4_customtables_table_gifts`
WHERE ct_gifts_requires_seven IS NOT NULL AND ct_gifts_requires_seven != 0
  AND NOT EXISTS (
    SELECT 1 FROM `DcVnchxg4_customtables_table_gift_requirements` r
    WHERE r.ct_gift_id = ct_id AND r.ct_sort = 7
  );

INSERT IGNORE INTO `DcVnchxg4_customtables_table_gift_requirements`
  (ct_gift_id, ct_sort, ct_req_kind, ct_req_text)
SELECT ct_id,  8, 'legacy_text', ct_gifts_requires_eight
FROM `DcVnchxg4_customtables_table_gifts`
WHERE ct_gifts_requires_eight IS NOT NULL AND ct_gifts_requires_eight != 0
  AND NOT EXISTS (
    SELECT 1 FROM `DcVnchxg4_customtables_table_gift_requirements` r
    WHERE r.ct_gift_id = ct_id AND r.ct_sort = 8
  );

INSERT IGNORE INTO `DcVnchxg4_customtables_table_gift_requirements`
  (ct_gift_id, ct_sort, ct_req_kind, ct_req_text)
SELECT ct_id,  9, 'legacy_text', ct_gifts_requires_nine
FROM `DcVnchxg4_customtables_table_gifts`
WHERE ct_gifts_requires_nine IS NOT NULL AND ct_gifts_requires_nine != 0
  AND NOT EXISTS (
    SELECT 1 FROM `DcVnchxg4_customtables_table_gift_requirements` r
    WHERE r.ct_gift_id = ct_id AND r.ct_sort = 9
  );

INSERT IGNORE INTO `DcVnchxg4_customtables_table_gift_requirements`
  (ct_gift_id, ct_sort, ct_req_kind, ct_req_text)
SELECT ct_id, 10, 'legacy_text', ct_gifts_requires_ten
FROM `DcVnchxg4_customtables_table_gifts`
WHERE ct_gifts_requires_ten IS NOT NULL AND ct_gifts_requires_ten != 0
  AND NOT EXISTS (
    SELECT 1 FROM `DcVnchxg4_customtables_table_gift_requirements` r
    WHERE r.ct_gift_id = ct_id AND r.ct_sort = 10
  );

INSERT IGNORE INTO `DcVnchxg4_customtables_table_gift_requirements`
  (ct_gift_id, ct_sort, ct_req_kind, ct_req_text)
SELECT ct_id, 11, 'legacy_text', ct_gifts_requires_eleven
FROM `DcVnchxg4_customtables_table_gifts`
WHERE ct_gifts_requires_eleven IS NOT NULL AND ct_gifts_requires_eleven != 0
  AND NOT EXISTS (
    SELECT 1 FROM `DcVnchxg4_customtables_table_gift_requirements` r
    WHERE r.ct_gift_id = ct_id AND r.ct_sort = 11
  );

INSERT IGNORE INTO `DcVnchxg4_customtables_table_gift_requirements`
  (ct_gift_id, ct_sort, ct_req_kind, ct_req_text)
SELECT ct_id, 12, 'legacy_text', ct_gifts_requires_twelve
FROM `DcVnchxg4_customtables_table_gifts`
WHERE ct_gifts_requires_twelve IS NOT NULL AND ct_gifts_requires_twelve != 0
  AND NOT EXISTS (
    SELECT 1 FROM `DcVnchxg4_customtables_table_gift_requirements` r
    WHERE r.ct_gift_id = ct_id AND r.ct_sort = 12
  );

INSERT IGNORE INTO `DcVnchxg4_customtables_table_gift_requirements`
  (ct_gift_id, ct_sort, ct_req_kind, ct_req_text)
SELECT ct_id, 13, 'legacy_text', ct_gifts_requires_thirteen
FROM `DcVnchxg4_customtables_table_gifts`
WHERE ct_gifts_requires_thirteen IS NOT NULL AND ct_gifts_requires_thirteen != 0
  AND NOT EXISTS (
    SELECT 1 FROM `DcVnchxg4_customtables_table_gift_requirements` r
    WHERE r.ct_gift_id = ct_id AND r.ct_sort = 13
  );

INSERT IGNORE INTO `DcVnchxg4_customtables_table_gift_requirements`
  (ct_gift_id, ct_sort, ct_req_kind, ct_req_text)
SELECT ct_id, 14, 'legacy_text', ct_gifts_requires_fourteen
FROM `DcVnchxg4_customtables_table_gifts`
WHERE ct_gifts_requires_fourteen IS NOT NULL AND ct_gifts_requires_fourteen != 0
  AND NOT EXISTS (
    SELECT 1 FROM `DcVnchxg4_customtables_table_gift_requirements` r
    WHERE r.ct_gift_id = ct_id AND r.ct_sort = 14
  );

INSERT IGNORE INTO `DcVnchxg4_customtables_table_gift_requirements`
  (ct_gift_id, ct_sort, ct_req_kind, ct_req_text)
SELECT ct_id, 15, 'legacy_text', ct_gifts_requires_fifteen
FROM `DcVnchxg4_customtables_table_gifts`
WHERE ct_gifts_requires_fifteen IS NOT NULL AND ct_gifts_requires_fifteen != 0
  AND NOT EXISTS (
    SELECT 1 FROM `DcVnchxg4_customtables_table_gift_requirements` r
    WHERE r.ct_gift_id = ct_id AND r.ct_sort = 15
  );

INSERT IGNORE INTO `DcVnchxg4_customtables_table_gift_requirements`
  (ct_gift_id, ct_sort, ct_req_kind, ct_req_text)
SELECT ct_id, 16, 'legacy_text', ct_gifts_requires_sixteen
FROM `DcVnchxg4_customtables_table_gifts`
WHERE ct_gifts_requires_sixteen IS NOT NULL AND ct_gifts_requires_sixteen != 0
  AND NOT EXISTS (
    SELECT 1 FROM `DcVnchxg4_customtables_table_gift_requirements` r
    WHERE r.ct_gift_id = ct_id AND r.ct_sort = 16
  );

INSERT IGNORE INTO `DcVnchxg4_customtables_table_gift_requirements`
  (ct_gift_id, ct_sort, ct_req_kind, ct_req_text)
SELECT ct_id, 17, 'legacy_text', ct_gifts_requires_seventeen
FROM `DcVnchxg4_customtables_table_gifts`
WHERE ct_gifts_requires_seventeen IS NOT NULL AND ct_gifts_requires_seventeen != 0
  AND NOT EXISTS (
    SELECT 1 FROM `DcVnchxg4_customtables_table_gift_requirements` r
    WHERE r.ct_gift_id = ct_id AND r.ct_sort = 17
  );

INSERT IGNORE INTO `DcVnchxg4_customtables_table_gift_requirements`
  (ct_gift_id, ct_sort, ct_req_kind, ct_req_text)
SELECT ct_id, 18, 'legacy_text', ct_gifts_requires_eighteen
FROM `DcVnchxg4_customtables_table_gifts`
WHERE ct_gifts_requires_eighteen IS NOT NULL AND ct_gifts_requires_eighteen != 0
  AND NOT EXISTS (
    SELECT 1 FROM `DcVnchxg4_customtables_table_gift_requirements` r
    WHERE r.ct_gift_id = ct_id AND r.ct_sort = 18
  );

INSERT IGNORE INTO `DcVnchxg4_customtables_table_gift_requirements`
  (ct_gift_id, ct_sort, ct_req_kind, ct_req_text)
SELECT ct_id, 19, 'legacy_text', ct_gifts_requires_nineteen
FROM `DcVnchxg4_customtables_table_gifts`
WHERE ct_gifts_requires_nineteen IS NOT NULL AND ct_gifts_requires_nineteen != 0
  AND NOT EXISTS (
    SELECT 1 FROM `DcVnchxg4_customtables_table_gift_requirements` r
    WHERE r.ct_gift_id = ct_id AND r.ct_sort = 19
  );

-- Spot-check: total requirements rows
SELECT 'gift_requirements total rows' AS label, COUNT(*) AS cnt
FROM `DcVnchxg4_customtables_table_gift_requirements`;

-- Consistency check: gifts with type data but no gift_type_map row
SELECT 'gifts with types but no map row' AS label, COUNT(*) AS cnt
FROM `DcVnchxg4_customtables_table_gifts` g
WHERE (ct_gift_type IS NOT NULL AND ct_gift_type != 0)
  AND NOT EXISTS (
    SELECT 1 FROM `DcVnchxg4_customtables_table_gift_type_map` m
    WHERE m.gift_id = g.ct_id
  );
