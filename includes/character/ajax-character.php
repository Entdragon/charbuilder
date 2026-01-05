<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * AJAX handlers for saving and loading character records.
 *
 * NOTE: Hook registration is owned by includes/character/index.php.
 *
 * Shared helpers live in includes/ajax-nonce.php:
 * - cg_ajax_require_nonce_multi()
 * - cg_ajax_require_post()
 */
require_once __DIR__ . '/../ajax-nonce.php';

if ( ! function_exists( 'cg_char_ajax_require_auth' ) ) {
    function cg_char_ajax_require_auth() {
        if ( ! is_user_logged_in() ) {
            wp_send_json_error( [ 'message' => 'Not logged in.' ], 401 );
        }
    }
}

/**
 * Cache table columns so we can be schema-adaptive.
 */
if ( ! function_exists( 'cg_char_table_columns' ) ) {
    function cg_char_table_columns( $table_name ) {
        static $cache = [];
        $key = (string) $table_name;

        if ( isset( $cache[ $key ] ) ) {
            return $cache[ $key ];
        }

        global $wpdb;
        $cols = $wpdb->get_col( "SHOW COLUMNS FROM `{$table_name}`", 0 );
        $cols = is_array( $cols ) ? array_map( 'strval', $cols ) : [];
        $cache[ $key ] = $cols;

        return $cache[ $key ];
    }
}

if ( ! function_exists( 'cg_char_has_col' ) ) {
    function cg_char_has_col( $table_name, $col ) {
        $cols = cg_char_table_columns( $table_name );
        return in_array( (string) $col, $cols, true );
    }
}

if ( ! function_exists( 'cg_char_sanitize_free_gifts' ) ) {
    function cg_char_sanitize_free_gifts( $raw ) {
        $raw = is_array( $raw ) ? array_values( $raw ) : [];
        $out = [ 0, 0, 0 ];
        for ( $i = 0; $i < 3; $i++ ) {
            $out[$i] = absint( $raw[$i] ?? 0 );
        }
        return $out;
    }
}

if ( ! function_exists( 'cg_char_sanitize_skill_marks' ) ) {
    function cg_char_sanitize_skill_marks( $raw ) {
        if ( ! is_array( $raw ) ) { return []; }
        $out = [];

        foreach ( $raw as $k => $v ) {
            $k = sanitize_text_field( (string) $k );
            if ( $k === '' ) { continue; }

            $n = absint( $v );
            if ( $n <= 0 ) { continue; }

            $out[$k] = $n;
        }

        return $out;
    }
}

if ( ! function_exists( 'cg_char_sanitize_gift_replacements' ) ) {
    function cg_char_sanitize_gift_replacements( $raw ) {
        if ( is_string( $raw ) ) {
            $decoded = json_decode( $raw, true );
            if ( is_array( $decoded ) ) {
                $raw = $decoded;
            } else {
                $raw = [];
            }
        }

        if ( ! is_array( $raw ) ) { return []; }

        $out = [];
        foreach ( $raw as $slot => $gift_id ) {
            $slot = sanitize_text_field( (string) $slot );
            if ( $slot === '' ) { continue; }

            $gid = absint( $gift_id );
            if ( $gid <= 0 ) { continue; }

            $out[$slot] = $gid;
        }

        return $out;
    }
}

/**
 * Normalize a DB row so JS always sees the modern keys, even if DB is legacy.
 */
if ( ! function_exists( 'cg_char_normalize_row_for_js' ) ) {
    function cg_char_normalize_row_for_js( $row ) {
        $row = is_array( $row ) ? $row : [];

        // Always string ids for JS comparisons
        if ( isset( $row['id'] ) ) {
            $row['id'] = (string) absint( $row['id'] );
        }

        // Map species/career id keys
        if ( ! array_key_exists( 'species_id', $row ) ) {
            $row['species_id'] = (string) absint( $row['species'] ?? 0 );
        } else {
            $row['species_id'] = (string) absint( $row['species_id'] ?? 0 );
        }

        if ( ! array_key_exists( 'career_id', $row ) ) {
            $row['career_id'] = (string) absint( $row['career'] ?? 0 );
        } else {
            $row['career_id'] = (string) absint( $row['career_id'] ?? 0 );
        }

        // Extras
        if ( array_key_exists( 'extra_career_1', $row ) ) {
            $row['extra_career_1'] = (string) absint( $row['extra_career_1'] ?? 0 );
        }
        if ( array_key_exists( 'extra_career_2', $row ) ) {
            $row['extra_career_2'] = (string) absint( $row['extra_career_2'] ?? 0 );
        }

        // Increased Trait: Career target (always present for JS)
        if ( ! array_key_exists( 'increased_trait_career_target', $row ) ) {
            $row['increased_trait_career_target'] = 'main';
        } else {
            $v = sanitize_text_field( (string) ( $row['increased_trait_career_target'] ?? '' ) );
            $v = trim( $v );
            if ( $v === '' ) { $v = 'main'; }
            if ( $v !== 'main' && ! ctype_digit( $v ) ) { $v = 'main'; }
            $row['increased_trait_career_target'] = $v;
        }

        // Map legacy trait columns → modern trait_* keys
        if ( ! array_key_exists( 'trait_body', $row ) ) {
            $row['trait_body'] = sanitize_text_field( (string) ( $row['body'] ?? '' ) );
        }
        if ( ! array_key_exists( 'trait_dexterity', $row ) ) {
            // legacy "speed" corresponds to Dexterity in this project
            $row['trait_dexterity'] = sanitize_text_field( (string) ( $row['speed'] ?? '' ) );
        }
        if ( ! array_key_exists( 'trait_sense', $row ) ) {
            // legacy "mind" corresponds to Sense in this project
            $row['trait_sense'] = sanitize_text_field( (string) ( $row['mind'] ?? '' ) );
        }
        if ( ! array_key_exists( 'trait_will', $row ) ) {
            $row['trait_will'] = sanitize_text_field( (string) ( $row['will'] ?? '' ) );
        }

        return $row;
    }
}

if ( ! function_exists( 'cg_char_send_db_error' ) ) {
    function cg_char_send_db_error( $public_message, $status = 500 ) {
        global $wpdb;
        $payload = [ 'message' => $public_message ];

        // Helpful on STAGE for admins; avoids leaking details to normal users.
        if ( is_user_logged_in() && current_user_can( 'manage_options' ) ) {
            $payload['db_error'] = (string) ( $wpdb->last_error ?? '' );
            $payload['db_query'] = (string) ( $wpdb->last_query ?? '' );
        }

        if ( $wpdb && ! empty( $wpdb->last_error ) ) {
            error_log( '[CG] DB error: ' . $wpdb->last_error );
        }

        wp_send_json_error( $payload, $status );
    }
}

if ( ! function_exists( 'cg_load_characters' ) ) {
    function cg_load_characters() {
        cg_ajax_require_post();
        cg_ajax_require_nonce_multi();
        cg_char_ajax_require_auth();

        global $wpdb;
        $tbl  = $wpdb->prefix . 'character_records';
        $user = get_current_user_id();

        // Build SELECT that works for either schema.
        $select = [
            'id',
            'name',
            'player_name',
            'age',
            'gender',
        ];

        // species/career id columns (new) or legacy columns aliased to *_id
        if ( cg_char_has_col( $tbl, 'species_id' ) ) {
            $select[] = 'species_id';
        } elseif ( cg_char_has_col( $tbl, 'species' ) ) {
            $select[] = 'species AS species_id';
        } else {
            $select[] = '0 AS species_id';
        }

        if ( cg_char_has_col( $tbl, 'career_id' ) ) {
            $select[] = 'career_id';
        } elseif ( cg_char_has_col( $tbl, 'career' ) ) {
            $select[] = 'career AS career_id';
        } else {
            $select[] = '0 AS career_id';
        }

        if ( cg_char_has_col( $tbl, 'extra_career_1' ) ) { $select[] = 'extra_career_1'; }
        if ( cg_char_has_col( $tbl, 'extra_career_2' ) ) { $select[] = 'extra_career_2'; }

        if ( cg_char_has_col( $tbl, 'increased_trait_career_target' ) ) {
            $select[] = 'increased_trait_career_target';
        }

        $sql = "SELECT " . implode( ", ", $select ) . " FROM {$tbl} WHERE user_id = %d ORDER BY id DESC";
        $rows = $wpdb->get_results( $wpdb->prepare( $sql, $user ), ARRAY_A );

        if ( $rows === null ) {
            cg_char_send_db_error( 'Database error while loading characters.' , 500 );
        }

        if ( ! is_array( $rows ) ) { $rows = []; }

        foreach ( $rows as &$r ) {
            $r = cg_char_normalize_row_for_js( $r );
        }
        unset( $r );

        wp_send_json_success( $rows );
    }
}

if ( ! function_exists( 'cg_get_character' ) ) {
    function cg_get_character() {
        cg_ajax_require_post();
        cg_ajax_require_nonce_multi();
        cg_char_ajax_require_auth();

        $id = absint( $_POST['id'] ?? 0 );
        if ( $id <= 0 ) {
            wp_send_json_error( [ 'message' => 'Invalid character id.' ], 400 );
        }

        global $wpdb;
        $tbl  = $wpdb->prefix . 'character_records';
        $user = get_current_user_id();

        $row = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$tbl} WHERE id = %d AND user_id = %d LIMIT 1",
                $id,
                $user
            ),
            ARRAY_A
        );

        if ( $row === null && ! empty( $wpdb->last_error ) ) {
            cg_char_send_db_error( 'Database error while loading character.' , 500 );
        }

        if ( ! $row ) {
            wp_send_json_error( [ 'message' => 'Character not found.' ], 404 );
        }

        // De-serialize complex fields
        $marks = maybe_unserialize( $row['skill_marks'] ?? '' );
        $row['skill_marks'] = is_array( $marks ) ? $marks : [];

        $gifts = [
            absint( $row['free_gift_1'] ?? 0 ),
            absint( $row['free_gift_2'] ?? 0 ),
            absint( $row['free_gift_3'] ?? 0 ),
        ];
        $row['free_gifts'] = $gifts;

        $replacements = maybe_unserialize( $row['career_gift_replacements'] ?? '' );
        $row['career_gift_replacements'] = is_array( $replacements ) ? $replacements : [];

        // Normalize to modern keys expected by JS
        $row = cg_char_normalize_row_for_js( $row );

        wp_send_json_success( $row );
    }
}

if ( ! function_exists( 'cg_save_character' ) ) {
    function cg_save_character() {
        cg_ajax_require_post();
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

        $id = absint( $data['id'] ?? 0 );

        // Accept both old and new keys from JS
        $species_id = absint( $data['species_id'] ?? $data['species'] ?? 0 );
        $career_id  = absint( $data['career_id']  ?? $data['career']  ?? 0 );

        $extraCareers = $data['extraCareers'] ?? [];
        if ( ! is_array( $extraCareers ) ) { $extraCareers = []; }

        $extra_1 = absint( $data['extra_career_1'] ?? ( $extraCareers[0] ?? 0 ) );
        $extra_2 = absint( $data['extra_career_2'] ?? ( $extraCareers[1] ?? 0 ) );

        // Increased Trait: Career target — ONLY overwrite if provided (or creating)
        $has_inc_target =
            array_key_exists( 'increased_trait_career_target', $data ) ||
            array_key_exists( 'increased_trait_career_targe', $data ); // tolerate old typo

        $inc_target = null;
        if ( $has_inc_target || $id === 0 ) {
            $inc_target_raw = $data['increased_trait_career_target']
                ?? ( $data['increased_trait_career_targe'] ?? null );

            $t = sanitize_text_field( (string) ( $inc_target_raw ?? 'main' ) );
            $t = trim( $t );
            if ( $t === '' ) { $t = 'main'; }
            if ( $t !== 'main' && ! ctype_digit( $t ) ) { $t = 'main'; }
            $inc_target = $t;
        }

        $trait_body      = sanitize_text_field( $data['trait_body']      ?? $data['body'] ?? '' );
        $trait_dexterity = sanitize_text_field( $data['trait_dexterity'] ?? $data['speed'] ?? '' );
        $trait_sense     = sanitize_text_field( $data['trait_sense']     ?? $data['mind'] ?? '' );
        $trait_will      = sanitize_text_field( $data['trait_will']      ?? $data['will'] ?? '' );
        $trait_career    = sanitize_text_field( $data['trait_career']    ?? '' );
        $trait_species   = sanitize_text_field( $data['trait_species']   ?? '' );

        $gifts       = cg_char_sanitize_free_gifts( $data['free_gifts'] ?? [] );
        $skill_marks = cg_char_sanitize_skill_marks( $data['skillMarks'] ?? [] );

        $has_gift_replacements = array_key_exists( 'career_gift_replacements', $data );
        $gift_replacements = $has_gift_replacements
            ? cg_char_sanitize_gift_replacements( $data['career_gift_replacements'] )
            : null;

        global $wpdb;
        $tbl  = $wpdb->prefix . 'character_records';
        $user = get_current_user_id();

        // Build fields ONLY from columns that exist (prevents unknown-column insert failures)
        $fields = [];

        if ( cg_char_has_col( $tbl, 'name' ) )         $fields['name'] = $name;
        if ( cg_char_has_col( $tbl, 'player_name' ) )  $fields['player_name'] = sanitize_text_field( $data['player_name'] ?? '' );
        if ( cg_char_has_col( $tbl, 'age' ) )          $fields['age'] = sanitize_text_field( $data['age'] ?? '' );
        if ( cg_char_has_col( $tbl, 'gender' ) )       $fields['gender'] = sanitize_text_field( $data['gender'] ?? '' );

        // ids: new columns or legacy columns
        if ( cg_char_has_col( $tbl, 'species_id' ) ) {
            $fields['species_id'] = $species_id;
        } elseif ( cg_char_has_col( $tbl, 'species' ) ) {
            $fields['species'] = (string) $species_id;
        }

        if ( cg_char_has_col( $tbl, 'career_id' ) ) {
            $fields['career_id'] = $career_id;
        } elseif ( cg_char_has_col( $tbl, 'career' ) ) {
            $fields['career'] = (string) $career_id;
        }

        // Extras (legacy table uses varchar)
        if ( cg_char_has_col( $tbl, 'extra_career_1' ) ) $fields['extra_career_1'] = (string) $extra_1;
        if ( cg_char_has_col( $tbl, 'extra_career_2' ) ) $fields['extra_career_2'] = (string) $extra_2;

        // Persist boost target only if provided (or creating)
        if ( $inc_target !== null && cg_char_has_col( $tbl, 'increased_trait_career_target' ) ) {
            $fields['increased_trait_career_target'] = $inc_target;
        }

        // Traits: prefer modern trait_* columns; otherwise map to legacy body/speed/mind/will
        if ( cg_char_has_col( $tbl, 'trait_body' ) ) {
            $fields['trait_body'] = $trait_body;
        } elseif ( cg_char_has_col( $tbl, 'body' ) ) {
            $fields['body'] = $trait_body;
        }

        if ( cg_char_has_col( $tbl, 'trait_dexterity' ) ) {
            $fields['trait_dexterity'] = $trait_dexterity;
        } elseif ( cg_char_has_col( $tbl, 'speed' ) ) {
            $fields['speed'] = $trait_dexterity;
        }

        if ( cg_char_has_col( $tbl, 'trait_sense' ) ) {
            $fields['trait_sense'] = $trait_sense;
        } elseif ( cg_char_has_col( $tbl, 'mind' ) ) {
            $fields['mind'] = $trait_sense;
        }

        if ( cg_char_has_col( $tbl, 'trait_will' ) ) {
            $fields['trait_will'] = $trait_will;
        } elseif ( cg_char_has_col( $tbl, 'will' ) ) {
            $fields['will'] = $trait_will;
        }

        if ( cg_char_has_col( $tbl, 'trait_career' ) )  $fields['trait_career']  = $trait_career;
        if ( cg_char_has_col( $tbl, 'trait_species' ) ) $fields['trait_species'] = $trait_species;

        if ( cg_char_has_col( $tbl, 'free_gift_1' ) ) $fields['free_gift_1'] = $gifts[0];
        if ( cg_char_has_col( $tbl, 'free_gift_2' ) ) $fields['free_gift_2'] = $gifts[1];
        if ( cg_char_has_col( $tbl, 'free_gift_3' ) ) $fields['free_gift_3'] = $gifts[2];

        if ( cg_char_has_col( $tbl, 'description' ) ) $fields['description'] = sanitize_textarea_field( $data['description'] ?? '' );
        if ( cg_char_has_col( $tbl, 'backstory' ) )   $fields['backstory']   = sanitize_textarea_field( $data['backstory'] ?? '' );

        if ( cg_char_has_col( $tbl, 'skill_marks' ) ) $fields['skill_marks'] = maybe_serialize( $skill_marks );
        if ( cg_char_has_col( $tbl, 'local_area' ) )  $fields['local_area']  = sanitize_text_field( $data['local_area'] ?? '' );
        if ( cg_char_has_col( $tbl, 'language' ) )    $fields['language']    = sanitize_text_field( $data['language'] ?? '' );

        // Career gift replacements: only overwrite if caller provided the key (or creating)
        if ( ( $has_gift_replacements || $id === 0 ) && cg_char_has_col( $tbl, 'career_gift_replacements' ) ) {
            $fields['career_gift_replacements'] = maybe_serialize( $has_gift_replacements ? $gift_replacements : [] );
        }

        // Update timestamp if column exists
        if ( cg_char_has_col( $tbl, 'updated' ) ) {
            $fields['updated'] = current_time( 'mysql' );
        }

        if ( $id > 0 ) {
            // Confirm ownership first
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
                cg_char_send_db_error( 'Database error while saving.', 500 );
            }
        } else {
            $fields['user_id'] = $user;
            $ok = $wpdb->insert( $tbl, $fields );
            if ( ! $ok ) {
                cg_char_send_db_error( 'Database error while saving.', 500 );
            }
            $id = (int) $wpdb->insert_id;
        }

        wp_send_json_success( [ 'id' => (string) $id ] );
    }
}
