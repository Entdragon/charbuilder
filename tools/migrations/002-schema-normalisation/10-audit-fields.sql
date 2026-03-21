-- ============================================================
-- Migration 002 / Phase 10: Audit Fields
-- Database: libraryo_wp_ainng  Prefix: DcVnchxg4_
--
-- Adds created_at and updated_at timestamp columns to all
-- affected canonical tables.
--
-- Safe to re-run: uses ADD COLUMN IF NOT EXISTS (MySQL 8+).
-- For MySQL 5.7: wrap in stored procedure or run manually.
-- ============================================================

SET NAMES utf8mb4;

-- ------------------------------------------------------------
-- gifts
-- ------------------------------------------------------------
ALTER TABLE `DcVnchxg4_customtables_table_gifts`
  ADD COLUMN IF NOT EXISTS `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  ADD COLUMN IF NOT EXISTS `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- ------------------------------------------------------------
-- gift_requirements
-- ------------------------------------------------------------
ALTER TABLE `DcVnchxg4_customtables_table_gift_requirements`
  ADD COLUMN IF NOT EXISTS `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  ADD COLUMN IF NOT EXISTS `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- ------------------------------------------------------------
-- gift_type_map (new table — already has no audit cols)
-- ------------------------------------------------------------
ALTER TABLE `DcVnchxg4_customtables_table_gift_type_map`
  ADD COLUMN IF NOT EXISTS `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  ADD COLUMN IF NOT EXISTS `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- ------------------------------------------------------------
-- gift_sections
-- ------------------------------------------------------------
ALTER TABLE `DcVnchxg4_customtables_table_gift_sections`
  ADD COLUMN IF NOT EXISTS `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  ADD COLUMN IF NOT EXISTS `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- ------------------------------------------------------------
-- gift_rules
-- ------------------------------------------------------------
ALTER TABLE `DcVnchxg4_customtables_table_gift_rules`
  ADD COLUMN IF NOT EXISTS `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  ADD COLUMN IF NOT EXISTS `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- ------------------------------------------------------------
-- gift_triggers
-- ------------------------------------------------------------
ALTER TABLE `DcVnchxg4_customtables_table_gift_triggers`
  ADD COLUMN IF NOT EXISTS `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  ADD COLUMN IF NOT EXISTS `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- ------------------------------------------------------------
-- careers
-- ------------------------------------------------------------
ALTER TABLE `DcVnchxg4_customtables_table_careers`
  ADD COLUMN IF NOT EXISTS `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  ADD COLUMN IF NOT EXISTS `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- ------------------------------------------------------------
-- career_skills (new table)
-- ------------------------------------------------------------
ALTER TABLE `DcVnchxg4_customtables_table_career_skills`
  ADD COLUMN IF NOT EXISTS `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  ADD COLUMN IF NOT EXISTS `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- ------------------------------------------------------------
-- career_gifts (new table)
-- ------------------------------------------------------------
ALTER TABLE `DcVnchxg4_customtables_table_career_gifts`
  ADD COLUMN IF NOT EXISTS `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  ADD COLUMN IF NOT EXISTS `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- ------------------------------------------------------------
-- species
-- ------------------------------------------------------------
ALTER TABLE `DcVnchxg4_customtables_table_species`
  ADD COLUMN IF NOT EXISTS `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  ADD COLUMN IF NOT EXISTS `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- ------------------------------------------------------------
-- species_traits (new table)
-- ------------------------------------------------------------
ALTER TABLE `DcVnchxg4_customtables_table_species_traits`
  ADD COLUMN IF NOT EXISTS `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  ADD COLUMN IF NOT EXISTS `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- ------------------------------------------------------------
-- books
-- ------------------------------------------------------------
ALTER TABLE `DcVnchxg4_customtables_table_books`
  ADD COLUMN IF NOT EXISTS `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  ADD COLUMN IF NOT EXISTS `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- ------------------------------------------------------------
-- equipment
-- ------------------------------------------------------------
ALTER TABLE `DcVnchxg4_customtables_table_equipment`
  ADD COLUMN IF NOT EXISTS `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  ADD COLUMN IF NOT EXISTS `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- ------------------------------------------------------------
-- weapons
-- ------------------------------------------------------------
ALTER TABLE `DcVnchxg4_customtables_table_weapons`
  ADD COLUMN IF NOT EXISTS `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  ADD COLUMN IF NOT EXISTS `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- ------------------------------------------------------------
-- skills
-- ------------------------------------------------------------
ALTER TABLE `DcVnchxg4_customtables_table_skills`
  ADD COLUMN IF NOT EXISTS `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  ADD COLUMN IF NOT EXISTS `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- ------------------------------------------------------------
-- trappings / trappings_map (if exists)
-- ------------------------------------------------------------
ALTER TABLE `DcVnchxg4_customtables_table_trappings`
  ADD COLUMN IF NOT EXISTS `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  ADD COLUMN IF NOT EXISTS `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;
