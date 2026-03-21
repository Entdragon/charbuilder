/**
 * server/routes/actions/migrate.js
 *
 * cg_migrate_junctions — one-shot data migration from old FK columns
 * on the careers / species tables into the Migration 002 junction tables:
 *   career_gifts  (career_id, gift_id, sort)
 *   career_skills (career_id, skill_id, sort)
 *   species_traits (species_id, trait_key, ref_id, text_value)
 *
 * Safe to call repeatedly: skips tables that already have rows, or
 * where old source columns cannot be found.
 *
 * Callable by any logged-in user. Returns a detailed report.
 */

const { query, queryOne, prefix } = require('../../db');

async function colExists(table, col) {
  try {
    await query(`SELECT \`${col}\` FROM ${table} LIMIT 0`);
    return true;
  } catch (_) {
    return false;
  }
}

async function countRows(table) {
  try {
    const rows = await query(`SELECT COUNT(*) AS n FROM ${table}`);
    return Number(rows[0]?.n ?? 0);
  } catch (_) {
    return -1;
  }
}

async function cg_migrate_junctions(req, res) {
  const p  = prefix();
  const c  = `${p}customtables_table_careers`;
  const sp = `${p}customtables_table_species`;
  const cg = `${p}customtables_table_career_gifts`;
  const cs = `${p}customtables_table_career_skills`;
  const st = `${p}customtables_table_species_traits`;
  const g  = `${p}customtables_table_gifts`;
  const sk = `${p}customtables_table_skills`;

  const report = {
    career_gifts:  { skipped: false, reason: '', inserted: 0, errors: [] },
    career_skills: { skipped: false, reason: '', inserted: 0, errors: [] },
    species_traits:{ skipped: false, reason: '', inserted: 0, errors: [] },
  };

  // ── 1. career_gifts ────────────────────────────────────────────────────────

  const cgCount = await countRows(cg);
  if (cgCount > 0) {
    report.career_gifts.skipped = true;
    report.career_gifts.reason  = `already has ${cgCount} rows`;
  } else {
    // Try common old-column name patterns for gift FKs on the careers table
    const giftCols = [
      ['ct_gifts_1', 'ct_gifts_2', 'ct_gifts_3'],
      ['ct_gift_1',  'ct_gift_2',  'ct_gift_3'],
      ['gifts_1',    'gifts_2',    'gifts_3'],
    ];

    let found = null;
    for (const trio of giftCols) {
      const ok0 = await colExists(c, trio[0]);
      if (ok0) { found = trio; break; }
    }

    if (!found) {
      report.career_gifts.skipped = true;
      report.career_gifts.reason  = 'no recognisable old gift-FK columns found on careers table';
    } else {
      const [col1, col2, col3] = found;
      report.career_gifts.source_cols = found;

      const careers = await query(`SELECT ct_id, \`${col1}\`, \`${col2}\`, \`${col3}\` FROM ${c}`);

      let inserted = 0;
      for (const row of careers) {
        const cid = row.ct_id;
        for (let i = 0; i < 3; i++) {
          const colName = found[i];
          const gid = row[colName];
          if (!gid) continue;
          try {
            await query(
              `INSERT IGNORE INTO ${cg} (career_id, gift_id, sort) VALUES (?, ?, ?)`,
              [cid, gid, i + 1]
            );
            inserted++;
          } catch (e) {
            report.career_gifts.errors.push(`career ${cid} slot ${i+1}: ${e.message}`);
          }
        }
      }
      report.career_gifts.inserted = inserted;
    }
  }

  // ── 2. career_skills ───────────────────────────────────────────────────────

  const csCount = await countRows(cs);
  if (csCount > 0) {
    report.career_skills.skipped = true;
    report.career_skills.reason  = `already has ${csCount} rows`;
  } else {
    const skillCols = [
      ['ct_skills_1', 'ct_skills_2', 'ct_skills_3'],
      ['ct_skill_1',  'ct_skill_2',  'ct_skill_3'],
      ['ct_skill_one','ct_skill_two','ct_skill_three'],
      ['skill_1',     'skill_2',     'skill_3'],
    ];

    let found = null;
    for (const trio of skillCols) {
      const ok0 = await colExists(c, trio[0]);
      if (ok0) { found = trio; break; }
    }

    if (!found) {
      report.career_skills.skipped = true;
      report.career_skills.reason  = 'no recognisable old skill-FK columns found on careers table';
    } else {
      report.career_skills.source_cols = found;

      const careers = await query(`SELECT ct_id, \`${found[0]}\`, \`${found[1]}\`, \`${found[2]}\` FROM ${c}`);

      let inserted = 0;
      for (const row of careers) {
        const cid = row.ct_id;
        for (let i = 0; i < 3; i++) {
          const colName = found[i];
          const sid = row[colName];
          if (!sid) continue;
          try {
            await query(
              `INSERT IGNORE INTO ${cs} (career_id, skill_id, sort) VALUES (?, ?, ?)`,
              [cid, sid, i + 1]
            );
            inserted++;
          } catch (e) {
            report.career_skills.errors.push(`career ${cid} slot ${i+1}: ${e.message}`);
          }
        }
      }
      report.career_skills.inserted = inserted;
    }
  }

  // ── 3. species_traits ──────────────────────────────────────────────────────

  const stCount = await countRows(st);
  if (stCount > 0) {
    report.species_traits.skipped = true;
    report.species_traits.reason  = `already has ${stCount} rows`;
  } else {
    // Gifts (ref_id = gift ct_id)
    const giftCols = [
      ['ct_gifts_1', 'ct_gifts_2', 'ct_gifts_3'],
      ['ct_gift_1',  'ct_gift_2',  'ct_gift_3'],
    ];

    let gFound = null;
    for (const trio of giftCols) {
      if (await colExists(sp, trio[0])) { gFound = trio; break; }
    }

    // Skills (text_value = skill name or id stored as text)
    const skillCols = [
      ['ct_skill_one','ct_skill_two','ct_skill_three'],
      ['ct_skills_1', 'ct_skills_2', 'ct_skills_3'],
      ['ct_skill_1',  'ct_skill_2',  'ct_skill_3'],
    ];
    let sFound = null;
    for (const trio of skillCols) {
      if (await colExists(sp, trio[0])) { sFound = trio; break; }
    }

    // Habitat / diet / cycle / senses / weapons — probe single cols
    const extraProbes = ['ct_habitat','ct_diet','ct_cycle','ct_sense_1','ct_sense_2','ct_sense_3','ct_weapon_1','ct_weapon_2','ct_weapon_3'];
    const extraFound = {};
    for (const col of extraProbes) {
      if (await colExists(sp, col)) extraFound[col] = true;
    }

    if (!gFound && !sFound && Object.keys(extraFound).length === 0) {
      report.species_traits.skipped = true;
      report.species_traits.reason  = 'no recognisable old columns found on species table';
    } else {
      report.species_traits.source_gift_cols  = gFound;
      report.species_traits.source_skill_cols = sFound;
      report.species_traits.source_extra_cols = Object.keys(extraFound);

      const colList = [
        ...(gFound || []),
        ...(sFound || []),
        ...Object.keys(extraFound),
      ];
      const selectCols = ['ct_id', ...colList].map(c => `\`${c}\``).join(', ');
      const species = await query(`SELECT ${selectCols} FROM ${sp}`);

      let inserted = 0;

      for (const row of species) {
        const sid = row.ct_id;

        // Gifts → ref_id
        if (gFound) {
          for (let i = 0; i < 3; i++) {
            const gid = row[gFound[i]];
            if (!gid) continue;
            try {
              await query(
                `INSERT IGNORE INTO ${st} (species_id, trait_key, ref_id, text_value) VALUES (?, ?, ?, NULL)`,
                [sid, `gift_${i+1}`, gid]
              );
              inserted++;
            } catch (e) {
              report.species_traits.errors = report.species_traits.errors || [];
              report.species_traits.errors.push(`species ${sid} gift_${i+1}: ${e.message}`);
            }
          }
        }

        // Skills → text_value (stored as text in species_traits)
        if (sFound) {
          const keys = ['skill_1','skill_2','skill_3'];
          for (let i = 0; i < 3; i++) {
            const val = row[sFound[i]];
            if (!val && val !== 0) continue;
            try {
              await query(
                `INSERT IGNORE INTO ${st} (species_id, trait_key, ref_id, text_value) VALUES (?, ?, NULL, ?)`,
                [sid, keys[i], String(val)]
              );
              inserted++;
            } catch (e) {
              report.species_traits.errors = report.species_traits.errors || [];
              report.species_traits.errors.push(`species ${sid} ${keys[i]}: ${e.message}`);
            }
          }
        }

        // Extra single-value traits
        const extraMap = {
          ct_habitat: 'habitat', ct_diet: 'diet', ct_cycle: 'cycle',
          ct_sense_1: 'sense_1', ct_sense_2: 'sense_2', ct_sense_3: 'sense_3',
          ct_weapon_1:'weapon_1',ct_weapon_2:'weapon_2',ct_weapon_3:'weapon_3',
        };
        for (const [col, key] of Object.entries(extraMap)) {
          if (!extraFound[col]) continue;
          const val = row[col];
          if (!val && val !== 0) continue;
          try {
            await query(
              `INSERT IGNORE INTO ${st} (species_id, trait_key, ref_id, text_value) VALUES (?, ?, ?, NULL)`,
              [sid, key, val]
            );
            inserted++;
          } catch (e) {
            report.species_traits.errors = report.species_traits.errors || [];
            report.species_traits.errors.push(`species ${sid} ${key}: ${e.message}`);
          }
        }
      }

      report.species_traits.inserted = inserted;
    }
  }

  res.json({ success: true, data: report });
}

module.exports = { cg_migrate_junctions };
