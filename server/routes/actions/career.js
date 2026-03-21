const { query, queryOne, prefix } = require('../../db');

async function cg_get_career_list(req, res) {
  const p = prefix();
  const c  = `${p}customtables_table_careers`;
  const cs = `${p}customtables_table_career_skills`;
  const cg = `${p}customtables_table_career_gifts`;
  const sk = `${p}customtables_table_skills`;

  const rows = await query(`
    SELECT
      c.ct_id          AS id,
      c.ct_career_name AS name,
      cg1.gift_id      AS gift_id_1,
      cg2.gift_id      AS gift_id_2,
      cg3.gift_id      AS gift_id_3,
      sk1.ct_skill_name AS skill_one,
      sk2.ct_skill_name AS skill_two,
      sk3.ct_skill_name AS skill_three
    FROM ${c} c
    LEFT JOIN ${cg} cg1 ON cg1.career_id = c.ct_id AND cg1.sort = 1
    LEFT JOIN ${cg} cg2 ON cg2.career_id = c.ct_id AND cg2.sort = 2
    LEFT JOIN ${cg} cg3 ON cg3.career_id = c.ct_id AND cg3.sort = 3
    LEFT JOIN ${cs} cs1 ON cs1.career_id = c.ct_id AND cs1.sort = 1
    LEFT JOIN ${cs} cs2 ON cs2.career_id = c.ct_id AND cs2.sort = 2
    LEFT JOIN ${cs} cs3 ON cs3.career_id = c.ct_id AND cs3.sort = 3
    LEFT JOIN ${sk} sk1 ON sk1.id = cs1.skill_id
    LEFT JOIN ${sk} sk2 ON sk2.id = cs2.skill_id
    LEFT JOIN ${sk} sk3 ON sk3.id = cs3.skill_id
    ORDER BY c.ct_career_name ASC
  `);

  res.json({ success: true, data: rows });
}

async function cg_get_career_gifts(req, res) {
  const careerId = parseInt(req.body.id, 10);
  if (!careerId || careerId <= 0) {
    return res.json({ success: false, data: 'Invalid career ID.' });
  }

  const p  = prefix();
  const c  = `${p}customtables_table_careers`;
  const cs = `${p}customtables_table_career_skills`;
  const cg = `${p}customtables_table_career_gifts`;
  const sk = `${p}customtables_table_skills`;
  const g  = `${p}customtables_table_gifts`;

  const row = await queryOne(`
    SELECT
      c.ct_career_name     AS careerName,

      cg1.gift_id          AS gift_id_1,
      g1.ct_gifts_name     AS gift_1,
      g1.ct_gifts_manifold AS manifold_1,

      cg2.gift_id          AS gift_id_2,
      g2.ct_gifts_name     AS gift_2,
      g2.ct_gifts_manifold AS manifold_2,

      cg3.gift_id          AS gift_id_3,
      g3.ct_gifts_name     AS gift_3,
      g3.ct_gifts_manifold AS manifold_3,

      sk1.ct_skill_name AS skill_one,
      sk2.ct_skill_name AS skill_two,
      sk3.ct_skill_name AS skill_three
    FROM ${c} c
    LEFT JOIN ${cg} cg1 ON cg1.career_id = c.ct_id AND cg1.sort = 1
    LEFT JOIN ${cg} cg2 ON cg2.career_id = c.ct_id AND cg2.sort = 2
    LEFT JOIN ${cg} cg3 ON cg3.career_id = c.ct_id AND cg3.sort = 3
    LEFT JOIN ${g}  g1  ON g1.ct_id = cg1.gift_id
    LEFT JOIN ${g}  g2  ON g2.ct_id = cg2.gift_id
    LEFT JOIN ${g}  g3  ON g3.ct_id = cg3.gift_id
    LEFT JOIN ${cs} cs1 ON cs1.career_id = c.ct_id AND cs1.sort = 1
    LEFT JOIN ${cs} cs2 ON cs2.career_id = c.ct_id AND cs2.sort = 2
    LEFT JOIN ${cs} cs3 ON cs3.career_id = c.ct_id AND cs3.sort = 3
    LEFT JOIN ${sk} sk1 ON sk1.id = cs1.skill_id
    LEFT JOIN ${sk} sk2 ON sk2.id = cs2.skill_id
    LEFT JOIN ${sk} sk3 ON sk3.id = cs3.skill_id
    WHERE c.ct_id = ?
  `, [careerId]);

  if (!row) return res.json({ success: false, data: 'Career not found.' });
  res.json({ success: true, data: row });
}

module.exports = { cg_get_career_list, cg_get_career_gifts };
