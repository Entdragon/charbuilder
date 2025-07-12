<?php
if ( ! defined( 'ABSPATH' ) ) exit;

add_action('wp_ajax_cg_get_skills_list', 'cg_get_skills_list');
function cg_get_skills_list() {
    global $wpdb;
    $rows = $wpdb->get_results("
        SELECT id, ct_skill_name 
        FROM DcVnchxg4_customtables_table_skills 
        ORDER BY ct_skill_name ASC
    ", ARRAY_A);
    wp_send_json_success($rows);
}
