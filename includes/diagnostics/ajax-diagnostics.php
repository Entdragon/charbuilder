<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Diagnostics AJAX handlers.
 *
 * NOTE: Hook registration is owned by includes/diagnostics/index.php.
 *
 * Shared helpers live in includes/ajax-nonce.php:
 * - cg_ajax_require_nonce_multi()
 * - cg_ajax_require_post()
 */
require_once __DIR__ . '/../ajax-nonce.php';

if ( ! function_exists( 'cg_diag_require_logged_in_read' ) ) {
    function cg_diag_require_logged_in_read() {
        if ( ! is_user_logged_in() ) {
            wp_send_json_error( [ 'message' => 'Not authenticated.' ], 401 );
        }
        // Keep capability light so subscribers work.
        if ( ! current_user_can( 'read' ) ) {
            wp_send_json_error( [ 'message' => 'Forbidden.' ], 403 );
        }
    }
}

if ( ! function_exists( 'cg_diag_require_admin' ) ) {
    function cg_diag_require_admin() {
        if ( ! is_user_logged_in() ) {
            wp_send_json_error( [ 'message' => 'Not authenticated.' ], 401 );
        }
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [ 'message' => 'Forbidden.' ], 403 );
        }
    }
}

/**
 * Return a snapshot of system diagnostics.
 * Admin-only (manage_options).
 */
if ( ! function_exists( 'cg_run_diagnostics' ) ) {
    function cg_run_diagnostics() {
        cg_ajax_require_post();
        cg_ajax_require_nonce_multi();
        cg_diag_require_admin();

        global $wpdb;

        // Test DB connectivity
        try {
            $db_test   = $wpdb->get_var( 'SELECT 1' );
            $db_status = ( $db_test == 1 ) ? 'ok' : 'unexpected result';
        } catch ( Exception $e ) {
            // Admin-only endpoint; safe to include message.
            $db_status = 'error: ' . $e->getMessage();
        }

        $data = [
            'wp_version'         => get_bloginfo( 'version' ),
            'php_version'        => PHP_VERSION,
            'php_memory_limit'   => ini_get( 'memory_limit' ),
            'wp_memory_limit'    => defined( 'WP_MEMORY_LIMIT' ) ? WP_MEMORY_LIMIT : ini_get( 'memory_limit' ),
            'memory_usage_bytes' => memory_get_usage(),
            'peak_usage_bytes'   => memory_get_peak_usage(),
            'db_status'          => $db_status,
            'timestamp'          => current_time( 'mysql' ),
        ];

        wp_send_json_success( $data );
    }
}

/**
 * Lightweight ping: time + memory.
 * Logged-in only (read).
 */
if ( ! function_exists( 'cg_ping' ) ) {
    function cg_ping() {
        cg_ajax_require_post();
        cg_ajax_require_nonce_multi();
        cg_diag_require_logged_in_read();

        $data = [
            'memory_usage_bytes' => memory_get_usage(),
            'peak_usage_bytes'   => memory_get_peak_usage(),
            'php_memory_limit'   => ini_get( 'memory_limit' ),
            'timestamp'          => current_time( 'mysql' ),
        ];

        wp_send_json_success( $data );
    }
}
