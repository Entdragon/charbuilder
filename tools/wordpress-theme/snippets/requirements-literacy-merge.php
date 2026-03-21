<?php
/**
 * requirements-literacy-merge.php
 * Library of Calabria — child theme snippet
 *
 * PURPOSE
 * -------
 * Merges duplicate literacy requirement lines on gift detail pages.
 *
 * Some gifts require a specific literacy (e.g. Zhongwén). The CustomTables
 * layout renders both a generic "Literacy" item (from the gift_requirements
 * gift_ref row) and a specific "Literacy: Zhongwén" item (from
 * ct_gifts_requires_special). This filter collapses them into one:
 *
 *   Before:  "Literacy"            (separate line)
 *            "Literacy: Zhongwén"  (separate line)
 *
 *   After:   "Literacy: Zhongwén"  (single merged line)
 *
 * INSTALLATION
 * ------------
 * Copy the two functions below into the child theme's functions.php.
 * No JavaScript file is needed.
 *
 * SERVER PATH
 * -----------
 * wp-content/themes/<child-theme>/functions.php  (append the functions below)
 *
 * VERIFICATION
 * ------------
 * 1. Visit a gift page that requires Literacy in a specific language
 *    (e.g. a Zhongwén-literacy gift).
 * 2. Confirm only one literacy line appears (e.g. "Literacy: Zhongwén").
 * 3. Confirm non-literacy requirements are unchanged.
 * 4. Test a gift that only has the generic "Literacy" requirement (no language):
 *    that line should remain as-is (no merging, nothing removed).
 */

if ( ! function_exists( 'loc_merge_literacy_requirements' ) ) {
    /**
     * Merge duplicate literacy requirement entries in rendered gift-page HTML.
     *
     * Runs as a the_content filter at priority 20 (after CT shortcodes at 11).
     * Only processes pages whose URL path contains '/gifts/' — adjust the
     * $uri check below if your gift pages live under a different path.
     *
     * @param  string $content  Rendered page content (fully-expanded shortcodes).
     * @return string           Content with literacy items merged.
     */
    function loc_merge_literacy_requirements( $content ) {
        // Fast-exit: skip if no literacy content at all.
        if ( stripos( $content, 'literacy' ) === false ) {
            return $content;
        }

        // Only run on gift detail pages.
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        if ( stripos( $uri, '/gifts/' ) === false ) {
            return $content;
        }

        return loc_dom_merge_literacy( $content );
    }
    add_filter( 'the_content', 'loc_merge_literacy_requirements', 20 );
}

if ( ! function_exists( 'loc_dom_merge_literacy' ) ) {
    /**
     * Parse HTML with DOMDocument, find sibling literacy elements in the same
     * parent container, and merge them.
     *
     * Algorithm:
     *  1. Find every leaf element whose text content is exactly "Literacy".
     *  2. For each, search its siblings in the same parent for a specific
     *     literacy item: "Literacy: X", "Must be literate in X", "Literate in X".
     *  3. If a specific sibling is found, update the generic element to
     *     "Literacy: X" and remove the specific sibling.
     *
     * @param  string $content  Raw HTML string.
     * @return string           Modified HTML string.
     */
    function loc_dom_merge_literacy( $content ) {
        $dom = new DOMDocument( '1.0', 'UTF-8' );
        libxml_use_internal_errors( true );

        // Wrap in a known root so loadHTML gives us a single body to iterate.
        $dom->loadHTML(
            '<html><head><meta charset="utf-8"></head><body>' . $content . '</body></html>',
            LIBXML_NOBLANKS
        );
        libxml_clear_errors();

        $xpath = new DOMXPath( $dom );

        /*
         * Find leaf elements whose normalised text is exactly "Literacy".
         * "Leaf" = no child elements (child::*), so we don't match headings
         * or labels that contain "Literacy" alongside other child nodes.
         */
        $generic_nodes = $xpath->query(
            '//*[not(child::*) and normalize-space(text())="Literacy"]'
        );

        if ( $generic_nodes->length === 0 ) {
            return $content;
        }

        $changed = false;

        foreach ( $generic_nodes as $generic ) {
            $parent = $generic->parentNode;
            if ( ! $parent ) {
                continue;
            }

            $specific_node = null;
            $language      = null;

            // Check every sibling in the same parent for a specific literacy pattern.
            foreach ( $parent->childNodes as $sibling ) {
                if ( $sibling->isSameNode( $generic ) ) {
                    continue;
                }
                if ( $sibling->nodeType !== XML_ELEMENT_NODE ) {
                    continue;
                }

                $text = trim( $sibling->textContent );

                if ( preg_match( '/^literacy:\s*(.+)$/i', $text, $m ) ) {
                    $language      = trim( $m[1] );
                    $specific_node = $sibling;
                    break;
                }
                if ( preg_match( '/^(?:must be literate|literate) in\s+(.+)$/i', $text, $m ) ) {
                    $language      = trim( $m[1] );
                    $specific_node = $sibling;
                    break;
                }
            }

            if ( $language && $specific_node ) {
                // Update generic to the merged value.
                $generic->textContent = 'Literacy: ' . $language;
                // Remove the now-redundant specific sibling.
                $parent->removeChild( $specific_node );
                $changed = true;
            }
        }

        if ( ! $changed ) {
            return $content;
        }

        // Serialise only the body's children — strips the html/head/body wrapper.
        $body   = $dom->getElementsByTagName( 'body' )->item( 0 );
        $output = '';
        foreach ( $body->childNodes as $child ) {
            $output .= $dom->saveHTML( $child );
        }

        return $output;
    }
}
