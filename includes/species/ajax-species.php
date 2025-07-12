<?php
if ( ! defined( 'ABSPATH' ) ) exit;

add_action('wp_ajax_cg_get_species_list', 'cg_get_species_list');
function cg_get_species_list() {
    global $wpdb;
    $rows = $wpdb->get_results("
        SELECT id, ct_species_name 
        FROM {$wpdb->prefix}customtables_table_species 
        ORDER BY ct_species_name ASC
    ", ARRAY_A);
    wp_send_json_success($rows);
}

add_action('wp_ajax_cg_get_species_profile', 'cg_get_species_profile');
function cg_get_species_profile() {
    $id = intval($_POST['id']);
    global $wpdb;

    $species = $wpdb->prefix . 'customtables_table_species';
    $gifts   = $wpdb->prefix . 'customtables_table_gifts';
    $habitat = $wpdb->prefix . 'customtables_table_habitat';
    $diet    = $wpdb->prefix . 'customtables_table_diet';
    $cycle   = $wpdb->prefix . 'customtables_table_cycle';
    $senses  = $wpdb->prefix . 'customtables_table_senses';
    $weapons = $wpdb->prefix . 'customtables_table_weapons';

    $row = $wpdb->get_row($wpdb->prepare("
        SELECT 
            s.ct_species_gift_one   AS gift_id_1,
            s.ct_species_gift_two   AS gift_id_2,
            s.ct_species_gift_three AS gift_id_3,
            g1.ct_gifts_name        AS gift_1,
            g2.ct_gifts_name        AS gift_2,
            g3.ct_gifts_name        AS gift_3,
            s.ct_species_gift_one_choice   AS bonus_1,
            s.ct_species_gift_two_choice   AS bonus_2,
            s.ct_species_gift_three_choice AS bonus_3,
            s.ct_species_skill_one         AS skill_one,
            s.ct_species_skill_two         AS skill_two,
            s.ct_species_skill_three       AS skill_three,
            h.ct_habitat_name              AS habitat,
            d.ct_diet_name                 AS diet,
            c.ct_cycle_name                AS cycle,
            s1.ct_senses_name              AS sense_1,
            s2.ct_senses_name              AS sense_2,
            s3.ct_senses_name              AS sense_3,
            w1.ct_weapons_name             AS weapon_1,
            w2.ct_weapons_name             AS weapon_2,
            w3.ct_weapons_name             AS weapon_3
        FROM {$species} s
        LEFT JOIN {$gifts}   g1 ON s.ct_species_gift_one   = g1.id
        LEFT JOIN {$gifts}   g2 ON s.ct_species_gift_two   = g2.id
        LEFT JOIN {$gifts}   g3 ON s.ct_species_gift_three = g3.id
        LEFT JOIN {$habitat} h  ON s.ct_species_habitat    = h.id
        LEFT JOIN {$diet}    d  ON s.ct_species_diet       = d.id
        LEFT JOIN {$cycle}   c  ON s.ct_species_cycle      = c.id
        LEFT JOIN {$senses}  s1 ON s.ct_species_senses_one   = s1.id
        LEFT JOIN {$senses}  s2 ON s.ct_species_senses_two   = s2.id
        LEFT JOIN {$senses}  s3 ON s.ct_species_senses_three = s3.id
        LEFT JOIN {$weapons} w1 ON s.ct_species_weapon_one   = w1.id
        LEFT JOIN {$weapons} w2 ON s.ct_species_weapon_two   = w2.id
        LEFT JOIN {$weapons} w3 ON s.ct_species_weapon_three = w3.id
        WHERE s.id = %d
    ", $id), ARRAY_A);

    wp_send_json_success($row);
}
