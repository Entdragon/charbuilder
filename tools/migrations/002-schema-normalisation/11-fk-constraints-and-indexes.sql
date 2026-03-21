-- ============================================================
-- Migration 002 / Phase 11: Indexes
-- Database: libraryo_wp_ainng  Prefix: DcVnchxg4_
--
-- Adds indexes on all FK and name/slug columns for query performance.
--
-- FK constraints have been intentionally omitted: CustomTables
-- manages its own inserts/deletes and is unaware of FK constraints,
-- which would cause insert failures if constraints were added.
--
-- Run AFTER all expand/migrate/contract phases and audit fields.
--
-- Each index is a separate ALTER TABLE statement so that a single
-- failure does not block the remaining indexes.
-- If an index name already exists, that statement will error —
-- skip it and continue with the rest.
--
-- trappings / trappings_map tables do not exist — skipped.
-- ============================================================

SET NAMES utf8mb4;

-- ------------------------------------------------------------
-- gift_type_map
-- ------------------------------------------------------------
ALTER TABLE `DcVnchxg4_customtables_table_gift_type_map`
  ADD INDEX `idx_gtm_gift_id` (`gift_id`);

ALTER TABLE `DcVnchxg4_customtables_table_gift_type_map`
  ADD INDEX `idx_gtm_type_id` (`type_id`);

-- ------------------------------------------------------------
-- gift_requirements
-- ------------------------------------------------------------
ALTER TABLE `DcVnchxg4_customtables_table_gift_requirements`
  ADD INDEX `idx_greq_gift_id` (`ct_gift_id`);

-- ------------------------------------------------------------
-- gift_sections
-- ------------------------------------------------------------
ALTER TABLE `DcVnchxg4_customtables_table_gift_sections`
  ADD INDEX `idx_gsec_gift_id` (`ct_gift_id`);

-- ------------------------------------------------------------
-- gift_rules
-- ------------------------------------------------------------
ALTER TABLE `DcVnchxg4_customtables_table_gift_rules`
  ADD INDEX `idx_grules_gift_id` (`ct_gift_id`);

-- ------------------------------------------------------------
-- gift_triggers
-- ------------------------------------------------------------
ALTER TABLE `DcVnchxg4_customtables_table_gift_triggers`
  ADD INDEX `idx_gtrig_gift_id` (`ct_gift_id`);

-- ------------------------------------------------------------
-- career_skills
-- ------------------------------------------------------------
ALTER TABLE `DcVnchxg4_customtables_table_career_skills`
  ADD INDEX `idx_cs_career_id` (`career_id`);

ALTER TABLE `DcVnchxg4_customtables_table_career_skills`
  ADD INDEX `idx_cs_skill_id` (`skill_id`);

-- ------------------------------------------------------------
-- career_gifts
-- ------------------------------------------------------------
ALTER TABLE `DcVnchxg4_customtables_table_career_gifts`
  ADD INDEX `idx_cg_career_id` (`career_id`);

ALTER TABLE `DcVnchxg4_customtables_table_career_gifts`
  ADD INDEX `idx_cg_gift_id` (`gift_id`);

-- ------------------------------------------------------------
-- species_traits
-- ------------------------------------------------------------
ALTER TABLE `DcVnchxg4_customtables_table_species_traits`
  ADD INDEX `idx_st_species_id` (`species_id`);

ALTER TABLE `DcVnchxg4_customtables_table_species_traits`
  ADD INDEX `idx_st_trait_key` (`trait_key`);

-- ------------------------------------------------------------
-- Name lookup indexes on core tables
-- ------------------------------------------------------------
ALTER TABLE `DcVnchxg4_customtables_table_gifts`
  ADD INDEX `idx_gifts_name` (`ct_gifts_name`(100));

ALTER TABLE `DcVnchxg4_customtables_table_careers`
  ADD INDEX `idx_careers_name` (`ct_career_name`(100));

ALTER TABLE `DcVnchxg4_customtables_table_species`
  ADD INDEX `idx_species_name` (`ct_species_name`(100));

ALTER TABLE `DcVnchxg4_customtables_table_skills`
  ADD INDEX `idx_skills_name` (`ct_skill_name`(100));

-- ------------------------------------------------------------
-- Verify: run these individually to confirm indexes were added
-- ------------------------------------------------------------
SHOW INDEX FROM `DcVnchxg4_customtables_table_gift_type_map`;
SHOW INDEX FROM `DcVnchxg4_customtables_table_gift_requirements`;
SHOW INDEX FROM `DcVnchxg4_customtables_table_career_skills`;
SHOW INDEX FROM `DcVnchxg4_customtables_table_career_gifts`;
SHOW INDEX FROM `DcVnchxg4_customtables_table_species_traits`;
