-- ============================================================
-- Migration 002 / Phase 06: Contract — Careers
-- Database: libraryo_wp_ainng  Prefix: DcVnchxg4_
--
-- PRECONDITIONS (verify before running):
--   1. Phase 05 spot-checks show 0 unresolved skills/gifts.
--   2. A fresh phpMyAdmin dump was taken immediately before this.
--   3. The Node.js app has been deployed with updated queries
--      (reading from career_skills/career_gifts, not from the
--       inline career_skill_* / career_gift_* columns).
--
-- This phase is NOT automatically reversible.
-- ============================================================

SET NAMES utf8mb4;

ALTER TABLE `DcVnchxg4_customtables_table_careers`
  DROP COLUMN IF EXISTS `ct_career_skill_one`,
  DROP COLUMN IF EXISTS `ct_career_skill_two`,
  DROP COLUMN IF EXISTS `ct_career_skill_three`,
  DROP COLUMN IF EXISTS `ct_career_gift_one`,
  DROP COLUMN IF EXISTS `ct_career_gift_two`,
  DROP COLUMN IF EXISTS `ct_career_gift_three`;

-- Verify
SELECT 'careers inline columns remaining' AS label, COUNT(*) AS cnt
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME   = 'DcVnchxg4_customtables_table_careers'
  AND (COLUMN_NAME LIKE 'ct_career_skill_%' OR COLUMN_NAME LIKE 'ct_career_gift_%');
