-- ============================================================
-- Migration 001: Drop redundant and backup tables
-- Database: libraryo_wp_ainng
-- Prefix: DcVnchxg4_
--
-- SAFE TO RUN: All tables below are either:
--   (a) old unprefixed duplicates superseded by DcVnchxg4_customtables_table_* versions
--   (b) temporary backup tables created during development data-fix work
--   (c) a dev copy of the character records table
--
-- A fresh phpMyAdmin dump was taken before this migration was written.
-- Run via phpMyAdmin → SQL tab. Verify the table list afterwards.
-- ============================================================


-- ------------------------------------------------------------
-- GROUP 1: Old bare/unprefixed tables (16 tables)
-- These were the original CustomTables plugin tables created
-- before the WordPress table prefix was applied. Every one has
-- an exact duplicate under DcVnchxg4_customtables_table_*.
-- The Node.js app queries only the prefixed versions.
-- ------------------------------------------------------------

DROP TABLE IF EXISTS `archtype`;
DROP TABLE IF EXISTS `books`;
DROP TABLE IF EXISTS `careers`;
DROP TABLE IF EXISTS `careertype`;
DROP TABLE IF EXISTS `cycle`;
DROP TABLE IF EXISTS `diet`;
DROP TABLE IF EXISTS `giftclass`;
DROP TABLE IF EXISTS `gifts`;
DROP TABLE IF EXISTS `gifttype`;
DROP TABLE IF EXISTS `habitat`;
DROP TABLE IF EXISTS `refresh`;
DROP TABLE IF EXISTS `senses`;
DROP TABLE IF EXISTS `skills`;
DROP TABLE IF EXISTS `skillsdescriptors`;
DROP TABLE IF EXISTS `species`;
DROP TABLE IF EXISTS `weapons`;


-- ------------------------------------------------------------
-- GROUP 2: Backup tables from development data-fix work (22 tables)
-- Created during gift_sections and gift_requirements normalisation
-- work in January–March 2026. The production data is now correct
-- and these snapshots are no longer needed.
-- ------------------------------------------------------------

-- March 2026 gift_rules/gift_sections row-level backups
DROP TABLE IF EXISTS `DcVnchxg4_bak_gr_134_296_20260307`;
DROP TABLE IF EXISTS `DcVnchxg4_bak_gs_134_296_20260307`;
DROP TABLE IF EXISTS `DcVnchxg4_bak_gs_reqdup_150_20260307`;

-- CustomTables-prefixed gift_sections snapshots (fix and resection passes)
DROP TABLE IF EXISTS `DcVnchxg4_customtables_bak_fix_20260307_gift_sections_134_296`;
DROP TABLE IF EXISTS `DcVnchxg4_customtables_bak_gift_sections_flight_82`;
DROP TABLE IF EXISTS `DcVnchxg4_customtables_bak_gift_sections_manual_82`;
DROP TABLE IF EXISTS `DcVnchxg4_customtables_bak_gift_sections_manual_180`;
DROP TABLE IF EXISTS `DcVnchxg4_customtables_bak_gift_sections_manual_559`;
DROP TABLE IF EXISTS `DcVnchxg4_customtables_bak_gift_sections_resection_flight_82`;

-- Wrapfix batch snapshots (wrap-tag correction passes)
DROP TABLE IF EXISTS `DcVnchxg4_customtables_bak_gift_sections_wrapfix_6s_megabatch`;
DROP TABLE IF EXISTS `DcVnchxg4_customtables_bak_gift_sections_wrapfix_7s_megabatch`;
DROP TABLE IF EXISTS `DcVnchxg4_customtables_bak_gift_sections_wrapfix_all_remaining`;
DROP TABLE IF EXISTS `DcVnchxg4_customtables_bak_gift_sections_wrapfix_batch1`;
DROP TABLE IF EXISTS `DcVnchxg4_customtables_bak_gift_sections_wrapfix_batch2`;
DROP TABLE IF EXISTS `DcVnchxg4_customtables_bak_gift_sections_wrapfix_batch3`;
DROP TABLE IF EXISTS `DcVnchxg4_customtables_bak_gift_sections_wrapfix_batch4`;
DROP TABLE IF EXISTS `DcVnchxg4_customtables_bak_gift_sections_wrapfix_batch5`;
DROP TABLE IF EXISTS `DcVnchxg4_customtables_bak_gift_sections_wrapfix_batch6`;
DROP TABLE IF EXISTS `DcVnchxg4_customtables_bak_gift_sections_wrapfix_batch7`;
DROP TABLE IF EXISTS `DcVnchxg4_customtables_bak_gift_sections_wrapfix_batch8`;
DROP TABLE IF EXISTS `DcVnchxg4_customtables_bak_gift_sections_wrapfix_megabatch_100`;

-- Trappings map backup (March 2026 before trappings_map rework)
DROP TABLE IF EXISTS `DcVnchxg4_customtables_table_trappings_map_bak_20260301`;


-- ------------------------------------------------------------
-- GROUP 3: Development copy of character records (1 table)
-- A dev-environment copy of character_records that accumulated
-- during local testing. The live table is DcVnchxg4_character_records.
-- ------------------------------------------------------------

DROP TABLE IF EXISTS `DcVnchxg4_character_records_dev`;


-- ============================================================
-- After running: verify the table count has dropped by 39.
-- Confirm DcVnchxg4_character_records still exists and has data.
-- ============================================================
