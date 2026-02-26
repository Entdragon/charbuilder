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
  const suffixes = [
    '', 'two','three','four','five','six',
    'seven','eight','nine','ten','eleven',
    'twelve','thirteen','fourteen','fifteen',
    'sixteen','seventeen','eighteen','nineteen',
  ];
  const requireCols = suffixes.map(s => s ? `ct_gifts_requires_${s}` : 'ct_gifts_requires').join(', ');

  const rows = await query(`
    SELECT
      ct_id                    AS id,
      ct_gifts_name            AS name,
      ct_gifts_allows_multiple AS allows_multiple,
      ct_gifts_manifold        AS ct_gifts_manifold,
      ${requireCols}
    FROM ${p}customtables_table_gifts
    ORDER BY ct_gifts_name ASC
  `);
  res.json({ success: true, data: rows });
}

async function cg_get_language_list(req, res) {
  const p = prefix();
  const DEFAULT_LANGUAGES = [
    'Calabrian', 'Common', 'Dwarven', 'Elven', 'Goblin', 'Hesperian',
    'Kawtaw', 'Mordic', 'Old Calabrian', 'Orcish', 'Sylvan', 'Urathi',
  ];
  const extraLangs = [];

  // Attempt 1: dedicated cg_character_language table (staging/future prod)
  try {
    const cols = await query(`DESCRIBE \`${p}cg_character_language\``);
    // Find the column that holds language text — prefer one with 'language' in the name,
    // skip id columns and any column that looks like a foreign key (ends in _id or _character)
    const langCol = cols
      .map(c => c.Field || c.field || '')
      .find(f => /language/i.test(f) && !/^ct_id$/i.test(f) && !/_character$/i.test(f));

    if (langCol) {
      const rows = await query(
        `SELECT DISTINCT \`${langCol}\` AS lang FROM \`${p}cg_character_language\`
         WHERE \`${langCol}\` IS NOT NULL AND \`${langCol}\` <> ''`
      );
      rows.forEach(r => {
        const v = String(r.lang || '').trim();
        if (v) extraLangs.push(v);
      });
    }
  } catch (_) {}

  // Attempt 2: language column in character_records
  try {
    const rows = await query(
      `SELECT DISTINCT language FROM ${p}character_records
       WHERE language IS NOT NULL AND language <> ''`
    );
    rows.forEach(r => {
      const v = String(r.language || '').trim();
      if (v) extraLangs.push(v);
    });
  } catch (_) {}

  const merged = [...new Set([...DEFAULT_LANGUAGES, ...extraLangs])].sort((a, b) =>
    a.toLowerCase().localeCompare(b.toLowerCase())
  );
  res.json({ success: true, data: merged });
}

module.exports = { cg_get_local_knowledge, cg_get_language_gift, cg_get_free_gifts, cg_get_language_list };
