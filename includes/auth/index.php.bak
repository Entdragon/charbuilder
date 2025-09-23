<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once __DIR__ . '/ajax-auth.php';

// AJAX registration (guests only)
add_action( 'wp_ajax_nopriv_cg_register_user', 'cg_register_user' );

// AJAX login (guests only)
add_action( 'wp_ajax_nopriv_cg_login_user',    'cg_login_user' );

// AJAX logout (logged-in users)
add_action( 'wp_ajax_cg_logout_user',         'cg_logout_user' );
