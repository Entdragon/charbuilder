// <?php
// if (!defined('ABSPATH')) { exit; }
// 
// function cg_file_version($rel_path) {
//   $abs = trailingslashit( plugin_dir_path(dirname(__FILE__)) ) . ltrim($rel_path, '/');
//   if (file_exists($abs)) { $ts = @filemtime($abs); if ($ts) return $ts; }
//   return '1.0.0';
// }
// 
// function cg_enqueue_assets() {
//   if (is_admin()) return;
//   if (!is_page_template('page-character-generator.php')) return;
// 
//   $base_url = plugin_dir_url(dirname(__FILE__)) . 'assets/';
//   $core_js  = 'js/dist/core.bundle.js';
//   $gifts_js = 'js/dist/gifts.bundle.js';
// 
//   wp_register_script('cg-core-bundle',  $base_url.$core_js,  array('jquery'), cg_file_version('assets/'.$core_js),  true);
//   wp_register_script('cg-gifts-bundle', $base_url.$gifts_js, array('jquery','cg-core-bundle'), cg_file_version('assets/'.$gifts_js), true);
// 
//   $guard = <<<JS
//     (function(){
//       if (window.__CG_APP_INITIALIZED__) { try{console.warn('[CG] init suppressed (already initialized)')}catch(e){}; return; }
//       window.__CG_APP_INITIALIZED__ = true;
//     })();
//   JS;
//   wp_add_inline_script('cg-core-bundle', $guard, 'before');
// 
//   $css_rel = 'css/dist/character-builder.css';
//   $css_abs = trailingslashit( plugin_dir_path(dirname(__FILE__)) ).'assets/'.$css_rel;
//   if (file_exists($css_abs)) {
//     wp_enqueue_style('cg-styles', $base_url.$css_rel, array(), cg_file_version('assets/'.$css_rel));
//   }
// 
//   wp_enqueue_script('cg-core-bundle');
//   wp_enqueue_script('cg-gifts-bundle');
// }
// add_action('wp_enqueue_scripts', 'cg_enqueue_assets', 20);
