<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Career AJAX handlers.
 *
 * NOTE: Hook registration is owned by includes/career/index.php.
 *
 * Shared helpers live in includes/ajax-nonce.php:
 * - cg_ajax_require_nonce_multi()
 * - cg_ajax_require_post()
 */
require_once __DIR__ . '/../ajax-nonce.php';

if ( ! function_exists( 'cg_get_career_list' ) ) {
    function cg_get_career_list() {
        cg_ajax_require_post();
        cg_ajax_require_nonce_multi();

        global $wpdb;
        $table = $wpdb->prefix . 'customtables_table_careers';

        // IMPORTANT:
        // Return gift_id_1..3 and skill_one..three so Extra Careers eligibility can be computed
        // client-side without firing one AJAX request per career (prevents 508 loop/WAF storms).
        $rows = $wpdb->get_results(
            "SELECT
               ct_id                 AS id,
               ct_career_name        AS name,
               ct_career_gift_one    AS gift_id_1,
               ct_career_gift_two    AS gift_id_2,
               ct_career_gift_three  AS gift_id_3,
               ct_career_skill_one   AS skill_one,
               ct_career_skill_two   AS skill_two,
               ct_career_skill_three AS skill_three
             FROM {$table}
             ORDER BY ct_career_name ASC",
            ARRAY_A
        );
        if ( $rows === null ) {
            wp_send_json_error( 'Unable to fetch career list.' );
        }
        wp_send_json_success( $rows );
    }
}

if ( ! function_exists( 'cg_get_career_gifts' ) ) {
    function cg_get_career_gifts() {
        cg_ajax_require_post();
        cg_ajax_require_nonce_multi();

        $career_id = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;
        if ( $career_id <= 0 ) {
            wp_send_json_error( 'Invalid career ID.' );
        }

        global $wpdb;
        $careers_tbl = $wpdb->prefix . 'customtables_table_careers';
        $gifts_tbl   = $wpdb->prefix . 'customtables_table_gifts';

        $sql = "
          SELECT
            c.ct_career_name        AS careerName,

            c.ct_career_gift_one    AS gift_id_1,
            g1.ct_gifts_name        AS gift_1,
            g1.ct_gifts_manifold    AS manifold_1,

            c.ct_career_gift_two    AS gift_id_2,
            g2.ct_gifts_name        AS gift_2,
            g2.ct_gifts_manifold    AS manifold_2,

            c.ct_career_gift_three  AS gift_id_3,
            g3.ct_gifts_name        AS gift_3,
            g3.ct_gifts_manifold    AS manifold_3,

            c.ct_career_skill_one    AS skill_one,
            c.ct_career_skill_two    AS skill_two,
            c.ct_career_skill_three  AS skill_three
          FROM {$careers_tbl} c
          LEFT JOIN {$gifts_tbl} g1 ON c.ct_career_gift_one   = g1.ct_id
          LEFT JOIN {$gifts_tbl} g2 ON c.ct_career_gift_two   = g2.ct_id
          LEFT JOIN {$gifts_tbl} g3 ON c.ct_career_gift_three = g3.ct_id
          WHERE c.ct_id = %d
        ";

        $profile = $wpdb->get_row( $wpdb->prepare( $sql, $career_id ), ARRAY_A );
        if ( ! $profile ) {
            wp_send_json_error( 'Career not found.' );
        }
        wp_send_json_success( $profile );
    }
}
