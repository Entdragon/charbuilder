-- ============================================================
-- Migration 002 / Phase 01: Expand — Gifts
-- Database: libraryo_wp_ainng  Prefix: DcVnchxg4_
--
-- Creates the gift_type_map join table (gift_requirements already
-- exists).  Does NOT remove any existing columns — the app keeps
-- reading the legacy columns until Phase 03.
--
-- Safe to re-run: all DDL uses IF NOT EXISTS / IF EXISTS guards.
-- ============================================================

SET NAMES utf8mb4;

-- ------------------------------------------------------------
-- gift_type_map  (replaces type_1 … type_8 on gifts)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `DcVnchxg4_customtables_table_gift_type_map` (
  `id`       INT          NOT NULL AUTO_INCREMENT,
  `gift_id`  INT          NOT NULL COMMENT 'FK → customtables_table_gifts.ct_id',
  `type_id`  INT          NOT NULL COMMENT 'FK → customtables_table_gifttype.ct_id',
  `sort`     TINYINT      NOT NULL DEFAULT 0 COMMENT 'preserves original column order (1-based)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_gtm_gift_type` (`gift_id`, `type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
  COMMENT='Normalised replacement for gifts.type_1 … type_8';

-- ------------------------------------------------------------
-- Verify gift_requirements exists (created in earlier work).
-- If it is missing this will error and alert the operator.
-- ------------------------------------------------------------
SELECT COUNT(*) AS gift_requirements_rows
FROM   `DcVnchxg4_customtables_table_gift_requirements`;
