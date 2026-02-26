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

module.exports = { cg_get_local_knowledge, cg_get_language_gift, cg_get_free_gifts };
