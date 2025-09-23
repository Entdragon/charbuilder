<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once __DIR__ . '/ajax-diagnostics.php';

// Run full diagnostics (logged‐in and guest)
add_action(   'wp_ajax_cg_run_diagnostics',        'cg_run_diagnostics' );
add_action(   'wp_ajax_nopriv_cg_run_diagnostics', 'cg_run_diagnostics' );

// Simple ping (logged‐in and guest)
add_action(   'wp_ajax_cg_ping',        'cg_ping' );
add_action(   'wp_ajax_nopriv_cg_ping', 'cg_ping' );

