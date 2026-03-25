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

// 1. Title Case equipment names
$rows      = cg_query("SELECT ct_id, ct_name FROM $eq WHERE published=1");
$eq_updated = 0;
foreach ($rows as $r) {
    $fixed = to_title_case($r['ct_name']);
    if ($fixed !== $r['ct_name']) {
        cg_query("UPDATE $eq SET ct_name = ? WHERE ct_id = ?", [$fixed, $r['ct_id']]);
        echo "  EQ [{$r['ct_id']}] " . json_encode($r['ct_name']) . " -> " . json_encode($fixed) . "\n";
        $eq_updated++;
    }
}
echo "Equipment names updated: $eq_updated\n";

// 2. Title Case weapon names
$rows      = cg_query("SELECT ct_id, ct_weapons_name FROM $wp WHERE published=1");
$wp_updated = 0;
foreach ($rows as $r) {
    $fixed = to_title_case($r['ct_weapons_name']);
    if ($fixed !== $r['ct_weapons_name']) {
        cg_query("UPDATE $wp SET ct_weapons_name = ? WHERE ct_id = ?", [$fixed, $r['ct_id']]);
        echo "  WP [{$r['ct_id']}] " . json_encode($r['ct_weapons_name']) . " -> " . json_encode($fixed) . "\n";
        $wp_updated++;
    }
}
echo "Weapon names updated: $wp_updated\n";

// 3. Unpublish the "Leather & Cloth Armor" duplicate (id=229, duplicate of id=228)
cg_query("UPDATE $eq SET published = 0 WHERE ct_id = 229");
echo "Unpublished duplicate: Leather & Cloth Armor (id=229)\n";

echo "Done.\n";
