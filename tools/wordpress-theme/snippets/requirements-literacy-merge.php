<?php
/**
 * requirements-literacy-merge.php
 * Library of Calabria — child theme snippet
 *
 * Merges paired generic/specific requirement boxes on gift pages via JavaScript.
 * Runs client-side so it is unaffected by page caching plugins.
 *
 * Patterns handled (all case-insensitive):
 *   "Literacy"           +  "Literacy: Zho-ngwén"  → "Literacy: Zho-ngwén"
 *   "Language"           +  "Language: Zhonggese"  → "Language: Zhonggese"
 *   "Mystic"             +  "Mystic: Elementalism" → "Mystic: Elementalism"
 *   "Piety"              +  "Piety: Solari"        → "Piety: Solari"
 *   "Mystic of: [Choice]"  +  "Mystic: Elementalism" → "Mystic: Elementalism"
 *   (any "X of: [Choice]" or "X: [Choice]" paired with "X: concrete value")
 *
 * INSTALLATION
 * Copy the function below into the child theme's functions.php (no <?php tag).
 *
 * SERVER PATH: wp-content/themes/<child-theme>/functions.php (append)
 */

if ( ! function_exists( 'loc_req_merge_script' ) ) {
    function loc_req_merge_script() {
        ?>
        <script>
        (function () {
            'use strict';

            function mergeRequirements() {
                // Find every requirements grid on the page.
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

                            // Pattern 1 — "X" (no colon) paired with "X: Y"
                            if (textI.indexOf(':') === -1) {
                                if (textJ.toLowerCase().indexOf(textI.toLowerCase() + ': ') === 0) {
                                    setCardText(cards[i], textJ);
                                    cards[j].parentNode.removeChild(cards[j]);
                                    removed.push(j);
                                    break;
                                }
                            }

                            // Pattern 2 — "X: [Choice]" or "X of: [Choice]" paired with "X: Y"
                            var choiceMatch = textI.match(/^(.*?)(?:\s+of)?:\s*\[Choice\]$/i);
                            if (choiceMatch) {
                                // Try the base word (e.g. "Mystic" from "Mystic of: [Choice]")
                                var base = choiceMatch[1].trim().split(/\s+/).shift();
                                if (
                                    textJ.toLowerCase().indexOf('[choice]') === -1 &&
                                    textJ.toLowerCase().indexOf(base.toLowerCase() + ':') === 0
                                ) {
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

            /**
             * Replace a card's visible text without removing its element.
             * Strips child nodes (links, <em>, etc.) and sets plain text.
             */
            function setCardText(card, text) {
                while (card.firstChild) {
                    card.removeChild(card.firstChild);
                }
                card.appendChild(document.createTextNode(text));
            }

            // Run after the DOM is ready.
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
