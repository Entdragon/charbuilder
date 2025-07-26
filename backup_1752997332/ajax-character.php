<?php

namespace CharacterGeneratorDev {

/**
 * AJAX handlers for saving and loading character records.
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Register AJAX endpoints.
 */
add_action( 'wp_ajax_cg_load_characters',        'cg_load_characters' );
add_action( 'wp_ajax_nopriv_cg_load_characters', 'cg_load_characters' );

add_action( 'wp_ajax_cg_get_character',         'cg_get_character' );
add_action( 'wp_ajax_nopriv_cg_get_character',  'cg_get_character' );

add_action( 'wp_ajax_cg_save_character',        'cg_save_character' );
add_action( 'wp_ajax_nopriv_cg_save_character', 'cg_save_character' );


/**
 * 1) Return only ID + name for the Load splash.
 */
function cg_load_characters() {
    check_ajax_referer( 'cg_nonce', 'security' );
    if ( ! is_user_logged_in() ) {
        wp_send_json_error( 'Not authenticated.' );
    }

    global $wpdb;
    $tbl  = $wpdb->prefix . 'character_records';
    $rows = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT id, name
             FROM {$tbl}
             WHERE user_id = %d
             ORDER BY updated DESC",
            get_current_user_id()
        ),
        ARRAY_A
    );

    if ( $rows === null ) {
        wp_send_json_error( 'Unable to load characters.' );
    }
    wp_send_json_success( $rows );
}

/**
 * 2) Return a full character record for editing.
 */
function cg_get_character() {
    check_ajax_referer( 'cg_nonce', 'security' );
    if ( ! is_user_logged_in() ) {
        wp_send_json_error( 'Not authenticated.' );
    }

    $id = intval( $_POST['id'] ?? 0 );
    if ( $id <= 0 ) {
        wp_send_json_error( 'Invalid character ID.' );
    }

    global $wpdb;
    $tbl  = $wpdb->prefix . 'character_records';
    $row  = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM {$tbl}
             WHERE id = %d
               AND user_id = %d
             LIMIT 1",
            $id,
            get_current_user_id()
        ),
        ARRAY_A
    );

    if ( ! $row ) {
        wp_send_json_error( 'Character not found.' );
    }

    //
    // 1) Ensure all six trait keys are present
    //
    foreach ( [ 'will', 'speed', 'body', 'mind', 'trait_species', 'trait_career' ] as $trait ) {
        $row[ $trait ] = isset( $row[ $trait ] ) 
            ? (string) $row[ $trait ] 
            : '';
    }

    //
    // 2) Skill marks → skillMarks
    //
    $marks = maybe_unserialize( $row['skill_marks'] ?? '' );
    $row['skill_marks'] = is_array( $marks ) ? $marks : [];
    $row['skillMarks']  = $row['skill_marks'];

    //
    // 3) Free gifts → array + individual free-choice selects
    //
    $free = [
        intval( $row['free_gift_1'] ?? 0 ),
        intval( $row['free_gift_2'] ?? 0 ),
        intval( $row['free_gift_3'] ?? 0 ),
    ];
    $row['free_gifts']    = $free;                // for PHP save consistency
    $row['freeGifts']     = $free;                // alternate JS key if you use it
    $row['free-choice-0'] = (string) $free[0];
    $row['free-choice-1'] = (string) $free[1];
    $row['free-choice-2'] = (string) $free[2];

    //
    // 4) Species & Career IDs
    //
    $row['species_id'] = (string) ( $row['species'] ?? '' );
    $row['career_id']  = (string) ( $row['career']  ?? '' );

    // Mirror into the flat keys your form-builder expects
    $row['species'] = $row['species_id'];
    $row['career']  = $row['career_id'];

    // And supply the nested profile object for the Profile tab
    $row['profile'] = [
        'species' => $row['species_id'],
        'career'  => $row['career_id'],
    ];

    wp_send_json_success( $row );
}


/**
 * 3) Save (insert or update) a full character record.
 *    (unchanged)
 */
function cg_save_character() {
    check_ajax_referer( 'cg_nonce', 'security' );
    if ( ! is_user_logged_in() ) {
        wp_send_json_error( 'Not authenticated.' );
    }

    $data = $_POST['character'] ?? [];
    if ( ! is_array( $data ) || empty( $data['name'] ) ) {
        wp_send_json_error( 'Invalid payload.' );
    }

    $gifts       = is_array( $data['free_gifts'] ) ? $data['free_gifts'] : [0,0,0];
    $skill_marks = is_array( $data['skillMarks'] )   ? $data['skillMarks']   : [];

    $fields = [
        'name'            => sanitize_text_field( $data['name'] ),
        'player_name'     => sanitize_text_field( $data['player_name'] ?? '' ),
        'age'             => sanitize_text_field( $data['age'] ?? '' ),
        'gender'          => sanitize_text_field( $data['gender'] ?? '' ),
        'will'            => sanitize_text_field( $data['will'] ?? '' ),
        'speed'           => sanitize_text_field( $data['speed'] ?? '' ),
        'body'            => sanitize_text_field( $data['body'] ?? '' ),
        'mind'            => sanitize_text_field( $data['mind'] ?? '' ),
        'trait_species'   => sanitize_text_field( $data['trait_species'] ?? '' ),
        'trait_career'    => sanitize_text_field( $data['trait_career'] ?? '' ),
        'species'         => sanitize_text_field( $data['species_id'] ?? '' ),
        'career'          => sanitize_text_field( $data['career_id'] ?? '' ),
        'free_gift_1'     => intval( $gifts[0] ?? 0 ),
        'free_gift_2'     => intval( $gifts[1] ?? 0 ),
        'free_gift_3'     => intval( $gifts[2] ?? 0 ),
        'description'     => sanitize_textarea_field( $data['description'] ?? '' ),
        'backstory'       => sanitize_textarea_field( $data['backstory'] ?? '' ),
        'motto'           => sanitize_text_field( $data['motto'] ?? '' ),
        'goal1'           => sanitize_text_field( $data['goal1'] ?? '' ),
        'goal2'           => sanitize_text_field( $data['goal2'] ?? '' ),
        'goal3'           => sanitize_text_field( $data['goal3'] ?? '' ),
        'skill_marks'     => maybe_serialize( $skill_marks ),
        'local_area'      => sanitize_text_field( $data['local_area'] ?? '' ),
        'language'        => sanitize_text_field( $data['language'] ?? '' ),
    ];

    global $wpdb;
    $tbl  = $wpdb->prefix . 'character_records';
    $user = get_current_user_id();

    if ( ! empty( $data['id'] ) ) {
        $id = intval( $data['id'] );
        $wpdb->update( $tbl, $fields, [ 'id' => $id, 'user_id' => $user ] );
    } else {
        $fields['user_id'] = $user;
        $wpdb->insert( $tbl, $fields );
        $id = $wpdb->insert_id;
    }

    wp_send_json_success( [ 'id' => $id ] );
}

} // namespace CharacterGeneratorDev
