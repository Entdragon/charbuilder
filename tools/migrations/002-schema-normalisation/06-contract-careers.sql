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
--
-- NOTE: IF EXISTS removed from DROP COLUMN — cPanel DB user
-- lacks information_schema access required for that syntax.
-- ============================================================

SET NAMES utf8mb4;

ALTER TABLE `DcVnchxg4_customtables_table_careers`
  DROP COLUMN `ct_career_skill_one`,
  DROP COLUMN `ct_career_skill_two`,
  DROP COLUMN `ct_career_skill_three`,
  DROP COLUMN `ct_career_gift_one`,
  DROP COLUMN `ct_career_gift_two`,
  DROP COLUMN `ct_career_gift_three`;

-- Verify: check remaining columns
SHOW COLUMNS FROM `DcVnchxg4_customtables_table_careers`;
