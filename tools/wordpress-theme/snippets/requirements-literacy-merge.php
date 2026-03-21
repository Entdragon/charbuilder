<?php
/**
 * requirements-literacy-merge.php
 * Library of Calabria — child theme snippet
 *
 * Merges paired generic/specific requirement boxes on gift pages.
 * Uses an output buffer (not the_content filter) so it catches content
 * rendered by CustomTables shortcodes or templates at any hook.
 *
 * Patterns handled:
 *   "Literacy"          +  "Literacy: Zho-ngwén"   → "Literacy: Zho-ngwén"
 *   "Language"          +  "Language: Zhonggese"   → "Language: Zhonggese"
 *   "Mystic"            +  "Mystic: Elementalism"  → "Mystic: Elementalism"
 *   "Piety"             +  "Piety: Solari"         → "Piety: Solari"
 *   "Mystic of: [Choice]"  +  "Mystic: Elementalism"  → "Mystic: Elementalism"
 *   (any "X: [Choice]"  +  "X: concrete value")
 *
 * INSTALLATION
 * Copy the functions below into the child theme's functions.php (no <?php tag).
 *
 * SERVER PATH: wp-content/themes/<child-theme>/functions.php (append)
 */

if ( ! function_exists( 'loc_req_ob_start' ) ) {
    /**
     * Start output buffering on any page whose URL contains 'gift', 'gift' etc.
     * The buffer callback runs after the full page is rendered.
     */
    function loc_req_ob_start() {
        $uri = isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : '';
        // Run on any URL that could be a gift detail page.
        // Adjust the needle if your gift pages live under a different path.
        if ( stripos( $uri, 'gift' ) === false ) {
            return;
        }
        ob_start( 'loc_req_ob_callback' );
    }
    add_action( 'template_redirect', 'loc_req_ob_start' );
}

if ( ! function_exists( 'loc_req_ob_callback' ) ) {
    /**
     * Output buffer callback: receives the complete page HTML, runs the merge,
     * and returns the (possibly modified) HTML.
     *
     * @param  string $html  Full page HTML.
     * @return string
     */
    function loc_req_ob_callback( $html ) {
        // Fast-exit: if none of the trigger words appear, skip DOM work entirely.
        $triggers = array( 'Literacy', 'Language', 'Mystic', 'Piety', '[Choice]' );
        $found = false;
        foreach ( $triggers as $t ) {
            if ( stripos( $html, $t ) !== false ) {
                $found = true;
                break;
            }
        }
        if ( ! $found ) {
            return $html;
        }

        return loc_req_dom_merge( $html );
    }
}

if ( ! function_exists( 'loc_req_dom_merge' ) ) {
    /**
     * Parse HTML with DOMDocument, find sibling requirement elements that form
     * a generic/specific pair, and collapse them into one.
     *
     * Two merge patterns:
     *
     *   Pattern 1 — bare generic + specific
     *     Generic:  "Literacy"            (no colon)
     *     Specific: "Literacy: Zho-ngwén" (same word + ": " prefix)
     *     Result:   generic element text becomes "Literacy: Zho-ngwén"; specific removed.
     *
     *   Pattern 2 — [Choice] placeholder + specific
     *     Generic:  "Mystic of: [Choice]"   (ends with [Choice])
     *     Specific: "Mystic: Elementalism"   (same base word + ": ", no [Choice])
     *     Result:   generic becomes "Mystic: Elementalism"; specific removed.
     *
     * Only sibling elements (same immediate parent) are ever compared, and only
     * those whose total text length is 1–120 chars, so large container divs are
     * never modified.
     *
     * @param  string $html  Full or partial HTML.
     * @return string
     */
    function loc_req_dom_merge( $html ) {
        $dom = new DOMDocument( '1.0', 'UTF-8' );
        libxml_use_internal_errors( true );
        $dom->loadHTML( $html );
        libxml_clear_errors();

        $xpath = new DOMXPath( $dom );

        // Gather all elements with short total text (requirement-box candidates).
        $candidates = $xpath->query(
            '//*[string-length(normalize-space(.)) >= 1 and string-length(normalize-space(.)) <= 120]'
        );

        // Group by parent.
        $by_parent = array();
        foreach ( $candidates as $node ) {
            if ( $node->parentNode === null ) {
                continue;
            }
            $text = trim( $node->textContent );
            if ( $text === '' ) {
                continue;
            }
            $key = spl_object_hash( $node->parentNode );
            $by_parent[ $key ][] = array( 'node' => $node, 'text' => $text );
        }

        $changed = false;
        $removed = new SplObjectStorage();

        foreach ( $by_parent as $group ) {
            $n = count( $group );
            if ( $n < 2 ) {
                continue;
            }

            for ( $i = 0; $i < $n; $i++ ) {
                $item = $group[ $i ];
                if ( $removed->contains( $item['node'] ) || $item['node']->parentNode === null ) {
                    continue;
                }
                $text = $item['text'];

                // ── Pattern 1: "X" with no colon ────────────────────────────────
                if ( strpos( $text, ':' ) === false ) {
                    for ( $j = 0; $j < $n; $j++ ) {
                        if ( $j === $i ) {
                            continue;
                        }
                        $other = $group[ $j ];
                        if ( $removed->contains( $other['node'] ) || $other['node']->parentNode === null ) {
                            continue;
                        }
                        if ( 0 === stripos( $other['text'], $text . ': ' ) ) {
                            loc_req_set_text( $dom, $item['node'], $other['text'] );
                            $other['node']->parentNode->removeChild( $other['node'] );
                            $removed->attach( $other['node'] );
                            $changed = true;
                            break;
                        }
                    }
                    continue;
                }

                // ── Pattern 2: "X: [Choice]" placeholder ────────────────────────
                if ( preg_match( '/^(.+?):\s*\[Choice\]$/i', $text, $m ) ) {
                    $prefix = trim( $m[1] );
                    for ( $j = 0; $j < $n; $j++ ) {
                        if ( $j === $i ) {
                            continue;
                        }
                        $other = $group[ $j ];
                        if ( $removed->contains( $other['node'] ) || $other['node']->parentNode === null ) {
                            continue;
                        }
                        $otext = $other['text'];
                        if ( 0 === stripos( $otext, $prefix . ': ' )
                            && false === stripos( $otext, '[Choice]' ) ) {
                            loc_req_set_text( $dom, $item['node'], $otext );
                            $other['node']->parentNode->removeChild( $other['node'] );
                            $removed->attach( $other['node'] );
                            $changed = true;
                            break;
                        }
                    }
                }
            }
        }

        if ( ! $changed ) {
            return $html;
        }

        return $dom->saveHTML();
    }
}

if ( ! function_exists( 'loc_req_set_text' ) ) {
    /**
     * Replace all child nodes of $node with a single plain-text node.
     * This strips any <em>/<strong> wrappers while keeping the element itself.
     *
     * @param DOMDocument $dom
     * @param DOMNode     $node
     * @param string      $text
     */
    function loc_req_set_text( $dom, $node, $text ) {
        while ( $node->firstChild ) {
            $node->removeChild( $node->firstChild );
        }
        $node->appendChild( $dom->createTextNode( $text ) );
    }
}
