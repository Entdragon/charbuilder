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

/**
 * Like cg_admin_require() but also accepts a valid sync_secret in $_POST.
 * Use for bulk sync endpoints that the WordPress mu-plugin may call server-to-server.
 */
function cg_admin_require_or_secret(): void {
    $secret = defined('CG_SYNC_SECRET') ? CG_SYNC_SECRET : '';
    $posted = trim($_POST['sync_secret'] ?? '');
    if ($secret !== '' && $posted !== '' && hash_equals($secret, $posted)) {
        return; // bypass — valid server-to-server secret
    }
    cg_admin_require();
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

// ── Trappings body normaliser ─────────────────────────────────────────────────

/**
 * Convert all @@TRAPPINGS: content in a section body to "- item" bullet lines.
 *
 * Handles three authoring styles:
 *   (A) Inline:  @@TRAPPINGS: item text          → - item text
 *   (B) Block:   @@TRAPPINGS:\n item\n item        → - item\n - item
 *   (C) Pre-tagged: @@TRAPPINGS:\n- item\n- item  → - item\n - item (deduped prefix)
 *
 * Lines already prefixed with "- " or "• " have the prefix stripped and re-added
 * so the final output is always uniform "- item".
 */
function cg_expand_trappings_to_list(string $body): string {
    $body  = str_replace(["\r\n", "\r"], "\n", $body);
    $lines = explode("\n", $body);
    $out   = [];
    $inBlock = false;   // true while we're inside a bare @@TRAPPINGS: block

    foreach ($lines as $line) {
        $t = rtrim($line);

        // Inline @@TRAPPINGS: item text
        if (preg_match('/^[ \t]*@@TRAPPINGS\s*:[ \t]+(.+)$/i', $t, $m)) {
            $out[] = '- ' . trim($m[1]);
            $inBlock = false;
            continue;
        }

        // Bare @@TRAPPINGS: marker on its own line — start collecting block items
        if (preg_match('/^[ \t]*@@TRAPPINGS\s*:[ \t]*$/i', $t)) {
            $inBlock = true;
            // Don't emit the marker line itself
            continue;
        }

        if ($inBlock) {
            if ($t === '') {
                // Blank line ends the block
                $inBlock = false;
                $out[] = '';
            } elseif (preg_match('/^@@/i', $t)) {
                // Another @@ directive ends the block — process this line normally below
                $inBlock = false;
                $out[] = $t;
            } else {
                // Strip any existing bullet prefix then re-apply "- "
                $clean = preg_replace('/^[-•]\s+/', '', $t);
                $out[] = '- ' . $clean;
            }
            continue;
        }

        $out[] = $t;
    }

    return implode("\n", $out);
}

// ── Markup block parser ───────────────────────────────────────────────────────

/**
 * Parse all @@DIRECTIVE: blocks from a gift description into a structured array.
 *
 * Each @@ that starts on its own line (or at the start of the text) opens a new
 * block; its content runs until the next @@ marker or end of string.
 *
 * Also normalises the common typo @@R:equires: → @@REQUIRES:.
 *
 * Returns array of ['directive'=>string, 'first_line'=>string, 'body'=>string].
 */
function cg_parse_markup_blocks(string $text): array {
    $text = str_replace(["\r\n", "\r"], "\n", $text);
    // Normalise typo
    $text = preg_replace('/@@R:equires\s*:/i', '@@REQUIRES:', $text);

    $blocks = [];

    // Match every @@WORD: that is either at the start of string or after a newline
    if (!preg_match_all('/(?:^|\n)(@@[A-Z][A-Z_]*\s*:[ \t]*)/mi', $text, $matches, PREG_OFFSET_CAPTURE)) {
        return $blocks;
    }

    $count = count($matches[0]);
    for ($i = 0; $i < $count; $i++) {
        $fullMatch  = $matches[0][$i][0];   // e.g. "\n@@PASSIVE: "
        $matchStart = $matches[0][$i][1];   // byte offset in $text
        $tagLen     = strlen($fullMatch);

        $contentStart = $matchStart + $tagLen;
        $contentEnd   = ($i + 1 < $count) ? $matches[0][$i + 1][1] : strlen($text);

        $directiveRaw = trim(strtoupper(preg_replace('/@@([A-Z][A-Z_]*)\s*:.*$/si', '$1', trim($fullMatch))));

        $content   = rtrim(substr($text, $contentStart, $contentEnd - $contentStart));
        $lines     = explode("\n", $content, 2);
        $firstLine = trim($lines[0]);
        $body      = isset($lines[1]) ? trim($lines[1]) : '';

        $blocks[] = [
            'directive'  => $directiveRaw,
            'first_line' => $firstLine,
            'body'       => $body,
        ];
    }

    return $blocks;
}

// ── General gift sync (all @@markup types) ────────────────────────────────────

/**
 * Parse all @@DIRECTIVE: blocks from a gift's description and rebuild ALL
 * four child tables: gift_rules, gift_sections, gift_triggers, gift_requirements.
 *
 * Rules:
 * - If description has no @@ markers, child tables are left untouched.
 * - If description has @@SECTION: markers, delegates to cg_sync_one_trappings_gift().
 * - Otherwise parses @@PASSIVE/ACTION/REACTION/etc → gift_rules,
 *                    @@TRIGGER            → gift_triggers,
 *                    @@REQUIRES           → gift_requirements,
 *                    @@SECTION / @@FLAVOUR → gift_sections.
 * - @@TRIGGER rows are deduplicated before insert.
 * - @@REQUIRES values are resolved to gift_ref if a matching gift name is found.
 */
function cg_sync_one_gift_general(array $gift): array {
    $id      = (int)   ($gift['ct_id']                            ?? 0);
    $desc    = trim((string) ($gift['ct_gifts_effect_description'] ?? ''));

    // Nothing to do if no @@ markup present — preserve manually-entered data
    if ($desc === '' || !preg_match('/@@[A-Z]/i', $desc)) {
        return ['no @@ markup in description — child tables unchanged'];
    }

    // Trappings-style (@@SECTION: markers) → dedicated handler
    if (stripos($desc, '@@SECTION:') !== false) {
        return cg_sync_one_trappings_gift($gift);
    }

    $p     = cg_prefix();
    $tr    = "{$p}customtables_table_gift_rules";
    $ts    = "{$p}customtables_table_gift_sections";
    $treq  = "{$p}customtables_table_gift_requirements";
    $ttrig = "{$p}customtables_table_gift_triggers";
    $tg    = "{$p}customtables_table_gifts";

    $changes = [];
    $blocks  = cg_parse_markup_blocks($desc);

    $rules        = [];
    $sections     = [];
    $requirements = [];
    $triggers     = [];
    $flavourText  = '';

    foreach ($blocks as $b) {
        $dir  = $b['directive'];
        $line = $b['first_line'];
        $body = $b['body'];

        switch ($dir) {
            case 'PASSIVE':
                $rules[] = ['type' => 'passive',         'summary' => $line, 'details' => $body];
                break;
            case 'ACTION':
                $rules[] = ['type' => 'action',          'summary' => $line, 'details' => $body];
                break;
            case 'REACTION':
                $rules[] = ['type' => 'reaction',        'summary' => $line, 'details' => $body];
                break;
            case 'IMPROVED_ACTION':
                $rules[] = ['type' => 'improved_action', 'summary' => $line, 'details' => $body];
                break;
            case 'LONG_ACTION':
                $rules[] = ['type' => 'long_action',     'summary' => $line, 'details' => $body];
                break;
            case 'START':
                $rules[] = ['type' => 'start',           'summary' => $line, 'details' => $body];
                break;
            case 'TRIGGER':
                if ($line !== '') $triggers[] = $line;
                break;
            case 'REQUIRES':
                // Each non-empty line (first + body) is a separate requirement
                foreach (array_filter(array_map('trim', array_merge([$line], explode("\n", $body)))) as $req) {
                    $requirements[] = $req;
                }
                break;
            case 'SECTION':
                $sections[] = ['heading' => $line, 'body' => $body];
                break;
            case 'FLAVOUR':
                $flavourText = $line . ($body !== '' ? "\n" . $body : '');
                break;
            case 'REFRESH':
                break; // already stored in ct_gifts_refresh column
        }
    }

    // Deduplicate triggers
    $triggers = array_values(array_unique($triggers));

    // ── gift_rules ────────────────────────────────────────────────────────────
    if (!empty($rules)) {
        try {
            $del = cg_exec("DELETE FROM $tr WHERE ct_gift_id = ?", [$id]);
            $changes[] = 'gift_rules deleted: ' . $del['rowCount'];
            $sort = 10;
            foreach ($rules as $rule) {
                cg_exec(
                    "INSERT INTO $tr
                        (ct_gift_id, ct_sort, ct_rule_type, ct_rule_title,
                         ct_cost_text, ct_limit_text, ct_summary, ct_details,
                         created_at, updated_at)
                     VALUES (?, ?, ?, '', '', '', ?, ?, NOW(), NOW())",
                    [$id, $sort, $rule['type'], $rule['summary'], $rule['details']]
                );
                $changes[] = "gift_rules ({$rule['type']}): " . mb_substr($rule['summary'], 0, 60);
                $sort += 10;
            }
        } catch (Throwable $e) {
            $changes[] = 'gift_rules error: ' . $e->getMessage();
        }
    }

    // ── gift_sections ─────────────────────────────────────────────────────────
    if (!empty($sections) || $flavourText !== '') {
        try {
            $del = cg_exec("DELETE FROM $ts WHERE ct_gift_id = ?", [$id]);
            $changes[] = 'gift_sections deleted: ' . $del['rowCount'];
            $sort = 10;
            foreach ($sections as $sec) {
                $h       = $sec['heading'];
                $secType = 'rules';
                if (preg_match('/^Action\s+["\(]/i', $h) ||
                    preg_match('/^(Improved Action|Reaction|Long Action)\s+["\(]/i', $h)) {
                    $secType = 'action_block';
                }
                cg_exec(
                    "INSERT INTO $ts
                        (ct_gift_id, ct_sort, ct_section_type, ct_heading, ct_body,
                         created_at, updated_at)
                     VALUES (?, ?, ?, ?, ?, NOW(), NOW())",
                    [$id, $sort, $secType, $h, $sec['body']]
                );
                $changes[] = "gift_sections '{$secType}': {$h}";
                $sort += 10;
            }
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
    }

    // ── gift_triggers ─────────────────────────────────────────────────────────
    if (!empty($triggers)) {
        try {
            $del = cg_exec("DELETE FROM $ttrig WHERE ct_gift_id = ?", [$id]);
            $changes[] = 'gift_triggers deleted: ' . $del['rowCount'];
            $sort = 10;
            foreach ($triggers as $trig) {
                cg_exec(
                    "INSERT INTO $ttrig
                        (ct_gift_id, ct_sort, ct_trigger_kind, ct_trigger_text,
                         created_at, updated_at)
                     VALUES (?, ?, 'condition', ?, NOW(), NOW())",
                    [$id, $sort, $trig]
                );
                $changes[] = "gift_triggers: {$trig}";
                $sort += 10;
            }
        } catch (Throwable $e) {
            $changes[] = 'gift_triggers error: ' . $e->getMessage();
        }
    }

    // ── gift_requirements ─────────────────────────────────────────────────────
    if (!empty($requirements)) {
        try {
            $del = cg_exec("DELETE FROM $treq WHERE ct_gift_id = ?", [$id]);
            $changes[] = 'gift_requirements deleted: ' . $del['rowCount'];
            $sort = 10;
            foreach ($requirements as $req) {
                // Try to resolve to a gift_ref first
                $refId   = 0;
                $refKind = 'text';
                try {
                    $refRows = cg_query(
                        "SELECT ct_id FROM $tg WHERE ct_gifts_name = ? AND published = 1 LIMIT 1",
                        [$req]
                    );
                    if (!empty($refRows)) {
                        $refId   = (int) $refRows[0]['ct_id'];
                        $refKind = 'gift_ref';
                    }
                } catch (Throwable $ignored) {}

                cg_exec(
                    "INSERT INTO $treq
                        (ct_gift_id, ct_sort, ct_req_kind, ct_req_ref_id, ct_req_text,
                         created_at, updated_at)
                     VALUES (?, ?, ?, ?, ?, NOW(), NOW())",
                    [$id, $sort, $refKind, $refId, $req]
                );
                $changes[] = "gift_requirements ({$refKind}): {$req}";
                $sort += 10;
            }
        } catch (Throwable $e) {
            $changes[] = 'gift_requirements error: ' . $e->getMessage();
        }
    }

    if (empty($rules) && empty($sections) && empty($triggers) && empty($requirements) && $flavourText === '') {
        $changes[] = '@@ markers present but no recognised blocks — child tables unchanged';
    }

    return $changes;
}

// ── Shared helper: sync child tables for one Trappings gift ──────────────────

/**
 * Wipe-and-rebuild gift_rules + gift_sections for a single Trappings gift.
 * Returns array of change-log strings.
 *
 * @param array $gift  Row from customtables_table_gifts
 *                     (keys: ct_id, ct_gifts_name, ct_gifts_effect, ct_gifts_effect_description)
 */
function cg_sync_one_trappings_gift(array $gift): array {
    $p     = cg_prefix();
    $tr    = "{$p}customtables_table_gift_rules";
    $ts    = "{$p}customtables_table_gift_sections";
    $ttrig = "{$p}customtables_table_gift_triggers";

    $id      = (int)   ($gift['ct_id']                      ?? 0);
    $summary = trim((string) ($gift['ct_gifts_effect']       ?? ''));
    $desc    = trim((string) ($gift['ct_gifts_effect_description'] ?? ''));

    // Fetch trigger field separately — column may not exist on all installs
    $trigField = trim((string) ($gift['ct_gifts_trigger'] ?? ''));
    if ($trigField === '' && $id > 0) {
        try {
            $trow = cg_query(
                "SELECT ct_gifts_trigger FROM {$p}customtables_table_gifts WHERE ct_id = ? LIMIT 1",
                [$id]
            );
            $trigField = trim((string) ($trow[0]['ct_gifts_trigger'] ?? ''));
        } catch (Throwable $ignored) { /* column doesn't exist on this install */ }
    }

    $changes = [];

    // ── Custom parser: split ONLY on @@SECTION: and @@FLAVOUR: ───────────────
    // We intentionally do NOT use cg_parse_markup_blocks() here because that
    // function splits on every @@WORD: directive, including @@TABLE:, which
    // would tear @@TABLE: blocks out of their parent section body.
    // By restricting the split pattern to only SECTION and FLAVOUR, @@TABLE:
    // (and all other directives) remain intact inside the section body.
    $desc = str_replace(["\r\n", "\r"], "\n", $desc);
    $blocks = [];
    preg_match_all('/(?:^|\n)(@@(SECTION|FLAVOUR)\s*:[ \t]*)/mi',
                   $desc, $ms, PREG_OFFSET_CAPTURE);
    $count = count($ms[0]);
    for ($i = 0; $i < $count; $i++) {
        $fullMatch    = $ms[0][$i][0];                // e.g. "\n@@SECTION: "
        $matchStart   = $ms[0][$i][1];
        $contentStart = $matchStart + strlen($fullMatch);
        $contentEnd   = ($i + 1 < $count) ? $ms[0][$i + 1][1] : strlen($desc);
        $content      = rtrim(substr($desc, $contentStart, $contentEnd - $contentStart));
        $lines        = explode("\n", $content, 2);
        $blocks[]     = [
            'directive'  => strtoupper(trim($ms[2][$i][0])),   // 'SECTION' or 'FLAVOUR'
            'first_line' => trim($lines[0]),
            'body'       => isset($lines[1]) ? trim($lines[1]) : '',
        ];
    }

    // Build rulesText for gift_rules ct_details (all SECTION heading+body joined)
    $rulesText = '';
    foreach ($blocks as $b) {
        if ($b['directive'] !== 'SECTION') continue;
        $part = $b['first_line'];
        if ($b['body'] !== '') $part .= "\n" . $b['body'];
        $rulesText .= ($rulesText !== '' ? "\n\n" : '') . $part;
    }

    // ── gift_rules: one passive row ──────────────────────────────────────────
    try {
        $del = cg_exec("DELETE FROM $tr WHERE ct_gift_id = ?", [$id]);
        $changes[] = 'gift_rules deleted: ' . $del['rowCount'];
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

    // ── gift_sections: insert in original document order ─────────────────────
    try {
        $del = cg_exec("DELETE FROM $ts WHERE ct_gift_id = ?", [$id]);
        $changes[] = 'gift_sections deleted: ' . $del['rowCount'];

        $sort       = 10;
        $hadContent = false;

        foreach ($blocks as $b) {
            if ($b['directive'] === 'SECTION') {
                $heading = $b['first_line'];
                $body    = $b['body'];

                $body = preg_replace('/\n?[ \t]*@@REFRESH[^\n]*/i', '', $body);
                // Convert @@TRAPPINGS: content to "- item" bullet lines
                $body = cg_expand_trappings_to_list($body);
                // Strip @@directives except @@TABLE: (template renders tables natively)
                $body = preg_replace('/[ \t]*@@(?!TABLE\b)[A-Z_]+\s*:[^\n]*/i', '', $body);
                $body = trim(preg_replace("/\n{3,}/", "\n\n", $body));

                if ($heading === '' && $body === '') continue;

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
                $sort      += 10;
                $hadContent = true;

            } elseif ($b['directive'] === 'FLAVOUR') {
                $flavourText = $b['first_line'];
                if ($b['body'] !== '') $flavourText .= "\n" . $b['body'];
                $flavourText = trim($flavourText);
                if ($flavourText === '') continue;

                cg_exec(
                    "INSERT INTO $ts
                        (ct_gift_id, ct_sort, ct_section_type, ct_heading, ct_body,
                         created_at, updated_at)
                     VALUES (?, ?, 'flavour', '', ?, NOW(), NOW())",
                    [$id, $sort, $flavourText]
                );
                $changes[] = "gift_sections 'flavour' inserted";
                $sort      += 10;
                $hadContent = true;
            }
        }

        // No SECTION or FLAVOUR blocks found → one plain rules row from full desc
        if (!$hadContent && $desc !== '') {
            $plainBody = preg_replace('/\n?[ \t]*@@REFRESH[^\n]*/i', '', $desc);
            $plainBody = cg_expand_trappings_to_list($plainBody);
            $plainBody = preg_replace('/[ \t]*@@(?!TABLE\b)[A-Z_]+\s*:[^\n]*/i', '', $plainBody);
            $plainBody = trim(preg_replace("/\n{3,}/", "\n\n", $plainBody));
            cg_exec(
                "INSERT INTO $ts
                    (ct_gift_id, ct_sort, ct_section_type, ct_heading, ct_body,
                     created_at, updated_at)
                 VALUES (?, 10, 'rules', '', ?, NOW(), NOW())",
                [$id, $plainBody]
            );
            $changes[] = "gift_sections 'rules' inserted (no blocks)";
        }
    } catch (Throwable $e) {
        $changes[] = 'gift_sections error: ' . $e->getMessage();
    }

    // ── gift_triggers: wipe stale rows; rebuild from ct_gifts_trigger field ──
    try {
        $del = cg_exec("DELETE FROM $ttrig WHERE ct_gift_id = ?", [$id]);
        $changes[] = 'gift_triggers deleted: ' . $del['rowCount'];
        if ($trigField !== '') {
            cg_exec(
                "INSERT INTO $ttrig
                    (ct_gift_id, ct_sort, ct_trigger_kind, ct_trigger_text,
                     created_at, updated_at)
                 VALUES (?, 10, 'condition', ?, NOW(), NOW())",
                [$id, $trigField]
            );
            $changes[] = "gift_triggers: {$trigField}";
        }
    } catch (Throwable $e) {
        $changes[] = 'gift_triggers error: ' . $e->getMessage();
    }

    return $changes;
}

// ── Sync Trappings gift child tables (all at once) ────────────────────────────

function cg_admin_sync_trappings_children(): void {
    cg_admin_require_or_secret();
    $p  = cg_prefix();
    $tg = "{$p}customtables_table_gifts";
    $gc = "{$p}customtables_table_giftclass";
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
        $id   = (int) ($gift['ct_id'] ?? 0);
        $name = (string) ($gift['ct_gifts_name'] ?? '');
        $giftIds[] = $id;

        $changes = cg_sync_one_trappings_gift($gift);

        $report[] = [
            'gift_id'   => $id,
            'gift_name' => $name,
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

// ── Sync single gift child tables (for pipeline / webhook use) ───────────────

/**
 * Sync child tables for a single gift by ID.
 *
 * Auth: standard admin session OR shared CG_SYNC_SECRET token.
 * The sync_secret path is used by the WordPress mu-plugin after a CT save.
 *
 * POST params:
 *   gift_id     int    required
 *   sync_secret string optional — bypasses session auth when CG_SYNC_SECRET matches
 */
function cg_admin_sync_single_gift(): void {
    // Allow server-to-server call when sync_secret matches configured secret
    $cfgSecret  = defined('CG_SYNC_SECRET') ? CG_SYNC_SECRET : '';
    $postSecret = trim($_POST['sync_secret'] ?? '');
    $hasSecret  = $cfgSecret !== '' && hash_equals($cfgSecret, $postSecret);

    if (!$hasSecret) {
        cg_admin_require();
    }

    $giftId = (int) ($_POST['gift_id'] ?? 0);
    if (!$giftId) {
        cg_json(['success' => false, 'data' => 'Missing gift_id.']);
        return;
    }

    $p  = cg_prefix();
    $tg = "{$p}customtables_table_gifts";
    $gc = "{$p}customtables_table_giftclass";

    // Fetch the gift row
    try {
        $rows = cg_query(
            "SELECT g.ct_id, g.ct_gifts_name, g.ct_gifts_effect,
                    g.ct_gifts_effect_description, gc.ct_class_name
             FROM $tg AS g
             LEFT JOIN $gc AS gc ON gc.ct_id = g.ct_gift_class
             WHERE g.ct_id = ?",
            [$giftId]
        );
    } catch (Throwable $e) {
        $rows = cg_query(
            "SELECT ct_id, ct_gifts_name, ct_gifts_effect, ct_gifts_effect_description
             FROM $tg WHERE ct_id = ?",
            [$giftId]
        );
    }

    if (empty($rows)) {
        cg_json(['success' => false, 'data' => "Gift {$giftId} not found."]);
        return;
    }

    $gift    = $rows[0];
    $changes = cg_sync_one_gift_general($gift);

    cg_json(['success' => true, 'data' => [
        'gift_id'   => $giftId,
        'gift_name' => $gift['ct_gifts_name'] ?? '',
        'changes'   => $changes,
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
