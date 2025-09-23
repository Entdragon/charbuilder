<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Return the Local Knowledge gift (ID 242).
 */
add_action( 'wp_ajax_cg_get_local_knowledge',       'cg_get_local_knowledge' );
add_action( 'wp_ajax_nopriv_cg_get_local_knowledge','cg_get_local_knowledge' );
function cg_get_local_knowledge() {
    check_ajax_referer( 'cg_nonce', 'security' );

    global $wpdb;
    $id  = 242;
    $tbl = "{$wpdb->prefix}customtables_table_gifts";

    $row = $wpdb->get_row(
        $wpdb->prepare(
            "
            SELECT
              ct_id               AS id,
              ct_gifts_name       AS name,
              ct_gifts_manifold   AS ct_gifts_manifold
            FROM {$tbl}
            WHERE ct_id = %d
            ",
            $id
        ),
        ARRAY_A
    );

    if ( ! $row ) {
        wp_send_json_error( 'Local Knowledge gift not found.' );
    }

    wp_send_json_success( $row );
}

/**
 * Return the Language gift (ID 236).
 */
add_action( 'wp_ajax_cg_get_language_gift',       'cg_get_language_gift' );
add_action( 'wp_ajax_nopriv_cg_get_language_gift','cg_get_language_gift' );
function cg_get_language_gift() {
    check_ajax_referer( 'cg_nonce', 'security' );

    global $wpdb;
    $id  = 236;
    $tbl = "{$wpdb->prefix}customtables_table_gifts";

    $row = $wpdb->get_row(
        $wpdb->prepare(
            "
            SELECT
              ct_id               AS id,
              ct_gifts_name       AS name,
              ct_gifts_manifold   AS ct_gifts_manifold
            FROM {$tbl}
            WHERE ct_id = %d
            ",
            $id
        ),
        ARRAY_A
    );

    if ( ! $row ) {
        wp_send_json_error( 'Language gift not found.' );
    }

    wp_send_json_success( $row );
}

/**
 * Return all free‐choice gifts.
 */
add_action( 'wp_ajax_cg_get_free_gifts',       'cg_get_free_gifts' );
add_action( 'wp_ajax_nopriv_cg_get_free_gifts','cg_get_free_gifts' );
function cg_get_free_gifts() {
    check_ajax_referer( 'cg_nonce', 'security' );

    global $wpdb;
    $tbl = "{$wpdb->prefix}customtables_table_gifts";

    // Base columns
    $cols = [
        'ct_id                   AS id',
        'ct_gifts_name           AS name',
        'ct_gifts_allows_multiple AS allows_multiple',
        'ct_gifts_manifold       AS ct_gifts_manifold',
    ];

    // Adds ct_gifts_requires, ct_gifts_requires_two … ct_gifts_requires_nineteen
    $suffixes = [
        '', 'two','three','four','five','six',
        'seven','eight','nine','ten','eleven',
        'twelve','thirteen','fourteen','fifteen',
        'sixteen','seventeen','eighteen','nineteen'
    ];
    foreach ( $suffixes as $s ) {
        $cols[] = $s
            ? "ct_gifts_requires_{$s}"
            : 'ct_gifts_requires';
    }

    $col_sql = implode( ', ', $cols );

    $rows = $wpdb->get_results(
        "
        SELECT {$col_sql}
        FROM {$tbl}
        ORDER BY ct_gifts_name ASC
        ",
        ARRAY_A
    );

    if ( $rows === null ) {
        wp_send_json_error( 'Unable to fetch free gifts.' );
    }

    wp_send_json_success( $rows );
}
