<?php
/**
 * Plugin Name: Character Generator
 * Description: Modular character creation system with custom tables, AJAX, and dynamic UI.
 * Version:     0.5.1
 * Author:      Your Name
 * Text Domain: character-generator
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Autoload core features (activation, shortcode-ui, & feature folders)
require_once __DIR__ . '/includes/index.php';

/**
 * Enqueue bundled/dist assets: CSS + JS.
 */
add_action( 'wp_enqueue_scripts', 'cg_enqueue_assets' );
function cg_enqueue_assets() {
    // Only load on the front end for logged-in users
    if ( ! is_user_logged_in() ) {
        return;
    }

    $version  = '0.5.1';
    $base_url = plugin_dir_url( __FILE__ );

    // ----------------------------------------------------
    // CSS: compiled SCSS → dist/core.css
    // ----------------------------------------------------
    wp_enqueue_style(
        'cg-core-style',
        $base_url . 'assets/css/dist/core.css',
        array(),
        $version
    );

    // ----------------------------------------------------
    // JS: esbuild bundles for gifts and core logic
    // ----------------------------------------------------
    wp_enqueue_script(
        'cg-gifts',
        $base_url . 'assets/js/dist/gifts.bundle.js',
        array( 'jquery' ),
        $version,
        true
    );
    wp_enqueue_script(
        'cg-core',
        $base_url . 'assets/js/dist/core.bundle.js',
        array( 'jquery', 'cg-gifts' ),
        $version,
        true
    );

    // ----------------------------------------------------
    // Pass AJAX URL + nonce into ng-core bundle
    // ----------------------------------------------------
    wp_localize_script(
        'cg-core',
        'CG_Ajax',
        array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'cg_nonce' ),
        )
    );
}
