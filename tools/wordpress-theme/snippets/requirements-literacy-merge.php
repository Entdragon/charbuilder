<?php
/**
 * requirements-literacy-merge.php
 * Library of Calabria — child theme snippet
 *
 * PURPOSE
 * -------
 * Enqueues gift-requirements-merge.js on gift detail pages.
 * That script merges duplicate literacy requirement lines, e.g.:
 *
 *   Before:  "Literacy"          +  "Literacy: Zhongwén"   (two separate lines)
 *   After:   "Literacy: Zhongwén"                          (one merged line)
 *
 * INSTALLATION
 * ------------
 * Copy the function below into your child theme's functions.php.
 * Also copy tools/wordpress-theme/js/gift-requirements-merge.js
 * to wp-content/themes/loc-child/js/gift-requirements-merge.js.
 *
 * CONFIGURATION
 * -------------
 * Adjust the $load_on_this_page logic if gift detail pages use a different
 * URL pattern, post type, or body class on your site.
 *
 * After installing:
 * 1. Visit a gift detail page that has a Literacy: [language] requirement.
 * 2. Open the browser console (F12 → Console).
 * 3. Look for "[LOC-req-merge]" log lines — they show what was found and merged.
 * 4. If "No items matched" appears, update REQUIREMENT_ITEM_SELECTOR inside the JS file.
 * 5. Once confirmed working, set DEBUG = false inside the JS file.
 *
 * SERVER PATHS
 * ------------
 * This snippet  → add contents to:  wp-content/themes/loc-child/functions.php
 * JS file       → copy to:          wp-content/themes/loc-child/js/gift-requirements-merge.js
 */

if ( ! function_exists( 'loc_enqueue_requirements_merge' ) ) {
    function loc_enqueue_requirements_merge() {

        // Load only on pages that show gift details.
        // Adjust this condition to match your site's URL structure or post type.
        $load_on_this_page = false;

        // Option A: load on any page whose URL path contains "/gifts/"
        if ( str_contains( $_SERVER['REQUEST_URI'] ?? '', '/gifts/' ) ) {
            $load_on_this_page = true;
        }

        // Option B: load on a specific WordPress page or post type (uncomment to use)
        // if ( is_singular( 'gift' ) ) {
        //     $load_on_this_page = true;
        // }

        // Option C: load on all pages (safe, but wasteful — useful during testing)
        // $load_on_this_page = true;

        if ( ! $load_on_this_page ) {
            return;
        }

        wp_enqueue_script(
            'loc-requirements-merge',
            get_stylesheet_directory_uri() . '/js/gift-requirements-merge.js',
            array( 'jquery' ),
            '1.0.0',
            true  // load in footer
        );
    }
    add_action( 'wp_enqueue_scripts', 'loc_enqueue_requirements_merge' );
}
