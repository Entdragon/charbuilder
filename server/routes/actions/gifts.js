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

  const requireSpecialSuffixes = ['','_two','_three','_four','_five','_six','_seven','_eight'];
  const requireSpecialCols = requireSpecialSuffixes
    .map(s => `ct_gifts_requires_special${s}`)
    .join(', ');

  const rows = await query(`
    SELECT
      ct_id                    AS id,
      ct_gifts_name            AS name,
      ct_gifts_allows_multiple AS allows_multiple,
      ct_gifts_manifold        AS ct_gifts_manifold,
      ct_gifts_requires_special,
      ${requireSpecialCols},
      ${requireCols}
    FROM ${p}customtables_table_gifts
    ORDER BY ct_gifts_name ASC
  `);
  res.json({ success: true, data: rows });
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
    // Fallback: return hardcoded list so the app keeps working even if DB migration fails
    console.error('[cg_get_language_list] DB error, using fallback:', err.message);
    res.json({ success: true, data: DEFAULT_LANGUAGES.slice().sort((a, b) => a.localeCompare(b)) });
  }
}

module.exports = { cg_get_local_knowledge, cg_get_language_gift, cg_get_free_gifts, cg_get_language_list };
