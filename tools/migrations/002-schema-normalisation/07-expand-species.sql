-- ============================================================
-- Migration 002 / Phase 07: Expand — Species
-- Database: libraryo_wp_ainng  Prefix: DcVnchxg4_
--
-- Creates species_traits child table.
-- Does NOT remove any existing columns yet.
--
-- Safe to re-run: uses CREATE TABLE IF NOT EXISTS.
-- ============================================================

SET NAMES utf8mb4;

-- ------------------------------------------------------------
-- species_traits  (replaces long text trait fields on species)
--
-- Each row represents one trait value for one species.
-- trait_key  = short machine name (e.g. 'gift_1', 'skill_one',
--              'habitat', 'diet', 'cycle', 'sense_1', 'weapon_1')
-- ref_id     = FK to the related lookup table (nullable)
-- text_value = raw text value when no FK lookup applies
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `DcVnchxg4_customtables_table_species_traits` (
  `id`         INT          NOT NULL AUTO_INCREMENT,
  `species_id` INT          NOT NULL COMMENT 'FK → customtables_table_species.ct_id',
  `trait_key`  VARCHAR(40)  NOT NULL COMMENT 'machine key: gift_1..3, skill_1..3, habitat, diet, cycle, sense_1..3, weapon_1..3',
  `ref_id`     INT              NULL COMMENT 'FK to the relevant lookup table (gifts, skills, habitat, diet, cycle, senses, weapons)',
  `text_value` VARCHAR(255)     NULL COMMENT 'free-text value when no FK applies',
  `sort`       TINYINT      NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_st_species_key` (`species_id`, `trait_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
  COMMENT='Normalised replacement for species inline gift/skill/trait columns';
