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

// ── Gift data quality report ──────────────────────────────────────────────────

function cg_admin_gift_quality_report(): void {
    cg_admin_require();
    $p = cg_prefix();
    $g = "{$p}customtables_table_gifts";

    $gifts = cg_query("
        SELECT ct_id, ct_gifts_name, ct_gifts_effect, ct_gifts_effect_description,
               ct_gift_trigger, published
        FROM $g
        ORDER BY ct_gifts_name ASC
    ");

    if (empty($gifts)) {
        cg_json(['success' => true, 'data' => ['total_gifts' => 0, 'total_issues' => 0, 'items' => []]]);
        return;
    }

    $giftIds = array_map(fn($r) => (int)$r['ct_id'], $gifts);
    $ph      = implode(',', array_fill(0, count($giftIds), '?'));

    // Section type info per gift — track 'rules' and 'flavour' specifically
    $sectionsMap = [];
    try {
        $sects = cg_query(
            "SELECT ct_gift_id, ct_section_type
             FROM {$p}customtables_table_gift_sections
             WHERE ct_gift_id IN ($ph)
               AND ct_section_type IN ('rules', 'flavour')
             GROUP BY ct_gift_id, ct_section_type",
            $giftIds
        );
        foreach ($sects as $s) {
            $gId = (int)$s['ct_gift_id'];
            if (!isset($sectionsMap[$gId])) $sectionsMap[$gId] = ['has_rules' => false, 'has_flavour' => false];
            if ($s['ct_section_type'] === 'rules')   $sectionsMap[$gId]['has_rules']   = true;
            if ($s['ct_section_type'] === 'flavour') $sectionsMap[$gId]['has_flavour'] = true;
        }
    } catch (Throwable $e) { /* gift_sections may not exist */ }

    // Rule counts per gift
    $rulesCountMap = [];
    try {
        $counts = cg_query(
            "SELECT ct_gift_id, COUNT(*) AS cnt
             FROM {$p}customtables_table_gift_rules
             WHERE ct_gift_id IN ($ph)
             GROUP BY ct_gift_id",
            $giftIds
        );
        foreach ($counts as $rc) {
            $rulesCountMap[(int)$rc['ct_gift_id']] = (int)$rc['cnt'];
        }
    } catch (Throwable $e) { /* gift_rules may not exist */ }

    $report = [];
    foreach ($gifts as $gift) {
        $id      = (int)$gift['ct_id'];
        $effect  = trim((string)($gift['ct_gifts_effect'] ?? ''));
        $effDesc = trim((string)($gift['ct_gifts_effect_description'] ?? ''));
        $trigger = trim((string)($gift['ct_gift_trigger'] ?? ''));
        $issues  = [];

        if ($effect === '') {
            $issues[] = ['type' => 'missing_effect',      'label' => 'Missing card effect'];
        } elseif (mb_strlen($effect) > 220) {
            $issues[] = ['type' => 'overlong_effect',     'label' => 'Overlong card effect (' . mb_strlen($effect) . ' chars)'];
        }

        if ($effDesc === '') {
            $issues[] = ['type' => 'missing_description', 'label' => 'Missing detail description'];
        }

        if ($effect === '' && $effDesc === '') {
            $si = $sectionsMap[$id] ?? null;
            if ($si && !$si['has_rules'] && $si['has_flavour']) {
                $issues[] = ['type' => 'flavour_fallback', 'label' => 'No rules section — only flavour section available as fallback'];
            }
        }

        if ($trigger !== '') {
            $issues[] = ['type' => 'trigger_unused', 'label' => 'Has trigger text (not yet shown in builder)'];
        }

        $rc = $rulesCountMap[$id] ?? 0;
        if ($rc > 0) {
            $issues[] = ['type' => 'rules_unused', 'label' => 'Has ' . $rc . ' rule ' . ($rc === 1 ? 'entry' : 'entries') . ' (not yet shown in builder)'];
        }

        if (!empty($issues)) {
            $report[] = [
                'gift_id'   => $id,
                'gift_name' => $gift['ct_gifts_name'] ?? 'Unnamed',
                'published' => (bool)(int)($gift['published'] ?? 0),
                'issues'    => $issues,
            ];
        }
    }

    cg_json(['success' => true, 'data' => [
        'total_gifts'  => count($gifts),
        'total_issues' => count($report),
        'items'        => $report,
    ]]);
}

// ── Sync Trappings gift child tables ─────────────────────────────────────────

function cg_admin_sync_trappings_children(): void {
    cg_admin_require();
    $p  = cg_prefix();
    $tg = "{$p}customtables_table_gifts";
    $gc = "{$p}customtables_table_giftclass";
    $tr = "{$p}customtables_table_gift_rules";
    $ts = "{$p}customtables_table_gift_sections";
    $tm = "{$p}customtables_table_gift_type_map";
    $tt = "{$p}customtables_table_gifttype";

    // ── 1. Find all Trappings gifts ───────────────────────────────────────────
    try {
        $gifts = cg_query("
            SELECT DISTINCT g.ct_id, g.ct_gifts_name,
                   g.ct_gifts_effect, g.ct_gifts_effect_description
            FROM $tg AS g
            LEFT JOIN $gc AS gc ON gc.ct_id = g.ct_gift_class
            WHERE g.ct_gifts_name LIKE '%Trappings%'
               OR LOWER(TRIM(COALESCE(gc.ct_class_name, ''))) = 'trappings'
            ORDER BY g.ct_gifts_name ASC
        ");
    } catch (Throwable $e) {
        // giftclass table may not exist — fall back to name-only match
        $gifts = cg_query("
            SELECT ct_id, ct_gifts_name,
                   ct_gifts_effect, ct_gifts_effect_description
            FROM $tg
            WHERE ct_gifts_name LIKE '%Trappings%'
            ORDER BY ct_gifts_name ASC
        ");
    }

    if (empty($gifts)) {
        cg_json(['success' => true, 'data' => ['processed' => 0, 'items' => []]]);
        return;
    }

    $report   = [];
    $giftIds  = [];

    foreach ($gifts as $gift) {
        $id      = (int) $gift['ct_id'];
        $name    = (string) ($gift['ct_gifts_name'] ?? '');
        $summary = trim((string) ($gift['ct_gifts_effect'] ?? ''));
        $desc    = trim((string) ($gift['ct_gifts_effect_description'] ?? ''));
        $giftIds[] = $id;
        $changes   = [];

        // ── 2. Split on @@FLAVOUR marker ────────────────────────────────────
        $parts       = explode('@@FLAVOUR', $desc, 2);
        $rulesText   = trim($parts[0]);
        $flavourText = isset($parts[1]) ? trim($parts[1]) : '';

        // ── 3. Wipe all gift_rules rows, then insert one clean row ───────────
        try {
            $deleted = cg_exec("DELETE FROM $tr WHERE ct_gift_id = ?", [$id]);
            $changes[] = 'gift_rules deleted: ' . $deleted['rowCount'];
            cg_exec(
                "INSERT INTO $tr
                    (ct_gift_id, ct_sort, ct_rule_type, ct_rule_title,
                     ct_cost_text, ct_limit_text, ct_summary, ct_details,
                     created_at, updated_at)
                 VALUES (?, 10, 'passive', '', '', '', ?, ?, NOW(), NOW())",
                [$id, $summary, $rulesText]
            );
            $changes[] = 'gift_rules inserted';
        } catch (Throwable $e) {
            $changes[] = 'gift_rules error: ' . $e->getMessage();
        }

        // ── 4. Wipe all gift_sections rows, then insert clean set ─────────────
        try {
            $deleted = cg_exec("DELETE FROM $ts WHERE ct_gift_id = ?", [$id]);
            $changes[] = 'gift_sections deleted: ' . $deleted['rowCount'];

            // Parse @@SECTION: blocks from rulesText into individual structured rows
            $rawParts = preg_split('/@@SECTION\s*:/i', $rulesText, -1, PREG_SPLIT_NO_EMPTY);
            $sort = 10;

            foreach ($rawParts as $rawPart) {
                $rawPart = trim($rawPart);
                if ($rawPart === '') continue;

                // First line = section heading, rest = body
                $nlPos   = strpos($rawPart, "\n");
                $heading = $nlPos !== false ? trim(substr($rawPart, 0, $nlPos)) : trim($rawPart);
                $body    = $nlPos !== false ? trim(substr($rawPart, $nlPos + 1)) : '';

                // Strip @@REFRESH lines entirely
                $body = preg_replace('/\n?[ \t]*@@REFRESH[^\n]*/i', '', $body);
                // Strip @@TRAPPINGS: label but keep its content (items list follows on next line(s))
                $body = preg_replace('/@@TRAPPINGS\s*:[ \t]*/i', '', $body);
                // Strip any remaining @@ directives (line only, content stays)
                $body = preg_replace('/[ \t]*@@[A-Z_]+\s*:[^\n]*/i', '', $body);
                // Collapse excess blank lines
                $body = trim(preg_replace("/\n{3,}/", "\n\n", $body));

                if ($heading === '' && $body === '') continue;

                // Determine section type from the heading
                $secType = 'rules';
                if (preg_match('/^Action\s+["\(]/i', $heading) ||
                    preg_match('/^(Improved Action|Reaction|Long Action)\s+["\(]/i', $heading)) {
                    $secType = 'action_block';
                }

                cg_exec(
                    "INSERT INTO $ts
                        (ct_gift_id, ct_sort, ct_section_type, ct_heading, ct_body,
                         created_at, updated_at)
                     VALUES (?, ?, ?, ?, ?, NOW(), NOW())",
                    [$id, $sort, $secType, $heading, $body]
                );
                $changes[] = "gift_sections '{$secType}' inserted: {$heading}";
                $sort += 10;
            }

            // If no @@SECTION: markers were found, fall back to one plain rules row
            if ($sort === 10 && $rulesText !== '') {
                $plainBody = preg_replace('/[ \t]*@@[A-Z_]+\s*:[^\n]*/i', '', $rulesText);
                $plainBody = trim(preg_replace("/\n{3,}/", "\n\n", $plainBody));
                cg_exec(
                    "INSERT INTO $ts
                        (ct_gift_id, ct_sort, ct_section_type, ct_heading, ct_body,
                         created_at, updated_at)
                     VALUES (?, 10, 'rules', '', ?, NOW(), NOW())",
                    [$id, $plainBody]
                );
                $changes[] = "gift_sections 'rules' inserted (no sections)";
                $sort = 20;
            }

            // 'flavour' section — only when @@FLAVOUR marker was present
            if ($flavourText !== '') {
                cg_exec(
                    "INSERT INTO $ts
                        (ct_gift_id, ct_sort, ct_section_type, ct_heading, ct_body,
                         created_at, updated_at)
                     VALUES (?, ?, 'flavour', '', ?, NOW(), NOW())",
                    [$id, $sort, $flavourText]
                );
                $changes[] = "gift_sections 'flavour' inserted";
            }
        } catch (Throwable $e) {
            $changes[] = 'gift_sections error: ' . $e->getMessage();
        }

        $report[] = [
            'gift_id'   => $id,
            'gift_name' => $name,
            'has_flavour' => $flavourText !== '',
            'changes'   => $changes,
        ];
    }

    // ── 6. Remove redundant gift_type_map entries for Trappings gifts ─────────
    $typeMapDeleted = 0;
    if (!empty($giftIds)) {
        try {
            $ph = implode(',', array_fill(0, count($giftIds), '?'));

            // Find gifttype IDs that represent "Trappings"
            $trappingsTypes = cg_query(
                "SELECT ct_id FROM $tt WHERE ct_type_name LIKE '%Trappings%'",
                []
            );
            if (!empty($trappingsTypes)) {
                $typeIds = array_map(fn($r) => (int) $r['ct_id'], $trappingsTypes);
                $tph     = implode(',', array_fill(0, count($typeIds), '?'));
                $res     = cg_exec(
                    "DELETE FROM $tm WHERE gift_id IN ($ph) AND type_id IN ($tph)",
                    array_merge($giftIds, $typeIds)
                );
                $typeMapDeleted = $res['rowCount'];
            }
        } catch (Throwable $e) {
            // gift_type_map or gifttype table may not exist — skip silently
        }
    }

    cg_json(['success' => true, 'data' => [
        'processed'        => count($report),
        'type_map_deleted' => $typeMapDeleted,
        'items'            => $report,
    ]]);
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
