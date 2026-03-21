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
--
-- NOTE: IF EXISTS is NOT used on DROP COLUMN because the cPanel
-- database user lacks information_schema access. All columns
-- listed here were confirmed present via SHOW COLUMNS before
-- this file was written.
-- ============================================================

SET NAMES utf8mb4;

-- ------------------------------------------------------------
-- Drop requires inline columns from gifts
-- ------------------------------------------------------------
ALTER TABLE `DcVnchxg4_customtables_table_gifts`
  DROP COLUMN `ct_gifts_requires`,
  DROP COLUMN `ct_gifts_requires_two`,
  DROP COLUMN `ct_gifts_requires_three`,
  DROP COLUMN `ct_gifts_requires_four`,
  DROP COLUMN `ct_gifts_requires_five`,
  DROP COLUMN `ct_gifts_requires_six`,
  DROP COLUMN `ct_gifts_requires_seven`,
  DROP COLUMN `ct_gifts_requires_eight`,
  DROP COLUMN `ct_gifts_requires_nine`,
  DROP COLUMN `ct_gifts_requires_ten`,
  DROP COLUMN `ct_gifts_requires_eleven`,
  DROP COLUMN `ct_gifts_requires_twelve`,
  DROP COLUMN `ct_gifts_requires_thirteen`,
  DROP COLUMN `ct_gifts_requires_fourteen`,
  DROP COLUMN `ct_gifts_requires_fifteen`,
  DROP COLUMN `ct_gifts_requires_sixteen`,
  DROP COLUMN `ct_gifts_requires_seventeen`,
  DROP COLUMN `ct_gifts_requires_eighteen`,
  DROP COLUMN `ct_gifts_requires_nineteen`;

-- ------------------------------------------------------------
-- Drop type inline columns from gifts
-- Actual column names: ct_gift_type, ct_gift_type_two … ct_gift_type_eight
-- ------------------------------------------------------------
ALTER TABLE `DcVnchxg4_customtables_table_gifts`
  DROP COLUMN `ct_gift_type`,
  DROP COLUMN `ct_gift_type_two`,
  DROP COLUMN `ct_gift_type_three`,
  DROP COLUMN `ct_gift_type_four`,
  DROP COLUMN `ct_gift_type_five`,
  DROP COLUMN `ct_gift_type_six`,
  DROP COLUMN `ct_gift_type_seven`,
  DROP COLUMN `ct_gift_type_eight`;

-- ------------------------------------------------------------
-- Drop requires_special inline columns
-- ------------------------------------------------------------
ALTER TABLE `DcVnchxg4_customtables_table_gifts`
  DROP COLUMN `ct_gifts_requires_special`,
  DROP COLUMN `ct_gifts_requires_special_two`,
  DROP COLUMN `ct_gifts_requires_special_three`,
  DROP COLUMN `ct_gifts_requires_special_four`,
  DROP COLUMN `ct_gifts_requires_special_five`,
  DROP COLUMN `ct_gifts_requires_special_six`,
  DROP COLUMN `ct_gifts_requires_special_seven`,
  DROP COLUMN `ct_gifts_requires_special_eight`;

-- Verify: remaining columns should NOT include requires_* or ct_gift_type*
-- (Does not require information_schema access)
SHOW COLUMNS FROM `DcVnchxg4_customtables_table_gifts`;
