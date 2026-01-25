<?php
/**
 * Canonical front-end asset loader for Character Builder (Stage).
 * - Loads exactly: core.bundle.js + core.css
 * - ONLY loads on pages containing the [character_generator] shortcode
 *   OR known builder page id/slug fallback (page 131 / 'character-generator')
 * - Blocks any script whose SRC contains 'assets/js/dist/gifts.bundle.js'
 * - Adds a universal jQuery AJAX nonce shim for admin-ajax requests:
 *     • Uses cg_nonce everywhere (matches server-side nonce expectations)
 *     • Overwrites nonce fields for cg_* actions (fixes 403/-1 + retry storms)
 * - Optionally preloads Skills/Species/Careers lists into JS globals to reduce AJAX
 *
 * NOTE: CG_NONCES is kept for backwards compatibility with older JS that expects
 * per-action nonce keys. All are set to the same cg_nonce token.
 */

if (!defined('ABSPATH')) { exit; }

function cg_is_character_generator_page() {
    static $cached = null;
    if ($cached !== null) { return $cached; }

    $cached = false;

    if (is_admin()) { return $cached; }

    // Manual override hook
    if (apply_filters('cg_force_builder_assets', false) === true) {
        $cached = true;
        return $cached;
    }

    // Fallback gate (we know your builder page was page-id-131 in the console)
    if (function_exists('is_page') && (is_page(131) || is_page('character-generator'))) {
        $cached = true;
        return $cached;
    }

    // Primary gate: shortcode present in post_content
    if (is_singular()) {
        global $post;
        if ($post && isset($post->post_content) && function_exists('has_shortcode')) {
            if (has_shortcode($post->post_content, 'character_generator')) {
                $cached = true;
                return $cached;
            }
        }
    }

    return $cached;
}

function cg_register_enqueue_core_assets() {
    if (!cg_is_character_generator_page()) { return; }

    static $did = false;
    if ($did) { return; }
    $did = true;

    $base_dir  = dirname(__DIR__);
    $main_file = $base_dir . '/character-generator.php';

    $core_rel = 'js/dist/core.bundle.js';
    $css_rel  = 'css/dist/core.css';

    $core_path = $base_dir . '/assets/' . $core_rel;
    $css_path  = $base_dir . '/assets/' . $css_rel;

    $core_url  = plugins_url('assets/' . $core_rel, $main_file);
    $css_url   = plugins_url('assets/' . $css_rel,  $main_file);

    $core_ver = file_exists($core_path) ? filemtime($core_path) : null;
    $css_ver  = file_exists($css_path)  ? filemtime($css_path)  : null;

    wp_register_script('cg-core-bundle', $core_url, ['jquery'], $core_ver, true);

    // Default nonce action used across the front-end:
    $cg_nonce = wp_create_nonce('cg_nonce');

    /**
     * Keep CG_NONCES for compatibility, but use cg_nonce for all.
     * IMPORTANT: include all actions used by the current core JS bundle.
     */
    $actions = [
        // Characters
        'cg_load_characters',
        'cg_list_characters',
        'cg_new_character',
        'cg_get_character',
        'cg_save_character',
        'cg_delete_character',

        // Gifts / skills / lists
        'cg_get_free_gifts',
        'cg_get_gifts',
        'cg_get_skills',
        'cg_get_species',
        'cg_get_careers',
        'cg_get_species_list',
        'cg_get_career_list',

        // Profiles used by core JS
        'cg_get_species_profile',
        'cg_get_career_profile',
        'cg_get_career_gifts',
    ];

    $action_nonces = [];
    foreach ($actions as $a) { $action_nonces[$a] = $cg_nonce; }

    wp_localize_script('cg-core-bundle', 'CG_AJAX', [
        'ajax_url'     => admin_url('admin-ajax.php'),
        'ajaxurl'      => admin_url('admin-ajax.php'),

        'nonce'        => $cg_nonce,
        'security'     => $cg_nonce,
        '_ajax_nonce'  => $cg_nonce,
    ]);
    wp_localize_script('cg-core-bundle', 'CG_NONCES', $action_nonces);

    // Optional preload lists (reduces AJAX on open)
    global $wpdb;
    if ($wpdb instanceof wpdb) {
        $ct_prefix = $wpdb->prefix . 'customtables_table_';

        $skills = $wpdb->get_results(
            "SELECT ct_id AS id, ct_skill_name AS name
             FROM {$ct_prefix}skills
             ORDER BY ct_skill_name ASC",
            ARRAY_A
        );
        $species = $wpdb->get_results(
            "SELECT ct_id AS id, ct_species_name AS name
             FROM {$ct_prefix}species
             ORDER BY ct_species_name ASC",
            ARRAY_A
        );
        $careers = $wpdb->get_results(
            "SELECT ct_id AS id, ct_career_name AS name
             FROM {$ct_prefix}careers
             ORDER BY ct_career_name ASC",
            ARRAY_A
        );

        wp_localize_script('cg-core-bundle', 'CG_SKILLS_LIST',  is_array($skills)  ? $skills  : []);
        wp_localize_script('cg-core-bundle', 'CG_SPECIES_LIST', is_array($species) ? $species : []);
        wp_localize_script('cg-core-bundle', 'CG_CAREERS_LIST', is_array($careers) ? $careers : []);
    }

    wp_add_inline_script(
        'cg-core-bundle',
        'window.CG_Ajax = window.CG_AJAX; window.CG_NONCE = (window.CG_AJAX && (CG_AJAX.security||CG_AJAX.nonce||CG_AJAX._ajax_nonce)) || "";',
        'before'
    );

    // AJAX shim: ONLY for cg_* actions; ALWAYS overwrite nonce fields.
    $shim = <<<'JS'
(function(w,$){
  if(!w || !$ || !$.ajaxPrefilter) return;

  // CG HARDEN: idempotent ajaxPrefilter
  if (w.__CG_AJAX_PREFILTER_INSTALLED__) return;
  w.__CG_AJAX_PREFILTER_INSTALLED__ = true;

  var env = w.CG_AJAX || {};
  var url = env.ajaxurl || env.ajax_url || w.ajaxurl || '/wp-admin/admin-ajax.php';
  var gen = (env.security || env.nonce || env._ajax_nonce || w.CG_NONCE || '');

  function isAdminAjax(u){ return /admin-ajax\.php/.test(String(u||'')); }
  function isCgAction(a){ return (typeof a === 'string') && a.indexOf('cg_') === 0; }

  function getActionFromData(d){
    if (typeof d === 'string') {
      try { return (new URLSearchParams(d)).get('action') || ''; }
      catch(e){ var m = String(d).match(/(?:^|&)action=([^&]+)/); return m ? decodeURIComponent(m[1]) : ''; }
    }
    if (d && typeof d === 'object' && d.action) return String(d.action);
    return '';
  }

  function setNonceQS(qs, n){
    var s = String(qs || '');
    try {
      var p = new URLSearchParams(s);
      p.set('security', n); p.set('nonce', n); p.set('_ajax_nonce', n);
      return p.toString();
    } catch(e) {
      s = s.replace(/(^|&)(security|nonce|_ajax_nonce)=[^&]*/g, '');
      s = s.replace(/^&+/, '').replace(/&+$/, '');
      return (s ? (s + '&') : '') +
        'security=' + encodeURIComponent(n) +
        '&nonce=' + encodeURIComponent(n) +
        '&_ajax_nonce=' + encodeURIComponent(n);
    }
  }

  $.ajaxPrefilter(function(options){
    options = options || {};
    options.url = options.url || url;
    if (!isAdminAjax(options.url)) return;

    var action = getActionFromData(options.data);
    if (!isCgAction(action)) return;

    var env2 = w.CG_AJAX || env || {};
    var gen2 = (env2.security || env2.nonce || env2._ajax_nonce || w.CG_NONCE || gen || "");
    if (!gen2) return;

    if (typeof options.data === 'string') options.data = setNonceQS(options.data, gen2);
    else if (options.data && typeof options.data === 'object') {
      options.data.security = gen2; options.data.nonce = gen2; options.data._ajax_nonce = gen2;
    } else options.data = setNonceQS('', gen2);
  });
})(window, window.jQuery);
JS;
    wp_add_inline_script('cg-core-bundle', $shim, 'before');

    wp_enqueue_script('cg-core-bundle');
    wp_enqueue_style('cg-core-style', $css_url, [], $css_ver);
}

function cg_block_gifts_bundle_any_handle() {
    if (!cg_is_character_generator_page()) { return; }

    static $did = false;
    if ($did) { return; }
    $did = true;

    global $wp_scripts;
    if (!($wp_scripts instanceof WP_Scripts)) { return; }

    $blocked = [];

    foreach (['cg-gifts-bundle','cg-gifts'] as $h) {
        if (wp_script_is($h, 'enqueued') || wp_script_is($h, 'registered')) {
            wp_dequeue_script($h);
            wp_deregister_script($h);
            $blocked[] = $h;
        }
    }

    foreach ($wp_scripts->registered as $handle => $obj) {
        $src = isset($obj->src) ? (string) $obj->src : '';
        if ($src && strpos($src, 'assets/js/dist/gifts.bundle.js') !== false) {
            wp_dequeue_script($handle);
            wp_deregister_script($handle);
            $blocked[] = $handle;
        }
    }

    if (!empty($blocked) && (wp_script_is('cg-core-bundle', 'registered') || wp_script_is('cg-core-bundle', 'enqueued'))) {
        $msg = "[CG] Blocked gifts.bundle.js from handles: " . implode(', ', array_unique($blocked));
        $js = "try{if(!window.CG_GIFTS_BLOCKED){window.CG_GIFTS_BLOCKED=true;console.warn(" . json_encode($msg) . ");}}catch(e){}";
        wp_add_inline_script('cg-core-bundle', $js, 'after');
    }
}

add_action('wp_enqueue_scripts', 'cg_register_enqueue_core_assets', 20);
add_action('wp_enqueue_scripts', 'cg_block_gifts_bundle_any_handle', 999);
