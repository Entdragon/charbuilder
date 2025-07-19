<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Handle new user registration via AJAX.
 */
function cg_register_user() {
    check_ajax_referer( 'cg_nonce', 'security' );

    if ( is_user_logged_in() ) {
        wp_send_json_error( 'You are already logged in.' );
    }

    $username = isset( $_POST['username'] ) ? sanitize_user( $_POST['username'] ) : '';
    $email    = isset( $_POST['email'] )    ? sanitize_email( $_POST['email'] )   : '';
    $password = isset( $_POST['password'] ) ? $_POST['password']                 : '';

    if ( empty( $username ) || empty( $email ) || empty( $password ) ) {
        wp_send_json_error( 'All fields are required.' );
    }

    if ( username_exists( $username ) ) {
        wp_send_json_error( 'Username already taken.' );
    }

    if ( email_exists( $email ) ) {
        wp_send_json_error( 'Email already registered.' );
    }

    // Create the user
    $user_id = wp_create_user( $username, $password, $email );
    if ( is_wp_error( $user_id ) ) {
        wp_send_json_error( $user_id->get_error_message() );
    }

    // Log them in immediately
    wp_set_current_user( $user_id );
    wp_set_auth_cookie( $user_id );

    wp_send_json_success( [ 'redirect' => home_url( '/character-generator' ) ] );
}

/**
 * Handle user login via AJAX.
 */
function cg_login_user() {
    check_ajax_referer( 'cg_nonce', 'security' );

    if ( is_user_logged_in() ) {
        wp_send_json_error( 'You are already logged in.' );
    }

    $creds = [
        'user_login'    => isset( $_POST['username'] ) ? sanitize_user( $_POST['username'] ) : '',
        'user_password' => isset( $_POST['password'] ) ? $_POST['password']               : '',
        'remember'      => true
    ];

    $user = wp_signon( $creds, is_ssl() );
    if ( is_wp_error( $user ) ) {
        wp_send_json_error( 'Invalid username or password.' );
    }

    wp_send_json_success( [ 'redirect' => home_url( '/character-generator' ) ] );
}

/**
 * Handle user logout via AJAX.
 */
function cg_logout_user() {
    check_ajax_referer( 'cg_nonce', 'security' );

    if ( ! is_user_logged_in() ) {
        wp_send_json_error( 'You are not logged in.' );
    }

    wp_logout();

    wp_send_json_success( [ 'redirect' => home_url() ] );
}
