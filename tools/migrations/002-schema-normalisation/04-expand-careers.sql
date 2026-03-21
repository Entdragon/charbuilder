-- ============================================================
-- Migration 002 / Phase 04: Expand — Careers
-- Database: libraryo_wp_ainng  Prefix: DcVnchxg4_
--
-- Creates career_skills and career_gifts mapping tables.
-- Does NOT remove any existing columns yet.
--
-- Safe to re-run: uses CREATE TABLE IF NOT EXISTS.
-- ============================================================

SET NAMES utf8mb4;

-- ------------------------------------------------------------
-- career_skills  (replaces career_skill_one/two/three)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `DcVnchxg4_customtables_table_career_skills` (
  `id`        INT NOT NULL AUTO_INCREMENT,
  `career_id` INT NOT NULL COMMENT 'FK → customtables_table_careers.ct_id',
  `skill_id`  INT NOT NULL COMMENT 'FK → customtables_table_skills.id',
  `sort`      TINYINT NOT NULL DEFAULT 1 COMMENT '1=one, 2=two, 3=three',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_cs_career_skill` (`career_id`, `skill_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
  COMMENT='Normalised replacement for careers.career_skill_one/two/three';

-- ------------------------------------------------------------
-- career_gifts  (replaces career_gift_one/two/three)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `DcVnchxg4_customtables_table_career_gifts` (
  `id`        INT NOT NULL AUTO_INCREMENT,
  `career_id` INT NOT NULL COMMENT 'FK → customtables_table_careers.ct_id',
  `gift_id`   INT NOT NULL COMMENT 'FK → customtables_table_gifts.ct_id',
  `sort`      TINYINT NOT NULL DEFAULT 1 COMMENT '1=one, 2=two, 3=three',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_cg_career_gift` (`career_id`, `gift_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
  COMMENT='Normalised replacement for careers.career_gift_one/two/three';
