<?php
/**
 * Template Name: Data Editor
 *
 * Library of Calabria — admin-only front-end CustomTables data editor.
 * Lets site administrators view and edit entries in the key CustomTables
 * tables without going into the WordPress plugin backend.
 *
 * PROTECTED: current_user_can('manage_options') — redirects all others.
 *
 * SERVER PATH
 * -----------
 * wp-content/themes/<child-theme>/page-data-editor.php
 *
 * SETUP
 * -----
 * 1. Upload this file to the child theme directory.
 * 2. Add data-editor-ajax.php contents to child theme functions.php.
 * 3. Upload css/data-editor.css and js/data-editor.js to the child theme.
 * 4. In WordPress Admin → Pages → Add New:
 *      Title: anything (e.g. "Data Editor")
 *      Permalink: recommend /data-editor/ (keep it obscure)
 *      Template: "Data Editor"
 *      Status: Published (the admin check is the security, not Draft status)
 * 5. Visit the page while logged in as admin.
 */

if ( ! current_user_can( 'manage_options' ) ) {
    wp_die( 'Access denied. WordPress administrator login required.', 'Access Denied', [ 'response' => 403 ] );
}

wp_enqueue_style(
    'loc-data-editor',
    get_stylesheet_directory_uri() . '/css/data-editor.css',
    [],
    '1.0.0'
);
wp_enqueue_script(
    'loc-data-editor',
    get_stylesheet_directory_uri() . '/js/data-editor.js',
    [ 'jquery' ],
    '1.0.0',
    true
);
wp_localize_script( 'loc-data-editor', 'LOC_DE', [
    'ajaxUrl' => admin_url( 'admin-ajax.php' ),
    'nonce'   => wp_create_nonce( 'loc_data_editor' ),
    'tables'  => [
        'gifts'     => 'Gifts',
        'careers'   => 'Careers',
        'species'   => 'Species',
        'skills'    => 'Skills',
        'equipment' => 'Equipment',
        'books'     => 'Books',
    ],
] );

get_header();
?>

<div id="loc-data-editor">

    <div class="de-page-header">
        <h1>Library of Calabria — Data Editor</h1>
        <p class="de-admin-badge">Admin only · changes write directly to the database</p>
    </div>

    <div class="de-toolbar">
        <div class="de-toolbar-left">
            <label for="de-table-select" class="de-label">Table:</label>
            <select id="de-table-select">
                <option value="">— select a table —</option>
            </select>
        </div>
        <div class="de-toolbar-right" id="de-search-wrap" style="display:none">
            <input type="search" id="de-search" placeholder="Search…" autocomplete="off">
        </div>
    </div>

    <div class="de-list-section" id="de-list-section" style="display:none">
        <div class="de-list-meta">
            <span id="de-record-count"></span>
            <div class="de-pagination">
                <button id="de-prev" type="button" disabled>‹ Prev</button>
                <span id="de-page-info"></span>
                <button id="de-next" type="button">Next ›</button>
            </div>
        </div>
        <div class="de-table-scroll">
            <table id="de-list-table">
                <thead><tr id="de-list-head"></tr></thead>
                <tbody id="de-list-body"></tbody>
            </table>
        </div>
    </div>

    <div class="de-form-overlay" id="de-form-overlay" style="display:none">
        <div class="de-form-panel">
            <div class="de-form-panel-header">
                <h2 id="de-form-title">Edit Record</h2>
                <button type="button" id="de-form-close" class="de-btn-icon" aria-label="Close">✕</button>
            </div>
            <form id="de-form" novalidate>
                <input type="hidden" id="de-record-table">
                <input type="hidden" id="de-record-id">

                <div class="de-fields-section">
                    <h3 class="de-section-label">Main fields</h3>
                    <div id="de-fields" class="de-fields-grid"></div>
                </div>

                <div id="de-child-tables-section" style="display:none">
                    <div id="de-child-tables"></div>
                </div>

                <div class="de-form-actions">
                    <button type="submit" id="de-save" class="de-btn de-btn-primary">Save Changes</button>
                    <button type="button" id="de-cancel" class="de-btn">Cancel</button>
                    <span id="de-saving-indicator" style="display:none">Saving…</span>
                </div>
            </form>
        </div>
    </div>

    <div id="de-toast" role="alert" aria-live="polite" style="display:none"></div>

</div>

<?php get_footer(); ?>
