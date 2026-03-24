const { query, queryOne, prefix } = require('../../db');

async function cg_get_career_trappings(req, res) {
  let careerId = parseInt(req.body.career_id, 10) || 0;
  const careerSlug = (req.body.career_slug || '').trim();

  if (careerId <= 0 && careerSlug) {
    const p = prefix();
    const cr = `${p}customtables_table_careers`;
    const found = await queryOne(`SELECT ct_id FROM ${cr} WHERE ct_slug = ? AND published = 1 LIMIT 1`, [careerSlug]);
    if (found) careerId = found.ct_id;
  }

  if (!careerId || careerId <= 0) {
    return res.json({ success: false, data: 'Invalid career ID or slug.' });
  }

  const p = prefix();
  const tm = `${p}customtables_table_trappings_map`;
  const eq = `${p}customtables_table_equipment`;
  const wp = `${p}customtables_table_weapons`;

  let rows;
  try {
    rows = await query(`
    SELECT
      tm.ct_id             AS map_id,
      tm.ct_item_kind      AS kind,
      tm.ct_item_slug      AS item_slug,
      tm.ct_qty            AS qty,
      tm.ct_token          AS token,
      tm.ct_resolve_method AS resolve_method,

      eq.ct_name           AS eq_name,
      eq.ct_slug           AS eq_slug,
      eq.ct_category       AS eq_category,
      eq.ct_cost_d         AS eq_cost_d,
      eq.ct_cost_text      AS eq_cost_text,
      eq.ct_armor_dice     AS eq_armor_dice,
      eq.ct_cover_dice     AS eq_cover_dice,
      eq.ct_skill_dice     AS eq_skill_dice,
      eq.ct_source_book    AS eq_source_book,
      eq.ct_pg_no          AS eq_pg_no,

      wp.ct_weapons_name   AS wp_name,
      wp.ct_slug           AS wp_slug,
      wp.ct_weapon_class   AS wp_class,
      wp.ct_attack_dice    AS wp_attack_dice,
      wp.ct_damage_mod     AS wp_damage_mod,
      wp.ct_range_band     AS wp_range_band,
      wp.ct_parry_die      AS wp_parry_die,
      wp.ct_cover_die      AS wp_cover_die,
      wp.ct_effect         AS wp_effect,
      wp.ct_cost_d         AS wp_cost_d,
      wp.ct_source_book    AS wp_source_book,
      wp.ct_pg_no          AS wp_pg_no

    FROM ${tm} tm

    LEFT JOIN ${eq} eq
      ON (tm.ct_item_kind = 'equipment' AND tm.ct_item_slug = eq.ct_slug AND eq.published = 1)
    LEFT JOIN ${wp} wp
      ON (tm.ct_item_kind = 'weapon'    AND tm.ct_item_slug = wp.ct_slug AND wp.published = 1)

    WHERE tm.ct_career_id = ? AND tm.published = 1
    ORDER BY tm.ct_id ASC
  `, [careerId]);
  } catch (err) {
    console.error('[cg_get_career_trappings] SQL error:', err.message || err);
    return res.json({ success: true, data: [] });
  }

  const trappings = rows.map(r => {
    const item = {
      map_id:         r.map_id,
      kind:           r.kind || 'equipment',
      qty:            r.qty  || 1,
      token:          r.token || '',
      resolve_method: r.resolve_method || '',
    };

    if (r.kind === 'weapon') {
      item.name         = r.wp_name   || r.token || r.item_slug || '';
      item.slug         = r.wp_slug   || r.item_slug || '';
      item.weapon_class = r.wp_class  || '';
      item.attack_dice  = r.wp_attack_dice || '';
      item.damage_mod   = r.wp_damage_mod  || 0;
      item.range_band   = r.wp_range_band  || 'Melee';
      item.parry_die    = r.wp_parry_die   || '';
      item.cover_die    = r.wp_cover_die   || '';
      item.effect       = r.wp_effect      || '';
      item.cost_d       = r.wp_cost_d      || null;
      item.source_book  = r.wp_source_book || '';
      item.pg_no        = r.wp_pg_no       || '';
    } else {
      item.name         = r.eq_name    || r.token || r.item_slug || '';
      item.slug         = r.eq_slug    || r.item_slug || '';
      item.category     = r.eq_category || '';
      item.cost_d       = r.eq_cost_d   || null;
      item.cost_text    = r.eq_cost_text || '';
      item.armor_dice   = r.eq_armor_dice  || '';
      item.cover_dice   = r.eq_cover_dice  || '';
      item.skill_dice   = r.eq_skill_dice  || '';
      item.source_book  = r.eq_source_book || '';
      item.pg_no        = r.eq_pg_no       || '';
    }

    if (!item.name && r.token) item.name = r.token;

    return item;
  });

  res.json({ success: true, data: trappings });
}

async function cg_get_equipment_catalog(req, res) {
  const p = prefix();
  const eq = `${p}customtables_table_equipment`;
  const wp = `${p}customtables_table_weapons`;

  const search   = (req.body.search   || '').trim();
  const category = (req.body.category || '').trim();
  const kind     = (req.body.kind     || '').trim();

  const results = [];

  if (!kind || kind === 'equipment') {
    let sql = `
      SELECT
        'equipment'     AS kind,
        ct_name         AS name,
        ct_slug         AS slug,
        ct_category     AS category,
        ct_subcategory  AS subcategory,
        ct_item_type    AS item_type,
        ct_cost_d       AS cost_d,
        ct_cost_text    AS cost_text,
        ct_armor_dice   AS armor_dice,
        ct_cover_dice   AS cover_dice,
        ct_skill_dice   AS skill_dice,
        ct_effect       AS effect,
        ct_notes        AS notes,
        ct_source_book  AS source_book,
        ct_pg_no        AS pg_no,
        ct_is_rare      AS is_rare
      FROM ${eq}
      WHERE published = 1
    `;
    const params = [];
    if (search) {
      sql += ` AND ct_name LIKE ?`;
      params.push(`%${search}%`);
    }
    if (category) {
      sql += ` AND ct_category = ?`;
      params.push(category);
    }
    sql += ` ORDER BY ct_category ASC, ct_name ASC LIMIT 500`;
    const rows = await query(sql, params);
    rows.forEach(r => results.push(r));
  }

  if (!kind || kind === 'weapon') {
    let sql = `
      SELECT
        'weapon'           AS kind,
        ct_weapons_name    AS name,
        ct_slug            AS slug,
        ct_weapon_class    AS category,
        ct_weapon_class    AS subcategory,
        ct_weapon_class    AS item_type,
        ct_cost_d          AS cost_d,
        NULL               AS cost_text,
        NULL               AS armor_dice,
        ct_cover_die       AS cover_dice,
        NULL               AS skill_dice,
        ct_effect          AS effect,
        ct_description     AS notes,
        ct_source_book     AS source_book,
        ct_pg_no           AS pg_no,
        0                  AS is_rare,
        ct_attack_dice     AS attack_dice,
        ct_damage_mod      AS damage_mod,
        ct_range_band      AS range_band,
        ct_parry_die       AS parry_die,
        ct_spark_die       AS spark_die
      FROM ${wp}
      WHERE published = 1 AND (ct_is_species_weapon IS NULL OR ct_is_species_weapon = 0)
    `;
    const params = [];
    if (search) {
      sql += ` AND ct_weapons_name LIKE ?`;
      params.push(`%${search}%`);
    }
    if (category && kind === 'weapon') {
      sql += ` AND ct_weapon_class = ?`;
      params.push(category);
    }
    sql += ` ORDER BY ct_weapon_class ASC, ct_weapons_name ASC LIMIT 500`;
    const rows = await query(sql, params);
    rows.forEach(r => results.push(r));
  }

  res.json({ success: true, data: results });
}

async function cg_get_money_list(req, res) {
  const p = prefix();
  try {
    const rows = await query(`
      SELECT
        ct_id             AS id,
        ct_name           AS name,
        ct_slug           AS slug,
        ct_kind           AS kind,
        ct_value_denarii  AS value_denarii,
        ct_trade_value_denarii AS trade_value_denarii,
        ct_exchange_rate_text  AS exchange_rate_text,
        ct_is_legal_tender     AS is_legal_tender,
        ct_is_proscribed       AS is_proscribed,
        ct_notes               AS notes,
        ct_source_book         AS source_book
      FROM ${p}customtables_table_money
      WHERE published = 1
      ORDER BY ct_value_denarii DESC
    `);
    res.json({ success: true, data: rows });
  } catch (err) {
    console.warn('[cg_get_money_list] query failed:', err.message);
    res.json({ success: true, data: [] });
  }
}

module.exports = { cg_get_career_trappings, cg_get_equipment_catalog, cg_get_money_list };
