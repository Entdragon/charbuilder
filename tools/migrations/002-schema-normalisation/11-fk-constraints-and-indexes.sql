-- ============================================================
-- Migration 002 / Phase 11: FK Constraints and Indexes
-- Database: libraryo_wp_ainng  Prefix: DcVnchxg4_
--
-- Adds FK constraints and missing indexes on all FK columns
-- and slug fields across the normalised tables.
--
-- Run AFTER all expand/migrate/contract phases and audit fields.
--
-- IMPORTANT: This file is designed to be run ONCE. The ADD INDEX
-- and ADD CONSTRAINT statements will error if the index or
-- constraint already exists. If you need to re-run, check
-- information_schema first and skip already-applied statements.
--
-- NOTE: If the data contains orphan rows (FK violations) the FK
-- constraint statements will fail. Run the consistency queries
-- at the bottom of Phase 02 and 05 first to confirm 0 orphans.
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- INDEXES (all FK columns, plus name/slug fields)
-- Skip any ALTER below if the index already exists.
-- ============================================================

-- gift_type_map
ALTER TABLE `DcVnchxg4_customtables_table_gift_type_map`
  ADD INDEX `idx_gtm_gift_id` (`gift_id`),
  ADD INDEX `idx_gtm_type_id` (`type_id`);

-- gift_requirements
ALTER TABLE `DcVnchxg4_customtables_table_gift_requirements`
  ADD INDEX `idx_greq_gift_id` (`ct_gift_id`);

-- gift_sections
ALTER TABLE `DcVnchxg4_customtables_table_gift_sections`
  ADD INDEX `idx_gsec_gift_id` (`ct_gift_id`);

-- gift_rules
ALTER TABLE `DcVnchxg4_customtables_table_gift_rules`
  ADD INDEX `idx_grules_gift_id` (`ct_gift_id`);

-- gift_triggers
ALTER TABLE `DcVnchxg4_customtables_table_gift_triggers`
  ADD INDEX `idx_gtrig_gift_id` (`ct_gift_id`);

-- career_skills
ALTER TABLE `DcVnchxg4_customtables_table_career_skills`
  ADD INDEX `idx_cs_career_id` (`career_id`),
  ADD INDEX `idx_cs_skill_id`  (`skill_id`);

-- career_gifts
ALTER TABLE `DcVnchxg4_customtables_table_career_gifts`
  ADD INDEX `idx_cg_career_id` (`career_id`),
  ADD INDEX `idx_cg_gift_id`   (`gift_id`);

-- species_traits
ALTER TABLE `DcVnchxg4_customtables_table_species_traits`
  ADD INDEX `idx_st_species_id` (`species_id`),
  ADD INDEX `idx_st_trait_key`  (`trait_key`);

-- trappings_map → career_id
ALTER TABLE `DcVnchxg4_customtables_table_trappings_map`
  ADD INDEX `idx_tmap_career_id` (`career_id`);

-- gifts name lookup
ALTER TABLE `DcVnchxg4_customtables_table_gifts`
  ADD INDEX `idx_gifts_name` (`ct_gifts_name`(100));

-- careers name lookup
ALTER TABLE `DcVnchxg4_customtables_table_careers`
  ADD INDEX `idx_careers_name` (`ct_career_name`(100));

-- species name lookup
ALTER TABLE `DcVnchxg4_customtables_table_species`
  ADD INDEX `idx_species_name` (`ct_species_name`(100));

-- skills name lookup
ALTER TABLE `DcVnchxg4_customtables_table_skills`
  ADD INDEX `idx_skills_name` (`ct_skill_name`(100));

-- ============================================================
-- FK CONSTRAINTS
-- ============================================================

-- gift_type_map → gifts
ALTER TABLE `DcVnchxg4_customtables_table_gift_type_map`
  ADD CONSTRAINT `fk_gtm_gift_id`
    FOREIGN KEY (`gift_id`)
    REFERENCES `DcVnchxg4_customtables_table_gifts` (`ct_id`)
    ON DELETE CASCADE ON UPDATE CASCADE;

-- gift_type_map → gifttype
ALTER TABLE `DcVnchxg4_customtables_table_gift_type_map`
  ADD CONSTRAINT `fk_gtm_type_id`
    FOREIGN KEY (`type_id`)
    REFERENCES `DcVnchxg4_customtables_table_gifttype` (`ct_id`)
    ON DELETE RESTRICT ON UPDATE CASCADE;

-- gift_requirements → gifts
ALTER TABLE `DcVnchxg4_customtables_table_gift_requirements`
  ADD CONSTRAINT `fk_greq_gift_id`
    FOREIGN KEY (`ct_gift_id`)
    REFERENCES `DcVnchxg4_customtables_table_gifts` (`ct_id`)
    ON DELETE CASCADE ON UPDATE CASCADE;

-- gift_sections → gifts
ALTER TABLE `DcVnchxg4_customtables_table_gift_sections`
  ADD CONSTRAINT `fk_gsec_gift_id`
    FOREIGN KEY (`ct_gift_id`)
    REFERENCES `DcVnchxg4_customtables_table_gifts` (`ct_id`)
    ON DELETE CASCADE ON UPDATE CASCADE;

-- gift_rules → gifts
ALTER TABLE `DcVnchxg4_customtables_table_gift_rules`
  ADD CONSTRAINT `fk_grules_gift_id`
    FOREIGN KEY (`ct_gift_id`)
    REFERENCES `DcVnchxg4_customtables_table_gifts` (`ct_id`)
    ON DELETE CASCADE ON UPDATE CASCADE;

-- gift_triggers → gifts
ALTER TABLE `DcVnchxg4_customtables_table_gift_triggers`
  ADD CONSTRAINT `fk_gtrig_gift_id`
    FOREIGN KEY (`ct_gift_id`)
    REFERENCES `DcVnchxg4_customtables_table_gifts` (`ct_id`)
    ON DELETE CASCADE ON UPDATE CASCADE;

-- career_skills → careers
ALTER TABLE `DcVnchxg4_customtables_table_career_skills`
  ADD CONSTRAINT `fk_cs_career_id`
    FOREIGN KEY (`career_id`)
    REFERENCES `DcVnchxg4_customtables_table_careers` (`ct_id`)
    ON DELETE CASCADE ON UPDATE CASCADE;

-- career_skills → skills
ALTER TABLE `DcVnchxg4_customtables_table_career_skills`
  ADD CONSTRAINT `fk_cs_skill_id`
    FOREIGN KEY (`skill_id`)
    REFERENCES `DcVnchxg4_customtables_table_skills` (`id`)
    ON DELETE RESTRICT ON UPDATE CASCADE;

-- career_gifts → careers
ALTER TABLE `DcVnchxg4_customtables_table_career_gifts`
  ADD CONSTRAINT `fk_cg_career_id`
    FOREIGN KEY (`career_id`)
    REFERENCES `DcVnchxg4_customtables_table_careers` (`ct_id`)
    ON DELETE CASCADE ON UPDATE CASCADE;

-- career_gifts → gifts
ALTER TABLE `DcVnchxg4_customtables_table_career_gifts`
  ADD CONSTRAINT `fk_cg_gift_id`
    FOREIGN KEY (`gift_id`)
    REFERENCES `DcVnchxg4_customtables_table_gifts` (`ct_id`)
    ON DELETE RESTRICT ON UPDATE CASCADE;

-- species_traits → species
ALTER TABLE `DcVnchxg4_customtables_table_species_traits`
  ADD CONSTRAINT `fk_st_species_id`
    FOREIGN KEY (`species_id`)
    REFERENCES `DcVnchxg4_customtables_table_species` (`ct_id`)
    ON DELETE CASCADE ON UPDATE CASCADE;

-- trappings_map → careers
ALTER TABLE `DcVnchxg4_customtables_table_trappings_map`
  ADD CONSTRAINT `fk_tmap_career_id`
    FOREIGN KEY (`career_id`)
    REFERENCES `DcVnchxg4_customtables_table_careers` (`ct_id`)
    ON DELETE CASCADE ON UPDATE CASCADE;

SET FOREIGN_KEY_CHECKS = 1;

-- Verify: list all FKs added by this migration
SELECT CONSTRAINT_NAME, TABLE_NAME, COLUMN_NAME,
       REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
FROM   information_schema.KEY_COLUMN_USAGE
WHERE  TABLE_SCHEMA = DATABASE()
  AND  REFERENCED_TABLE_NAME IS NOT NULL
  AND  TABLE_NAME LIKE 'DcVnchxg4_customtables_table_%'
ORDER  BY TABLE_NAME, CONSTRAINT_NAME;
