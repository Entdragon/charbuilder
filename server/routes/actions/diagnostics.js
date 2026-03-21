const { query, queryOne, prefix } = require('../../db');
const os = require('os');

async function cg_ping(req, res) {
  res.json({ success: true, data: {
    memory_usage_bytes: process.memoryUsage().heapUsed,
    peak_usage_bytes:   process.memoryUsage().heapTotal,
    timestamp:          new Date().toISOString(),
  }});
}

async function cg_run_diagnostics(req, res) {
  if (!req.session.isAdmin) {
    return res.status(403).json({ success: false, data: 'Forbidden.' });
  }
  let db_status = 'unknown';
  try {
    const rows = await query('SELECT 1 AS val');
    db_status = rows[0]?.val === 1 ? 'ok' : 'unexpected result';
  } catch (e) {
    db_status = 'error: ' + e.message;
  }
  res.json({ success: true, data: {
    node_version:        process.version,
    platform:            process.platform,
    memory_usage_bytes:  process.memoryUsage().heapUsed,
    peak_usage_bytes:    process.memoryUsage().heapTotal,
    db_status,
    timestamp:           new Date().toISOString(),
  }});
}

/**
 * cg_schema_probe — checks junction table row counts, samples data,
 * and probes whether old FK columns still exist on careers/species tables.
 * Callable by any logged-in user (non-destructive SELECT only).
 */
async function cg_schema_probe(req, res) {
  const p  = prefix();
  const c  = `${p}customtables_table_careers`;
  const sp = `${p}customtables_table_species`;
  const cg = `${p}customtables_table_career_gifts`;
  const cs = `${p}customtables_table_career_skills`;
  const st = `${p}customtables_table_species_traits`;
  const g  = `${p}customtables_table_gifts`;
  const sk = `${p}customtables_table_skills`;

  const result = {};

  // ── Junction table counts ──────────────────────────────────────────────────

  try {
    const [row] = await query(`SELECT COUNT(*) AS n FROM ${cg}`);
    result.career_gifts_count = row ? Number(row.n) : 0;
  } catch (e) {
    result.career_gifts_count = `error: ${e.message}`;
  }

  try {
    const [row] = await query(`SELECT COUNT(*) AS n FROM ${cs}`);
    result.career_skills_count = row ? Number(row.n) : 0;
  } catch (e) {
    result.career_skills_count = `error: ${e.message}`;
  }

  try {
    const [row] = await query(`SELECT COUNT(*) AS n FROM ${st}`);
    result.species_traits_count = row ? Number(row.n) : 0;
  } catch (e) {
    result.species_traits_count = `error: ${e.message}`;
  }

  // ── Sample junction rows ───────────────────────────────────────────────────

  try {
    const rows = await query(`SELECT * FROM ${cg} LIMIT 3`);
    result.career_gifts_sample = rows;
  } catch (e) {
    result.career_gifts_sample = `error: ${e.message}`;
  }

  try {
    const rows = await query(`SELECT * FROM ${st} LIMIT 5`);
    result.species_traits_sample = rows;
  } catch (e) {
    result.species_traits_sample = `error: ${e.message}`;
  }

  // ── Probe old FK columns on careers table ──────────────────────────────────
  // Try common legacy column name patterns. If SELECT succeeds, the column exists.

  const careerLegacyCols = [
    'ct_gifts_1', 'ct_gifts_2', 'ct_gifts_3',
    'ct_gift_1',  'ct_gift_2',  'ct_gift_3',
    'ct_skills_1','ct_skills_2','ct_skills_3',
    'ct_skill_one','ct_skill_two','ct_skill_three',
  ];
  result.careers_legacy_cols = {};
  for (const col of careerLegacyCols) {
    try {
      const rows = await query(`SELECT \`${col}\` FROM ${c} LIMIT 1`);
      result.careers_legacy_cols[col] = rows.length ? rows[0][col] : '(empty table)';
    } catch (_) {
      result.careers_legacy_cols[col] = null;
    }
  }

  // ── Probe old FK columns on species table ──────────────────────────────────

  const speciesLegacyCols = [
    'ct_gifts_1', 'ct_gifts_2', 'ct_gifts_3',
    'ct_gift_1',  'ct_gift_2',  'ct_gift_3',
    'ct_skills_1','ct_skills_2','ct_skills_3',
    'ct_skill_one','ct_skill_two','ct_skill_three',
    'ct_habitat', 'ct_diet', 'ct_cycle',
  ];
  result.species_legacy_cols = {};
  for (const col of speciesLegacyCols) {
    try {
      const rows = await query(`SELECT \`${col}\` FROM ${sp} LIMIT 1`);
      result.species_legacy_cols[col] = rows.length ? rows[0][col] : '(empty table)';
    } catch (_) {
      result.species_legacy_cols[col] = null;
    }
  }

  // ── Sample one career profile via current junction query ───────────────────

  try {
    const sample = await queryOne(`
      SELECT c.ct_id, c.ct_career_name,
             cg1.gift_id AS gift_id_1, g1.ct_gifts_name AS gift_1,
             cg2.gift_id AS gift_id_2, g2.ct_gifts_name AS gift_2,
             cg3.gift_id AS gift_id_3, g3.ct_gifts_name AS gift_3
      FROM ${c} c
      LEFT JOIN ${cg} cg1 ON cg1.career_id = c.ct_id AND cg1.sort = 1
      LEFT JOIN ${cg} cg2 ON cg2.career_id = c.ct_id AND cg2.sort = 2
      LEFT JOIN ${cg} cg3 ON cg3.career_id = c.ct_id AND cg3.sort = 3
      LEFT JOIN ${g}  g1  ON g1.ct_id = cg1.gift_id
      LEFT JOIN ${g}  g2  ON g2.ct_id = cg2.gift_id
      LEFT JOIN ${g}  g3  ON g3.ct_id = cg3.gift_id
      ORDER BY c.ct_id ASC LIMIT 1
    `);
    result.career_profile_sample = sample;
  } catch (e) {
    result.career_profile_sample = `error: ${e.message}`;
  }

  // ── Sample one species profile via current junction query ──────────────────

  try {
    const sample = await queryOne(`
      SELECT sp.ct_id, sp.ct_species_name,
             tg1.ref_id AS gift_id_1, g1.ct_gifts_name AS gift_1,
             tg2.ref_id AS gift_id_2, g2.ct_gifts_name AS gift_2,
             tg3.ref_id AS gift_id_3, g3.ct_gifts_name AS gift_3
      FROM ${sp} sp
      LEFT JOIN ${st} tg1 ON tg1.species_id = sp.ct_id AND tg1.trait_key = 'gift_1'
      LEFT JOIN ${st} tg2 ON tg2.species_id = sp.ct_id AND tg2.trait_key = 'gift_2'
      LEFT JOIN ${st} tg3 ON tg3.species_id = sp.ct_id AND tg3.trait_key = 'gift_3'
      LEFT JOIN ${g}  g1  ON g1.ct_id = tg1.ref_id
      LEFT JOIN ${g}  g2  ON g2.ct_id = tg2.ref_id
      LEFT JOIN ${g}  g3  ON g3.ct_id = tg3.ref_id
      ORDER BY sp.ct_id ASC LIMIT 1
    `);
    result.species_profile_sample = sample;
  } catch (e) {
    result.species_profile_sample = `error: ${e.message}`;
  }

  res.json({ success: true, data: result });
}

module.exports = { cg_ping, cg_run_diagnostics, cg_schema_probe };
