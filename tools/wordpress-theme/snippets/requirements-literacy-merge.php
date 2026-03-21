<?php
/**
 * requirements-literacy-merge.php
 * Library of Calabria — child theme snippet
 *
 * Merges paired generic/specific requirement boxes on gift pages, e.g.:
 *
 *   "Literacy"  +  "Literacy: Zho-ngwén"   → "Literacy: Zho-ngwén"
 *   "Language"  +  "Language: Zhonggese"   → "Language: Zhonggese"
 *   "Mystic of: [Choice]"  +  "Mystic: Elementalism"  → "Mystic: Elementalism"
 *
 * INSTALLATION
 * Copy the two functions below into the child theme's functions.php (no <?php tag).
 *
 * SERVER PATH: wp-content/themes/<child-theme>/functions.php (append)
 */

if ( ! function_exists( 'loc_merge_gift_requirements' ) ) {
    function loc_merge_gift_requirements( $content ) {
        // Fast-exit: skip pages with none of the relevant keywords.
        if ( false === stripos( $content, 'Literacy' )
            && false === stripos( $content, 'Language' )
            && false === stripos( $content, '[Choice]' ) ) {
            return $content;
        }
        return loc_dom_merge_gift_pairs( $content );
    }
    add_filter( 'the_content', 'loc_merge_gift_requirements', 20 );
}

if ( ! function_exists( 'loc_dom_merge_gift_pairs' ) ) {
    /**
     * Parse the rendered HTML with DOMDocument and merge paired requirement elements
     * that share the same parent container.
     *
     * Two merge patterns are handled:
     *
     *   Pattern 1 — generic + specific
     *     Generic element text:  "Literacy"         (no colon)
     *     Specific sibling text: "Literacy: Zho-ngwén"  (starts with generic + ": ")
     *     Result: generic element becomes "Literacy: Zho-ngwén", specific removed.
     *
     *   Pattern 2 — placeholder + specific
     *     Generic element text:  "Mystic of: [Choice]"  (contains "[Choice]")
     *     Specific sibling text: "Mystic: Elementalism"  (starts with same prefix, no "[Choice]")
     *     Result: generic element becomes "Mystic: Elementalism", specific removed.
     *
     * Only elements whose total text content is 1–120 characters are considered,
     * which excludes large container elements while including individual requirement boxes.
     * The match always requires both elements to share the same immediate parent, so
     * content in unrelated page sections is never altered.
     *
     * @param  string $content  Rendered HTML from WordPress/CustomTables.
     * @return string           Modified HTML with merged pairs.
     */
    function loc_dom_merge_gift_pairs( $content ) {
        $dom = new DOMDocument( '1.0', 'UTF-8' );
        libxml_use_internal_errors( true );
        $dom->loadHTML(
            '<html><head><meta charset="utf-8"></head><body>' . $content . '</body></html>',
            LIBXML_NOBLANKS
        );
        libxml_clear_errors();

        $xpath = new DOMXPath( $dom );

        /*
         * Select all elements whose total text length is between 1 and 120 chars.
         * Using normalize-space(.) (dot = current node including all descendants)
         * means we correctly read text even when it is wrapped in <em>, <strong>, etc.
         */
        $candidates = $xpath->query(
            '//*[string-length(normalize-space(.)) >= 1 and string-length(normalize-space(.)) <= 120]'
        );

        // Group candidate elements by their immediate parent.
        $by_parent = [];
        foreach ( $candidates as $node ) {
            if ( $node->parentNode === null ) {
                continue;
            }
            $text = trim( $node->textContent );
            if ( $text === '' ) {
                continue;
            }
            $by_parent[ spl_object_hash( $node->parentNode ) ][] = [
                'node' => $node,
                'text' => $text,
            ];
        }

        $changed = false;
        $removed = new SplObjectStorage();

        foreach ( $by_parent as $group ) {
            if ( count( $group ) < 2 ) {
                continue;
            }

            $n = count( $group );
            for ( $i = 0; $i < $n; $i++ ) {
                $item = $group[ $i ];

                // Skip nodes already removed from the DOM.
                if ( $removed->contains( $item['node'] ) || $item['node']->parentNode === null ) {
                    continue;
                }

                $text = $item['text'];

                // ── Pattern 1: "X" (no colon) — look for sibling "X: Y" ──────────
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
                            loc_replace_element_text( $dom, $item['node'], $other['text'] );
                            $other['node']->parentNode->removeChild( $other['node'] );
                            $removed->attach( $other['node'] );
                            $changed = true;
                            break;
                        }
                    }
                    continue;
                }

                // ── Pattern 2: "X: [Choice]" — look for sibling "X: Y" (concrete) ─
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
                            loc_replace_element_text( $dom, $item['node'], $otext );
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
            return $content;
        }

        // Serialise only the body children — strips the html/head/body wrapper.
        $body   = $dom->getElementsByTagName( 'body' )->item( 0 );
        $output = '';
        foreach ( $body->childNodes as $child ) {
            $output .= $dom->saveHTML( $child );
        }
        return $output;
    }
}

if ( ! function_exists( 'loc_replace_element_text' ) ) {
    /**
     * Replace all child nodes of $node with a single plain text node.
     * This removes any <em>/<strong> wrappers while keeping the element itself.
     *
     * @param DOMDocument $dom
     * @param DOMElement  $node
     * @param string      $text
     */
    function loc_replace_element_text( $dom, $node, $text ) {
        while ( $node->firstChild ) {
            $node->removeChild( $node->firstChild );
        }
        $node->appendChild( $dom->createTextNode( $text ) );
    }
}
