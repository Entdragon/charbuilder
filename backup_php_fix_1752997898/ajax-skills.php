<?php

namespace CharacterGeneratorDev {

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Return a flat list of all skills for the Skills tab.
 */
function cg_get_skills_list() {
    check_ajax_referer( 'cg_nonce', 'security' );

    global $wpdb;
    $table = $wpdb->prefix . 'customtables_table_skills';

    $rows = $wpdb->get_results(
        "SELECT id, ct_skill_name AS name
         FROM {$table}
         ORDER BY ct_skill_name ASC",
        ARRAY_A
    );

    if ( $rows === null ) {
        wp_send_json_error( 'Unable to fetch skills list.' );
    }

    wp_send_json_success( $rows );
}

/**
 * Return full details for a single skill (if your UI needs it).
 */
function cg_get_skill_detail() {
    check_ajax_referer( 'cg_nonce', 'security' );

    $skill_id = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;
    if ( $skill_id <= 0 ) {
        wp_send_json_error( 'Invalid skill ID.' );
    }

    global $wpdb;
    $table = $wpdb->prefix . 'customtables_table_skills';

    $row = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT id, ct_skill_name AS name, ct_skill_description AS description
             FROM {$table}
             WHERE id = %d",
            $skill_id
        ),
        ARRAY_A
    );

    if ( ! $row ) {
        wp_send_json_error( 'Skill not found.' );
    }

    wp_send_json_success( $row );
}

} // namespace CharacterGeneratorDev
