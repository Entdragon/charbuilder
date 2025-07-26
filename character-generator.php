<?php
/**
 * Plugin Name:     Character Generator Dev
 * Plugin URI:      https://yourdomain.com/character-generator-dev
 * Description:     Development version of the Character Generator plugin.
 * Version:         0.6.1-dev
 * Author:          Your Name
 * Author URI:      https://yourdomain.com
 * Text Domain:     character-generator-dev
 */

namespace CG_Dev;

if ( ! defined( 'ABSPATH' ) ) exit;

use WP_Query;
use WP_User;

// Activation Hook
\register_activation_hook( __FILE__, __NAMESPACE__ . '\\activate' );
function activate() {
    global $wpdb;
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    $table   = $wpdb->prefix . 'character_records_dev';
    $charset = $wpdb->get_charset_collate();

    $sql = "
    CREATE TABLE {$table} (
      id           MEDIUMINT(9)  NOT NULL AUTO_INCREMENT,
      user_id      BIGINT(20)    NOT NULL,
      name         VARCHAR(100)  NOT NULL,
      age          VARCHAR(10)   NOT NULL,
      gender       VARCHAR(20)   NOT NULL,
      will         VARCHAR(4)    NOT NULL,
      speed        VARCHAR(4)    NOT NULL,
      body         VARCHAR(4)    NOT NULL,
      mind         VARCHAR(4)    NOT NULL,
      species      VARCHAR(4)    NOT NULL,
      career       VARCHAR(4)    NOT NULL,
      free_gift_1  MEDIUMINT(9),
      free_gift_2  MEDIUMINT(9),
      free_gift_3  MEDIUMINT(9),
      local_area   TEXT,
      language     TEXT,
      skill_marks  TEXT,
      created      DATETIME DEFAULT CURRENT_TIMESTAMP,
      updated      DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (id)
    ) {$charset};
    ";
    \dbDelta( $sql );
}

// Enqueue Scripts
\add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\\enqueue_assets' );
function enqueue_assets() {
    if ( ! \is_user_logged_in() ) return;

    $base_url = \plugin_dir_url( __FILE__ );

    $ver_gifts = file_exists( __DIR__ . '/assets/js/dist/gifts.bundle.js' )
        ? filemtime( __DIR__ . '/assets/js/dist/gifts.bundle.js' )
        : time();

    $ver_core = file_exists( __DIR__ . '/assets/js/dist/core.bundle.js' )
        ? filemtime( __DIR__ . '/assets/js/dist/core.bundle.js' )
        : time();

    $ver_css = file_exists( __DIR__ . '/assets/css/dist/core.css' )
        ? filemtime( __DIR__ . '/assets/css/dist/core.css' )
        : time();

    \wp_enqueue_style( 'cgdev-core-style', $base_url . 'assets/css/dist/core.css', array(), $ver_css );

    \wp_enqueue_script( 'cgdev-gifts', $base_url . 'assets/js/dist/gifts.bundle.js', array( 'jquery' ), $ver_gifts, true );
    \wp_enqueue_script( 'cgdev-core', $base_url . 'assets/js/dist/core.bundle.js', array( 'jquery', 'cgdev-gifts' ), $ver_core, true );

    \wp_localize_script( 'cgdev-core', 'CGDEV_Ajax', [
        'ajax_url' => \admin_url( 'admin-ajax.php' ),
        'nonce'    => \wp_create_nonce( 'cgdev_nonce' ),
    ] );

    global $wpdb;
    $ct_prefix = 'DcVnchxg4_customtables_table_';

    $skills = $wpdb->get_results("SELECT ct_id AS id, ct_skill_name AS name FROM {$ct_prefix}skills ORDER BY ct_skill_name ASC", ARRAY_A);
    $species = $wpdb->get_results("SELECT ct_id AS id, ct_species_name AS name FROM {$ct_prefix}species ORDER BY ct_species_name ASC", ARRAY_A);
    $careers = $wpdb->get_results("SELECT ct_id AS id, ct_career_name AS name FROM {$ct_prefix}careers ORDER BY ct_career_name ASC", ARRAY_A);

    \wp_localize_script( 'cgdev-core', 'CGDEV_SKILLS_LIST', $skills );
    \wp_localize_script( 'cgdev-core', 'CGDEV_SPECIES_LIST', $species );
    \wp_localize_script( 'cgdev-core', 'CGDEV_CAREERS_LIST', $careers );
}

// You can optionally include other logic
require_once __DIR__ . '/includes/index.php';
require_once __DIR__ . '/includes/shortcode-ui.php';
