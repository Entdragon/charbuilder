<?php
// Fast, proxy-friendly rebuild of the Urban Jungle junction tables.
// Replicates uj_build_joins_internal() but batches each junction table into a
// single multi-row INSERT so we make ~20 proxy calls instead of hundreds.
chdir('/home/runner/workspace');
require 'php/includes/config.php';
require 'php/includes/db.php';
require 'php/actions/uj.php';

$t0 = microtime(true);
function logln(string $s): void {
    file_put_contents('/tmp/uj-rebuild.log', $s . "\n", FILE_APPEND);
    echo $s . "\n";
}
@unlink('/tmp/uj-rebuild.log');
logln('start: ' . date('c'));

// Normalized name → id maps.
$skillMap = [];
foreach (cg_query("SELECT id, name FROM `" . uj_tbl('skills') . "`") as $r) {
    $skillMap[uj_normalize_name($r['name'])] = (int)$r['id'];
}
$giftMap = [];
foreach (cg_query("SELECT id, name FROM `" . uj_tbl('gifts') . "`") as $r) {
    $giftMap[uj_normalize_name($r['name'])] = (int)$r['id'];
}
$soakMap = [];
foreach (cg_query("SELECT id, name FROM `" . uj_tbl('soaks') . "`") as $r) {
    $soakMap[uj_normalize_name($r['name'])] = (int)$r['id'];
}
logln('maps: skills=' . count($skillMap) . ' gifts=' . count($giftMap) . ' soaks=' . count($soakMap));

// Accumulate tuples per junction table.
$rows = [
    'species_skills' => [], 'species_gifts' => [],
    'types_skills'   => [], 'types_gifts'   => [], 'types_soaks' => [],
    'careers_skills' => [], 'careers_gifts' => [],
];

// Species
foreach (cg_query("SELECT id, skill_1, skill_2, skill_3, gift_1, gift_2 FROM `" . uj_tbl('species') . "` WHERE published=1") as $sp) {
    foreach ([[$sp['skill_1'],1],[$sp['skill_2'],2],[$sp['skill_3'],3]] as [$raw,$ord]) {
        $n = uj_normalize_name((string)$raw);
        if ($n && isset($skillMap[$n])) $rows['species_skills'][] = [(int)$sp['id'], $skillMap[$n], $ord];
    }
    foreach ([[$sp['gift_1'],1],[$sp['gift_2'],2]] as [$raw,$ord]) {
        $n = uj_normalize_name((string)$raw);
        if ($n && isset($giftMap[$n])) $rows['species_gifts'][] = [(int)$sp['id'], $giftMap[$n], $ord];
    }
}
// Types
foreach (cg_query("SELECT id, skill_1, skill_2, skill_3, gift_1, soak_1, soak_2 FROM `" . uj_tbl('types') . "` WHERE published=1") as $ty) {
    foreach ([[$ty['skill_1'],1],[$ty['skill_2'],2],[$ty['skill_3'],3]] as [$raw,$ord]) {
        $n = uj_normalize_name((string)$raw);
        if ($n && isset($skillMap[$n])) $rows['types_skills'][] = [(int)$ty['id'], $skillMap[$n], $ord];
    }
    $g = uj_normalize_name((string)($ty['gift_1'] ?? ''));
    if ($g && isset($giftMap[$g])) $rows['types_gifts'][] = [(int)$ty['id'], $giftMap[$g], 1];
    foreach ([[$ty['soak_1'],1],[$ty['soak_2'],2]] as [$raw,$ord]) {
        $n = uj_normalize_name((string)$raw);
        if ($n && isset($soakMap[$n])) $rows['types_soaks'][] = [(int)$ty['id'], $soakMap[$n], $ord];
    }
}
// Careers
foreach (cg_query("SELECT id, skill_1, skill_2, skill_3, gift_1, gift_2 FROM `" . uj_tbl('careers') . "` WHERE published=1") as $ca) {
    foreach ([[$ca['skill_1'],1],[$ca['skill_2'],2],[$ca['skill_3'],3]] as [$raw,$ord]) {
        $n = uj_normalize_name((string)$raw);
        if ($n && isset($skillMap[$n])) $rows['careers_skills'][] = [(int)$ca['id'], $skillMap[$n], $ord];
    }
    foreach ([[$ca['gift_1'],1],[$ca['gift_2'],2]] as [$raw,$ord]) {
        $n = uj_normalize_name((string)$raw);
        if ($n && isset($giftMap[$n])) $rows['careers_gifts'][] = [(int)$ca['id'], $giftMap[$n], $ord];
    }
}

// Column names per table (parent_id, child_id, sort_order).
$cols = [
    'species_skills' => ['species_id','skill_id'],
    'species_gifts'  => ['species_id','gift_id'],
    'types_skills'   => ['type_id','skill_id'],
    'types_gifts'    => ['type_id','gift_id'],
    'types_soaks'    => ['type_id','soak_id'],
    'careers_skills' => ['career_id','skill_id'],
    'careers_gifts'  => ['career_id','gift_id'],
];

$total = 0;
foreach ($rows as $tbl => $tuples) {
    cg_exec("DELETE FROM `" . uj_tbl($tbl) . "`", []);
    if (!$tuples) { logln("$tbl: 0 rows"); continue; }
    [$c1, $c2] = $cols[$tbl];
    // Chunk to keep payloads reasonable.
    foreach (array_chunk($tuples, 200) as $chunk) {
        $ph = implode(',', array_fill(0, count($chunk), '(?,?,?)'));
        $params = [];
        foreach ($chunk as $t) { $params[] = $t[0]; $params[] = $t[1]; $params[] = $t[2]; }
        cg_exec("INSERT IGNORE INTO `" . uj_tbl($tbl) . "` ($c1, $c2, sort_order) VALUES $ph", $params);
    }
    $total += count($tuples);
    logln("$tbl: " . count($tuples) . " rows");
}

logln('done: ' . $total . ' rows in ' . round(microtime(true) - $t0, 1) . 's at ' . date('c'));
