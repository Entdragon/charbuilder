<?php
/**
 * CLI runner — seeds the spells table.
 * Usage: php cli-install-spells.php
 * Run from ~/public_html/characters/
 */
declare(strict_types=1);

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/actions/spells.php';

cg_ensure_spells_table();

$t    = cg_spells_table();
$rows = cg_spell_rows();

cg_exec("TRUNCATE TABLE {$t}");

$sql = "INSERT INTO {$t}
            (ct_name, ct_equip, ct_range, ct_attack_dice, ct_effect, ct_descriptors, ct_gift_name, ct_sort)
        VALUES (?,?,?,?,?,?,?,?)";

$inserted = 0;
foreach ($rows as $r) {
    try {
        cg_exec($sql, $r);
        $inserted++;
    } catch (Throwable $e) {
        echo "  FAILED '{$r[0]}': " . $e->getMessage() . "\n";
    }
}

echo "Done. Inserted {$inserted} spells into {$t}.\n";
