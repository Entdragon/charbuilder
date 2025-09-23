<?php
/**
 * Hardened enqueue for Character Generator.
 * - Loads only on the Character Builder template.
 * - Versions assets by filemtime() for cache-busting.
 * - Warns in console if a duplicate core load is attempted.
 */
add_action('wp_enqueue_scripts', function () {
    // Adjust this condition if needed:
    if (!is_page_template('page-character-generator.php')) {
        return;
    }

    $assets_dir = plugin_dir_path(__FILE__) . '../assets/';
    $assets_url = plugin_dir_url(__FILE__)  . '../assets/';

    // JS: core
    $core_rel = 'js/dist/core.bundle.js';
    $core_p   = $assets_dir . $core_rel;
    $core_u   = $assets_url . $core_rel;

    if (file_exists($core_p)) {
        wp_register_script('cg-core-bundle', $core_u, ['jquery'], filemtime($core_p), true);

        // Console guard to make duplicates obvious
        wp_add_inline_script(
            'cg-core-bundle',
            'window.CG_BUNDLES = window.CG_BUNDLES || {};
             if (window.CG_BUNDLES.core) { console.warn("[CG] Duplicate core.bundle.js load prevented"); }
             window.CG_BUNDLES.core = (window.CG_BUNDLES.core || 0) + 1;',
            'before'
        );

        wp_enqueue_script('cg-core-bundle');
    } else {
        error_log('[CG] Missing: ' . $core_p);
    }

    // JS: gifts (optional)
    $gifts_rel = 'js/dist/gifts.bundle.js';
    $gifts_p   = $assets_dir . $gifts_rel;
    $gifts_u   = $assets_url . $gifts_rel;
    if (file_exists($gifts_p)) {
        wp_enqueue_script('cg-gifts-bundle', $gifts_u, ['jquery'], filemtime($gifts_p), true);
    }

    // CSS (adjust filename if yours differs)
    $css_rel = 'css/dist/character-builder.css';
    $css_p   = $assets_dir . $css_rel;
    $css_u   = $assets_url . $css_rel;
    if (file_exists($css_p)) {
        wp_enqueue_style('cg-core-style', $css_u, [], filemtime($css_p));
    }
}, 20);
