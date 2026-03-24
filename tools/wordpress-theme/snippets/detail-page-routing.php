<?php
/**
 * Detail Page Routing
 *
 * Registers the `slug` query variable and adds rewrite rules for the three
 * detail page types:
 *
 *   /gift/{slug}/     → Gift Detail Page template
 *   /career/{slug}/   → Career Detail Page template
 *   /species/{slug}/  → Species Detail Page template
 *
 * INSTALLATION
 * ────────────
 * 1. Paste the entire contents of this file into your child theme's
 *    functions.php (at the bottom, before the closing ?> if one exists).
 *
 * 2. In WordPress Admin → Settings → Permalinks, click Save Changes.
 *    This flushes the rewrite cache so the new rules take effect.
 *    You must do this once after pasting — nothing will work until you do.
 *
 * PREREQUISITES
 * ─────────────
 * You need three WordPress pages with slugs and templates matching:
 *
 *   Page slug  │ Page template to assign
 *   ───────────┼─────────────────────────
 *   gift       │ Gift Detail Page
 *   career     │ Career Detail Page
 *   species    │ Species Detail Page
 *
 * The page slug is the last part of the page's permalink — e.g. the page
 * whose URL is /gift/ has slug "gift". If your parent page slugs differ
 * from the above, update LOC_GIFT_BASE, LOC_CAREER_BASE and LOC_SPECIES_BASE
 * below to match.
 *
 * HOW IT WORKS
 * ────────────
 * WordPress normally intercepts /gift/agility/ and tries to find a child
 * page called "agility" under the "gift" page. These rewrite rules
 * short-circuit that: /gift/agility/ is routed to the gift page with
 * ?slug=agility appended, and get_query_var('slug') returns "agility".
 */

define( 'LOC_GIFT_BASE',    'gift' );
define( 'LOC_CAREER_BASE',  'career' );
define( 'LOC_SPECIES_BASE', 'species' );

/* ── 1. Register `slug` as a recognised query variable ──────────────────── */

add_filter( 'query_vars', function ( $vars ) {
    $vars[] = 'slug';
    return $vars;
} );

/* ── 2. Add rewrite rules ───────────────────────────────────────────────── */

add_action( 'init', function () {
    $gift    = LOC_GIFT_BASE;
    $career  = LOC_CAREER_BASE;
    $species = LOC_SPECIES_BASE;

    // /gift/{slug}/
    add_rewrite_rule(
        '^' . $gift . '/([^/]+)/?$',
        'index.php?pagename=' . $gift . '&slug=$matches[1]',
        'top'
    );

    // /career/{slug}/
    add_rewrite_rule(
        '^' . $career . '/([^/]+)/?$',
        'index.php?pagename=' . $career . '&slug=$matches[1]',
        'top'
    );

    // /species/{slug}/
    add_rewrite_rule(
        '^' . $species . '/([^/]+)/?$',
        'index.php?pagename=' . $species . '&slug=$matches[1]',
        'top'
    );
} );

/* ── 3. Prevent the detail page itself from showing as a 404 ────────────── */

/**
 * When WordPress loads /gift/agility/ it finds the "gift" page, but then
 * its 404 logic may fire because it thinks "agility" is a missing child page.
 * This filter tells WordPress: if we matched one of our detail-page routes
 * (i.e. we have a slug query var AND we're on the detail page), treat it as
 * a valid singular page instead of a 404.
 */
add_action( 'wp', function () {
    if ( ! get_query_var( 'slug' ) ) {
        return;
    }

    $bases = array( LOC_GIFT_BASE, LOC_CAREER_BASE, LOC_SPECIES_BASE );

    global $wp_query;
    $pagename = get_query_var( 'pagename' );

    if ( in_array( $pagename, $bases, true ) && $wp_query->is_404() ) {
        $wp_query->is_404      = false;
        $wp_query->is_page     = true;
        $wp_query->is_singular = true;
        status_header( 200 );
    }
} );
