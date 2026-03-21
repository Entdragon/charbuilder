-- ============================================================
-- Migration 002 / Phase 08: Migrate — Species
-- Database: libraryo_wp_ainng  Prefix: DcVnchxg4_
--
-- Backfills species_traits from the inline FK columns on
-- customtables_table_species.
--
-- Safe to re-run: uses INSERT IGNORE throughout.
-- ============================================================

SET NAMES utf8mb4;

-- Gifts
INSERT IGNORE INTO `DcVnchxg4_customtables_table_species_traits`
  (species_id, trait_key, ref_id, sort)
SELECT ct_id, 'gift_1', ct_species_gift_one, 1
FROM `DcVnchxg4_customtables_table_species`
WHERE ct_species_gift_one IS NOT NULL AND ct_species_gift_one != 0;

INSERT IGNORE INTO `DcVnchxg4_customtables_table_species_traits`
  (species_id, trait_key, ref_id, sort)
SELECT ct_id, 'gift_2', ct_species_gift_two, 2
FROM `DcVnchxg4_customtables_table_species`
WHERE ct_species_gift_two IS NOT NULL AND ct_species_gift_two != 0;

INSERT IGNORE INTO `DcVnchxg4_customtables_table_species_traits`
  (species_id, trait_key, ref_id, sort)
SELECT ct_id, 'gift_3', ct_species_gift_three, 3
FROM `DcVnchxg4_customtables_table_species`
WHERE ct_species_gift_three IS NOT NULL AND ct_species_gift_three != 0;

-- Skills (stored as free text on species)
INSERT IGNORE INTO `DcVnchxg4_customtables_table_species_traits`
  (species_id, trait_key, text_value, sort)
SELECT ct_id, 'skill_1', ct_species_skill_one, 1
FROM `DcVnchxg4_customtables_table_species`
WHERE ct_species_skill_one IS NOT NULL AND TRIM(ct_species_skill_one) != '';

INSERT IGNORE INTO `DcVnchxg4_customtables_table_species_traits`
  (species_id, trait_key, text_value, sort)
SELECT ct_id, 'skill_2', ct_species_skill_two, 2
FROM `DcVnchxg4_customtables_table_species`
WHERE ct_species_skill_two IS NOT NULL AND TRIM(ct_species_skill_two) != '';

INSERT IGNORE INTO `DcVnchxg4_customtables_table_species_traits`
  (species_id, trait_key, text_value, sort)
SELECT ct_id, 'skill_3', ct_species_skill_three, 3
FROM `DcVnchxg4_customtables_table_species`
WHERE ct_species_skill_three IS NOT NULL AND TRIM(ct_species_skill_three) != '';

-- Habitat
INSERT IGNORE INTO `DcVnchxg4_customtables_table_species_traits`
  (species_id, trait_key, ref_id, sort)
SELECT ct_id, 'habitat', ct_species_habitat, 1
FROM `DcVnchxg4_customtables_table_species`
WHERE ct_species_habitat IS NOT NULL AND ct_species_habitat != 0;

-- Diet
INSERT IGNORE INTO `DcVnchxg4_customtables_table_species_traits`
  (species_id, trait_key, ref_id, sort)
SELECT ct_id, 'diet', ct_species_diet, 1
FROM `DcVnchxg4_customtables_table_species`
WHERE ct_species_diet IS NOT NULL AND ct_species_diet != 0;

-- Cycle
INSERT IGNORE INTO `DcVnchxg4_customtables_table_species_traits`
  (species_id, trait_key, ref_id, sort)
SELECT ct_id, 'cycle', ct_species_cycle, 1
FROM `DcVnchxg4_customtables_table_species`
WHERE ct_species_cycle IS NOT NULL AND ct_species_cycle != 0;

-- Senses
INSERT IGNORE INTO `DcVnchxg4_customtables_table_species_traits`
  (species_id, trait_key, ref_id, sort)
SELECT ct_id, 'sense_1', ct_species_senses_one, 1
FROM `DcVnchxg4_customtables_table_species`
WHERE ct_species_senses_one IS NOT NULL AND ct_species_senses_one != 0;

INSERT IGNORE INTO `DcVnchxg4_customtables_table_species_traits`
  (species_id, trait_key, ref_id, sort)
SELECT ct_id, 'sense_2', ct_species_senses_two, 2
FROM `DcVnchxg4_customtables_table_species`
WHERE ct_species_senses_two IS NOT NULL AND ct_species_senses_two != 0;

INSERT IGNORE INTO `DcVnchxg4_customtables_table_species_traits`
  (species_id, trait_key, ref_id, sort)
SELECT ct_id, 'sense_3', ct_species_senses_three, 3
FROM `DcVnchxg4_customtables_table_species`
WHERE ct_species_senses_three IS NOT NULL AND ct_species_senses_three != 0;

-- Weapons
INSERT IGNORE INTO `DcVnchxg4_customtables_table_species_traits`
  (species_id, trait_key, ref_id, sort)
SELECT ct_id, 'weapon_1', ct_species_weapon_one, 1
FROM `DcVnchxg4_customtables_table_species`
WHERE ct_species_weapon_one IS NOT NULL AND ct_species_weapon_one != 0;

INSERT IGNORE INTO `DcVnchxg4_customtables_table_species_traits`
  (species_id, trait_key, ref_id, sort)
SELECT ct_id, 'weapon_2', ct_species_weapon_two, 2
FROM `DcVnchxg4_customtables_table_species`
WHERE ct_species_weapon_two IS NOT NULL AND ct_species_weapon_two != 0;

INSERT IGNORE INTO `DcVnchxg4_customtables_table_species_traits`
  (species_id, trait_key, ref_id, sort)
SELECT ct_id, 'weapon_3', ct_species_weapon_three, 3
FROM `DcVnchxg4_customtables_table_species`
WHERE ct_species_weapon_three IS NOT NULL AND ct_species_weapon_three != 0;

-- Spot-check
SELECT 'species_traits total rows' AS label, COUNT(*) AS cnt
FROM `DcVnchxg4_customtables_table_species_traits`;

SELECT trait_key, COUNT(*) AS species_count
FROM `DcVnchxg4_customtables_table_species_traits`
GROUP BY trait_key
ORDER BY trait_key;
