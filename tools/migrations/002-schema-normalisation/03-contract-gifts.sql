-- ============================================================
-- Migration 002 / Phase 03: Contract — Gifts
-- Database: libraryo_wp_ainng  Prefix: DcVnchxg4_
--
-- PRECONDITIONS (verify before running):
--   1. Phase 02 consistency checks returned 0 problem rows.
--   2. A fresh phpMyAdmin dump was taken immediately before this.
--   3. The Node.js app has been deployed with updated queries
--      (reading from gift_type_map and gift_requirements, not
--       from the inline type_* / requires_* columns).
--
-- This phase is NOT automatically reversible.
-- Restore from the pre-phase-03 dump if rollback is needed.
-- ============================================================

SET NAMES utf8mb4;

-- ------------------------------------------------------------
-- Drop requires_1 … requires_19 inline columns from gifts
-- Actual column names in the table use the full word suffixes.
-- ------------------------------------------------------------
ALTER TABLE `DcVnchxg4_customtables_table_gifts`
  DROP COLUMN IF EXISTS `ct_gifts_requires`,
  DROP COLUMN IF EXISTS `ct_gifts_requires_two`,
  DROP COLUMN IF EXISTS `ct_gifts_requires_three`,
  DROP COLUMN IF EXISTS `ct_gifts_requires_four`,
  DROP COLUMN IF EXISTS `ct_gifts_requires_five`,
  DROP COLUMN IF EXISTS `ct_gifts_requires_six`,
  DROP COLUMN IF EXISTS `ct_gifts_requires_seven`,
  DROP COLUMN IF EXISTS `ct_gifts_requires_eight`,
  DROP COLUMN IF EXISTS `ct_gifts_requires_nine`,
  DROP COLUMN IF EXISTS `ct_gifts_requires_ten`,
  DROP COLUMN IF EXISTS `ct_gifts_requires_eleven`,
  DROP COLUMN IF EXISTS `ct_gifts_requires_twelve`,
  DROP COLUMN IF EXISTS `ct_gifts_requires_thirteen`,
  DROP COLUMN IF EXISTS `ct_gifts_requires_fourteen`,
  DROP COLUMN IF EXISTS `ct_gifts_requires_fifteen`,
  DROP COLUMN IF EXISTS `ct_gifts_requires_sixteen`,
  DROP COLUMN IF EXISTS `ct_gifts_requires_seventeen`,
  DROP COLUMN IF EXISTS `ct_gifts_requires_eighteen`,
  DROP COLUMN IF EXISTS `ct_gifts_requires_nineteen`;

-- ------------------------------------------------------------
-- Drop type_1 … type_8 inline columns from gifts
-- ------------------------------------------------------------
ALTER TABLE `DcVnchxg4_customtables_table_gifts`
  DROP COLUMN IF EXISTS `ct_gifts_type_1`,
  DROP COLUMN IF EXISTS `ct_gifts_type_2`,
  DROP COLUMN IF EXISTS `ct_gifts_type_3`,
  DROP COLUMN IF EXISTS `ct_gifts_type_4`,
  DROP COLUMN IF EXISTS `ct_gifts_type_5`,
  DROP COLUMN IF EXISTS `ct_gifts_type_6`,
  DROP COLUMN IF EXISTS `ct_gifts_type_7`,
  DROP COLUMN IF EXISTS `ct_gifts_type_8`;

-- Drop requires_special inline columns (legacy free-text, superseded by gift_requirements)
ALTER TABLE `DcVnchxg4_customtables_table_gifts`
  DROP COLUMN IF EXISTS `ct_gifts_requires_special`,
  DROP COLUMN IF EXISTS `ct_gifts_requires_special_two`,
  DROP COLUMN IF EXISTS `ct_gifts_requires_special_three`,
  DROP COLUMN IF EXISTS `ct_gifts_requires_special_four`,
  DROP COLUMN IF EXISTS `ct_gifts_requires_special_five`,
  DROP COLUMN IF EXISTS `ct_gifts_requires_special_six`,
  DROP COLUMN IF EXISTS `ct_gifts_requires_special_seven`,
  DROP COLUMN IF EXISTS `ct_gifts_requires_special_eight`;

-- Verify columns are gone
SELECT 'gifts columns remaining' AS label, COUNT(*) AS cnt
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME   = 'DcVnchxg4_customtables_table_gifts'
  AND (COLUMN_NAME LIKE 'ct_gifts_requires%' OR COLUMN_NAME LIKE 'ct_gifts_type_%');
