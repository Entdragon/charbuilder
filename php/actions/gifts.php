<?php
require_once __DIR__ . '/../includes/db.php';

const CG_DEFAULT_LANGUAGES = [
    'Calabrian','Common','Dwarven','Elven','Goblin','Hesperian',
    'Kawtaw','Mordic','Old Calabrian','Orcish','Sylvan','Urathi',
];

function cg_get_local_knowledge(): void {
    $p   = cg_prefix();
    $row = cg_query_one(
        "SELECT ct_id AS id, ct_gifts_name AS name, ct_gifts_manifold AS ct_gifts_manifold
         FROM {$p}customtables_table_gifts WHERE ct_id = 242 LIMIT 1"
    );
    if (!$row) {
        cg_json(['success' => false, 'data' => 'Local Knowledge gift not found.']);
        return;
    }
    cg_json(['success' => true, 'data' => $row]);
}

function cg_get_language_gift(): void {
    $p   = cg_prefix();
    $row = cg_query_one(
        "SELECT ct_id AS id, ct_gifts_name AS name, ct_gifts_manifold AS ct_gifts_manifold
         FROM {$p}customtables_table_gifts WHERE ct_id = 236 LIMIT 1"
    );
    if (!$row) {
        cg_json(['success' => false, 'data' => 'Language gift not found.']);
        return;
    }
    cg_json(['success' => true, 'data' => $row]);
}

function cg_get_free_gifts(): void {
    $p = cg_prefix();
    $g = "{$p}customtables_table_gifts";
    $r = "{$p}customtables_table_gift_requirements";

    // Fetch all gifts (base columns still present after migration)
    $rows = cg_query("
        SELECT
          ct_id                    AS id,
          ct_gifts_name            AS name,
          ct_gifts_allows_multiple AS allows_multiple,
          ct_gifts_manifold        AS ct_gifts_manifold
        FROM {$g}
        ORDER BY ct_gifts_name ASC
    ");

    // Index by id for fast lookup
    $byId = [];
    foreach ($rows as $row) {
        $byId[(int) $row['id']] = $row;
    }

    // Fetch all requirements in one query
    $reqs = cg_query("
        SELECT ct_gift_id, ct_sort, ct_req_kind, ct_req_ref_id, ct_req_text
        FROM {$r}
        ORDER BY ct_gift_id ASC, ct_sort ASC
    ");

    // Column-name suffix arrays matching the old flat schema
    // Sort 1 → '' (bare column), sort 2 → '_two', etc.
    $requireSuffixes = [
        1 => '',        2 => '_two',     3 => '_three',   4 => '_four',
        5 => '_five',   6 => '_six',     7 => '_seven',   8 => '_eight',
        9 => '_nine',  10 => '_ten',    11 => '_eleven', 12 => '_twelve',
       13 => '_thirteen', 14 => '_fourteen', 15 => '_fifteen', 16 => '_sixteen',
       17 => '_seventeen', 18 => '_eighteen', 19 => '_nineteen',
    ];
    $specialSuffixes = [
        1 => '',      2 => '_two',   3 => '_three', 4 => '_four',
        5 => '_five', 6 => '_six',   7 => '_seven', 8 => '_eight',
    ];

    foreach ($reqs as $req) {
        $gId  = (int) $req['ct_gift_id'];
        $sort = (int) $req['ct_sort'];
        $kind = (string) $req['ct_req_kind'];

        if (!isset($byId[$gId])) {
            continue;
        }

        if ($kind === 'gift_ref') {
            // Prerequisite gift reference — maps to ct_gifts_requires[_suffix]
            if (!isset($requireSuffixes[$sort])) continue;
            $col = 'ct_gifts_requires' . $requireSuffixes[$sort];
            $byId[$gId][$col] = (int) $req['ct_req_ref_id'];

        } elseif ($kind === 'legacy_text') {
            // Old INT value stored as text — treat as gift ID
            if (!isset($requireSuffixes[$sort])) continue;
            $col = 'ct_gifts_requires' . $requireSuffixes[$sort];
            $byId[$gId][$col] = (int) $req['ct_req_text'];

        } elseif ($kind === 'special_text') {
            // Free-text requirement — maps to ct_gifts_requires_special[_suffix]
            if (!isset($specialSuffixes[$sort])) continue;
            $col = 'ct_gifts_requires_special' . $specialSuffixes[$sort];
            $byId[$gId][$col] = (string) $req['ct_req_text'];
        }
    }

    cg_json(['success' => true, 'data' => array_values($byId)]);
}

function cg_ensure_language_table(): void {
    $p     = cg_prefix();
    $table = "`{$p}cg_languages`";

    cg_exec("
        CREATE TABLE IF NOT EXISTS {$table} (
          id         INT AUTO_INCREMENT PRIMARY KEY,
          name       VARCHAR(100) NOT NULL,
          sort_order INT NOT NULL DEFAULT 0,
          UNIQUE KEY uq_cg_lang_name (name)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");

    $count = (int) (cg_query_one("SELECT COUNT(*) AS cnt FROM {$table}")['cnt'] ?? 0);
    if ($count === 0) {
        $langs = CG_DEFAULT_LANGUAGES;
        $placeholders = implode(', ', array_map(fn($i) => "(?, {$i})", range(0, count($langs) - 1)));
        cg_exec(
            "INSERT IGNORE INTO {$table} (name, sort_order) VALUES {$placeholders}",
            $langs
        );
    }
}

function cg_get_language_list(): void {
    $p = cg_prefix();
    try {
        cg_ensure_language_table();
        $rows = cg_query("SELECT name FROM `{$p}cg_languages` ORDER BY sort_order ASC, name ASC");
        $list = array_values(array_filter(array_map(fn($r) => trim($r['name'] ?? ''), $rows)));
        cg_json(['success' => true, 'data' => $list]);
    } catch (Throwable $e) {
        error_log('[CG] cg_get_language_list DB error: ' . $e->getMessage());
        $fallback = CG_DEFAULT_LANGUAGES;
        sort($fallback);
        cg_json(['success' => true, 'data' => $fallback]);
    }
}
