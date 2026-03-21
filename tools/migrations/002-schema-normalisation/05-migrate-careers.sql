-- ============================================================
-- Migration 002 / Phase 05: Migrate — Careers
-- Database: libraryo_wp_ainng  Prefix: DcVnchxg4_
--
-- Backfills career_skills and career_gifts from the repeated
-- inline columns on customtables_table_careers.
--
-- career_skill_one/two/three store the skill *name* as free text.
-- career_gift_one/two/three store the gift *id* (FK to gifts).
--
-- Skills are looked up by name to resolve their id.
-- Safe to re-run: uses INSERT IGNORE throughout.
-- ============================================================

SET NAMES utf8mb4;

-- ============================================================
-- PART A: Backfill career_skills (name → id resolution)
-- ============================================================

INSERT IGNORE INTO `DcVnchxg4_customtables_table_career_skills`
  (career_id, skill_id, sort)
SELECT c.ct_id, s.id, 1
FROM   `DcVnchxg4_customtables_table_careers` c
JOIN   `DcVnchxg4_customtables_table_skills`  s
    ON TRIM(LOWER(s.ct_skill_name)) = TRIM(LOWER(c.ct_career_skill_one))
WHERE  c.ct_career_skill_one IS NOT NULL AND TRIM(c.ct_career_skill_one) != '';

INSERT IGNORE INTO `DcVnchxg4_customtables_table_career_skills`
  (career_id, skill_id, sort)
SELECT c.ct_id, s.id, 2
FROM   `DcVnchxg4_customtables_table_careers` c
JOIN   `DcVnchxg4_customtables_table_skills`  s
    ON TRIM(LOWER(s.ct_skill_name)) = TRIM(LOWER(c.ct_career_skill_two))
WHERE  c.ct_career_skill_two IS NOT NULL AND TRIM(c.ct_career_skill_two) != '';

INSERT IGNORE INTO `DcVnchxg4_customtables_table_career_skills`
  (career_id, skill_id, sort)
SELECT c.ct_id, s.id, 3
FROM   `DcVnchxg4_customtables_table_careers` c
JOIN   `DcVnchxg4_customtables_table_skills`  s
    ON TRIM(LOWER(s.ct_skill_name)) = TRIM(LOWER(c.ct_career_skill_three))
WHERE  c.ct_career_skill_three IS NOT NULL AND TRIM(c.ct_career_skill_three) != '';

-- ============================================================
-- PART B: Backfill career_gifts (id column — direct insert)
-- ============================================================

INSERT IGNORE INTO `DcVnchxg4_customtables_table_career_gifts`
  (career_id, gift_id, sort)
SELECT ct_id, ct_career_gift_one, 1
FROM   `DcVnchxg4_customtables_table_careers`
WHERE  ct_career_gift_one IS NOT NULL AND ct_career_gift_one != 0;

INSERT IGNORE INTO `DcVnchxg4_customtables_table_career_gifts`
  (career_id, gift_id, sort)
SELECT ct_id, ct_career_gift_two, 2
FROM   `DcVnchxg4_customtables_table_careers`
WHERE  ct_career_gift_two IS NOT NULL AND ct_career_gift_two != 0;

INSERT IGNORE INTO `DcVnchxg4_customtables_table_career_gifts`
  (career_id, gift_id, sort)
SELECT ct_id, ct_career_gift_three, 3
FROM   `DcVnchxg4_customtables_table_careers`
WHERE  ct_career_gift_three IS NOT NULL AND ct_career_gift_three != 0;

-- Spot-checks
SELECT 'career_skills rows' AS label, COUNT(*) AS cnt
FROM `DcVnchxg4_customtables_table_career_skills`;

SELECT 'career_gifts rows' AS label, COUNT(*) AS cnt
FROM `DcVnchxg4_customtables_table_career_gifts`;

-- Careers where skill_one was populated but no career_skills row resolved
-- (indicates a name mismatch — investigate before proceeding to contract)
SELECT 'careers skill_one unresolved' AS label, COUNT(*) AS cnt
FROM `DcVnchxg4_customtables_table_careers` c
WHERE c.ct_career_skill_one IS NOT NULL AND TRIM(c.ct_career_skill_one) != ''
  AND NOT EXISTS (
    SELECT 1 FROM `DcVnchxg4_customtables_table_career_skills` cs
    WHERE cs.career_id = c.ct_id AND cs.sort = 1
  );
