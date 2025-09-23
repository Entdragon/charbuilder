<?php
if ( ! defined( 'ABSPATH' ) ) exit;

register_activation_hook( __FILE__, 'cg_activate' );
function cg_activate() {
    global $wpdb;
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    $table   = $wpdb->prefix . 'character_records';
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
      skill_marks  TEXT, -- JSON string of skill marks
      created      DATETIME DEFAULT CURRENT_TIMESTAMP,
      updated      DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (id)
    ) {$charset};
    ";
    dbDelta( $sql );
}
