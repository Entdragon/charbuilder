<?php
/**
 * Twenty Twenty-Four Child functions.php
 * (brace-count: each callback fn has its closing braces)
 */
require_once get_stylesheet_directory() . '/snippets/character-generator-embed.php';

// 1) Register menus
add_action( 'after_setup_theme', function() {
    register_nav_menus( [
        'primary' => __( 'Primary Menu', 'twentytwentyfour-child' ),
        'footer'  => __( 'Footer Menu',  'twentytwentyfour-child' ),
    ] );
} ); // <-- closes the add_action callback

add_filter( 'wp_nav_menu_objects', function( $items ) {
    if ( is_user_logged_in() ) return $items;

    foreach ( $items as $key => $item ) {
        if ( strpos( $item->url, '/character-generator' ) !== false ) {
            unset( $items[$key] );
        }
    }

    return $items;
} );

// 2) Enqueue child stylesheet and auth script (conditionally)
add_action( 'wp_enqueue_scripts', function() {
    wp_enqueue_style(
        'twentytwentyfour-child-style',
        get_stylesheet_uri(),
        [],
        wp_get_theme()->get( 'Version' )
    );

    // Auth script: only load on login or register pages
    if ( is_page( [ 'login', 'register' ] ) ) {
        wp_enqueue_script(
            'cg-auth',
            get_stylesheet_directory_uri() . '/js/cg-auth.js',
            [ 'jquery' ],
            null,
            true
        );

        wp_localize_script( 'cg-auth', 'CG_Auth', [
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'cg_nonce' ),
        ] );
    }
} ); // <-- closes the add_action callback

// 2b) Shared Library Search form helper
if ( ! function_exists( 'loc_library_search_action_url' ) ) {
    function loc_library_search_action_url() {
        return home_url( '/library-search/' );
    }
}

if ( ! function_exists( 'loc_library_search_current_value' ) ) {
    function loc_library_search_current_value() {
        if ( ! isset( $_GET['q'] ) ) {
            return '';
        }

        return sanitize_text_field( wp_unslash( $_GET['q'] ) );
    }
}

if ( ! function_exists( 'loc_library_clean_multi_value_param' ) ) {
    function loc_library_clean_multi_value_param( $raw_values ) {
        $clean = array();

        foreach ( (array) $raw_values as $value ) {
            $value = sanitize_text_field( (string) $value );
            if ( $value !== '' ) {
                $clean[] = $value;
            }
        }

        return array_values( array_unique( $clean ) );
    }
}

if ( ! function_exists( 'loc_render_library_search_controls' ) ) {
    function loc_render_library_search_controls( $args = array() ) {
        $defaults = array(
            'wrapper_class'      => '',
            'label'              => 'Search the Library',
            'input_id'           => 'loc-library-search-q',
            'input_name'         => 'q',
            'input_type'         => 'search',
            'input_class'        => 'loc-library-search-input',
            'placeholder'        => 'Search gifts, equipment, weapons, species...',
            'show_current_query' => true,
        );

        $args = wp_parse_args( $args, $defaults );

        $current_value = '';
        if ( ! empty( $args['show_current_query'] ) ) {
            $current_value = loc_library_search_current_value();
        }

        $has_wrapper = trim( (string) $args['wrapper_class'] ) !== '';

        if ( $has_wrapper ) {
            echo '<div class="' . esc_attr( $args['wrapper_class'] ) . '">';
        }

        echo '<label for="' . esc_attr( $args['input_id'] ) . '" class="screen-reader-text">' . esc_html( $args['label'] ) . '</label>';
        echo '<input type="' . esc_attr( $args['input_type'] ) . '" id="' . esc_attr( $args['input_id'] ) . '" name="' . esc_attr( $args['input_name'] ) . '" value="' . esc_attr( $current_value ) . '" placeholder="' . esc_attr( $args['placeholder'] ) . '" class="' . esc_attr( $args['input_class'] ) . '" required>';

        if ( $has_wrapper ) {
            echo '</div>';
        }
    }
}

if ( ! function_exists( 'loc_render_library_search_form' ) ) {
    function loc_render_library_search_form( $args = array() ) {
        $defaults = array(
            'action'             => loc_library_search_action_url(),
            'wrapper_class'      => 'loc-library-search-wrap',
            'form_class'         => 'loc-library-search-form',
            'label'              => 'Search the Library',
            'input_id'           => 'loc-library-search-q',
            'input_name'         => 'q',
            'input_class'        => 'loc-library-search-input',
            'button_class'       => 'loc-library-search-button',
            'placeholder'        => 'Search gifts, equipment, weapons, species...',
            'button_label'       => 'Search',
            'helper_text'        => '',
            'helper_class'       => 'loc-library-search-help',
            'show_current_query' => true,
        );

        $args = wp_parse_args( $args, $defaults );

        echo '<div class="' . esc_attr( $args['wrapper_class'] ) . '">';
        echo '<form method="get" action="' . esc_url( $args['action'] ) . '" class="' . esc_attr( $args['form_class'] ) . '">';

        loc_render_library_search_controls( array(
            'wrapper_class'      => '',
            'label'              => $args['label'],
            'input_id'           => $args['input_id'],
            'input_name'         => $args['input_name'],
            'input_type'         => 'search',
            'input_class'        => $args['input_class'],
            'placeholder'        => $args['placeholder'],
            'show_current_query' => ! empty( $args['show_current_query'] ),
        ) );

        echo '<button type="submit" class="' . esc_attr( $args['button_class'] ) . '">' . esc_html( $args['button_label'] ) . '</button>';
        echo '</form>';

        if ( trim( (string) $args['helper_text'] ) !== '' ) {
            echo '<div class="' . esc_attr( $args['helper_class'] ) . '">' . esc_html( $args['helper_text'] ) . '</div>';
        }

        echo '</div>';
    }
}

// 2c) Shared Library Search engine helpers
if ( ! function_exists( 'loc_library_table_exists' ) ) {
    function loc_library_table_exists( $table_name ) {
        global $wpdb;
        static $cache = array();

        $key = 'table::' . $table_name;
        if ( isset( $cache[ $key ] ) ) {
            return $cache[ $key ];
        }

        $exists = (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*)
                 FROM information_schema.TABLES
                 WHERE TABLE_SCHEMA = DATABASE()
                   AND TABLE_NAME = %s",
                $table_name
            )
        ) > 0;

        $cache[ $key ] = $exists;
        return $exists;
    }
}

if ( ! function_exists( 'loc_library_table_has_column' ) ) {
    function loc_library_table_has_column( $table_name, $column_name ) {
        global $wpdb;
        static $cache = array();

        $key = $table_name . '::' . $column_name;
        if ( isset( $cache[ $key ] ) ) {
            return $cache[ $key ];
        }

        $exists = (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*)
                 FROM information_schema.COLUMNS
                 WHERE TABLE_SCHEMA = DATABASE()
                   AND TABLE_NAME = %s
                   AND COLUMN_NAME = %s",
                $table_name,
                $column_name
            )
        ) > 0;

        $cache[ $key ] = $exists;
        return $exists;
    }
}

if ( ! function_exists( 'loc_library_first_existing_column' ) ) {
    function loc_library_first_existing_column( $table_name, $candidates ) {
        foreach ( (array) $candidates as $candidate ) {
            if ( loc_library_table_has_column( $table_name, $candidate ) ) {
                return $candidate;
            }
        }

        return '';
    }
}

if ( ! function_exists( 'loc_library_normalize_text' ) ) {
    function loc_library_normalize_text( $text ) {
        $text = wp_strip_all_tags( (string) $text );
        $text = html_entity_decode( $text, ENT_QUOTES, 'UTF-8' );
        $text = preg_replace( '/\s+/u', ' ', $text );
        return trim( (string) $text );
    }
}

if ( ! function_exists( 'loc_library_humanize_filter_value' ) ) {
    function loc_library_humanize_filter_value( $value ) {
        $value = trim( (string) $value );
        if ( $value === '' ) {
            return '';
        }

        $value = str_replace( array( '_', '-' ), ' ', $value );
        $value = preg_replace( '/\s+/u', ' ', $value );
        $value = trim( (string) $value );

        if ( $value === '' ) {
            return '';
        }

        if ( function_exists( 'mb_convert_case' ) ) {
            return mb_convert_case( $value, MB_CASE_TITLE, 'UTF-8' );
        }

        return ucwords( $value );
    }
}

if ( ! function_exists( 'loc_library_clean_excerpt_source' ) ) {
    function loc_library_clean_excerpt_source( $text ) {
        $text = (string) $text;
        $text = html_entity_decode( $text, ENT_QUOTES, 'UTF-8' );
        $text = wp_strip_all_tags( $text );
        $text = str_replace( array( "\r\n", "\r" ), "\n", $text );

        $text = preg_replace( '/\s*@@([A-Z0-9_ -]+):\s*/u', "\n@@$1: ", $text );

        $lines = preg_split( '/\n+/u', $text );
        $parts = array();

        foreach ( (array) $lines as $line ) {
            $line = trim( (string) $line );
            if ( $line === '' ) {
                continue;
            }

            if ( preg_match( '/^@@[A-Z0-9_ -]+:\s*(.*)$/u', $line, $matches ) ) {
                $payload = trim( (string) $matches[1] );
                if ( $payload !== '' ) {
                    $parts[] = $payload;
                }
                continue;
            }

            $line = preg_replace( '/@@[A-Z0-9_ -]+:\s*/u', ' ', $line );
            $line = trim( (string) $line );

            if ( $line !== '' ) {
                $parts[] = $line;
            }
        }

        if ( ! empty( $parts ) ) {
            $text = implode( ' • ', $parts );
        }

        $text = preg_replace( '/\s+/u', ' ', $text );
        return trim( (string) $text );
    }
}

if ( ! function_exists( 'loc_library_build_excerpt' ) ) {
    function loc_library_build_excerpt( $row, $candidate_fields ) {
        $fallback = '';

        foreach ( (array) $candidate_fields as $field ) {
            if ( ! isset( $row->{$field} ) || trim( (string) $row->{$field} ) === '' ) {
                continue;
            }

            $clean = loc_library_clean_excerpt_source( $row->{$field} );
            if ( $clean === '' ) {
                continue;
            }

            if ( $fallback === '' ) {
                $fallback = $clean;
            }

            if ( function_exists( 'mb_strlen' ) ) {
                if ( mb_strlen( $clean, 'UTF-8' ) >= 24 ) {
                    return wp_trim_words( $clean, 28, '…' );
                }
            } else {
                if ( strlen( $clean ) >= 24 ) {
                    return wp_trim_words( $clean, 28, '…' );
                }
            }
        }

        if ( $fallback !== '' ) {
            return wp_trim_words( $fallback, 28, '…' );
        }

        return '';
    }
}

if ( ! function_exists( 'loc_library_search_score' ) ) {
    function loc_library_search_score( $title, $slug, $term, $excerpt = '' ) {
        $title_l   = function_exists( 'mb_strtolower' ) ? mb_strtolower( (string) $title, 'UTF-8' )   : strtolower( (string) $title );
        $slug_l    = function_exists( 'mb_strtolower' ) ? mb_strtolower( (string) $slug, 'UTF-8' )    : strtolower( (string) $slug );
        $term_l    = function_exists( 'mb_strtolower' ) ? mb_strtolower( (string) $term, 'UTF-8' )    : strtolower( (string) $term );
        $excerpt_l = function_exists( 'mb_strtolower' ) ? mb_strtolower( (string) $excerpt, 'UTF-8' ) : strtolower( (string) $excerpt );

        $score = 0;

        if ( $title_l === $term_l ) {
            $score += 120;
        }
        if ( $slug_l === $term_l ) {
            $score += 110;
        }
        if ( $term_l !== '' && strpos( $title_l, $term_l ) === 0 ) {
            $score += 90;
        }
        if ( $term_l !== '' && strpos( $slug_l, $term_l ) === 0 ) {
            $score += 80;
        }
        if ( $term_l !== '' && strpos( $title_l, $term_l ) !== false ) {
            $score += 60;
        }
        if ( $term_l !== '' && strpos( $slug_l, $term_l ) !== false ) {
            $score += 50;
        }
        if ( $term_l !== '' && $excerpt_l !== '' && strpos( $excerpt_l, $term_l ) !== false ) {
            $score += 20;
        }

        return $score;
    }
}

if ( ! function_exists( 'loc_library_result_url' ) ) {
    function loc_library_result_url( $type, $row, $slug ) {
        $slug = trim( (string) $slug );
        if ( $slug === '' ) {
            return home_url( '/' );
        }

        switch ( $type ) {
            case 'gifts':
                return home_url( '/gift/' . rawurlencode( $slug ) . '/' );

            case 'careers':
                return home_url( '/career/' . rawurlencode( $slug ) . '/' );

            case 'skills':
                return home_url( '/skill/' . rawurlencode( $slug ) . '/' );

            case 'species':
                return home_url( '/species/' . rawurlencode( $slug ) . '/' );

            case 'books':
                return home_url( '/book/' . rawurlencode( $slug ) . '/' );

            case 'weapons':
                $is_species_weapon = 0;

                if ( isset( $row->ct_is_species_weapon ) ) {
                    $is_species_weapon = (int) $row->ct_is_species_weapon;
                }
                if ( isset( $row->weapon_is_species_weapon ) ) {
                    $is_species_weapon = (int) $row->weapon_is_species_weapon;
                }

                if ( $is_species_weapon === 1 ) {
                    return home_url( '/species-weapons/' . rawurlencode( $slug ) . '/' );
                }

                return home_url( '/equipment/' . rawurlencode( $slug ) . '/' );

            case 'equipment':
            default:
                return home_url( '/equipment/' . rawurlencode( $slug ) . '/' );
        }
    }
}

if ( ! function_exists( 'loc_library_run_search_table' ) ) {
    function loc_library_run_search_table( $config, $search_term ) {
        global $wpdb;

        $table = $config['table'];

        if ( ! loc_library_table_exists( $table ) ) {
            return array();
        }

        $title_col = loc_library_first_existing_column( $table, $config['title_columns'] );
        $slug_col  = loc_library_first_existing_column( $table, $config['slug_columns'] );

        if ( $title_col === '' || $slug_col === '' ) {
            return array();
        }

        $excerpt_columns = array();
        foreach ( (array) $config['excerpt_columns'] as $column_name ) {
            if ( loc_library_table_has_column( $table, $column_name ) ) {
                $excerpt_columns[] = $column_name;
            }
        }

        $search_columns = array_unique( array_merge( array( $title_col, $slug_col ), $excerpt_columns ) );

        if ( empty( $search_columns ) ) {
            return array();
        }

        $selects = array(
            "`{$title_col}` AS result_title",
            "`{$slug_col}` AS result_slug",
        );

        foreach ( $excerpt_columns as $column_name ) {
            $selects[] = "`{$column_name}`";
        }

        if ( ! empty( $config['extra_columns'] ) ) {
            foreach ( (array) $config['extra_columns'] as $column_name ) {
                if ( loc_library_table_has_column( $table, $column_name ) ) {
                    $selects[] = "`{$column_name}`";
                }
            }
        }

        $where  = array();
        $params = array();

        if ( ! empty( $config['published_column'] ) && loc_library_table_has_column( $table, $config['published_column'] ) ) {
            $where[] = "`{$config['published_column']}` = 1";
        }

        if ( ! empty( $config['prepared_where'] ) && is_array( $config['prepared_where'] ) ) {
            foreach ( $config['prepared_where'] as $prepared_clause ) {
                if ( empty( $prepared_clause['sql'] ) ) {
                    continue;
                }

                $where[] = '(' . $prepared_clause['sql'] . ')';

                if ( ! empty( $prepared_clause['params'] ) && is_array( $prepared_clause['params'] ) ) {
                    foreach ( $prepared_clause['params'] as $prepared_param ) {
                        $params[] = $prepared_param;
                    }
                }
            }
        }

        if ( ! empty( $config['where_sql'] ) ) {
            $where[] = $config['where_sql'];
        }

        $like = '%' . $wpdb->esc_like( $search_term ) . '%';

        $search_parts = array();
        foreach ( $search_columns as $column_name ) {
            $search_parts[] = "`{$column_name}` LIKE %s";
            $params[] = $like;
        }

        $where[] = '(' . implode( ' OR ', $search_parts ) . ')';

        $limit = isset( $config['limit'] ) ? (int) $config['limit'] : 30;
        if ( $limit < 1 ) {
            $limit = 30;
        }

        $sql = "
            SELECT " . implode( ",\n                   ", array_unique( $selects ) ) . "
            FROM `{$table}`
            WHERE " . implode( ' AND ', $where ) . "
            LIMIT %d
        ";

        $params[] = $limit;
        $prepared = $wpdb->prepare( $sql, $params );
        $rows     = $wpdb->get_results( $prepared );

        if ( empty( $rows ) ) {
            return array();
        }

        $results = array();

        foreach ( $rows as $row ) {
            $title = isset( $row->result_title ) ? loc_library_normalize_text( $row->result_title ) : '';
            $slug  = isset( $row->result_slug ) ? loc_library_normalize_text( $row->result_slug ) : '';

            if ( $title === '' || $slug === '' ) {
                continue;
            }

            $excerpt = loc_library_build_excerpt( $row, $excerpt_columns );

            $results[] = array(
                'type'    => $config['type'],
                'label'   => $config['label'],
                'title'   => $title,
                'slug'    => $slug,
                'url'     => loc_library_result_url( $config['type'], $row, $slug ),
                'excerpt' => $excerpt,
                'score'   => loc_library_search_score( $title, $slug, $search_term, $excerpt ),
            );
        }

        usort( $results, function( $a, $b ) {
            if ( (int) $a['score'] !== (int) $b['score'] ) {
                return ( (int) $b['score'] <=> (int) $a['score'] );
            }

            return strcasecmp( $a['title'], $b['title'] );
        } );

        return $results;
    }
}

if ( ! function_exists( 'loc_library_default_search_configs' ) ) {
    function loc_library_default_search_configs() {
        return array(
            array(
                'type'             => 'gifts',
                'label'            => 'Gifts',
                'table'            => 'DcVnchxg4_customtables_table_gifts',
                'title_columns'    => array( 'ct_gifts_name', 'ct_name' ),
                'slug_columns'     => array( 'ct_slug' ),
                'excerpt_columns'  => array( 'ct_gifts_effect_description', 'ct_gifts_effect', 'ct_description', 'ct_notes' ),
                'extra_columns'    => array(),
                'published_column' => 'published',
                'where_sql'        => '',
                'prepared_where'   => array(),
                'limit'            => 30,
            ),
            array(
                'type'             => 'equipment',
                'label'            => 'Equipment',
                'table'            => 'DcVnchxg4_customtables_table_equipment',
                'title_columns'    => array( 'ct_name' ),
                'slug_columns'     => array( 'ct_slug' ),
                'excerpt_columns'  => array( 'ct_description', 'ct_effect', 'ct_notes', 'ct_descriptors' ),
                'extra_columns'    => array(),
                'published_column' => 'published',
                'where_sql'        => '',
                'prepared_where'   => array(),
                'limit'            => 30,
            ),
            array(
                'type'             => 'weapons',
                'label'            => 'Weapons',
                'table'            => 'DcVnchxg4_customtables_table_weapons',
                'title_columns'    => array( 'ct_name', 'ct_weapons_name' ),
                'slug_columns'     => array( 'ct_slug' ),
                'excerpt_columns'  => array( 'ct_effect', 'ct_descriptors', 'ct_attack_dice', 'ct_range_band' ),
                'extra_columns'    => array( 'ct_is_species_weapon' ),
                'published_column' => 'published',
                'where_sql'        => '',
                'prepared_where'   => array(),
                'limit'            => 30,
            ),
            array(
                'type'             => 'species',
                'label'            => 'Species',
                'table'            => 'DcVnchxg4_customtables_table_species',
                'title_columns'    => array( 'ct_species_name', 'ct_name' ),
                'slug_columns'     => array( 'ct_slug' ),
                'excerpt_columns'  => array( 'ct_species_description', 'ct_description' ),
                'extra_columns'    => array(),
                'published_column' => 'published',
                'where_sql'        => '',
                'prepared_where'   => array(),
                'limit'            => 30,
            ),
            array(
                'type'             => 'careers',
                'label'            => 'Careers',
                'table'            => 'DcVnchxg4_customtables_table_careers',
                'title_columns'    => array( 'ct_career_name', 'ct_name' ),
                'slug_columns'     => array( 'ct_slug' ),
                'excerpt_columns'  => array( 'ct_career_description', 'ct_description', 'ct_notes' ),
                'extra_columns'    => array(),
                'published_column' => 'published',
                'where_sql'        => '',
                'prepared_where'   => array(),
                'limit'            => 30,
            ),
            array(
                'type'             => 'skills',
                'label'            => 'Skills',
                'table'            => 'DcVnchxg4_customtables_table_skills',
                'title_columns'    => array( 'ct_skill_name', 'ct_name', 'ct_skills_name' ),
                'slug_columns'     => array( 'ct_slug' ),
                'excerpt_columns'  => array( 'ct_skill_description', 'ct_description', 'ct_notes' ),
                'extra_columns'    => array(),
                'published_column' => 'published',
                'where_sql'        => '',
                'prepared_where'   => array(),
                'limit'            => 30,
            ),
            array(
                'type'             => 'books',
                'label'            => 'Books',
                'table'            => 'DcVnchxg4_customtables_table_books',
                'title_columns'    => array( 'ct_book_name', 'ct_name' ),
                'slug_columns'     => array( 'ct_ct_slug', 'ct_slug' ),
                'excerpt_columns'  => array( 'ct_book_description', 'ct_abstract', 'ct_description', 'ct_notes' ),
                'extra_columns'    => array(),
                'published_column' => 'published',
                'where_sql'        => '',
                'prepared_where'   => array(),
                'limit'            => 30,
            ),
        );
    }
}

if ( ! function_exists( 'loc_library_run_unified_search' ) ) {
    function loc_library_run_unified_search( $search_term, $search_configs = null ) {
        $search_term = trim( (string) $search_term );

        if ( ! is_array( $search_configs ) ) {
            $search_configs = loc_library_default_search_configs();
        }

        $grouped_results = array();
        $total_results   = 0;

        if ( $search_term !== '' ) {
            foreach ( $search_configs as $config ) {
                $items = loc_library_run_search_table( $config, $search_term );

                if ( ! empty( $items ) ) {
                    $grouped_results[ $config['type'] ] = array(
                        'label' => $config['label'],
                        'items' => $items,
                    );
                    $total_results += count( $items );
                }
            }
        }

        return array(
            'search_term'     => $search_term,
            'search_configs'  => $search_configs,
            'grouped_results' => $grouped_results,
            'total_results'   => $total_results,
        );
    }
}

// 3) Rewrite rules for detail pages
add_action( 'init', function() {

    // Equipment detail page:
    // /equipment/<slug>/  ->  page slug: equipment-detail
    add_rewrite_rule(
        '^equipment/([^/]+)/?$',
        'index.php?pagename=equipment-detail&equipment_slug=$matches[1]',
        'top'
    );

    $base_rules = [
        'book'    => 'book',
        'career'  => 'career',
        'gift'    => 'gift',
        'skill'   => 'skill',
        'species' => 'species',
        'refresh' => 'refresh-details',
    ];

    foreach ( $base_rules as $slug => $page ) {
        add_rewrite_rule(
            "^{$slug}/([^/]+)/?$",
            'index.php?pagename=' . $page . '&slug=$matches[1]',
            'top'
        );
    }

    $types = [
        'species-habitat',
        'species-diet',
        'species-cycle',
        'species-senses',
        'species-weapons',
        'career-type',
        'career-archtype',
        'gift-class',
        'gift-type',
        'skill-descriptor',
    ];

    foreach ( $types as $type ) {
        add_rewrite_rule(
            "^{$type}/([^/]+)/?$",
            'index.php?pagename=' . $type . '-detail&slug=$matches[1]',
            'top'
        );
    }
} ); // <-- closes the init callback

// 4) Allow custom query vars
add_filter( 'query_vars', function( $vars ) {
    $vars[] = 'slug';
    $vars[] = 'equipment_slug';
    return $vars;
} ); // <-- closes the filter callback

// 5) Friendly‐URL fallback redirect
add_action( 'template_redirect', function() {
    if ( is_404() ) {
        global $wpdb;

        $uri = trim( $_SERVER['REQUEST_URI'], '/' );
        if ( ! $uri ) return;

        $lookup_map = [
            'species-habitat'  => [ 'DcVnchxg4_customtables_table_habitat',           'ct_slug' ],
            'species-diet'     => [ 'DcVnchxg4_customtables_table_diet',              'ct_slug' ],
            // …rest of your mappings…
            'skill-descriptor' => [ 'DcVnchxg4_customtables_table_skillsdescriptors', 'ct_slug' ],
        ];

        foreach ( $lookup_map as $prefix => list( $table, $field ) ) {
            $count = (int) $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT COUNT(*) FROM {$table} WHERE {$field} = %s",
                    $uri
                )
            );

            if ( $count > 0 ) {
                wp_redirect( home_url( "/{$prefix}/{$uri}/" ), 301 );
                exit;
            }
        }
    }
} ); // <-- closes the template_redirect callback

if ( ! function_exists( 'loc_req_merge_script' ) ) {
    function loc_req_merge_script() {
        ?>
        <script>
        (function () {
            'use strict';
            function mergeRequirements() {
                var grids = document.querySelectorAll('.skill-grid');
                grids.forEach(function (grid) {
                    var cards = Array.from(grid.querySelectorAll('.skill-card'));
                    if (cards.length < 2) return;
                    var removed = [];
                    for (var i = 0; i < cards.length; i++) {
                        if (removed.indexOf(i) !== -1) continue;
                        var textI = cards[i].textContent.trim();
                        for (var j = 0; j < cards.length; j++) {
                            if (i === j || removed.indexOf(j) !== -1) continue;
                            var textJ = cards[j].textContent.trim();
                            if (textI.indexOf(':') === -1) {
                                if (textJ.toLowerCase().indexOf(textI.toLowerCase() + ': ') === 0) {
                                    setCardText(cards[i], textJ);
                                    cards[j].parentNode.removeChild(cards[j]);
                                    removed.push(j);
                                    break;
                                }
                            }
                            var choiceMatch = textI.match(/^(.*?)(?:\s+of)?:\s*\[Choice\]$/i);
                            if (choiceMatch) {
                                var base = choiceMatch[1].trim().split(/\s+/).shift();
                                if (textJ.toLowerCase().indexOf('[choice]') === -1 &&
                                    textJ.toLowerCase().indexOf(base.toLowerCase() + ':') === 0) {
                                    setCardText(cards[i], textJ);
                                    cards[j].parentNode.removeChild(cards[j]);
                                    removed.push(j);
                                    break;
                                }
                            }
                        }
                    }
                });
            }
            function setCardText(card, text) {
                while (card.firstChild) { card.removeChild(card.firstChild); }
                card.appendChild(document.createTextNode(text));
            }
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', mergeRequirements);
            } else {
                mergeRequirements();
            }
        }());
        </script>
        <?php
    }
    add_action( 'wp_footer', 'loc_req_merge_script' );
}

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
