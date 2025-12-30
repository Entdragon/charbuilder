<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * AJAX handlers for saving and loading character records.
 *
 * NOTE: Hook registration is owned by includes/character/index.php.
 */
require_once __DIR__ . '/../ajax-nonce.php';

function cg_char_ajax_require_post() {
    $method = $_SERVER['REQUEST_METHOD'] ?? '';
    if ( strtoupper( $method ) !== 'POST' ) {
        wp_send_json_error( [ 'message' => 'Invalid request method.' ], 405 );
    }
}

function cg_char_ajax_require_auth() {
    if ( ! is_user_logged_in() ) {
        wp_send_json_error( [ 'message' => 'Not authenticated.' ], 401 );
    }
    // Keep capability light so subscribers work.
    if ( ! current_user_can( 'read' ) ) {
        wp_send_json_error( [ 'message' => 'Forbidden.' ], 403 );
    }
}

function cg_char_sanitize_free_gifts( $raw ) {
    $raw = is_array( $raw ) ? array_values( $raw ) : [];
    $out = [ 0, 0, 0 ];
    for ( $i = 0; $i < 3; $i++ ) {
        $out[$i] = absint( $raw[$i] ?? 0 );
    }
    return $out;
}

function cg_char_sanitize_skill_marks( $raw ) {
    if ( ! is_array( $raw ) ) return [];
    $out = [];
    foreach ( $raw as $k => $v ) {
        // Skill ids are numeric-ish; keep only digits.
        if ( is_int( $k ) ) $key = (string) absint( $k );
        else $key = preg_replace( '/[^0-9]/', '', (string) $k );

        if ( $key === '' ) continue;

        // Marks should be integers; clamp to a sane range.
        $val = is_numeric( $v ) ? (int) $v : 0;
        if ( $val < 0 ) $val = 0;
        if ( $val > 20 ) $val = 20;

        $out[$key] = $val;
    }
    return $out;
}

function cg_load_characters() {
    cg_char_ajax_require_post();
    cg_ajax_require_nonce_multi();
    cg_char_ajax_require_auth();

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
        wp_send_json_error( [ 'message' => 'Unable to load characters.' ], 500 );
    }

    wp_send_json_success( $rows );
}

function cg_get_character() {
    cg_char_ajax_require_post();
    cg_ajax_require_nonce_multi();
    cg_char_ajax_require_auth();

    $id = absint( $_POST['id'] ?? 0 );
    if ( $id <= 0 ) {
        wp_send_json_error( [ 'message' => 'Invalid character ID.' ], 400 );
    }

    global $wpdb;
    $tbl  = $wpdb->prefix . 'character_records';

    $row = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT *
             FROM {$tbl}
             WHERE id = %d
               AND user_id = %d
             LIMIT 1",
            $id,
            get_current_user_id()
        ),
        ARRAY_A
    );

    if ( ! $row ) {
        wp_send_json_error( [ 'message' => 'Character not found.' ], 404 );
    }

    foreach ( [ 'will', 'speed', 'body', 'mind', 'trait_species', 'trait_career' ] as $trait ) {
        $row[ $trait ] = isset( $row[ $trait ] ) ? (string) $row[ $trait ] : '';
    }

    $marks = maybe_unserialize( $row['skill_marks'] ?? '' );
    $row['skill_marks'] = is_array( $marks ) ? $marks : [];
    $row['skillMarks']  = $row['skill_marks'];

    $free = [
        absint( $row['free_gift_1'] ?? 0 ),
        absint( $row['free_gift_2'] ?? 0 ),
        absint( $row['free_gift_3'] ?? 0 ),
    ];

    $row['free_gifts']    = $free;
    $row['freeGifts']     = $free;
    $row['free-choice-0'] = (string) $free[0];
    $row['free-choice-1'] = (string) $free[1];
    $row['free-choice-2'] = (string) $free[2];

    $row['species_id'] = (string) absint( $row['species'] ?? 0 );
    $row['career_id']  = (string) absint( $row['career']  ?? 0 );

    $row['species'] = $row['species_id'];
    $row['career']  = $row['career_id'];

    $row['profile'] = [
        'species' => $row['species_id'],
        'career'  => $row['career_id'],
    ];

    wp_send_json_success( $row );
}

function cg_save_character() {
    cg_char_ajax_require_post();
    cg_ajax_require_nonce_multi();
    cg_char_ajax_require_auth();

    $data = $_POST['character'] ?? [];
    if ( ! is_array( $data ) ) {
        wp_send_json_error( [ 'message' => 'Invalid payload.' ], 400 );
    }

    $name = sanitize_text_field( $data['name'] ?? '' );
    if ( $name === '' ) {
        wp_send_json_error( [ 'message' => 'Name is required.' ], 400 );
    }

    $gifts       = cg_char_sanitize_free_gifts( $data['free_gifts'] ?? [] );
    $skill_marks = cg_char_sanitize_skill_marks( $data['skillMarks'] ?? [] );

    $fields = [
        'name'          => $name,
        'player_name'   => sanitize_text_field( $data['player_name'] ?? '' ),
        'age'           => sanitize_text_field( $data['age'] ?? '' ),
        'gender'        => sanitize_text_field( $data['gender'] ?? '' ),

        'will'          => sanitize_text_field( $data['will'] ?? '' ),
        'speed'         => sanitize_text_field( $data['speed'] ?? '' ),
        'body'          => sanitize_text_field( $data['body'] ?? '' ),
        'mind'          => sanitize_text_field( $data['mind'] ?? '' ),

        'trait_species' => sanitize_text_field( $data['trait_species'] ?? '' ),
        'trait_career'  => sanitize_text_field( $data['trait_career'] ?? '' ),

        // ids should be ints in storage
        'species'       => absint( $data['species_id'] ?? 0 ),
        'career'        => absint( $data['career_id'] ?? 0 ),

        'free_gift_1'   => $gifts[0],
        'free_gift_2'   => $gifts[1],
        'free_gift_3'   => $gifts[2],

        'description'   => sanitize_textarea_field( $data['description'] ?? '' ),
        'backstory'     => sanitize_textarea_field( $data['backstory'] ?? '' ),
        'motto'         => sanitize_text_field( $data['motto'] ?? '' ),
        'goal1'         => sanitize_text_field( $data['goal1'] ?? '' ),
        'goal2'         => sanitize_text_field( $data['goal2'] ?? '' ),
        'goal3'         => sanitize_text_field( $data['goal3'] ?? '' ),

        'skill_marks'   => maybe_serialize( $skill_marks ),

        'local_area'    => sanitize_text_field( $data['local_area'] ?? '' ),
        'language'      => sanitize_text_field( $data['language'] ?? '' ),
    ];

    global $wpdb;
    $tbl  = $wpdb->prefix . 'character_records';
    $user = get_current_user_id();

    $id = absint( $data['id'] ?? 0 );

    if ( $id > 0 ) {
        // Confirm ownership first (so "no rows affected" doesn't hide "not yours / not found")
        $exists = (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(1) FROM {$tbl} WHERE id = %d AND user_id = %d",
                $id,
                $user
            )
        );

        if ( $exists !== 1 ) {
            wp_send_json_error( [ 'message' => 'Character not found.' ], 404 );
        }

        $ok = $wpdb->update( $tbl, $fields, [ 'id' => $id, 'user_id' => $user ] );
        if ( $ok === false ) {
            wp_send_json_error( [ 'message' => 'Database error while saving.' ], 500 );
        }
    } else {
        $fields['user_id'] = $user;
        $ok = $wpdb->insert( $tbl, $fields );
        if ( ! $ok ) {
            wp_send_json_error( [ 'message' => 'Database error while saving.' ], 500 );
        }
        $id = (int) $wpdb->insert_id;
    }

    wp_send_json_success( [ 'id' => (string) $id ] );
}
