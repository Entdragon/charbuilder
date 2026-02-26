const { query, queryOne, prefix } = require('../../db');

async function cg_get_career_list(req, res) {
  const p = prefix();
  const rows = await query(`
    SELECT
      ct_id                 AS id,
      ct_career_name        AS name,
      ct_career_gift_one    AS gift_id_1,
      ct_career_gift_two    AS gift_id_2,
      ct_career_gift_three  AS gift_id_3,
      ct_career_skill_one   AS skill_one,
      ct_career_skill_two   AS skill_two,
      ct_career_skill_three AS skill_three
    FROM ${p}customtables_table_careers
    ORDER BY ct_career_name ASC
  `);
  res.json({ success: true, data: rows });
}

async function cg_get_career_gifts(req, res) {
  const careerId = parseInt(req.body.id, 10);
  if (!careerId || careerId <= 0) {
    return res.json({ success: false, data: 'Invalid career ID.' });
  }

  const p = prefix();
  const c = `${p}customtables_table_careers`;
  const g = `${p}customtables_table_gifts`;

  const row = await queryOne(`
    SELECT
      c.ct_career_name        AS careerName,

      c.ct_career_gift_one    AS gift_id_1,
      g1.ct_gifts_name        AS gift_1,
      g1.ct_gifts_manifold    AS manifold_1,

      c.ct_career_gift_two    AS gift_id_2,
      g2.ct_gifts_name        AS gift_2,
      g2.ct_gifts_manifold    AS manifold_2,

      c.ct_career_gift_three  AS gift_id_3,
      g3.ct_gifts_name        AS gift_3,
      g3.ct_gifts_manifold    AS manifold_3,

      c.ct_career_skill_one    AS skill_one,
      c.ct_career_skill_two    AS skill_two,
      c.ct_career_skill_three  AS skill_three
    FROM ${c} c
    LEFT JOIN ${g} g1 ON c.ct_career_gift_one   = g1.ct_id
    LEFT JOIN ${g} g2 ON c.ct_career_gift_two   = g2.ct_id
    LEFT JOIN ${g} g3 ON c.ct_career_gift_three = g3.ct_id
    WHERE c.ct_id = ?
  `, [careerId]);

  if (!row) return res.json({ success: false, data: 'Career not found.' });
  res.json({ success: true, data: row });
}

module.exports = { cg_get_career_list, cg_get_career_gifts };
