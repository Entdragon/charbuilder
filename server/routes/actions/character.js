const { query, queryOne, prefix } = require('../../db');

function safeJson(val) {
  if (!val) return null;
  if (typeof val === 'object') return JSON.stringify(val);
  return val;
}

function normalizeRow(row) {
  if (!row) return null;
  row.id         = String(row.id || '');
  row.species_id = String(row.species || row.species_id || 0);
  row.career_id  = String(row.career  || row.career_id  || 0);

  if (row.extra_career_1 != null) row.extra_career_1 = String(row.extra_career_1 || 0);
  if (row.extra_career_2 != null) row.extra_career_2 = String(row.extra_career_2 || 0);

  if (row.skill_marks) {
    try {
      if (typeof row.skill_marks === 'string') row.skill_marks = JSON.parse(row.skill_marks);
    } catch (e) { row.skill_marks = {}; }
  } else {
    row.skill_marks = {};
  }

  if (row.career_gift_replacements) {
    try {
      if (typeof row.career_gift_replacements === 'string') {
        row.career_gift_replacements = JSON.parse(row.career_gift_replacements);
      }
    } catch (e) { row.career_gift_replacements = {}; }
  } else {
    row.career_gift_replacements = {};
  }

  return row;
}

async function cg_load_characters(req, res) {
  const p   = prefix();
  const uid = req.session.userId;
  const rows = await query(
    `SELECT id, name, player_name, age, gender, species AS species_id, career AS career_id
     FROM ${p}character_records WHERE user_id = ? ORDER BY updated DESC`,
    [uid]
  );
  const normalized = rows.map(r => ({
    ...r,
    id:         String(r.id),
    species_id: String(r.species_id || 0),
    career_id:  String(r.career_id  || 0),
  }));
  res.json({ success: true, data: normalized });
}

async function cg_get_character(req, res) {
  const charId = parseInt(req.body.id, 10);
  if (!charId || charId <= 0) return res.json({ success: false, data: 'Invalid character ID.' });

  const p   = prefix();
  const uid = req.session.userId;
  const row = await queryOne(
    `SELECT * FROM ${p}character_records WHERE id = ? AND user_id = ? LIMIT 1`,
    [charId, uid]
  );
  if (!row) return res.json({ success: false, data: 'Character not found.' });
  res.json({ success: true, data: normalizeRow(row) });
}

async function cg_save_character(req, res) {
  const data = req.body.character || {};
  const p    = prefix();
  const uid  = req.session.userId;
  const id   = parseInt(data.id, 10) || 0;

  const skillMarks = data.skill_marks || {};
  const giftReplacements = data.career_gift_replacements || {};
  const freeGifts = Array.isArray(data.free_gifts)
    ? data.free_gifts
    : [data.free_gift_1, data.free_gift_2, data.free_gift_3];

  const fields = {
    name:                         (data.name || '').toString().slice(0, 100),
    player_name:                  (data.player_name || '').toString().slice(0, 255),
    age:                          (data.age  || '').toString().slice(0, 10),
    gender:                       (data.gender || '').toString().slice(0, 20),
    will:                         (data.will  || '').toString().slice(0, 4),
    speed:                        (data.speed || '').toString().slice(0, 4),
    body:                         (data.body  || '').toString().slice(0, 4),
    mind:                         (data.mind  || '').toString().slice(0, 4),
    species:                      parseInt(data.species_id || data.species, 10) || 0,
    career:                       parseInt(data.career_id  || data.career,  10) || 0,
    free_gift_1:                  parseInt(freeGifts[0], 10) || 0,
    free_gift_2:                  parseInt(freeGifts[1], 10) || 0,
    free_gift_3:                  parseInt(freeGifts[2], 10) || 0,
    career_gift_replacements:     JSON.stringify(giftReplacements),
    local_area:                   (data.local_area || '').toString(),
    language:                     (data.language   || '').toString(),
    skill_marks:                  JSON.stringify(skillMarks),
    description:                  (data.description || '').toString(),
    backstory:                    (data.backstory   || '').toString(),
    motto:                        (data.motto  || '').toString(),
    goal1:                        (data.goal1  || '').toString(),
    goal2:                        (data.goal2  || '').toString(),
    goal3:                        (data.goal3  || '').toString(),
    extra_career_1:               parseInt(data.extra_career_1, 10) || null,
    extra_trait_career_1:         (data.extra_trait_career_1 || null),
    extra_career_2:               parseInt(data.extra_career_2, 10) || null,
    extra_trait_career_2:         (data.extra_trait_career_2 || null),
    trait_species:                (data.trait_species || '').toString().slice(0, 10),
    trait_career:                 (data.trait_career  || '').toString().slice(0, 10),
    increased_trait_career_target:(data.increased_trait_career_target || null),
    updated:                      new Date().toISOString().slice(0, 19).replace('T', ' '),
  };

  if (id > 0) {
    const exists = await queryOne(
      `SELECT id FROM ${p}character_records WHERE id = ? AND user_id = ? LIMIT 1`,
      [id, uid]
    );
    if (!exists) return res.json({ success: false, data: 'Character not found.' });

    const setClauses = Object.keys(fields).map(k => `\`${k}\` = ?`).join(', ');
    await query(
      `UPDATE ${p}character_records SET ${setClauses} WHERE id = ? AND user_id = ?`,
      [...Object.values(fields), id, uid]
    );
    res.json({ success: true, data: { id: String(id) } });
  } else {
    fields.user_id = uid;
    fields.created = new Date().toISOString().slice(0, 19).replace('T', ' ');
    const cols    = Object.keys(fields).map(k => `\`${k}\``).join(', ');
    const holders = Object.keys(fields).map(() => '?').join(', ');
    const result  = await query(
      `INSERT INTO ${p}character_records (${cols}) VALUES (${holders})`,
      Object.values(fields)
    );
    res.json({ success: true, data: { id: String(result.insertId) } });
  }
}

module.exports = { cg_load_characters, cg_get_character, cg_save_character };
