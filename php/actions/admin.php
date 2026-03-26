<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

// ── Guard: all admin actions require administrator ────────────────────────────
function cg_admin_require(): void {
    if (!cg_is_logged_in() || empty($_SESSION['cg_is_admin'])) {
        http_response_code(403);
        cg_json(['success' => false, 'data' => 'Admin access required.']);
        exit;
    }
}

// ── Gifts ─────────────────────────────────────────────────────────────────────

function cg_admin_list_gifts(): void {
    cg_admin_require();
    $p   = cg_prefix();
    $t   = $p . 'customtables_table_gifts';
    $search = trim($_POST['search'] ?? '');
    $params = [];
    $where  = '';
    if ($search !== '') {
        $where    = "WHERE ct_gifts_name LIKE ?";
        $params[] = '%' . $search . '%';
    }
    $rows = cg_query("SELECT ct_id, ct_gifts_name, published FROM $t $where ORDER BY ct_gifts_name ASC", $params);
    cg_json(['success' => true, 'data' => $rows]);
}

function cg_admin_get_gift(): void {
    cg_admin_require();
    $id = (int) ($_POST['id'] ?? 0);
    if (!$id) { cg_json(['success' => false, 'data' => 'Missing id.']); return; }

    $p = cg_prefix();
    $t = $p . 'customtables_table_gifts';

    $gift = cg_query_one("SELECT * FROM $t WHERE ct_id = ?", [$id]);
    if (!$gift) { cg_json(['success' => false, 'data' => 'Gift not found.']); return; }

    // prev/next by name order
    $name   = $gift['ct_gifts_name'];
    $prev   = cg_query_one(
        "SELECT ct_id FROM $t WHERE ct_gifts_name < ? ORDER BY ct_gifts_name DESC LIMIT 1",
        [$name]
    );
    $next   = cg_query_one(
        "SELECT ct_id FROM $t WHERE ct_gifts_name > ? ORDER BY ct_gifts_name ASC LIMIT 1",
        [$name]
    );

    cg_json(['success' => true, 'data' => [
        'gift'    => $gift,
        'prev_id' => $prev['ct_id'] ?? null,
        'next_id' => $next['ct_id'] ?? null,
    ]]);
}

function cg_admin_save_gift(): void {
    cg_admin_require();
    $id = (int) ($_POST['id'] ?? 0);
    if (!$id) { cg_json(['success' => false, 'data' => 'Missing id.']); return; }

    $p = cg_prefix();
    $t = $p . 'customtables_table_gifts';

    $allowed = [
        'ct_gifts_name'             => 'string',
        'ct_gifts_effect'           => 'string',
        'ct_gifts_effect_description' => 'string',
        'ct_gift_trigger'           => 'string',
        'ct_pg_no'                  => 'string',
        'ct_slug'                   => 'string',
        'ct_gifts_manifold'         => 'int',
        'ct_gifts_allows_multiple'  => 'int',
        'published'                 => 'int',
    ];

    $sets   = [];
    $params = [];
    foreach ($allowed as $col => $type) {
        if (!array_key_exists($col, $_POST)) continue;
        $val = $_POST[$col];
        if ($type === 'int') $val = (int) $val;
        else                  $val = (string) $val;
        $sets[]   = "$col = ?";
        $params[] = $val;
    }

    if (!$sets) { cg_json(['success' => false, 'data' => 'Nothing to update.']); return; }

    $params[] = $id;
    cg_exec("UPDATE $t SET " . implode(', ', $sets) . ", updated_at = NOW() WHERE ct_id = ?", $params);
    cg_json(['success' => true, 'data' => 'Saved.']);
}

// ── Gift child tables ─────────────────────────────────────────────────────────

function cg_admin_get_gift_children(): void {
    cg_admin_require();
    $gift_id = (int) ($_POST['gift_id'] ?? 0);
    if (!$gift_id) { cg_json(['success' => false, 'data' => 'Missing gift_id.']); return; }
    $p = cg_prefix();

    $rules = cg_query(
        "SELECT ct_id, ct_sort, ct_rule_type, ct_rule_title, ct_cost_text, ct_limit_text, ct_summary, ct_details
         FROM {$p}customtables_table_gift_rules
         WHERE ct_gift_id = ? ORDER BY ct_sort, ct_id",
        [$gift_id]
    );
    $sections = cg_query(
        "SELECT ct_id, ct_sort, ct_section_type, ct_heading, ct_body
         FROM {$p}customtables_table_gift_sections
         WHERE ct_gift_id = ? ORDER BY ct_sort, ct_id",
        [$gift_id]
    );
    cg_json(['success' => true, 'data' => ['rules' => $rules, 'sections' => $sections]]);
}

function cg_admin_save_gift_rule(): void {
    cg_admin_require();
    $ct_id   = (int) ($_POST['ct_id']   ?? 0);
    $gift_id = (int) ($_POST['gift_id'] ?? 0);
    if (!$gift_id) { cg_json(['success' => false, 'data' => 'Missing gift_id.']); return; }
    $p = cg_prefix();
    $t = $p . 'customtables_table_gift_rules';

    $fields = [
        'ct_sort'       => (int)    ($_POST['ct_sort']       ?? 10),
        'ct_rule_type'  => (string) ($_POST['ct_rule_type']  ?? 'passive'),
        'ct_rule_title' => (string) ($_POST['ct_rule_title'] ?? ''),
        'ct_cost_text'  => (string) ($_POST['ct_cost_text']  ?? ''),
        'ct_limit_text' => (string) ($_POST['ct_limit_text'] ?? ''),
        'ct_summary'    => (string) ($_POST['ct_summary']    ?? ''),
        'ct_details'    => (string) ($_POST['ct_details']    ?? ''),
    ];

    if ($ct_id > 0) {
        $sets   = array_map(fn($k) => "$k = ?", array_keys($fields));
        $params = array_merge(array_values($fields), [$ct_id]);
        cg_exec("UPDATE $t SET " . implode(', ', $sets) . ", updated_at = NOW() WHERE ct_id = ?", $params);
        cg_json(['success' => true, 'data' => ['ct_id' => $ct_id]]);
    } else {
        $cols   = implode(', ', array_merge(['ct_gift_id'], array_keys($fields)));
        $phs    = implode(', ', array_fill(0, count($fields) + 1, '?'));
        $params = array_merge([$gift_id], array_values($fields));
        $res    = cg_exec("INSERT INTO $t ($cols, created_at, updated_at) VALUES ($phs, NOW(), NOW())", $params);
        cg_json(['success' => true, 'data' => ['ct_id' => (int)($res['lastInsertId'] ?? 0)]]);
    }
}

function cg_admin_delete_gift_rule(): void {
    cg_admin_require();
    $ct_id = (int) ($_POST['ct_id'] ?? 0);
    if (!$ct_id) { cg_json(['success' => false, 'data' => 'Missing ct_id.']); return; }
    $p = cg_prefix();
    cg_exec("DELETE FROM {$p}customtables_table_gift_rules WHERE ct_id = ?", [$ct_id]);
    cg_json(['success' => true, 'data' => 'Deleted.']);
}

function cg_admin_save_gift_section(): void {
    cg_admin_require();
    $ct_id   = (int) ($_POST['ct_id']   ?? 0);
    $gift_id = (int) ($_POST['gift_id'] ?? 0);
    if (!$gift_id) { cg_json(['success' => false, 'data' => 'Missing gift_id.']); return; }
    $p = cg_prefix();
    $t = $p . 'customtables_table_gift_sections';

    $fields = [
        'ct_sort'         => (int)    ($_POST['ct_sort']         ?? 10),
        'ct_section_type' => (string) ($_POST['ct_section_type'] ?? 'rules'),
        'ct_heading'      => (string) ($_POST['ct_heading']      ?? ''),
        'ct_body'         => (string) ($_POST['ct_body']         ?? ''),
    ];

    if ($ct_id > 0) {
        $sets   = array_map(fn($k) => "$k = ?", array_keys($fields));
        $params = array_merge(array_values($fields), [$ct_id]);
        cg_exec("UPDATE $t SET " . implode(', ', $sets) . ", updated_at = NOW() WHERE ct_id = ?", $params);
        cg_json(['success' => true, 'data' => ['ct_id' => $ct_id]]);
    } else {
        $cols   = implode(', ', array_merge(['ct_gift_id'], array_keys($fields)));
        $phs    = implode(', ', array_fill(0, count($fields) + 1, '?'));
        $params = array_merge([$gift_id], array_values($fields));
        $res    = cg_exec("INSERT INTO $t ($cols, created_at, updated_at) VALUES ($phs, NOW(), NOW())", $params);
        cg_json(['success' => true, 'data' => ['ct_id' => (int)($res['lastInsertId'] ?? 0)]]);
    }
}

function cg_admin_delete_gift_section(): void {
    cg_admin_require();
    $ct_id = (int) ($_POST['ct_id'] ?? 0);
    if (!$ct_id) { cg_json(['success' => false, 'data' => 'Missing ct_id.']); return; }
    $p = cg_prefix();
    cg_exec("DELETE FROM {$p}customtables_table_gift_sections WHERE ct_id = ?", [$ct_id]);
    cg_json(['success' => true, 'data' => 'Deleted.']);
}

// ── Weapons ───────────────────────────────────────────────────────────────────

function cg_admin_list_weapons(): void {
    cg_admin_require();
    $p      = cg_prefix();
    $t      = $p . 'customtables_table_weapons';
    $search = trim($_POST['search'] ?? '');
    $params = [];
    $where  = '';
    if ($search !== '') {
        $where    = "WHERE ct_weapons_name LIKE ?";
        $params[] = '%' . $search . '%';
    }
    $rows = cg_query("SELECT ct_id, ct_weapons_name, ct_weapon_class, published FROM $t $where ORDER BY ct_weapons_name ASC", $params);
    cg_json(['success' => true, 'data' => $rows]);
}

function cg_admin_get_weapon(): void {
    cg_admin_require();
    $id = (int) ($_POST['id'] ?? 0);
    if (!$id) { cg_json(['success' => false, 'data' => 'Missing id.']); return; }

    $p   = cg_prefix();
    $t   = $p . 'customtables_table_weapons';
    $row = cg_query_one("SELECT * FROM $t WHERE ct_id = ?", [$id]);
    if (!$row) { cg_json(['success' => false, 'data' => 'Weapon not found.']); return; }

    $name = $row['ct_weapons_name'];
    $prev = cg_query_one(
        "SELECT ct_id FROM $t WHERE ct_weapons_name < ? ORDER BY ct_weapons_name DESC LIMIT 1",
        [$name]
    );
    $next = cg_query_one(
        "SELECT ct_id FROM $t WHERE ct_weapons_name > ? ORDER BY ct_weapons_name ASC LIMIT 1",
        [$name]
    );

    cg_json(['success' => true, 'data' => [
        'weapon'  => $row,
        'prev_id' => $prev['ct_id'] ?? null,
        'next_id' => $next['ct_id'] ?? null,
    ]]);
}

function cg_admin_save_weapon(): void {
    cg_admin_require();
    $id = (int) ($_POST['id'] ?? 0);
    if (!$id) { cg_json(['success' => false, 'data' => 'Missing id.']); return; }

    $p = cg_prefix();
    $t = $p . 'customtables_table_weapons';

    $allowed = [
        'ct_weapons_name'    => 'string',
        'ct_weapon_class'    => 'string',
        'ct_equip'           => 'string',
        'ct_range_band'      => 'string',
        'ct_attack_dice'     => 'string',
        'ct_effect'          => 'string',
        'ct_descriptors'     => 'string',
        'ct_damage_mod'      => 'string',
        'ct_description'     => 'string',
        'ct_pg_no'           => 'string',
        'ct_slug'            => 'string',
        'ct_cost_d'          => 'string',
        'published'          => 'int',
    ];

    $sets   = [];
    $params = [];
    foreach ($allowed as $col => $type) {
        if (!array_key_exists($col, $_POST)) continue;
        $val = $_POST[$col];
        if ($type === 'int') $val = (int) $val;
        else                  $val = (string) $val;
        $sets[]   = "$col = ?";
        $params[] = $val;
    }

    if (!$sets) { cg_json(['success' => false, 'data' => 'Nothing to update.']); return; }

    $params[] = $id;
    cg_exec("UPDATE $t SET " . implode(', ', $sets) . ", updated_at = NOW() WHERE ct_id = ?", $params);
    cg_json(['success' => true, 'data' => 'Saved.']);
}
