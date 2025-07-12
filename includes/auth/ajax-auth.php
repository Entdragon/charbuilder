<?php
if ( ! defined( 'ABSPATH' ) ) exit;

// ---------------------------
// 👤 Custom Registration
// ---------------------------
add_action( 'wp_ajax_nopriv_cg_register_user', 'cg_register_user' );
function cg_register_user() {
  check_ajax_referer( 'cg_nonce', 'security' );

  $username = sanitize_user( $_POST['username'] ?? '' );
  $email    = sanitize_email( $_POST['email'] ?? '' );
  $password = $_POST['password'] ?? '';

  if ( empty($username) || empty($email) || empty($password) ) {
    wp_send_json_error( 'All fields are required.' );
  }

  if ( username_exists( $username ) ) {
    wp_send_json_error( 'Username already taken.' );
  }

  if ( email_exists( $email ) ) {
    wp_send_json_error( 'Email already registered.' );
  }

  $user_id = wp_insert_user([
    'user_login' => $username,
    'user_email' => $email,
    'user_pass'  => $password,
    'role'       => 'subscriber'
  ]);

  if ( is_wp_error( $user_id ) ) {
    wp_send_json_error( 'Failed to create account. Please try again.' );
  }

  wp_set_current_user( $user_id );
  wp_set_auth_cookie( $user_id );
  wp_send_json_success([ 'redirect' => home_url('/character-generator') ]);
}


// ---------------------------
// 🔐 Custom Login
// ---------------------------
add_action( 'wp_ajax_nopriv_cg_login_user', 'cg_login_user' );
function cg_login_user() {
  check_ajax_referer( 'cg_nonce', 'security' );

  $creds = [
    'user_login'    => $_POST['username'] ?? '',
    'user_password' => $_POST['password'] ?? '',
    'remember'      => true
  ];

  $user = wp_signon( $creds, is_ssl() );

  if ( is_wp_error( $user ) ) {
    wp_send_json_error( 'Invalid username or password.' );
  }

  wp_send_json_success([ 'redirect' => home_url('/character-generator') ]);
}
