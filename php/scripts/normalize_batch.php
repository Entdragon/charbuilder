<?php
require_once __DIR__ . '/../includes/db.php';

function to_title_case(string $s): string {
    $s = mb_strtolower(trim($s), 'UTF-8');
    return preg_replace_callback(
        '/(?:^|(?<=[ \-,\/\(]))([a-zA-Z\x80-\xFF])/u',
        fn($m) => mb_strtoupper($m[1], 'UTF-8'),
        $s
    );
}

$p  = cg_prefix();
$eq = $p . 'customtables_table_equipment';
$wp = $p . 'customtables_table_weapons';

// ── Equipment: collect pending changes ────────────────────────────────────────
$eq_rows = cg_query("SELECT ct_id, ct_name FROM $eq WHERE published=1");
$eq_changes = [];
foreach ($eq_rows as $r) {
    $fixed = to_title_case($r['ct_name']);
    if ($fixed !== $r['ct_name']) $eq_changes[$r['ct_id']] = $fixed;
}

if ($eq_changes) {
    // Build a single UPDATE … CASE WHEN query
    $cases  = '';
    $ids    = [];
    $params = [];
    foreach ($eq_changes as $id => $name) {
        $cases   .= " WHEN ct_id = $id THEN ?";
        $params[] = $name;
        $ids[]    = $id;
    }
    $in = implode(',', $ids);
    cg_query("UPDATE $eq SET ct_name = CASE $cases END WHERE ct_id IN ($in)", $params);
    echo count($eq_changes) . " equipment names updated in one batch.\n";
} else {
    echo "No equipment names need updating.\n";
}

// ── Weapons: collect pending changes ──────────────────────────────────────────
$wp_rows = cg_query("SELECT ct_id, ct_weapons_name FROM $wp WHERE published=1");
$wp_changes = [];
foreach ($wp_rows as $r) {
    $fixed = to_title_case($r['ct_weapons_name']);
    if ($fixed !== $r['ct_weapons_name']) $wp_changes[$r['ct_id']] = $fixed;
}

if ($wp_changes) {
    $cases  = '';
    $ids    = [];
    $params = [];
    foreach ($wp_changes as $id => $name) {
        $cases   .= " WHEN ct_id = $id THEN ?";
        $params[] = $name;
        $ids[]    = $id;
    }
    $in = implode(',', $ids);
    cg_query("UPDATE $wp SET ct_weapons_name = CASE $cases END WHERE ct_id IN ($in)", $params);
    echo count($wp_changes) . " weapon names updated in one batch.\n";
} else {
    echo "No weapon names need updating.\n";
}

// ── Unpublish Leather & Cloth Armor duplicate (id=229) ────────────────────────
$dup = cg_query("SELECT ct_id, ct_name FROM $eq WHERE ct_id = 229");
if ($dup && ($dup[0]['published'] ?? 1)) {
    cg_query("UPDATE $eq SET published = 0 WHERE ct_id = 229");
    echo "Unpublished duplicate: Leather & Cloth Armor (id=229)\n";
} else {
    echo "Duplicate id=229 already handled or not found.\n";
}

echo "Done.\n";
