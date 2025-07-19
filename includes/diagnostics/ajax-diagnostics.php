<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Return a snapshot of system diagnostics.
 */
function cg_run_diagnostics() {
    check_ajax_referer( 'cg_nonce', 'security' );

    global $wpdb;

    // Test DB connectivity
    try {
        $db_test   = $wpdb->get_var( 'SELECT 1' );
        $db_status = ( $db_test == 1 ) ? 'ok' : 'unexpected result';
    } catch ( Exception $e ) {
        $db_status = 'error: ' . $e->getMessage();
    }

    $data = [
        'wp_version'          => get_bloginfo( 'version' ),
        'php_version'         => PHP_VERSION,
        'memory_usage_bytes'  => memory_get_usage(),
        'peak_usage_bytes'    => memory_get_peak_usage(),
        'php_memory_limit'    => ini_get( 'memory_limit' ),
        'wp_memory_limit'     => defined( 'WP_MEMORY_LIMIT' ) ? WP_MEMORY_LIMIT : ini_get( 'memory_limit' ),
        'db_status'           => $db_status,
        'timestamp'           => current_time( 'mysql' ),
    ];

    wp_send_json_success( $data );
}

/**
 * Lightweight ping: memory + time.
 */
function cg_ping() {
    check_ajax_referer( 'cg_nonce', 'security' );

    $data = [
        'memory_usage_bytes' => memory_get_usage(),
        'peak_usage_bytes'   => memory_get_peak_usage(),
        'php_memory_limit'   => ini_get( 'memory_limit' ),
        'timestamp'          => current_time( 'mysql' ),
    ];

    wp_send_json_success( $data );
}
