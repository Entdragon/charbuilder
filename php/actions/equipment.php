<?php
require_once __DIR__ . '/../includes/db.php';

// ── Shared trappings JOIN builder ─────────────────────────────────────────────

function cg_build_trappings_sql(string $p): string {
    $tm = "{$p}customtables_table_trappings_map";
    $eq = "{$p}customtables_table_equipment";
    $wp = "{$p}customtables_table_weapons";
    $ia = "{$p}customtables_table_item_aliases";

    return "
    SELECT
      tm.ct_id                AS map_id,
      tm.ct_item_kind         AS kind,
      tm.ct_item_slug         AS item_slug,
      tm.ct_qty               AS qty,
      tm.ct_token             AS token,
      tm.ct_resolve_method    AS resolve_method,
      tm.ct_gift_id           AS gift_id,
      tm.ct_gift_name         AS gift_name,
      tm.ct_source_type       AS source_type,

      COALESCE(eq.ct_name,       eq_a.ct_name)        AS eq_name,
      COALESCE(eq.ct_slug,       eq_a.ct_slug)        AS eq_slug,
      COALESCE(eq.ct_category,   eq_a.ct_category)    AS eq_category,
      COALESCE(eq.ct_cost_d,     eq_a.ct_cost_d)      AS eq_cost_d,
      COALESCE(eq.ct_cost_text,  eq_a.ct_cost_text)   AS eq_cost_text,
      COALESCE(eq.ct_armor_dice, eq_a.ct_armor_dice)  AS eq_armor_dice,
      COALESCE(eq.ct_cover_dice, eq_a.ct_cover_dice)  AS eq_cover_dice,
      COALESCE(eq.ct_skill_dice, eq_a.ct_skill_dice)  AS eq_skill_dice,
      COALESCE(eq.ct_source_book,eq_a.ct_source_book) AS eq_source_book,
      COALESCE(eq.ct_pg_no,      eq_a.ct_pg_no)       AS eq_pg_no,

      COALESCE(wp.ct_weapons_name, wp_a.ct_weapons_name) AS wp_name,
      COALESCE(wp.ct_slug,         wp_a.ct_slug)         AS wp_slug,
      COALESCE(wp.ct_weapon_class, wp_a.ct_weapon_class) AS wp_class,
      COALESCE(wp.ct_attack_dice,  wp_a.ct_attack_dice)  AS wp_attack_dice,
      COALESCE(wp.ct_damage_mod,   wp_a.ct_damage_mod)   AS wp_damage_mod,
      COALESCE(wp.ct_range_band,   wp_a.ct_range_band)   AS wp_range_band,
      COALESCE(wp.ct_parry_die,    wp_a.ct_parry_die)    AS wp_parry_die,
      COALESCE(wp.ct_cover_die,    wp_a.ct_cover_die)    AS wp_cover_die,
      COALESCE(wp.ct_effect,       wp_a.ct_effect)       AS wp_effect,
      COALESCE(wp.ct_cost_d,       wp_a.ct_cost_d)       AS wp_cost_d,
      COALESCE(wp.ct_source_book,  wp_a.ct_source_book)  AS wp_source_book,
      COALESCE(wp.ct_pg_no,        wp_a.ct_pg_no)        AS wp_pg_no

    FROM {$tm} tm

    LEFT JOIN {$eq} eq
      ON  tm.ct_item_kind = 'equipment'
      AND tm.ct_item_slug = eq.ct_slug
      AND eq.published    = 1

    LEFT JOIN {$ia} ia_eq
      ON  tm.ct_item_kind  = 'equipment'
      AND eq.ct_id         IS NULL
      AND ia_eq.ct_target_kind  = 'equipment'
      AND ia_eq.ct_alias_norm COLLATE utf8mb4_unicode_ci = tm.ct_item_slug COLLATE utf8mb4_unicode_ci
      AND ia_eq.published  = 1
    LEFT JOIN {$eq} eq_a
      ON  ia_eq.ct_target_slug IS NOT NULL
      AND eq_a.ct_slug COLLATE utf8mb4_unicode_ci = ia_eq.ct_target_slug COLLATE utf8mb4_unicode_ci
      AND eq_a.published = 1

    LEFT JOIN {$wp} wp
      ON  tm.ct_item_kind = 'weapon'
      AND tm.ct_item_slug = wp.ct_slug
      AND wp.published    = 1

    LEFT JOIN {$ia} ia_wp
      ON  tm.ct_item_kind  = 'weapon'
      AND wp.ct_id         IS NULL
      AND ia_wp.ct_target_kind  = 'weapon'
      AND ia_wp.ct_alias_norm COLLATE utf8mb4_unicode_ci = tm.ct_item_slug COLLATE utf8mb4_unicode_ci
      AND ia_wp.published  = 1
    LEFT JOIN {$wp} wp_a
      ON  ia_wp.ct_target_slug IS NOT NULL
      AND wp_a.ct_slug COLLATE utf8mb4_unicode_ci = ia_wp.ct_target_slug COLLATE utf8mb4_unicode_ci
      AND wp_a.published = 1
    ";
}

function cg_map_trapping_row(array $r): array {
    $item = [
        'map_id'         => $r['map_id'],
        'kind'           => $r['kind'] ?: 'equipment',
        'qty'            => $r['qty']  ?: 1,
        'token'          => $r['token'] ?: '',
        'resolve_method' => $r['resolve_method'] ?: '',
        'gift_id'        => $r['gift_id']   ?: null,
        'gift_name'      => $r['gift_name'] ?: '',
        'source_type'    => $r['source_type'] ?: '',
    ];

    if ($r['kind'] === 'weapon') {
        $item['name']         = $r['wp_name']        ?: ($r['token'] ?: ($r['item_slug'] ?: ''));
        $item['slug']         = $r['wp_slug']        ?: ($r['item_slug'] ?: '');
        $item['weapon_class'] = $r['wp_class']       ?: '';
        $item['attack_dice']  = $r['wp_attack_dice'] ?: '';
        $item['damage_mod']   = $r['wp_damage_mod']  ?: 0;
        $item['range_band']   = $r['wp_range_band']  ?: 'Melee';
        $item['parry_die']    = $r['wp_parry_die']   ?: '';
        $item['cover_die']    = $r['wp_cover_die']   ?: '';
        $item['effect']       = $r['wp_effect']      ?: '';
        $item['cost_d']       = $r['wp_cost_d']      ?: null;
        $item['source_book']  = $r['wp_source_book'] ?: '';
        $item['pg_no']        = $r['wp_pg_no']       ?: '';
    } else {
        $item['name']         = $r['eq_name']        ?: ($r['token'] ?: ($r['item_slug'] ?: ''));
        $item['slug']         = $r['eq_slug']        ?: ($r['item_slug'] ?: '');
        $item['category']     = $r['eq_category']    ?: '';
        $item['cost_d']       = $r['eq_cost_d']      ?: null;
        $item['cost_text']    = $r['eq_cost_text']   ?: '';
        $item['armor_dice']   = $r['eq_armor_dice']  ?: '';
        $item['cover_dice']   = $r['eq_cover_dice']  ?: '';
        $item['skill_dice']   = $r['eq_skill_dice']  ?: '';
        $item['source_book']  = $r['eq_source_book'] ?: '';
        $item['pg_no']        = $r['eq_pg_no']       ?: '';
    }

    if (!$item['name'] && $r['token']) $item['name'] = $r['token'];
    return $item;
}

// ── Career trappings ──────────────────────────────────────────────────────────

function cg_get_career_trappings(): void {
    $careerId   = (int) ($_POST['career_id']   ?? 0);
    $careerSlug = trim($_POST['career_slug'] ?? '');

    if ($careerId <= 0 && $careerSlug !== '') {
        $p  = cg_prefix();
        $cr = "{$p}customtables_table_careers";
        $found = cg_query_one(
            "SELECT ct_id FROM {$cr} WHERE ct_slug = ? AND published = 1 LIMIT 1",
            [$careerSlug]
        );
        if ($found) $careerId = (int) $found['ct_id'];
    }

    if ($careerId <= 0) {
        cg_json(['success' => false, 'data' => 'Invalid career ID or slug.']);
        return;
    }

    $p   = cg_prefix();
    $sql = cg_build_trappings_sql($p);

    try {
        $rows = cg_query(
            $sql . " WHERE tm.ct_career_id = ? AND tm.published = 1 ORDER BY tm.ct_id ASC",
            [$careerId]
        );
        cg_json(['success' => true, 'data' => array_map('cg_map_trapping_row', $rows)]);
    } catch (Throwable $e) {
        error_log('[cg_get_career_trappings] SQL error: ' . $e->getMessage());
        cg_json(['success' => true, 'data' => []]);
    }
}

// ── Gift trappings ────────────────────────────────────────────────────────────

function cg_get_gift_trappings(): void {
    $giftId   = (int) ($_POST['gift_id']   ?? 0);
    $giftSlug = trim($_POST['gift_slug'] ?? '');

    if ($giftId <= 0 && $giftSlug === '') {
        cg_json(['success' => false, 'data' => 'Invalid gift ID or slug.']);
        return;
    }

    $p   = cg_prefix();
    $sql = cg_build_trappings_sql($p);

    try {
        if ($giftId > 0) {
            $rows = cg_query(
                $sql . " WHERE tm.ct_gift_id = ? AND tm.published = 1 ORDER BY tm.ct_id ASC",
                [$giftId]
            );
        } else {
            $rows = cg_query(
                $sql . " WHERE tm.ct_gift_slug = ? AND tm.published = 1 ORDER BY tm.ct_id ASC",
                [$giftSlug]
            );
        }
        cg_json(['success' => true, 'data' => array_map('cg_map_trapping_row', $rows)]);
    } catch (Throwable $e) {
        error_log('[cg_get_gift_trappings] SQL error: ' . $e->getMessage());
        cg_json(['success' => true, 'data' => []]);
    }
}

// ── Equipment catalog ─────────────────────────────────────────────────────────

function cg_get_equipment_catalog(): void {
    $p        = cg_prefix();
    $eq       = "{$p}customtables_table_equipment";
    $wp       = "{$p}customtables_table_weapons";
    $search   = trim($_POST['search']   ?? '');
    $category = trim($_POST['category'] ?? '');
    $kind     = trim($_POST['kind']     ?? '');

    $results = [];

    if (!$kind || $kind === 'equipment') {
        // ORDER BY ct_slug ASC within the same name so the canonical (shorter) slug is
        // always encountered first when we deduplicate by name below.
        $sql    = "
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
            FROM {$eq}
            WHERE published = 1
        ";
        $params = [];
        if ($search !== '') {
            $sql   .= " AND ct_name LIKE ?";
            $params[] = "%{$search}%";
        }
        if ($category !== '') {
            $sql   .= " AND ct_category = ?";
            $params[] = $category;
        }
        $sql .= " ORDER BY ct_category ASC, ct_name ASC, ct_slug ASC LIMIT 500";
        foreach (cg_query($sql, $params) as $r) $results[] = $r;
    }

    if (!$kind || $kind === 'weapon') {
        $sql    = "
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
            FROM {$wp}
            WHERE published = 1
              AND (ct_is_species_weapon IS NULL OR ct_is_species_weapon = 0)
              AND NOT (ct_weapon_class = 'brawling' AND ct_slug != 'cestus')
        ";
        $params = [];
        if ($search !== '') {
            $sql   .= " AND ct_weapons_name LIKE ?";
            $params[] = "%{$search}%";
        }
        if ($category !== '' && $kind === 'weapon') {
            $sql   .= " AND ct_weapon_class = ?";
            $params[] = $category;
        }
        $sql .= " ORDER BY ct_weapon_class ASC, ct_weapons_name ASC LIMIT 500";
        foreach (cg_query($sql, $params) as $r) $results[] = $r;
    }

    // Deduplicate by lowercase name — the DB may have multiple rows for the same item
    // (e.g. canonical slug + a -boj bundle variant with identical display name).
    // The first occurrence wins; canonical slugs sort before variant suffixes due to ORDER BY above.
    $seen    = [];
    $deduped = [];
    foreach ($results as $r) {
        $key = strtolower(trim($r['name'] ?? ''));
        if ($key === '' || !isset($seen[$key])) {
            $seen[$key]  = true;
            $deduped[]   = $r;
        }
    }

    cg_json(['success' => true, 'data' => $deduped]);
}

// ── Money list ────────────────────────────────────────────────────────────────

function cg_get_money_list(): void {
    $p = cg_prefix();
    try {
        $rows = cg_query("
            SELECT
              ct_id                  AS id,
              ct_name                AS name,
              ct_slug                AS slug,
              ct_kind                AS kind,
              ct_value_denarii       AS value_denarii,
              ct_trade_value_denarii AS trade_value_denarii,
              ct_exchange_rate_text  AS exchange_rate_text,
              ct_is_legal_tender     AS is_legal_tender,
              ct_is_proscribed       AS is_proscribed,
              ct_notes               AS notes,
              ct_source_book         AS source_book
            FROM {$p}customtables_table_money
            WHERE published = 1
            ORDER BY ct_value_denarii DESC
        ");
        cg_json(['success' => true, 'data' => $rows]);
    } catch (Throwable $e) {
        error_log('[cg_get_money_list] query failed: ' . $e->getMessage());
        cg_json(['success' => true, 'data' => []]);
    }
}
