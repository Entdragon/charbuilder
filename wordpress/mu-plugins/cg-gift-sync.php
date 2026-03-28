<?php
/**
 * CG Gift Sync — mu-plugin
 *
 * Automatically syncs gift child tables in the character generator whenever
 * a gift record is saved in the CustomTables WordPress admin.
 *
 * ── Setup ────────────────────────────────────────────────────────────────────
 * 1. Copy this file to wp-content/mu-plugins/cg-gift-sync.php
 *
 * 2. Add these constants to wp-config.php (before the "stop editing" line):
 *
 *      // Character generator sync settings
 *      define('CG_CHARGEN_URL',  'https://characters.libraryofcalbria.com');
 *      define('CG_SYNC_SECRET',  'your-secret-here');   // same value as CG_SYNC_SECRET in config.php
 *      define('CG_CT_TABLE_NAME', 'gifts');              // CustomTables internal table name for gifts
 *
 * 3. Generate a strong random secret (e.g. from a password manager or openssl):
 *      openssl rand -hex 32
 *    Use the SAME value in wp-config.php and in config.php on the char-gen server.
 *
 * ── How it works ─────────────────────────────────────────────────────────────
 * CustomTables admin pages use URLs like:
 *   /wp-admin/admin.php?page=customtables-listofrecords&tableid=X&action=editrecord&id=Y
 *
 * When an admin submits the edit form, this plugin:
 *  1. Detects the CustomTables gift save (page slug + tableid check)
 *  2. Records the gift ID being modified
 *  3. At request shutdown, fires a non-blocking POST to the character generator
 *     endpoint cg_admin_sync_single_gift with the gift ID and sync secret
 *
 * The sync is fire-and-forget (non-blocking) so it never slows down the
 * WordPress admin page.
 *
 * ── Logging ──────────────────────────────────────────────────────────────────
 * Sync attempts are written to the WordPress error log. Check wp-content/debug.log
 * (or your hosting error log) if syncs aren't firing.
 */

defined('ABSPATH') || exit;

// ── Config defaults (overridable in wp-config.php) ───────────────────────────

if (!defined('CG_CHARGEN_URL'))   define('CG_CHARGEN_URL',   'https://characters.libraryofcalbria.com');
if (!defined('CG_SYNC_SECRET'))   define('CG_SYNC_SECRET',   '');
if (!defined('CG_CT_TABLE_NAME')) define('CG_CT_TABLE_NAME', 'gifts');

// ── Detect CustomTables gift save ─────────────────────────────────────────────

add_action('admin_init', function () {

    // Only act on POST requests to the WP admin
    if (empty($_POST) || !is_admin()) {
        return;
    }

    // Page slug must reference CustomTables
    $page = sanitize_key($_GET['page'] ?? '');
    if (stripos($page, 'customtables') === false) {
        return;
    }

    // Resolve the gifts table ID from the CustomTables meta table if needed
    $tableId = intval($_GET['tableid'] ?? 0);
    if (!$tableId) {
        return;
    }

    // Confirm this table ID corresponds to the gifts table
    if (!cg_gift_sync_is_gifts_table($tableId)) {
        return;
    }

    // Gift record ID (present for edit operations; absent for new records)
    $giftId = intval($_GET['id'] ?? 0);
    if (!$giftId) {
        // New record — we won't know the ID until CT has assigned it.
        // Register a shutdown handler that queries for the newest record.
        cg_gift_sync_register_new_record_shutdown();
        return;
    }

    // Existing record edit — store ID for shutdown handler
    $GLOBALS['cg_gift_sync_id'] = $giftId;
    cg_gift_sync_register_shutdown();
});

// ── Helpers ───────────────────────────────────────────────────────────────────

/**
 * Check if a CustomTables table ID corresponds to the configured gifts table name.
 * Uses a static cache so the DB is only queried once per request.
 */
function cg_gift_sync_is_gifts_table(int $tableId): bool {
    static $cache = [];
    if (isset($cache[$tableId])) {
        return $cache[$tableId];
    }

    global $wpdb;
    $targetName = CG_CT_TABLE_NAME;

    // Try common CT meta-table structures
    $candidates = [
        "SELECT tablename  FROM {$wpdb->prefix}customtables_table_tables WHERE id = %d",
        "SELECT tablename  FROM {$wpdb->prefix}customtables_table_tables WHERE tableid = %d",
        "SELECT ct_tablename FROM {$wpdb->prefix}customtables_table_tables WHERE ct_id = %d",
    ];

    $found = false;
    foreach ($candidates as $sql) {
        try {
            $name = $wpdb->get_var($wpdb->prepare($sql, $tableId));
            if ($name !== null) {
                $found = (strtolower(trim($name)) === strtolower(trim($targetName)));
                break;
            }
        } catch (Throwable $e) {
            // Table doesn't exist in this structure — try next
        }
    }

    $cache[$tableId] = $found;
    return $found;
}

/**
 * Register a shutdown handler that will call the sync endpoint for a known gift ID.
 */
function cg_gift_sync_register_shutdown(): void {
    register_shutdown_function(function () {
        $giftId = $GLOBALS['cg_gift_sync_id'] ?? 0;
        if (!$giftId) {
            return;
        }
        cg_gift_sync_call($giftId);
    });
}

/**
 * Register a shutdown handler for new records — queries the DB to find the
 * just-inserted gift ID (the row with the highest ct_id in the gifts table).
 */
function cg_gift_sync_register_new_record_shutdown(): void {
    register_shutdown_function(function () {
        global $wpdb;
        $table  = $wpdb->prefix . 'customtables_table_gifts';
        $giftId = (int) $wpdb->get_var("SELECT MAX(ct_id) FROM {$table}");
        if ($giftId > 0) {
            cg_gift_sync_call($giftId);
        }
    });
}

/**
 * Fire a non-blocking POST to the character generator sync endpoint.
 *
 * @param int $giftId  The ct_id of the gift to sync.
 */
function cg_gift_sync_call(int $giftId): void {
    $secret = CG_SYNC_SECRET;
    if ($secret === '') {
        error_log('[CG Gift Sync] CG_SYNC_SECRET is not configured — skipping sync for gift ' . $giftId);
        return;
    }

    $url  = rtrim(CG_CHARGEN_URL, '/') . '/ajax.php';
    $body = http_build_query([
        'action'      => 'cg_admin_sync_single_gift',
        'gift_id'     => $giftId,
        'sync_secret' => $secret,
    ]);

    // Use wp_remote_post with blocking=false for fire-and-forget
    $result = wp_remote_post($url, [
        'body'     => $body,
        'timeout'  => 5,
        'blocking' => false,
        'headers'  => ['Content-Type' => 'application/x-www-form-urlencoded'],
        'sslverify'=> true,
    ]);

    if (is_wp_error($result)) {
        error_log('[CG Gift Sync] Failed to call sync for gift ' . $giftId . ': ' . $result->get_error_message());
    } else {
        error_log('[CG Gift Sync] Sync triggered for gift ' . $giftId);
    }
}
