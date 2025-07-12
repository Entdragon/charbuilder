<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Return the Local Knowledge gift (ID 242)
 */
add_action( 'wp_ajax_cg_get_local_knowledge', 'cg_get_local_knowledge' );
function cg_get_local_knowledge() {
    check_ajax_referer( 'cg_nonce', 'security' );
    global $wpdb;

    $row = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT id, ct_gifts_name
             FROM {$wpdb->prefix}customtables_table_gifts
             WHERE id = %d",
            242
        ),
        ARRAY_A
    );

    if ( $row ) {
        wp_send_json_success( $row );
    } else {
        wp_send_json_error( 'Local Knowledge gift not found' );
    }
}

/**
 * Return the Language gift (ID 236)
 */
add_action( 'wp_ajax_cg_get_language_gift', 'cg_get_language_gift' );
function cg_get_language_gift() {
    check_ajax_referer( 'cg_nonce', 'security' );
    global $wpdb;

    $row = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT id, ct_gifts_name
             FROM {$wpdb->prefix}customtables_table_gifts
             WHERE id = %d",
            236
        ),
        ARRAY_A
    );

    if ( $row ) {
        wp_send_json_success( $row );
    } else {
        wp_send_json_error( 'Language gift not found' );
    }
}

/**
 * Return all free gifts for the Free Choice selectors.
 */
add_action( 'wp_ajax_cg_get_free_gifts', 'cg_get_free_gifts' );
function cg_get_free_gifts() {
    check_ajax_referer( 'cg_nonce', 'security' );
    global $wpdb;

    $table = $wpdb->prefix . 'customtables_table_gifts';

    // Base columns
    $cols = [
        'id',
        'ct_gifts_name',
        'ct_gifts_allows_multiple',
        'ct_gifts_manifold',
    ];

    // Suffixes for requires fields 1…19
    $words = [
        '', 'two', 'three', 'four', 'five', 'six',
        'seven', 'eight', 'nine', 'ten', 'eleven',
        'twelve', 'thirteen', 'fourteen', 'fifteen',
        'sixteen', 'seventeen', 'eighteen', 'nineteen'
    ];

    foreach ( $words as $w ) {
        $cols[] = 'ct_gifts_requires' . ( $w ? "_{$w}" : '' );
    }

    $col_sql = implode( ', ', $cols );

    $rows = $wpdb->get_results( "
        SELECT {$col_sql}
        FROM {$table}
        ORDER BY ct_gifts_name ASC
    ", ARRAY_A );

    wp_send_json_success( $rows );
}
