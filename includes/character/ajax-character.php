<?php
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'wp_ajax_cg_load_characters', 'cg_load_characters' );
function cg_load_characters() {
    check_ajax_referer( 'cg_nonce', 'security' );
    global $wpdb;
    $tbl = $wpdb->prefix . 'character_records';
    $rows = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT id, name FROM {$tbl} WHERE user_id = %d ORDER BY updated DESC",
            get_current_user_id()
        ), ARRAY_A
    );
    wp_send_json_success( $rows );
}

add_action( 'wp_ajax_cg_get_character', 'cg_get_character' );
function cg_get_character() {
    check_ajax_referer( 'cg_nonce', 'security' );
    global $wpdb;

    $id  = intval( $_POST['id'] );
    $uid = get_current_user_id();
    $tbl = $wpdb->prefix . 'character_records';

    $row = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM {$tbl} WHERE id = %d AND user_id = %d LIMIT 1",
            $id, $uid
        ), ARRAY_A
    );

    if ( ! $row ) wp_send_json_error('Character not found.');

    // Deserialize skill marks
    if ( isset($row['skill_marks']) ) {
        $row['skill_marks'] = maybe_unserialize( $row['skill_marks'] );
    }

    wp_send_json_success( $row );
}


add_action( 'wp_ajax_cg_save_character', 'cg_save_character' );
function cg_save_character() {
    check_ajax_referer( 'cg_nonce', 'security' );
    global $wpdb;

    $tbl  = $wpdb->prefix . 'character_records';
    $user = get_current_user_id();
    $data = $_POST['character'];

    $fields = [
        'name'        => sanitize_text_field( $data['name'] ),
        'age'         => sanitize_text_field( $data['age'] ),
        'gender'      => sanitize_text_field( $data['gender'] ),
        'description' => sanitize_text_field( $data['description'] ?? '' ),
        'motto'       => sanitize_text_field( $data['motto'] ?? '' ),
        'goal1'       => sanitize_text_field( $data['goal1'] ?? '' ),
        'goal2'       => sanitize_text_field( $data['goal2'] ?? '' ),
        'goal3'       => sanitize_text_field( $data['goal3'] ?? '' ),
        'will'        => sanitize_text_field( $data['will'] ),
        'speed'       => sanitize_text_field( $data['speed'] ),
        'body'        => sanitize_text_field( $data['body'] ),
        'mind'        => sanitize_text_field( $data['mind'] ),
        'trait_species'=> sanitize_text_field( $data['trait_species'] ?? '' ),
        'trait_career' => sanitize_text_field( $data['trait_career'] ?? '' ),
        'species'     => sanitize_text_field( $data['species'] ),
        'career'      => sanitize_text_field( $data['career'] ),
        'free_gift_1' => intval( $data['free_gifts'][0] ?? 0 ),
        'free_gift_2' => intval( $data['free_gifts'][1] ?? 0 ),
        'free_gift_3' => intval( $data['free_gifts'][2] ?? 0 ),
        'local_area'  => sanitize_text_field( $data['local_area'] ?? '' ),
        'language'    => sanitize_text_field( $data['language'] ?? '' ),
        'skill_marks' => maybe_serialize( $data['skill_marks'] ?? [] ),
    ];

    if ( ! empty( $data['id'] ) ) {
        $wpdb->update( $tbl, $fields, [ 'id' => intval($data['id']), 'user_id' => $user ] );
        $id = intval( $data['id'] );
    } else {
        $fields['user_id'] = $user;
        $wpdb->insert( $tbl, $fields );
        $id = $wpdb->insert_id;
    }

    wp_send_json_success([ 'id' => $id ]);
}

