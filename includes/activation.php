<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Schema installer / upgrader for the character_records table.
 *
 * Why this exists:
 * - The activation hook MUST be registered from the main plugin file.
 * - We still define cg_activate() here, but character-generator.php registers the hook correctly.
 * - We also run a lightweight schema-version check on load to upgrade older installs that never ran activation.
 */

if ( ! function_exists( 'cg_schema_version' ) ) {
    function cg_schema_version() {
        // bump this when table schema changes
        return '20260104_1';
    }
}

if ( ! function_exists( 'cg_install_character_records_table' ) ) {
    function cg_install_character_records_table() {
        global $wpdb;

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $table   = $wpdb->prefix . 'character_records';
        $charset = $wpdb->get_charset_collate();

        // This SQL intentionally matches the current legacy table plus additions.
        // dbDelta() will CREATE or ALTER to add missing columns/indexes without dropping existing data.
        $sql = "
        CREATE TABLE {$table} (
          id           MEDIUMINT(9)  NOT NULL AUTO_INCREMENT,
          user_id      BIGINT(20)    NOT NULL,

          name         VARCHAR(100)  NOT NULL,
          player_name  VARCHAR(255)  NULL,

          age          VARCHAR(10)   NOT NULL,
          gender       VARCHAR(20)   NOT NULL,

          will         VARCHAR(4)    NOT NULL,
          speed        VARCHAR(4)    NOT NULL,
          body         VARCHAR(4)    NOT NULL,
          mind         VARCHAR(4)    NOT NULL,

          species      VARCHAR(4)    NOT NULL,
          career       VARCHAR(4)    NOT NULL,

          free_gift_1  MEDIUMINT(9)  NULL,
          free_gift_2  MEDIUMINT(9)  NULL,
          free_gift_3  MEDIUMINT(9)  NULL,

          career_gift_replacements TEXT NULL,

          local_area   TEXT NULL,
          language     TEXT NULL,

          skill_marks  TEXT NULL,

          created      DATETIME DEFAULT CURRENT_TIMESTAMP,
          updated      DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

          trait_species VARCHAR(10) NULL,
          trait_career  VARCHAR(10) NULL,

          description  LONGTEXT NOT NULL,
          backstory    LONGTEXT NOT NULL,

          motto        TEXT NULL,
          goal1        TEXT NULL,
          goal2        TEXT NULL,
          goal3        TEXT NULL,

          extra_career_1       VARCHAR(4)  NULL,
          extra_trait_career_1 VARCHAR(10) NULL,
          extra_career_2       VARCHAR(4)  NULL,
          extra_trait_career_2 VARCHAR(10) NULL,

          -- NEW (Jan 2026): persist which career receives the Increased Trait: Career boost ('main' or careerId)
          increased_trait_career_target VARCHAR(20) NULL,

          PRIMARY KEY (id),
          KEY user_id (user_id)
        ) {$charset};
        ";

        dbDelta( $sql );

        update_option( 'cg_schema_version', cg_schema_version() );
    }
}

if ( ! function_exists( 'cg_activate' ) ) {
    function cg_activate() {
        cg_install_character_records_table();
    }
}

if ( ! function_exists( 'cg_maybe_upgrade_schema' ) ) {
    function cg_maybe_upgrade_schema() {
        $want = cg_schema_version();
        $have = get_option( 'cg_schema_version', '' );

        if ( $have !== $want ) {
            cg_install_character_records_table();
        }
    }
}

// Ensure upgrades happen even if the old activation hook never fired.
add_action( 'plugins_loaded', 'cg_maybe_upgrade_schema', 5 );
