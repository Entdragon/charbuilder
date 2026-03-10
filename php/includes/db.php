<?php
/**
 * Database layer — PDO connection + query helpers.
 * Direct MySQL connection (works on same server as WordPress).
 */

require_once __DIR__ . '/config.php';

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

function cg_prefix(): string {
    return CG_DB_PREFIX;
}

/**
 * Run a query and return all rows.
 */
function cg_query(string $sql, array $params = []): array {
    $stmt = cg_pdo()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * Run a query and return the first row (or null).
 */
function cg_query_one(string $sql, array $params = []): ?array {
    $stmt = cg_pdo()->prepare($sql);
    $stmt->execute($params);
    $row = $stmt->fetch();
    return $row !== false ? $row : null;
}

/**
 * Run an INSERT/UPDATE/DELETE. Returns the PDOStatement for lastInsertId access.
 */
function cg_exec(string $sql, array $params = []): PDOStatement {
    $stmt = cg_pdo()->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

/**
 * Return the last auto-increment insert ID.
 */
function cg_last_insert_id(): string {
    return cg_pdo()->lastInsertId();
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
        // Non-fatal — app keeps working even without these columns
        error_log('[CG] ensureBattleColumns: ' . $e->getMessage());
    }
}
