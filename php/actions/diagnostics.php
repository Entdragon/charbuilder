<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/password.php';

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

    $p = cg_prefix();

    // Core table checks
    $tables = [
        'users'                      => "{$p}users",
        'character_records'          => "{$p}character_records",
        'careers'                    => "{$p}customtables_table_careers",
        'gifts'                      => "{$p}customtables_table_gifts",
        'skills'                     => "{$p}customtables_table_skills",
        'species'                    => "{$p}customtables_table_species",
        'gift_requirements'          => "{$p}customtables_table_gift_requirements",
        'gift_type_map'              => "{$p}customtables_table_gift_type_map",
        'career_gifts'               => "{$p}customtables_table_career_gifts",
        'career_skills'              => "{$p}customtables_table_career_skills",
        'species_traits'             => "{$p}customtables_table_species_traits",
    ];
    foreach ($tables as $key => $table) {
        try {
            $row = cg_query_one("SELECT COUNT(*) AS cnt FROM `{$table}`");
            $results[$key] = 'ok (' . ($row['cnt'] ?? 0) . ' rows)';
        } catch (Throwable $e) {
            $results[$key] = 'error: ' . $e->getMessage();
        }
    }

    // Check which columns exist on the gifts table
    try {
        $cols = cg_query("SHOW COLUMNS FROM `{$p}customtables_table_gifts`");
        $results['gifts_columns'] = implode(', ', array_column($cols, 'Field'));
    } catch (Throwable $e) {
        $results['gifts_columns'] = 'error: ' . $e->getMessage();
    }

    // Check columns on careers table
    try {
        $cols = cg_query("SHOW COLUMNS FROM `{$p}customtables_table_careers`");
        $results['careers_columns'] = implode(', ', array_column($cols, 'Field'));
    } catch (Throwable $e) {
        $results['careers_columns'] = 'error: ' . $e->getMessage();
    }

    // Check columns on species table
    try {
        $cols = cg_query("SHOW COLUMNS FROM `{$p}customtables_table_species`");
        $results['species_columns'] = implode(', ', array_column($cols, 'Field'));
    } catch (Throwable $e) {
        $results['species_columns'] = 'error: ' . $e->getMessage();
    }

    // Probe each failing action individually
    try {
        $g = "{$p}customtables_table_gifts";
        cg_query("SELECT ct_id, ct_gifts_name, ct_gifts_allows_multiple, ct_gifts_manifold FROM {$g} LIMIT 1");
        $results['probe_gifts_base'] = 'ok';
    } catch (Throwable $e) {
        $results['probe_gifts_base'] = 'error: ' . $e->getMessage();
    }

    try {
        $r = "{$p}customtables_table_gift_requirements";
        cg_query("SELECT ct_gift_id, ct_sort, ct_req_kind, ct_req_ref_id, ct_req_text FROM {$r} LIMIT 1");
        $results['probe_gift_requirements'] = 'ok';
    } catch (Throwable $e) {
        $results['probe_gift_requirements'] = 'error: ' . $e->getMessage();
    }

    try {
        $c  = "{$p}customtables_table_careers";
        $cg = "{$p}customtables_table_career_gifts";
        $cs = "{$p}customtables_table_career_skills";
        $sk = "{$p}customtables_table_skills";
        cg_query("
            SELECT c.ct_id,
                   MAX(CASE WHEN cg.sort = 1 THEN cg.gift_id END) AS gift_id_1,
                   MAX(CASE WHEN cs.sort = 1 THEN sk.ct_skill_name END) AS skill_one
            FROM {$c} c
            LEFT JOIN {$cg} cg ON c.ct_id = cg.career_id
            LEFT JOIN {$cs} cs ON c.ct_id = cs.career_id
            LEFT JOIN {$sk} sk ON cs.skill_id = sk.id
            GROUP BY c.ct_id LIMIT 1
        ");
        $results['probe_career_list'] = 'ok';
    } catch (Throwable $e) {
        $results['probe_career_list'] = 'error: ' . $e->getMessage();
    }

    try {
        $sp = "{$p}customtables_table_species";
        $st = "{$p}customtables_table_species_traits";
        cg_query("SELECT sp.ct_id, t.trait_key FROM {$sp} sp LEFT JOIN {$st} t ON sp.ct_id = t.species_id LIMIT 1");
        $results['probe_species_traits'] = 'ok';
    } catch (Throwable $e) {
        $results['probe_species_traits'] = 'error: ' . $e->getMessage();
    }

    // ── Auth diagnostics ──────────────────────────────────────────────────────
    // Check users table is reachable and report the hash format of a sample user.
    // This helps diagnose "can't log in" issues on production without exposing
    // any actual credentials or passwords.
    try {
        $uRow = cg_query_one("SELECT COUNT(*) AS cnt FROM `{$p}users`");
        $results['auth_users_count'] = (int)($uRow['cnt'] ?? 0);
    } catch (Throwable $e) {
        $results['auth_users_count'] = 'error: ' . $e->getMessage();
    }

    try {
        $sample = cg_query_one("SELECT ID, user_pass FROM `{$p}users` ORDER BY ID LIMIT 1");
        if ($sample) {
            $hash   = $sample['user_pass'] ?? '';
            $prefix = substr($hash, 0, 4);
            // Classify the hash format without exposing any credentials
            if ($prefix === '$wp$') {
                $fmt = 'wp-bcrypt ($wp$)';
            } elseif ($prefix === '$2y$' || $prefix === '$2a$' || $prefix === '$2b$') {
                $fmt = 'native-bcrypt (' . $prefix . ')';
            } elseif ($prefix === '$P$' || $prefix === '$H$') {
                $fmt = 'phpass (' . $prefix . ')';
            } elseif (strlen($hash) === 32 && ctype_xdigit($hash)) {
                $fmt = 'legacy-md5';
            } else {
                $fmt = 'unknown prefix: ' . $prefix;
            }
            $results['auth_sample_hash_format'] = $fmt;
            $results['auth_can_verify_phpass']   = function_exists('cg_check_password') ? 'yes' : 'no';
        } else {
            $results['auth_sample_hash_format'] = 'no users found';
        }
    } catch (Throwable $e) {
        $results['auth_sample_hash_format'] = 'error: ' . $e->getMessage();
    }

    // Check PHP's password_verify is available (required for bcrypt hashes)
    $results['auth_password_verify_available'] = function_exists('password_verify') ? 'yes' : 'no';

    // Check PHP version — password_hash bcrypt requires >= 5.5
    $results['auth_php_version'] = PHP_VERSION;

    // Check session is working
    $results['auth_session_status'] = match(session_status()) {
        PHP_SESSION_DISABLED => 'disabled',
        PHP_SESSION_NONE     => 'not started',
        PHP_SESSION_ACTIVE   => 'active',
        default              => 'unknown',
    };

    // Check proxy config (without revealing the secret)
    $results['auth_proxy_configured'] = (CG_PROXY_URL && CG_PROXY_SECRET) ? 'yes' : 'no';

    cg_json(['success' => true, 'data' => $results]);
}

/**
 * Admin-only: set a user's password directly using our own phpass hash.
 * Bypasses WordPress bcrypt plugins entirely.
 * POST: user_id, password, admin_key
 */
function cg_admin_set_password(): void {
    $adminKey = defined('CG_ADMIN_KEY') ? CG_ADMIN_KEY : '';
    $provided = $_POST['admin_key'] ?? '';
    if (!$adminKey || !hash_equals($adminKey, $provided)) {
        cg_json(['success' => false, 'data' => 'Forbidden']);
        return;
    }

    $userId   = (int) ($_POST['user_id']  ?? 0);
    $password = $_POST['password'] ?? '';
    if (!$userId || strlen($password) < 4) {
        cg_json(['success' => false, 'data' => 'user_id and password (min 4 chars) required']);
        return;
    }

    $hash = cg_hash_password($password);
    $p    = cg_prefix();
    cg_exec(
        "UPDATE {$p}users SET user_pass = ? WHERE ID = ?",
        [$hash, $userId]
    );

    // Verify it works immediately
    $row = cg_query_one("SELECT user_pass FROM {$p}users WHERE ID = ? LIMIT 1", [$userId]);
    $ok  = $row && cg_check_password($password, $row['user_pass']);

    cg_json(['success' => true, 'data' => [
        'user_id'  => $userId,
        'hash_set' => substr($hash, 0, 12) . '…',
        'verified' => $ok,
    ]]);
}
