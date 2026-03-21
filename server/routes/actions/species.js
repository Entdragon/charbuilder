const { query, queryOne, prefix } = require('../../db');

async function cg_get_species_list(req, res) {
  const p = prefix();
  const rows = await query(
    `SELECT ct_id AS id, ct_species_name AS name FROM ${p}customtables_table_species ORDER BY ct_species_name ASC`
  );
  res.json({ success: true, data: rows });
}

async function cg_get_species_profile(req, res) {
  const speciesId = parseInt(req.body.id, 10);
  if (!speciesId || speciesId <= 0) {
    return res.json({ success: false, data: 'Invalid species ID.' });
  }

  const p  = prefix();
  const s  = `${p}customtables_table_species`;
  const st = `${p}customtables_table_species_traits`;
  const g  = `${p}customtables_table_gifts`;
  const h  = `${p}customtables_table_habitat`;
  const d  = `${p}customtables_table_diet`;
  const cy = `${p}customtables_table_cycle`;
  const sn = `${p}customtables_table_senses`;
  const w  = `${p}customtables_table_weapons`;

  const row = await queryOne(`
    SELECT
      sp.ct_species_name AS speciesName,

      tg1.ref_id           AS gift_id_1,
      g1.ct_gifts_name     AS gift_1,
      g1.ct_gifts_manifold AS manifold_1,

      tg2.ref_id           AS gift_id_2,
      g2.ct_gifts_name     AS gift_2,
      g2.ct_gifts_manifold AS manifold_2,

      tg3.ref_id           AS gift_id_3,
      g3.ct_gifts_name     AS gift_3,
      g3.ct_gifts_manifold AS manifold_3,

      ts1.text_value     AS skill_one,
      ts2.text_value     AS skill_two,
      ts3.text_value     AS skill_three,

      hb.ct_habitat_name AS habitat,
      dt.ct_diet_name    AS diet,
      cyc.ct_cycle_name  AS cycle,

      sn1.ct_senses_name AS sense_1,
      sn2.ct_senses_name AS sense_2,
      sn3.ct_senses_name AS sense_3,

      w1.ct_weapons_name AS weapon_1,
      w2.ct_weapons_name AS weapon_2,
      w3.ct_weapons_name AS weapon_3

    FROM ${s} sp

    LEFT JOIN ${st} tg1 ON tg1.species_id = sp.ct_id AND tg1.trait_key = 'gift_1'
    LEFT JOIN ${st} tg2 ON tg2.species_id = sp.ct_id AND tg2.trait_key = 'gift_2'
    LEFT JOIN ${st} tg3 ON tg3.species_id = sp.ct_id AND tg3.trait_key = 'gift_3'
    LEFT JOIN ${g}  g1  ON g1.ct_id = tg1.ref_id
    LEFT JOIN ${g}  g2  ON g2.ct_id = tg2.ref_id
    LEFT JOIN ${g}  g3  ON g3.ct_id = tg3.ref_id

    LEFT JOIN ${st} ts1 ON ts1.species_id = sp.ct_id AND ts1.trait_key = 'skill_1'
    LEFT JOIN ${st} ts2 ON ts2.species_id = sp.ct_id AND ts2.trait_key = 'skill_2'
    LEFT JOIN ${st} ts3 ON ts3.species_id = sp.ct_id AND ts3.trait_key = 'skill_3'

    LEFT JOIN ${st} th  ON th.species_id  = sp.ct_id AND th.trait_key = 'habitat'
    LEFT JOIN ${h}  hb  ON hb.ct_id = th.ref_id

    LEFT JOIN ${st} tdt ON tdt.species_id = sp.ct_id AND tdt.trait_key = 'diet'
    LEFT JOIN ${d}  dt  ON dt.ct_id = tdt.ref_id

    LEFT JOIN ${st} tcy ON tcy.species_id = sp.ct_id AND tcy.trait_key = 'cycle'
    LEFT JOIN ${cy} cyc ON cyc.ct_id = tcy.ref_id

    LEFT JOIN ${st} tsn1 ON tsn1.species_id = sp.ct_id AND tsn1.trait_key = 'sense_1'
    LEFT JOIN ${st} tsn2 ON tsn2.species_id = sp.ct_id AND tsn2.trait_key = 'sense_2'
    LEFT JOIN ${st} tsn3 ON tsn3.species_id = sp.ct_id AND tsn3.trait_key = 'sense_3'
    LEFT JOIN ${sn} sn1  ON sn1.ct_id = tsn1.ref_id
    LEFT JOIN ${sn} sn2  ON sn2.ct_id = tsn2.ref_id
    LEFT JOIN ${sn} sn3  ON sn3.ct_id = tsn3.ref_id

    LEFT JOIN ${st} tw1 ON tw1.species_id = sp.ct_id AND tw1.trait_key = 'weapon_1'
    LEFT JOIN ${st} tw2 ON tw2.species_id = sp.ct_id AND tw2.trait_key = 'weapon_2'
    LEFT JOIN ${st} tw3 ON tw3.species_id = sp.ct_id AND tw3.trait_key = 'weapon_3'
    LEFT JOIN ${w}  w1  ON w1.ct_id = tw1.ref_id
    LEFT JOIN ${w}  w2  ON w2.ct_id = tw2.ref_id
    LEFT JOIN ${w}  w3  ON w3.ct_id = tw3.ref_id

    WHERE sp.ct_id = ?
  `, [speciesId]);

  if (!row) return res.json({ success: false, data: 'Species not found.' });
  res.json({ success: true, data: row });
}

module.exports = { cg_get_species_list, cg_get_species_profile };
