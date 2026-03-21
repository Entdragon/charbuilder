-- ============================================================
-- Migration 002 / Phase 09: Contract — Species
-- Database: libraryo_wp_ainng  Prefix: DcVnchxg4_
--
-- PRECONDITIONS (verify before running):
--   1. Phase 08 spot-checks look correct.
--   2. A fresh phpMyAdmin dump was taken immediately before this.
--   3. The Node.js app has been deployed with updated queries
--      reading from species_traits, not inline FK columns.
--
-- This phase is NOT automatically reversible.
--
-- NOTE: IF EXISTS removed from DROP COLUMN — cPanel DB user
-- lacks information_schema access required for that syntax.
-- ============================================================

SET NAMES utf8mb4;

ALTER TABLE `DcVnchxg4_customtables_table_species`
  DROP COLUMN `ct_species_gift_one`,
  DROP COLUMN `ct_species_gift_two`,
  DROP COLUMN `ct_species_gift_three`,
  DROP COLUMN `ct_species_skill_one`,
  DROP COLUMN `ct_species_skill_two`,
  DROP COLUMN `ct_species_skill_three`,
  DROP COLUMN `ct_species_habitat`,
  DROP COLUMN `ct_species_diet`,
  DROP COLUMN `ct_species_cycle`,
  DROP COLUMN `ct_species_senses_one`,
  DROP COLUMN `ct_species_senses_two`,
  DROP COLUMN `ct_species_senses_three`,
  DROP COLUMN `ct_species_weapon_one`,
  DROP COLUMN `ct_species_weapon_two`,
  DROP COLUMN `ct_species_weapon_three`;

-- Verify: check remaining columns
SHOW COLUMNS FROM `DcVnchxg4_customtables_table_species`;
