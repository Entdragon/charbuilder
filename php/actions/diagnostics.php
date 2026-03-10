<?php
require_once __DIR__ . '/../includes/db.php';

function cg_ping(): void {
    cg_json(['success' => true, 'data' => 'pong']);
}

function cg_run_diagnostics(): void {
    $results = [];

    // DB connection
    try {
        cg_pdo();
        $results['db'] = 'ok';
    } catch (Throwable $e) {
        $results['db'] = 'error: ' . $e->getMessage();
    }

    // Table checks
    $p      = cg_prefix();
    $tables = [
        'users'                      => "{$p}users",
        'character_records'          => "{$p}character_records",
        'careers'                    => "{$p}customtables_table_careers",
        'gifts'                      => "{$p}customtables_table_gifts",
        'skills'                     => "{$p}customtables_table_skills",
        'species'                    => "{$p}customtables_table_species",
    ];
    foreach ($tables as $key => $table) {
        try {
            $row = cg_query_one("SELECT COUNT(*) AS cnt FROM `{$table}`");
            $results[$key] = 'ok (' . ($row['cnt'] ?? 0) . ' rows)';
        } catch (Throwable $e) {
            $results[$key] = 'error: ' . $e->getMessage();
        }
    }

    cg_json(['success' => true, 'data' => $results]);
}
