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
