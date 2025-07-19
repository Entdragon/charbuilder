<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'wp_ajax_cg_get_species_list',       'cg_get_species_list' );
add_action( 'wp_ajax_nopriv_cg_get_species_list','cg_get_species_list' );
function cg_get_species_list() {
    check_ajax_referer( 'cg_nonce', 'security' );
    global $wpdb;
    $table = $wpdb->prefix . 'customtables_table_species';

    $rows = $wpdb->get_results(
        "SELECT
           ct_id             AS id,
           ct_species_name   AS name
         FROM {$table}
         ORDER BY ct_species_name ASC",
        ARRAY_A
    );

    if ( $rows === null ) {
        wp_send_json_error( 'Unable to fetch species list.' );
    }
    wp_send_json_success( $rows );
}


add_action( 'wp_ajax_cg_get_species_profile', 'cg_get_species_profile' );
add_action( 'wp_ajax_nopriv_cg_get_species_profile', 'cg_get_species_profile' );
function cg_get_species_profile() {
    check_ajax_referer( 'cg_nonce', 'security' );

    $species_id = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;
    if ( $species_id <= 0 ) {
        wp_send_json_error( 'Invalid species ID.' );
    }

    global $wpdb;
    $species_tbl = $wpdb->prefix . 'customtables_table_species';
    $gifts_tbl   = $wpdb->prefix . 'customtables_table_gifts';
    $habitat_tbl = $wpdb->prefix . 'customtables_table_habitat';
    $diet_tbl    = $wpdb->prefix . 'customtables_table_diet';
    $cycle_tbl   = $wpdb->prefix . 'customtables_table_cycle';
    $senses_tbl  = $wpdb->prefix . 'customtables_table_senses';
    $weapons_tbl = $wpdb->prefix . 'customtables_table_weapons';

    // Pull in the speciesName so the Skills tab can show it
    $sql = "
      SELECT
        s.ct_species_name        AS speciesName,

        s.ct_species_gift_one    AS gift_id_1,
        g1.ct_gifts_name         AS gift_1,
        g1.ct_gifts_manifold     AS manifold_1,

        s.ct_species_gift_two    AS gift_id_2,
        g2.ct_gifts_name         AS gift_2,
        g2.ct_gifts_manifold     AS manifold_2,

        s.ct_species_gift_three  AS gift_id_3,
        g3.ct_gifts_name         AS gift_3,
        g3.ct_gifts_manifold     AS manifold_3,

        s.ct_species_skill_one   AS skill_one,
        s.ct_species_skill_two   AS skill_two,
        s.ct_species_skill_three AS skill_three,

        h.ct_habitat_name        AS habitat,
        d.ct_diet_name           AS diet,
        c.ct_cycle_name          AS cycle,

        s1.ct_senses_name        AS sense_1,
        s2.ct_senses_name        AS sense_2,
        s3.ct_senses_name        AS sense_3,

        w1.ct_weapons_name       AS weapon_1,
        w2.ct_weapons_name       AS weapon_2,
        w3.ct_weapons_name       AS weapon_3

      FROM {$species_tbl} s
      LEFT JOIN {$gifts_tbl}   g1 ON s.ct_species_gift_one   = g1.ct_id
      LEFT JOIN {$gifts_tbl}   g2 ON s.ct_species_gift_two   = g2.ct_id
      LEFT JOIN {$gifts_tbl}   g3 ON s.ct_species_gift_three = g3.ct_id

      LEFT JOIN {$habitat_tbl} h  ON s.ct_species_habitat  = h.ct_id
      LEFT JOIN {$diet_tbl}    d  ON s.ct_species_diet     = d.ct_id
      LEFT JOIN {$cycle_tbl}   c  ON s.ct_species_cycle    = c.ct_id

      LEFT JOIN {$senses_tbl}  s1 ON s.ct_species_senses_one   = s1.ct_id
      LEFT JOIN {$senses_tbl}  s2 ON s.ct_species_senses_two   = s2.ct_id
      LEFT JOIN {$senses_tbl}  s3 ON s.ct_species_senses_three = s3.ct_id

      LEFT JOIN {$weapons_tbl} w1 ON s.ct_species_weapon_one   = w1.ct_id
      LEFT JOIN {$weapons_tbl} w2 ON s.ct_species_weapon_two   = w2.ct_id
      LEFT JOIN {$weapons_tbl} w3 ON s.ct_species_weapon_three = w3.ct_id

      WHERE s.ct_id = %d
    ";

    $profile = $wpdb->get_row( $wpdb->prepare( $sql, $species_id ), ARRAY_A );
    if ( ! $profile ) {
        wp_send_json_error( 'Species profile not found.' );
    }

    wp_send_json_success( $profile );
}
