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

    $suffixes = ['','two','three','four','five','six',
                 'seven','eight','nine','ten','eleven',
                 'twelve','thirteen','fourteen','fifteen',
                 'sixteen','seventeen','eighteen','nineteen'];
    $requireCols = implode(', ', array_map(
        fn($s) => $s ? "ct_gifts_requires_{$s}" : 'ct_gifts_requires',
        $suffixes
    ));

    $specialSuffixes = ['','_two','_three','_four','_five','_six','_seven','_eight'];
    $requireSpecialCols = implode(', ', array_map(
        fn($s) => "ct_gifts_requires_special{$s}",
        $specialSuffixes
    ));

    $rows = cg_query("
        SELECT
          ct_id                    AS id,
          ct_gifts_name            AS name,
          ct_gifts_allows_multiple AS allows_multiple,
          ct_gifts_manifold        AS ct_gifts_manifold,
          ct_gifts_requires_special,
          {$requireSpecialCols},
          {$requireCols}
        FROM {$p}customtables_table_gifts
        ORDER BY ct_gifts_name ASC
    ");
    cg_json(['success' => true, 'data' => $rows]);
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
