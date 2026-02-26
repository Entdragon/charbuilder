const { query, queryOne, prefix } = require('../../db');

async function cg_get_skills_list(req, res) {
  const p = prefix();
  const rows = await query(
    `SELECT id, ct_skill_name AS name FROM ${p}customtables_table_skills ORDER BY ct_skill_name ASC`
  );
  res.json({ success: true, data: rows });
}

async function cg_get_skill_detail(req, res) {
  const skillId = parseInt(req.body.id, 10);
  if (!skillId || skillId <= 0) {
    return res.json({ success: false, data: 'Invalid skill ID.' });
  }
  const p = prefix();
  const row = await queryOne(
    `SELECT id, ct_skill_name AS name, ct_skill_description AS description
     FROM ${p}customtables_table_skills WHERE id = ?`,
    [skillId]
  );
  if (!row) return res.json({ success: false, data: 'Skill not found.' });
  res.json({ success: true, data: row });
}

module.exports = { cg_get_skills_list, cg_get_skill_detail };
