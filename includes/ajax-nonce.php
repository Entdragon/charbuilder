<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Shared nonce validator for Character Generator AJAX endpoints.
 *
 * Accepts:
 * - generic nonce action: 'cg_nonce'
 * - per-action nonce: $_REQUEST['action'] (e.g. 'cg_get_species_list')
 * - legacy retry nonce: 'cg_ajax'
 *
 * Looks in common nonce fields: security, nonce, _ajax_nonce, _wpnonce.
 * On failure: wp_die('-1', 403) (matches WP AJAX behavior)
 */
if ( ! function_exists( 'cg_ajax_require_nonce_multi' ) ) {
    function cg_ajax_require_nonce_multi( $default_action = 'cg_nonce' ) {
        $nonce = '';

        foreach ( [ 'security', 'nonce', '_ajax_nonce', '_wpnonce' ] as $k ) {
            if ( isset( $_REQUEST[ $k ] ) && $_REQUEST[ $k ] !== '' ) {
                $nonce = sanitize_text_field( wp_unslash( $_REQUEST[ $k ] ) );
                break;
            }
        }

        $req_action = '';
        if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] !== '' ) {
            $req_action = sanitize_key( wp_unslash( $_REQUEST['action'] ) );
        }

        $ok = false;
        if ( $nonce !== '' ) {
            if ( wp_verify_nonce( $nonce, $default_action ) ) {
                $ok = true;
            } elseif ( $req_action && wp_verify_nonce( $nonce, $req_action ) ) {
                $ok = true;
            } elseif ( wp_verify_nonce( $nonce, 'cg_ajax' ) ) {
                $ok = true;
            }
        }

        if ( ! $ok ) {
            wp_die( '-1', 403 );
        }
        return true;
    }
}
