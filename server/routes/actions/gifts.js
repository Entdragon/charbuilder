const { query, queryOne, prefix } = require('../../db');

async function cg_get_local_knowledge(req, res) {
  const p = prefix();
  const row = await queryOne(
    `SELECT ct_id AS id, ct_gifts_name AS name, ct_gifts_manifold AS ct_gifts_manifold
     FROM ${p}customtables_table_gifts WHERE ct_id = 242 LIMIT 1`
  );
  if (!row) return res.json({ success: false, data: 'Local Knowledge gift not found.' });
  res.json({ success: true, data: row });
}

async function cg_get_language_gift(req, res) {
  const p = prefix();
  const row = await queryOne(
    `SELECT ct_id AS id, ct_gifts_name AS name, ct_gifts_manifold AS ct_gifts_manifold
     FROM ${p}customtables_table_gifts WHERE ct_id = 236 LIMIT 1`
  );
  if (!row) return res.json({ success: false, data: 'Language gift not found.' });
  res.json({ success: true, data: row });
}

async function cg_get_free_gifts(req, res) {
  const p = prefix();

  let rows;
  try {
    rows = await query(`
      SELECT
        ct_id                              AS id,
        ct_gifts_name                      AS name,
        ct_gifts_allows_multiple           AS allows_multiple,
        ct_gifts_manifold                  AS ct_gifts_manifold,
        ct_gift_class                      AS giftclass,
        ct_gifts_effect_description        AS effect_description
      FROM ${p}customtables_table_gifts
      WHERE LOWER(TRIM(ct_gift_class)) != 'natural'
      ORDER BY ct_gifts_name ASC
    `);
  } catch (err) {
    if (err && /Unknown column.*ct_gift[s]?_class|ct_gifts_effect_description/i.test(err.message)) {
      rows = await query(`
        SELECT
          ct_id                    AS id,
          ct_gifts_name            AS name,
          ct_gifts_allows_multiple AS allows_multiple,
          ct_gifts_manifold        AS ct_gifts_manifold,
          NULL                     AS giftclass,
          NULL                     AS effect_description
        FROM ${p}customtables_table_gifts
        ORDER BY ct_gifts_name ASC
      `);
    } else {
      throw err;
    }
  }

  const giftMap = new Map();
  rows.forEach(r => {
    const id = String(r.id || '');
    if (id) giftMap.set(id, r);
  });

  let prereqRows = [];
  let reqRows = [];
  let sectionRows = [];

  try {
    prereqRows = await query(
      `SELECT id, gift_id, slot, kind, raw_text, req_key, req_value, trait_key, die_min, comparator, qty_required
       FROM ${p}customtables_table_gift_prereq
       ORDER BY gift_id ASC, slot ASC`
    );
  } catch (_) {}

  try {
    reqRows = await query(
      `SELECT ct_id, ct_gift_id AS gift_id, ct_sort AS sort, ct_req_kind AS kind, ct_req_ref_id AS ref_id, ct_req_text AS text
       FROM ${p}customtables_table_gift_requirements
       ORDER BY ct_gift_id ASC, ct_sort ASC`
    );
  } catch (_) {}

  try {
    sectionRows = await query(
      `SELECT ct_gift_id AS gift_id, MIN(ct_sort) AS min_sort, ct_body AS body
       FROM ${p}customtables_table_gift_sections
       WHERE ct_section_type = 'rules'
       GROUP BY ct_gift_id`
    );
  } catch (_) {}

  prereqRows.forEach(pr => {
    const id = String(pr.gift_id || '');
    if (!giftMap.has(id)) return;
    const g = giftMap.get(id);
    if (!g.prereqs) g.prereqs = [];
    g.prereqs.push(pr);
  });

  reqRows.forEach(rr => {
    const id = String(rr.gift_id || '');
    if (!giftMap.has(id)) return;
    const g = giftMap.get(id);
    if (!g.requirements) g.requirements = [];
    g.requirements.push(rr);
  });

  sectionRows.forEach(sr => {
    const id = String(sr.gift_id || '');
    if (!giftMap.has(id)) return;
    const g = giftMap.get(id);
    if (!g.effect_description || !String(g.effect_description || '').trim()) {
      const body = String(sr.body || '').trim();
      if (body) g.effect_description = body.length > 180 ? body.slice(0, 177) + '…' : body;
    }
  });

  res.json({ success: true, data: rows });
}

async function cg_get_gift_prereqs(req, res) {
  const p = prefix();
  const giftId = parseInt(req.body.gift_id || req.query.gift_id || '', 10);
  if (!giftId) return res.json({ success: false, data: 'Missing gift_id.' });

  let prereqs = [];
  let requirements = [];

  try {
    prereqs = await query(
      `SELECT id, gift_id, slot, kind, raw_text, req_key, req_value, trait_key, die_min, comparator, qty_required
       FROM ${p}customtables_table_gift_prereq
       WHERE gift_id = ?
       ORDER BY slot ASC`,
      [giftId]
    );
  } catch (_) {}

  try {
    requirements = await query(
      `SELECT ct_id, ct_gift_id AS gift_id, ct_sort AS sort, ct_req_kind AS kind, ct_req_ref_id AS ref_id, ct_req_text AS text
       FROM ${p}customtables_table_gift_requirements
       WHERE ct_gift_id = ?
       ORDER BY ct_sort ASC`,
      [giftId]
    );
  } catch (_) {}

  res.json({ success: true, data: { prereqs, requirements } });
}

const DEFAULT_LANGUAGES = [
  'Calabrian', 'Common', 'Dwarven', 'Elven', 'Goblin', 'Hesperian',
  'Kawtaw', 'Mordic', 'Old Calabrian', 'Orcish', 'Sylvan', 'Urathi',
];

async function ensureLanguageTable() {
  const p = prefix();
  const table = `\`${p}cg_languages\``;

  await query(`
    CREATE TABLE IF NOT EXISTS ${table} (
      id         INT AUTO_INCREMENT PRIMARY KEY,
      name       VARCHAR(100) NOT NULL,
      sort_order INT NOT NULL DEFAULT 0,
      UNIQUE KEY uq_cg_lang_name (name)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
  `);

  const existing = await query(`SELECT COUNT(*) AS cnt FROM ${table}`);
  const count = parseInt((existing[0] || {}).cnt || 0, 10);
  if (count === 0) {
    const placeholders = DEFAULT_LANGUAGES.map((_, i) => `(?, ${i})`).join(', ');
    await query(
      `INSERT IGNORE INTO ${table} (name, sort_order) VALUES ${placeholders}`,
      DEFAULT_LANGUAGES
    );
  }
}

async function cg_get_language_list(req, res) {
  const p = prefix();
  try {
    await ensureLanguageTable();
    const rows = await query(
      `SELECT name FROM \`${p}cg_languages\` ORDER BY sort_order ASC, name ASC`
    );
    const list = rows.map(r => String(r.name || '').trim()).filter(Boolean);
    res.json({ success: true, data: list });
  } catch (err) {
    console.error('[cg_get_language_list] DB error, using fallback:', err.message);
    res.json({ success: true, data: DEFAULT_LANGUAGES.slice().sort((a, b) => a.localeCompare(b)) });
  }
}

module.exports = { cg_get_local_knowledge, cg_get_language_gift, cg_get_free_gifts, cg_get_language_list, cg_get_gift_prereqs };
