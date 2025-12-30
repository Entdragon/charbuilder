<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once __DIR__ . '/ajax-diagnostics.php';

/**
 * Diagnostics endpoints.
 *
 * Hardened:
 * - cg_run_diagnostics: logged-in ADMIN only (manage_options)
 * - cg_ping: logged-in users only (read)
 *
 * NOTE: No nopriv hooks on purpose (avoid public info leakage).
 */
add_action( 'wp_ajax_cg_run_diagnostics', 'cg_run_diagnostics' );
add_action( 'wp_ajax_cg_ping',           'cg_ping' );
