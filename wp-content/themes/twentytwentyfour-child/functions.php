<?php

namespace CharacterGeneratorDev {

/**
 * Twenty Twenty-Four Child functions.php
 * (brace-count: each callback fn has its closing braces)
 */

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
});

// 2) Enqueue child stylesheet and auth script (conditionally)
add_action( 'wp_enqueue_scripts', function() {
    wp_enqueue_style(
        'twentytwentyfour-child-style',
        get_stylesheet_uri(),
        [],
        wp_get_theme()->get( 'Version' )
    );

    // Auth script: only load on login or register pages
    if ( is_page( ['login', 'register'] ) ) {
        wp_enqueue_script(
            'cg-auth',
            get_stylesheet_directory_uri() . '/js/cg-auth.js',
            ['jquery'],
            null,
            true
        );

        wp_localize_script( 'cg-auth', 'CG_Auth', [
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'cg_nonce' )
        ]);
    }
} ); // <-- closes the add_action callback

// 3) Rewrite rules for detail pages
add_action( 'init', function() {
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

// 4) Allow custom `slug` query var
add_filter( 'query_vars', function( $vars ) {
    $vars[] = 'slug';
    return $vars;
} ); // <-- closes the filter callback

// 5) Friendly‐URL fallback redirect
add_action( 'template_redirect', function() {
    if ( is_404() ) {
        global $wpdb;
        $uri = trim( $_SERVER['REQUEST_URI'], '/' );
        if ( ! $uri ) return;

        $lookup_map = [
            'species-habitat'   => [ 'DcVnchxg4_customtables_table_habitat',        'ct_slug' ],
            'species-diet'      => [ 'DcVnchxg4_customtables_table_diet',           'ct_slug' ],
            // …rest of your mappings…
            'skill-descriptor'  => [ 'DcVnchxg4_customtables_table_skillsdescriptors', 'ct_slug' ],
        ];

        foreach ( $lookup_map as $prefix => list( $table, $field ) ) {
            $count = (int) $wpdb->get_var( $wpdb->prepare(
                "SELECT COUNT(*) FROM {$table} WHERE {$field} = %s",
                $uri
            ) );
            if ( $count > 0 ) {
                wp_redirect( home_url( "/{$prefix}/{$uri}/" ), 301 );
                exit;
            }
        }
    }
} ); // <-- closes the template_redirect callback

} // namespace CharacterGeneratorDev
