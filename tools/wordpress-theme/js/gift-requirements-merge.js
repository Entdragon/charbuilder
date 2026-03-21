/**
 * gift-requirements-merge.js
 * Library of Calabria — child theme
 *
 * PURPOSE
 * -------
 * On gift detail pages, the requirements list can show two separate lines
 * for a literacy requirement that should be one:
 *
 *   Literacy                      ← generic (from gift_requirements gift_ref row)
 *   Literacy: Zhongwén            ← specific (from ct_gifts_requires_special)
 *
 * This script merges them into a single line: "Literacy: Zhongwén"
 *
 * SETUP
 * -----
 * 1. Find the correct CSS selector for requirement list items on your gift pages.
 *    Enable debug mode (DEBUG = true) and check the browser console to see which
 *    selectors are being found and which items are matched.
 *
 * 2. Update REQUIREMENT_ITEM_SELECTOR below to match your CT layout's HTML.
 *    Common CustomTables patterns:
 *      '.ct_record td'          — table-based CT layouts
 *      '.ct_record li'          — list-based CT layouts
 *      '.customtables li'       — another common wrapper
 *      '.entry-content li'      — if requirements are in the main content area
 *
 * 3. Once confirmed working, set DEBUG = false to silence console output.
 *
 * ENQUEUED BY
 * -----------
 * tools/wordpress-theme/snippets/requirements-literacy-merge.php → functions.php
 *
 * SERVER PATH
 * -----------
 * wp-content/themes/loc-child/js/gift-requirements-merge.js
 */

(function ($) {
    'use strict';

    /* ---- Configuration ---- */

    var DEBUG = true;   // set false once selectors confirmed working

    /**
     * CSS selector that matches each individual requirement item element.
     * Each matched element should contain the text of one requirement (e.g. "Literacy").
     * Adjust this to match your CustomTables layout HTML.
     */
    var REQUIREMENT_ITEM_SELECTOR = 'li, td, .ct-field-value, .ct_record_field, .req-item';

    /**
     * Optional: restrict the search to a container that wraps the requirements
     * section. Leave as '' to search the whole page.
     */
    var REQUIREMENTS_CONTAINER_SELECTOR = '';

    /* ---- End configuration ---- */

    var log = DEBUG ? function () { console.log('[LOC-req-merge]', ...arguments); } : function () {};

    /**
     * Extract the literacy language from a specific literacy string.
     *
     * Handles patterns:
     *   "Literacy: Zhongwén"          → "Zhongwén"
     *   "Must be literate in Zhongwén" → "Zhongwén"
     *   "Literate in Zhongwén"         → "Zhongwén"
     */
    function extractLiteracyLanguage(text) {
        var m;
        m = text.match(/^literacy:\s*(.+)$/i);
        if (m) { return m[1].trim(); }
        m = text.match(/^must be literate in\s+(.+)$/i);
        if (m) { return m[1].trim(); }
        m = text.match(/^literate in\s+(.+)$/i);
        if (m) { return m[1].trim(); }
        return null;
    }

    /**
     * Merge literacy requirements in a given container element.
     * Called once per container found on the page.
     */
    function mergeInContainer($container, containerDesc) {
        var $items = $container.find(REQUIREMENT_ITEM_SELECTOR).filter(function () {
            var text = $.trim($(this).text());
            return text.length > 0 && text.length < 160;
        });

        if ($items.length === 0) {
            log('Container found but no items matched REQUIREMENT_ITEM_SELECTOR:', containerDesc, REQUIREMENT_ITEM_SELECTOR);
            return;
        }

        log('Container:', containerDesc, '— checking', $items.length, 'items');

        // Separate generic ("Literacy") from specific ("Literacy: X", "Must be literate in X")
        var $generic  = [];
        var $specific = [];

        $items.each(function () {
            var text = $.trim($(this).text());
            var lang = extractLiteracyLanguage(text);
            if (lang) {
                log('  Specific literacy item found:', text);
                $specific.push({ el: this, lang: lang });
            } else if (/^literacy\.?$/i.test(text)) {
                log('  Generic literacy item found:', text);
                $generic.push(this);
            }
        });

        if ($generic.length === 0) {
            log('  No generic "Literacy" item — nothing to merge.');
            return;
        }
        if ($specific.length === 0) {
            log('  No specific literacy item — nothing to merge.');
            return;
        }

        // Merge: update the first generic element with the language, remove the rest.
        var firstGeneric = $generic[0];
        var specificLangs = $specific.map(function (s) { return s.lang; });
        var mergedLabel = 'Literacy: ' + specificLangs.join(', ');

        log('  Merging → "' + mergedLabel + '"');

        // Update generic element text, preserve any wrapping tags inside it (e.g. <a>)
        if ($(firstGeneric).children().length === 0) {
            // Plain text node — safe to replace directly
            $(firstGeneric).text(mergedLabel);
        } else {
            // Has child elements — update just the first text node
            firstGeneric.childNodes.forEach(function (node) {
                if (node.nodeType === Node.TEXT_NODE && $.trim(node.textContent) !== '') {
                    node.textContent = mergedLabel;
                }
            });
        }

        // Remove extra generic items (shouldn't usually exist, but guard anyway)
        for (var i = 1; i < $generic.length; i++) {
            log('  Removing extra generic item');
            $($generic[i]).remove();
        }

        // Remove all specific items (they are now covered by the merged generic)
        $specific.forEach(function (s) {
            log('  Removing specific item:', $.trim($(s.el).text()));
            $(s.el).remove();
        });
    }

    function run() {
        var $page = REQUIREMENTS_CONTAINER_SELECTOR
            ? $(REQUIREMENTS_CONTAINER_SELECTOR)
            : $('body');

        if ($page.length === 0) {
            log('REQUIREMENTS_CONTAINER_SELECTOR not found on page — skipping.');
            return;
        }

        // Try to find the natural requirements container automatically.
        // Adjust REQUIREMENTS_CONTAINER_SELECTOR if this is picking up the wrong section.
        var $containers = REQUIREMENTS_CONTAINER_SELECTOR
            ? $page
            : $page.find('[class*="requirement"], [class*="requires"], .ct-record, .entry-content').filter(':first');

        if ($containers.length === 0) {
            log('No container found. Falling back to <body>.');
            $containers = $page;
        }

        $containers.each(function (i) {
            mergeInContainer($(this), ($(this).attr('class') || $(this).prop('tagName') || 'element') + '[' + i + ']');
        });
    }

    $(document).ready(function () {
        run();
    });

})(jQuery);
