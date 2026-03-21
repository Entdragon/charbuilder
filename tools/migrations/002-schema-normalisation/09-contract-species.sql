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
-- ============================================================

SET NAMES utf8mb4;

ALTER TABLE `DcVnchxg4_customtables_table_species`
  DROP COLUMN IF EXISTS `ct_species_gift_one`,
  DROP COLUMN IF EXISTS `ct_species_gift_two`,
  DROP COLUMN IF EXISTS `ct_species_gift_three`,
  DROP COLUMN IF EXISTS `ct_species_skill_one`,
  DROP COLUMN IF EXISTS `ct_species_skill_two`,
  DROP COLUMN IF EXISTS `ct_species_skill_three`,
  DROP COLUMN IF EXISTS `ct_species_habitat`,
  DROP COLUMN IF EXISTS `ct_species_diet`,
  DROP COLUMN IF EXISTS `ct_species_cycle`,
  DROP COLUMN IF EXISTS `ct_species_senses_one`,
  DROP COLUMN IF EXISTS `ct_species_senses_two`,
  DROP COLUMN IF EXISTS `ct_species_senses_three`,
  DROP COLUMN IF EXISTS `ct_species_weapon_one`,
  DROP COLUMN IF EXISTS `ct_species_weapon_two`,
  DROP COLUMN IF EXISTS `ct_species_weapon_three`;

-- Verify
SELECT 'species inline trait columns remaining' AS label, COUNT(*) AS cnt
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME   = 'DcVnchxg4_customtables_table_species'
  AND (   COLUMN_NAME LIKE 'ct_species_gift_%'
       OR COLUMN_NAME LIKE 'ct_species_skill_%'
       OR COLUMN_NAME LIKE 'ct_species_habitat'
       OR COLUMN_NAME LIKE 'ct_species_diet'
       OR COLUMN_NAME LIKE 'ct_species_cycle'
       OR COLUMN_NAME LIKE 'ct_species_senses_%'
       OR COLUMN_NAME LIKE 'ct_species_weapon_%');
