<?php
/**
 * data-editor-ajax.php
 * Library of Calabria — child theme snippet
 *
 * Registers three wp-admin AJAX endpoints that power the front-end data editor
 * (page-data-editor.php). All endpoints require a logged-in WordPress admin.
 *
 * INSTALLATION
 * ------------
 * Paste the entire contents of this file into the child theme's functions.php.
 *
 * ENDPOINTS (all wp_ajax_ only — never wp_ajax_nopriv_)
 * -------------------------------------------------------
 * loc_de_list   — paginated + searchable record list for a table
 * loc_de_get    — full record data (main fields + child rows) for one entry
 * loc_de_save   — UPDATE main record + child rows; returns success/error
 *
 * SERVER PATH
 * -----------
 * Add contents to: wp-content/themes/<child-theme>/functions.php
 */

/* ============================================================
 * Table whitelist — the only tables editable through this tool
 * ============================================================ */

if ( ! defined( 'LOC_DE_TABLES' ) ) {
    define( 'LOC_DE_TABLES', [
        'gifts'     => 'customtables_table_gifts',
        'careers'   => 'customtables_table_careers',
        'species'   => 'customtables_table_species',
        'skills'    => 'customtables_table_skills',
        'equipment' => 'customtables_table_equipment',
        'books'     => 'customtables_table_books',
    ] );
}

/* ============================================================
 * Child table config — which child tables exist per parent
 * ============================================================
 * For each child table: fk = foreign key column pointing to parent ct_id,
 * editable = columns the editor can UPDATE (ct_id is always read-only).
 */

if ( ! defined( 'LOC_DE_CHILD_TABLES' ) ) {
    define( 'LOC_DE_CHILD_TABLES', [
        'gifts' => [
            'gift_sections' => [
                'table'    => 'customtables_table_gift_sections',
                'fk'       => 'ct_gift_id',
                'editable' => [ 'ct_sort', 'ct_section_type', 'ct_heading', 'ct_body' ],
            ],
            'gift_requirements' => [
                'table'    => 'customtables_table_gift_requirements',
                'fk'       => 'ct_gift_id',
                'editable' => [ 'ct_sort', 'ct_req_kind', 'ct_req_ref_id', 'ct_req_text' ],
            ],
        ],
    ] );
}

/* ============================================================
 * Internal helpers
 * ============================================================ */

/**
 * Validate + resolve a table slug to its full prefixed table name.
 * Returns null if the slug is not in the whitelist.
 *
 * @param  string $slug  e.g. 'gifts'
 * @return string|null   e.g. 'DcVnchxg4_customtables_table_gifts' or null
 */
function loc_de_resolve_table( $slug ) {
    global $wpdb;
    $tables = LOC_DE_TABLES;
    if ( ! isset( $tables[ $slug ] ) ) {
        return null;
    }
    return $wpdb->prefix . $tables[ $slug ];
}

/**
 * Require nonce + admin capability; die on failure.
 */
function loc_de_require_admin() {
    check_ajax_referer( 'loc_data_editor', 'nonce' );
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( 'Permission denied.', 403 );
    }
}

/**
 * Fetch column metadata from INFORMATION_SCHEMA for a fully-qualified table.
 * Returns array of assoc arrays: column_name, data_type, char_length, is_nullable.
 *
 * @param  string $full_table  e.g. 'DcVnchxg4_customtables_table_gifts'
 * @return array
 */
function loc_de_get_columns( $full_table ) {
    global $wpdb;

    return $wpdb->get_results(
        $wpdb->prepare(
            "SELECT COLUMN_NAME AS column_name,
                    DATA_TYPE AS data_type,
                    CHARACTER_MAXIMUM_LENGTH AS char_length,
                    IS_NULLABLE AS is_nullable,
                    COLUMN_DEFAULT AS column_default
             FROM INFORMATION_SCHEMA.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME   = %s
             ORDER BY ORDINAL_POSITION",
            $full_table
        ),
        ARRAY_A
    );
}

/**
 * From a column list, return the subset to show in the list view.
 * Chooses: ct_id always first, then up to 4 non-TEXT, non-blob columns.
 *
 * @param  array $columns  from loc_de_get_columns()
 * @return array           filtered column metadata
 */
function loc_de_list_columns( $columns ) {
    $large_types = [ 'text', 'mediumtext', 'longtext', 'tinytext', 'blob', 'mediumblob', 'longblob' ];
    $result      = [];
    $extras      = 0;

    foreach ( $columns as $col ) {
        if ( $col['column_name'] === 'ct_id' ) {
            array_unshift( $result, $col );
            continue;
        }
        if ( $extras < 4 && ! in_array( $col['data_type'], $large_types, true ) ) {
            $result[] = $col;
            $extras++;
        }
    }
    return $result;
}

/**
 * Return the column name most likely to be the "name/title" of each record.
 * Used to build the LIKE search clause.
 *
 * @param  array $columns
 * @return string|null
 */
function loc_de_name_column( $columns ) {
    foreach ( $columns as $col ) {
        $n = $col['column_name'];
        if ( preg_match( '/_(name|title|slug)$/', $n ) ) {
            return $n;
        }
    }
    return null;
}

/* ============================================================
 * AJAX: loc_de_list — paginated, searchable record list
 * ============================================================ */

if ( ! function_exists( 'loc_de_ajax_list_records' ) ) {
    function loc_de_ajax_list_records() {
        loc_de_require_admin();

        $slug  = sanitize_key( $_POST['table'] ?? '' );
        $table = loc_de_resolve_table( $slug );
        if ( ! $table ) {
            wp_send_json_error( 'Invalid table.' );
        }

        $page    = max( 1, intval( $_POST['page'] ?? 1 ) );
        $per     = 25;
        $offset  = ( $page - 1 ) * $per;
        $search  = sanitize_text_field( $_POST['search'] ?? '' );

        global $wpdb;

        $columns     = loc_de_get_columns( $table );
        $list_cols   = loc_de_list_columns( $columns );
        $name_col    = loc_de_name_column( $columns );
        $col_names   = array_column( $list_cols, 'column_name' );
        $col_sql     = implode( ', ', array_map( function($c) { return "`{$c}`"; }, $col_names ) );

        // Build WHERE clause for search
        $where = '1=1';
        if ( $search !== '' && $name_col ) {
            $where = $wpdb->prepare( "`{$name_col}` LIKE %s", '%' . $wpdb->esc_like( $search ) . '%' );
        }

        $total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM `{$table}` WHERE {$where}" );

        $rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT {$col_sql} FROM `{$table}` WHERE {$where} ORDER BY ct_id ASC LIMIT %d OFFSET %d",
                $per,
                $offset
            ),
            ARRAY_A
        );

        wp_send_json_success( [
            'columns' => $list_cols,
            'rows'    => $rows,
            'total'   => $total,
            'page'    => $page,
            'per'     => $per,
        ] );
    }
    add_action( 'wp_ajax_loc_de_list', 'loc_de_ajax_list_records' );
}

/* ============================================================
 * AJAX: loc_de_get — full record + child rows
 * ============================================================ */

if ( ! function_exists( 'loc_de_ajax_get_record' ) ) {
    function loc_de_ajax_get_record() {
        loc_de_require_admin();

        $slug  = sanitize_key( $_POST['table'] ?? '' );
        $id    = intval( $_POST['id'] ?? 0 );
        $table = loc_de_resolve_table( $slug );

        if ( ! $table || $id <= 0 ) {
            wp_send_json_error( 'Invalid table or ID.' );
        }

        global $wpdb;

        $record = $wpdb->get_row(
            $wpdb->prepare( "SELECT * FROM `{$table}` WHERE ct_id = %d", $id ),
            ARRAY_A
        );

        if ( ! $record ) {
            wp_send_json_error( 'Record not found.' );
        }

        $columns    = loc_de_get_columns( $table );
        $child_data = [];

        $child_config = LOC_DE_CHILD_TABLES;
        if ( isset( $child_config[ $slug ] ) ) {
            foreach ( $child_config[ $slug ] as $child_key => $cfg ) {
                $child_table = $wpdb->prefix . $cfg['table'];
                $fk          = $cfg['fk'];

                $child_rows = $wpdb->get_results(
                    $wpdb->prepare(
                        "SELECT * FROM `{$child_table}` WHERE `{$fk}` = %d ORDER BY ct_sort ASC, ct_id ASC",
                        $id
                    ),
                    ARRAY_A
                );

                $child_data[ $child_key ] = [
                    'rows'     => $child_rows,
                    'editable' => $cfg['editable'],
                    'fk'       => $fk,
                ];
            }
        }

        wp_send_json_success( [
            'record'       => $record,
            'columns'      => $columns,
            'child_tables' => $child_data,
        ] );
    }
    add_action( 'wp_ajax_loc_de_get', 'loc_de_ajax_get_record' );
}

/* ============================================================
 * AJAX: loc_de_save — UPDATE main record + child rows
 * ============================================================ */

if ( ! function_exists( 'loc_de_ajax_save_record' ) ) {
    function loc_de_ajax_save_record() {
        loc_de_require_admin();

        $slug  = sanitize_key( $_POST['table'] ?? '' );
        $id    = intval( $_POST['id'] ?? 0 );
        $table = loc_de_resolve_table( $slug );

        if ( ! $table || $id <= 0 ) {
            wp_send_json_error( 'Invalid table or ID.' );
        }

        // Verify record exists before attempting update
        global $wpdb;
        $exists = $wpdb->get_var( $wpdb->prepare( "SELECT ct_id FROM `{$table}` WHERE ct_id = %d", $id ) );
        if ( ! $exists ) {
            wp_send_json_error( 'Record not found.' );
        }

        // Build allowed column set from INFORMATION_SCHEMA (prevents injection via field names)
        $all_columns   = loc_de_get_columns( $table );
        $allowed_names = array_column( $all_columns, 'column_name' );
        $col_types     = array_column( $all_columns, 'data_type', 'column_name' );

        // Main record fields
        $submitted_fields = $_POST['fields'] ?? [];
        if ( ! is_array( $submitted_fields ) ) {
            wp_send_json_error( 'Invalid fields payload.' );
        }

        $update_data   = [];
        $update_format = [];
        $int_types     = [ 'int', 'bigint', 'smallint', 'mediumint', 'tinyint' ];
        // Long-text columns may contain HTML markup (e.g. gift body text); use
        // wp_kses_post() so safe tags are preserved rather than stripped.
        $richtext_types = [ 'text', 'mediumtext', 'longtext', 'tinytext' ];

        foreach ( $submitted_fields as $col => $val ) {
            $col = sanitize_key( $col );
            if ( $col === 'ct_id' ) { continue; }
            if ( ! in_array( $col, $allowed_names, true ) ) { continue; }

            $dtype = $col_types[ $col ] ?? 'varchar';
            if ( in_array( $dtype, $int_types, true ) ) {
                $update_data[ $col ]   = ( $val === '' || $val === null ) ? null : intval( $val );
                $update_format[]       = '%d';
            } elseif ( in_array( $dtype, $richtext_types, true ) ) {
                $update_data[ $col ]   = wp_kses_post( $val );
                $update_format[]       = '%s';
            } else {
                $update_data[ $col ]   = sanitize_text_field( $val );
                $update_format[]       = '%s';
            }
        }

        $main_result = true;
        if ( ! empty( $update_data ) ) {
            $main_result = $wpdb->update(
                $table,
                $update_data,
                [ 'ct_id' => $id ],
                $update_format,
                [ '%d' ]
            );
        }

        if ( $main_result === false ) {
            wp_send_json_error( 'Database error updating main record: ' . $wpdb->last_error );
        }

        // Child table rows
        $child_errors  = [];
        $child_config  = LOC_DE_CHILD_TABLES;
        $child_payload = $_POST['child_rows'] ?? [];

        if ( isset( $child_config[ $slug ] ) && is_array( $child_payload ) ) {
            foreach ( $child_config[ $slug ] as $child_key => $cfg ) {
                if ( ! isset( $child_payload[ $child_key ] ) ) { continue; }
                if ( ! is_array( $child_payload[ $child_key ] ) ) { continue; }

                $child_table     = $wpdb->prefix . $cfg['table'];
                $allowed_editable = $cfg['editable'];

                foreach ( $child_payload[ $child_key ] as $row_id => $row_fields ) {
                    $row_id = intval( $row_id );
                    if ( $row_id <= 0 || ! is_array( $row_fields ) ) { continue; }

                    // Confirm this child row belongs to the parent record (prevents IDOR)
                    $owner = $wpdb->get_var( $wpdb->prepare(
                        "SELECT ct_id FROM `{$child_table}` WHERE ct_id = %d AND `{$cfg['fk']}` = %d",
                        $row_id,
                        $id
                    ) );
                    if ( ! $owner ) { continue; }

                    $row_data   = [];
                    $row_format = [];

                    foreach ( $row_fields as $col => $val ) {
                        $col = sanitize_key( $col );
                        if ( ! in_array( $col, $allowed_editable, true ) ) { continue; }

                        if ( $col === 'ct_sort' || $col === 'ct_req_ref_id' ) {
                            $row_data[ $col ]   = ( $val === '' || $val === null ) ? null : intval( $val );
                            $row_format[]       = '%d';
                        } elseif ( $col === 'ct_body' ) {
                            // ct_body is mediumtext and may contain HTML markup.
                            $row_data[ $col ]   = wp_kses_post( $val );
                            $row_format[]       = '%s';
                        } else {
                            $row_data[ $col ]   = sanitize_text_field( $val );
                            $row_format[]       = '%s';
                        }
                    }

                    if ( ! empty( $row_data ) ) {
                        $res = $wpdb->update(
                            $child_table,
                            $row_data,
                            [ 'ct_id' => $row_id ],
                            $row_format,
                            [ '%d' ]
                        );
                        if ( $res === false ) {
                            $child_errors[] = "Child {$child_key} row {$row_id}: " . $wpdb->last_error;
                        }
                    }
                }
            }
        }

        if ( ! empty( $child_errors ) ) {
            wp_send_json_error( 'Some child rows failed: ' . implode( '; ', $child_errors ) );
        }

        wp_send_json_success( [ 'message' => 'Saved.' ] );
    }
    add_action( 'wp_ajax_loc_de_save', 'loc_de_ajax_save_record' );
}
