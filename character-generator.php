<?php
/**
 * Plugin Name:     Character Generator
 * Plugin URI:      https://yourdomain.com/character-generator
 * Description:     Modular character creation system with custom tables, AJAX, and dynamic UI.
 * Version:         0.6.0
 * Author:          Your Name
 * Author URI:      https://yourdomain.com
 * Text Domain:     character-generator
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Core includes
require_once __DIR__ . '/includes/index.php';
require_once __DIR__ . '/includes/shortcode-ui.php';

add_action( 'wp_enqueue_scripts', 'cg_enqueue_assets' );
function cg_enqueue_assets() {
    if ( ! is_user_logged_in() ) {
        return;
    }

    $ver      = '0.6.0';
    $base_url = plugin_dir_url( __FILE__ );

    // 1) Core CSS
    wp_enqueue_style(
        'cg-core-style',
        $base_url . 'assets/css/dist/core.css',
        array(),
        $ver
    );

    // 2) JS Bundles
    wp_enqueue_script(
        'cg-gifts',
        $base_url . 'assets/js/dist/gifts.bundle.js',
        array( 'jquery' ),
        $ver,
        true
    );
    wp_enqueue_script(
        'cg-core',
        $base_url . 'assets/js/dist/core.bundle.js',
        array( 'jquery', 'cg-gifts' ),
        $ver,
        true
    );

    // 3) Ajax URL + nonce
    wp_localize_script(
        'cg-core',
        'CG_Ajax',
        array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'cg_nonce' ),
        )
    );

    global $wpdb;

    // If your tables truly live under the "DcVnchxg4_" prefix, force it here:
    $ct_prefix = 'DcVnchxg4_customtables_table_';

    // 4) Skills list
    $skills = $wpdb->get_results(
        "SELECT
           ct_id           AS id,
           ct_skill_name   AS name
         FROM {$ct_prefix}skills
         ORDER BY ct_skill_name ASC",
        ARRAY_A
    );
    wp_localize_script( 'cg-core', 'CG_SKILLS_LIST', $skills );

    // 5) Species list
    $species = $wpdb->get_results(
        "SELECT
           ct_id             AS id,
           ct_species_name   AS name
         FROM {$ct_prefix}species
         ORDER BY ct_species_name ASC",
        ARRAY_A
    );
    wp_localize_script( 'cg-core', 'CG_SPECIES_LIST', $species );

    // 6) Careers list
    $careers = $wpdb->get_results(
        "SELECT
           ct_id            AS id,
           ct_career_name   AS name
         FROM {$ct_prefix}careers
         ORDER BY ct_career_name ASC",
        ARRAY_A
    );
    wp_localize_script( 'cg-core', 'CG_CAREERS_LIST', $careers );
}
