<?php
require_once __DIR__ . '/../includes/db.php';

function cg_get_species_list(): void {
    $p    = cg_prefix();
    $rows = cg_query(
        "SELECT ct_id AS id, ct_species_name AS name
         FROM {$p}customtables_table_species ORDER BY ct_species_name ASC"
    );
    cg_json(['success' => true, 'data' => $rows]);
}

function cg_get_species_profile(): void {
    $speciesId = (int) ($_POST['species_id'] ?? ($_POST['id'] ?? 0));
    if ($speciesId <= 0) {
        cg_json(['success' => false, 'data' => 'Invalid species ID.']);
        return;
    }

    $p  = cg_prefix();
    $sp = "{$p}customtables_table_species";
    $st = "{$p}customtables_table_species_traits";

    $species = cg_query_one(
        "SELECT ct_id AS id, ct_species_name AS speciesName FROM {$sp} WHERE ct_id = ?",
        [$speciesId]
    );
    if (!$species) {
        cg_json(['success' => false, 'data' => 'Species not found.']);
        return;
    }

    // Fetch all traits for this species in one query
    $traits = cg_query(
        "SELECT trait_key, ref_id, text_value FROM {$st} WHERE species_id = ?",
        [$speciesId]
    );
    $byKey = [];
    foreach ($traits as $t) {
        $byKey[$t['trait_key']] = $t;
    }

    // Collect ref_ids that need lookup
    $giftIds   = array_filter([
        $byKey['gift_1']['ref_id']   ?? null,
        $byKey['gift_2']['ref_id']   ?? null,
        $byKey['gift_3']['ref_id']   ?? null,
    ]);
    $habitatId = $byKey['habitat']['ref_id']  ?? null;
    $dietId    = $byKey['diet']['ref_id']     ?? null;
    $cycleId   = $byKey['cycle']['ref_id']    ?? null;
    $senseIds  = array_filter([
        $byKey['sense_1']['ref_id']  ?? null,
        $byKey['sense_2']['ref_id']  ?? null,
        $byKey['sense_3']['ref_id']  ?? null,
    ]);
    $weaponIds = array_filter([
        $byKey['weapon_1']['ref_id'] ?? null,
        $byKey['weapon_2']['ref_id'] ?? null,
        $byKey['weapon_3']['ref_id'] ?? null,
    ]);

    // Helper: fetch rows by IDs from a table, return id→row map
    $fetchById = function(string $table, string $idCol, string $nameCol, array $ids): array {
        if (!$ids) return [];
        $ids = array_values(array_unique(array_map('intval', $ids)));
        $ph  = implode(',', array_fill(0, count($ids), '?'));
        $rows = cg_query("SELECT {$idCol} AS id, {$nameCol} AS name FROM {$table} WHERE {$idCol} IN ({$ph})", $ids);
        $map = [];
        foreach ($rows as $r) {
            $map[(int)$r['id']] = $r['name'];
        }
        return $map;
    };

    $gifts   = $fetchById("{$p}customtables_table_gifts",   'ct_id', 'ct_gifts_name',    array_values($giftIds));
    $giftsM  = [];
    if ($giftIds) {
        $ph   = implode(',', array_fill(0, count($giftIds), '?'));
        $mRows = cg_query(
            "SELECT ct_id AS id, ct_gifts_manifold AS manifold FROM {$p}customtables_table_gifts WHERE ct_id IN ({$ph})",
            array_values($giftIds)
        );
        foreach ($mRows as $r) {
            $giftsM[(int)$r['id']] = $r['manifold'];
        }
    }
    $habitats = $fetchById("{$p}customtables_table_habitat", 'ct_id', 'ct_habitat_name', $habitatId ? [$habitatId] : []);
    $diets    = $fetchById("{$p}customtables_table_diet",    'ct_id', 'ct_diet_name',    $dietId    ? [$dietId]    : []);
    $cycles   = $fetchById("{$p}customtables_table_cycle",   'ct_id', 'ct_cycle_name',   $cycleId   ? [$cycleId]   : []);
    $senses   = $fetchById("{$p}customtables_table_senses",  'ct_id', 'ct_senses_name',  array_values($senseIds));

    // Fetch full weapon data (not just names) so the battle array can show attack dice, range, damage, effect.
    $weapons = [];
    if ($weaponIds) {
        $wIds = array_values(array_unique(array_map('intval', $weaponIds)));
        $ph   = implode(',', array_fill(0, count($wIds), '?'));
        $wRows = cg_query(
            "SELECT ct_id, ct_weapons_name, ct_attack_dice, ct_range_band, ct_damage_mod, ct_effect
               FROM {$p}customtables_table_weapons WHERE ct_id IN ({$ph})",
            $wIds
        );
        foreach ($wRows as $r) {
            $weapons[(int)$r['ct_id']] = $r;
        }
    }

    // Helper to look up a gift field
    $giftField = function(?int $id, array $map): ?string {
        return ($id && isset($map[$id])) ? $map[$id] : null;
    };

    $g1 = isset($byKey['gift_1']) ? (int)$byKey['gift_1']['ref_id'] : null;
    $g2 = isset($byKey['gift_2']) ? (int)$byKey['gift_2']['ref_id'] : null;
    $g3 = isset($byKey['gift_3']) ? (int)$byKey['gift_3']['ref_id'] : null;
    $s1 = isset($byKey['sense_1']) ? (int)$byKey['sense_1']['ref_id'] : null;
    $s2 = isset($byKey['sense_2']) ? (int)$byKey['sense_2']['ref_id'] : null;
    $s3 = isset($byKey['sense_3']) ? (int)$byKey['sense_3']['ref_id'] : null;
    $w1 = isset($byKey['weapon_1']) ? (int)$byKey['weapon_1']['ref_id'] : null;
    $w2 = isset($byKey['weapon_2']) ? (int)$byKey['weapon_2']['ref_id'] : null;
    $w3 = isset($byKey['weapon_3']) ? (int)$byKey['weapon_3']['ref_id'] : null;

    $result = [
        'speciesName' => $species['speciesName'],

        'gift_id_1'  => $g1,
        'gift_1'     => $giftField($g1, $gifts),
        'manifold_1' => $giftField($g1, $giftsM),

        'gift_id_2'  => $g2,
        'gift_2'     => $giftField($g2, $gifts),
        'manifold_2' => $giftField($g2, $giftsM),

        'gift_id_3'  => $g3,
        'gift_3'     => $giftField($g3, $gifts),
        'manifold_3' => $giftField($g3, $giftsM),

        'skill_one'   => $byKey['skill_1']['text_value'] ?? null,
        'skill_two'   => $byKey['skill_2']['text_value'] ?? null,
        'skill_three' => $byKey['skill_3']['text_value'] ?? null,

        'habitat' => ($habitatId && isset($habitats[$habitatId])) ? $habitats[$habitatId] : null,
        'diet'    => ($dietId    && isset($diets[$dietId]))        ? $diets[$dietId]       : null,
        'cycle'   => ($cycleId   && isset($cycles[$cycleId]))      ? $cycles[$cycleId]     : null,

        'sense_1' => $giftField($s1, $senses),
        'sense_2' => $giftField($s2, $senses),
        'sense_3' => $giftField($s3, $senses),

        'weapon_1' => ($w1 && isset($weapons[$w1])) ? [
            'name'        => $weapons[$w1]['ct_weapons_name'] ?? '',
            'attack_dice' => $weapons[$w1]['ct_attack_dice']  ?? '',
            'range_band'  => $weapons[$w1]['ct_range_band']   ?? 'Close',
            'damage_mod'  => $weapons[$w1]['ct_damage_mod']   ?? null,
            'effect'      => $weapons[$w1]['ct_effect']       ?? '',
        ] : null,
        'weapon_2' => ($w2 && isset($weapons[$w2])) ? [
            'name'        => $weapons[$w2]['ct_weapons_name'] ?? '',
            'attack_dice' => $weapons[$w2]['ct_attack_dice']  ?? '',
            'range_band'  => $weapons[$w2]['ct_range_band']   ?? 'Close',
            'damage_mod'  => $weapons[$w2]['ct_damage_mod']   ?? null,
            'effect'      => $weapons[$w2]['ct_effect']       ?? '',
        ] : null,
        'weapon_3' => ($w3 && isset($weapons[$w3])) ? [
            'name'        => $weapons[$w3]['ct_weapons_name'] ?? '',
            'attack_dice' => $weapons[$w3]['ct_attack_dice']  ?? '',
            'range_band'  => $weapons[$w3]['ct_range_band']   ?? 'Close',
            'damage_mod'  => $weapons[$w3]['ct_damage_mod']   ?? null,
            'effect'      => $weapons[$w3]['ct_effect']       ?? '',
        ] : null,
    ];

    cg_json(['success' => true, 'data' => $result]);
}
