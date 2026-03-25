<?php
/**
 * Database layer — PDO connection + query helpers.
 *
 * Two modes (mirrors server/db.js):
 *   PROXY mode  — CG_PROXY_URL + CG_PROXY_SECRET are set.
 *                 Sends SQL to the PHP proxy on the WordPress server via HTTPS.
 *                 Used on Replit dev where direct MySQL is firewalled.
 *   DIRECT mode — DB_HOST / DB_USER / DB_PASS / DB_NAME are set.
 *                 Connects to MySQL via PDO.
 *                 Used on the live cPanel server.
 */

require_once __DIR__ . '/config.php';

// ── Proxy mode ─────────────────────────────────────────────────────────────────

define('CG_PROXY_URL',    getenv('CG_PROXY_URL')    ?: '');
define('CG_PROXY_SECRET', getenv('CG_PROXY_SECRET') ?: '');

function cg_proxy_query(string $sql, array $params = []): array {
    $payload = json_encode(['sql' => $sql, 'params' => $params]);
    $opts = [
        'http' => [
            'method'  => 'POST',
            'header'  => implode("\r\n", [
                'Content-Type: application/json',
                'X-CG-Secret: ' . CG_PROXY_SECRET,
                'Content-Length: ' . strlen($payload),
            ]),
            'content' => $payload,
            'timeout' => 15,
            'ignore_errors' => true,
        ],
    ];
    $ctx  = stream_context_create($opts);
    $raw  = @file_get_contents(CG_PROXY_URL, false, $ctx);
    if ($raw === false) {
        throw new \RuntimeException('DB proxy request failed.');
    }
    $data = json_decode($raw, true);
    if (!is_array($data)) {
        throw new \RuntimeException('DB proxy returned invalid JSON.');
    }
    if (!empty($data['error'])) {
        throw new \RuntimeException('DB proxy error: ' . $data['error']);
    }
    return $data;
}

// ── Direct (PDO) mode ─────────────────────────────────────────────────────────

$_cg_pdo = null;

function cg_pdo(): PDO {
    global $_cg_pdo;
    if ($_cg_pdo !== null) return $_cg_pdo;

    $dsn = sprintf(
        'mysql:host=%s;dbname=%s;charset=%s',
        CG_DB_HOST, CG_DB_NAME, CG_DB_CHARSET
    );
    $_cg_pdo = new PDO($dsn, CG_DB_USER, CG_DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
    return $_cg_pdo;
}

// ── Unified helpers ────────────────────────────────────────────────────────────

function cg_prefix(): string {
    return CG_DB_PREFIX;
}

/**
 * Run a query and return all rows as an associative array.
 */
function cg_query(string $sql, array $params = []): array {
    if (CG_PROXY_URL && CG_PROXY_SECRET) {
        $data = cg_proxy_query($sql, $params);
        return $data['rows'] ?? [];
    }
    $stmt = cg_pdo()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * Run a query and return the first row (or null).
 */
function cg_query_one(string $sql, array $params = []): ?array {
    $rows = cg_query($sql, $params);
    return $rows[0] ?? null;
}

/**
 * Run an INSERT/UPDATE/DELETE.
 * Returns an array with 'rowCount' and 'lastInsertId'.
 */
function cg_exec(string $sql, array $params = []): array {
    if (CG_PROXY_URL && CG_PROXY_SECRET) {
        $data = cg_proxy_query($sql, $params);
        return [
            'rowCount'     => (int) ($data['rowCount']     ?? 0),
            'lastInsertId' => (int) ($data['lastInsertId'] ?? 0),
        ];
    }
    $stmt = cg_pdo()->prepare($sql);
    $stmt->execute($params);
    return [
        'rowCount'     => $stmt->rowCount(),
        'lastInsertId' => (int) cg_pdo()->lastInsertId(),
    ];
}

/**
 * Return the last auto-increment insert ID.
 */
function cg_last_insert_id(): int {
    if (CG_PROXY_URL && CG_PROXY_SECRET) {
        // Not meaningful outside a cg_exec() call; caller should use cg_exec()['lastInsertId'].
        return 0;
    }
    return (int) cg_pdo()->lastInsertId();
}

/**
 * Ensure the character_records table has the weapons and armor columns.
 * Safe to call on every request — uses IF NOT EXISTS-style check.
 */
function cg_ensure_battle_columns(): void {
    static $done = false;
    if ($done) return;
    $done = true;

    $p     = cg_prefix();
    $table = $p . 'character_records';

    try {
        $cols = cg_query(
            "SELECT COLUMN_NAME FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?
               AND COLUMN_NAME IN ('weapons','armor')",
            [$table]
        );
        $existing = array_column($cols, 'COLUMN_NAME');

        if (!in_array('weapons', $existing)) {
            cg_exec("ALTER TABLE `{$table}` ADD COLUMN weapons TEXT DEFAULT NULL");
        }
        if (!in_array('armor', $existing)) {
            cg_exec("ALTER TABLE `{$table}` ADD COLUMN armor TEXT DEFAULT NULL");
        }
    } catch (Throwable $e) {
        error_log('[CG] ensureBattleColumns: ' . $e->getMessage());
    }
}
