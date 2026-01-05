<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Auth AJAX handlers.
 *
 * NOTE: Hook registration is owned by includes/auth/index.php.
 *
 * Nonce + shared helpers live in includes/ajax-nonce.php:
 * - cg_ajax_require_nonce_multi()
 * - cg_ajax_require_post()
 */
require_once __DIR__ . '/../ajax-nonce.php';

/**
 * Handle new user registration via AJAX.
 */
if ( ! function_exists( 'cg_register_user' ) ) {
    function cg_register_user() {
        cg_ajax_require_post();
        cg_ajax_require_nonce_multi();

        if ( is_user_logged_in() ) {
            wp_send_json_error( 'You are already logged in.' );
        }

        $username = isset( $_POST['username'] ) ? sanitize_user( wp_unslash( $_POST['username'] ) ) : '';
        $email    = isset( $_POST['email'] )    ? sanitize_email( wp_unslash( $_POST['email'] ) )   : '';
        $password = isset( $_POST['password'] ) ? (string) wp_unslash( $_POST['password'] )         : '';

        if ( empty( $username ) || empty( $email ) || empty( $password ) ) {
            wp_send_json_error( 'All fields are required.' );
        }

        if ( username_exists( $username ) ) {
            wp_send_json_error( 'Username already taken.' );
        }

        if ( email_exists( $email ) ) {
            wp_send_json_error( 'Email already registered.' );
        }

        $user_id = wp_create_user( $username, $password, $email );
        if ( is_wp_error( $user_id ) ) {
            wp_send_json_error( $user_id->get_error_message() );
        }

        wp_set_current_user( $user_id );
        wp_set_auth_cookie( $user_id );

        wp_send_json_success( [ 'redirect' => home_url( '/character-generator' ) ] );
    }
}

/**
 * Handle user login via AJAX.
 */
if ( ! function_exists( 'cg_login_user' ) ) {
    function cg_login_user() {
        cg_ajax_require_post();
        cg_ajax_require_nonce_multi();

        if ( is_user_logged_in() ) {
            wp_send_json_error( 'You are already logged in.' );
        }

        $creds = [
            'user_login'    => isset( $_POST['username'] ) ? sanitize_user( wp_unslash( $_POST['username'] ) ) : '',
            'user_password' => isset( $_POST['password'] ) ? (string) wp_unslash( $_POST['password'] )         : '',
            'remember'      => true,
        ];

        $user = wp_signon( $creds, is_ssl() );
        if ( is_wp_error( $user ) ) {
            wp_send_json_error( 'Invalid username or password.' );
        }

        wp_send_json_success( [ 'redirect' => home_url( '/character-generator' ) ] );
    }
}

/**
 * Handle user logout via AJAX.
 */
if ( ! function_exists( 'cg_logout_user' ) ) {
    function cg_logout_user() {
        cg_ajax_require_post();
        cg_ajax_require_nonce_multi();

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( 'You are not logged in.' );
        }

        wp_logout();

        wp_send_json_success( [ 'redirect' => home_url() ] );
    }
}
