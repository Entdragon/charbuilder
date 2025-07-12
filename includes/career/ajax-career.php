<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Return the list of careers for the Career dropdown.
 */
add_action( 'wp_ajax_cg_get_career_list', 'cg_get_career_list' );
function cg_get_career_list() {
    check_ajax_referer( 'cg_nonce', 'security' );

    global $wpdb;
    $table = $wpdb->prefix . 'customtables_table_careers';

    $rows = $wpdb->get_results(
        "SELECT id, ct_career_name 
         FROM {$table}
         ORDER BY ct_career_name ASC",
        ARRAY_A
    );

    wp_send_json_success( $rows );
}

/**
 * Return gifts, skills and bonuses for a single career.
 */
add_action( 'wp_ajax_cg_get_career_gifts', 'cg_get_career_gifts' );
function cg_get_career_gifts() {
    check_ajax_referer( 'cg_nonce', 'security' );

    $career_id = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;
    if ( $career_id <= 0 ) {
        wp_send_json_error( 'Invalid career ID' );
    }

    global $wpdb;
    $careers = $wpdb->prefix . 'customtables_table_careers';
    $gifts   = $wpdb->prefix . 'customtables_table_gifts';

    $row = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT 
               c.ct_career_gift_one   AS gift_id_1,
               c.ct_career_gift_two   AS gift_id_2,
               c.ct_career_gift_three AS gift_id_3,
               g1.ct_gifts_name       AS gift_1,
               g2.ct_gifts_name       AS gift_2,
               g3.ct_gifts_name       AS gift_3,
               c.ct_career_gift_one_choice   AS bonus_1,
               c.ct_career_gift_two_choice   AS bonus_2,
               c.ct_career_gift_three_choice AS bonus_3,
               c.ct_career_skill_one         AS skill_one,
               c.ct_career_skill_two         AS skill_two,
               c.ct_career_skill_three       AS skill_three
             FROM {$careers} c
             LEFT JOIN {$gifts} g1 ON c.ct_career_gift_one   = g1.id
             LEFT JOIN {$gifts} g2 ON c.ct_career_gift_two   = g2.id
             LEFT JOIN {$gifts} g3 ON c.ct_career_gift_three = g3.id
             WHERE c.id = %d",
            $career_id
        ),
        ARRAY_A
    );

    if ( ! $row ) {
        wp_send_json_error( 'Career not found' );
    }

    wp_send_json_success( $row );
}

/**
 * Return careers eligible as “extra careers” based on current gifts.
 */
add_action( 'wp_ajax_cg_get_eligible_extra_careers', 'cg_get_eligible_extra_careers' );
function cg_get_eligible_extra_careers() {
    check_ajax_referer( 'cg_nonce', 'security' );

    $selected = isset( $_POST['gifts'] ) && is_array( $_POST['gifts'] )
        ? array_map( 'intval', $_POST['gifts'] )
        : [];

    global $wpdb;
    $table = $wpdb->prefix . 'customtables_table_careers';

    $careers = $wpdb->get_results(
        "SELECT 
           id,
           ct_career_name,
           ct_career_gift_one,
           ct_career_gift_two,
           ct_career_gift_three
         FROM {$table}",
        OBJECT
    );

    $eligible = [];
    foreach ( $careers as $c ) {
        $g1 = (int) $c->ct_career_gift_one;
        $g2 = (int) $c->ct_career_gift_two;
        $g3 = (int) $c->ct_career_gift_three;

        if ( in_array( $g1, $selected, true )
          && in_array( $g2, $selected, true )
          && in_array( $g3, $selected, true ) ) {
            $eligible[] = [
                'id'   => (string) $c->id,
                'name' => $c->ct_career_name,
            ];
        }
    }

    wp_send_json_success( $eligible );
}
