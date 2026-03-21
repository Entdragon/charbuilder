/**
 * data-editor.js
 * Library of Calabria — front-end admin data editor
 *
 * Requires: jQuery (loaded by WordPress), LOC_DE config (localised via wp_localize_script)
 *
 * SERVER PATH: wp-content/themes/<child-theme>/js/data-editor.js
 */
(function ($) {
    'use strict';

    var cfg   = window.LOC_DE || {};
    var ajax  = cfg.ajaxUrl || '/wp-admin/admin-ajax.php';
    var nonce = cfg.nonce   || '';

    /* ── State ────────────────────────────────────────────────── */

    var state = {
        table   : '',
        page    : 1,
        search  : '',
        total   : 0,
        per     : 25,
        columns : [],
    };

    /* ── DOM refs ─────────────────────────────────────────────── */

    var $tableSelect = $( '#de-table-select' );
    var $searchWrap  = $( '#de-search-wrap' );
    var $search      = $( '#de-search' );
    var $listSection = $( '#de-list-section' );
    var $listHead    = $( '#de-list-head' );
    var $listBody    = $( '#de-list-body' );
    var $recordCount = $( '#de-record-count' );
    var $pageInfo    = $( '#de-page-info' );
    var $prev        = $( '#de-prev' );
    var $next        = $( '#de-next' );
    var $overlay     = $( '#de-form-overlay' );
    var $formTitle   = $( '#de-form-title' );
    var $form        = $( '#de-form' );
    var $fields      = $( '#de-fields' );
    var $childWrap   = $( '#de-child-tables-section' );
    var $childTables = $( '#de-child-tables' );
    var $recordTable = $( '#de-record-table' );
    var $recordId    = $( '#de-record-id' );
    var $saveBtn     = $( '#de-save' );
    var $saving      = $( '#de-saving-indicator' );
    var $toast       = $( '#de-toast' );

    /* ── Utilities ────────────────────────────────────────────── */

    /**
     * Send a wp-admin AJAX request.
     * data may be a plain object or a serialised query string (from $.param).
     * action and nonce are always injected.
     */
    function post( action, data, done, fail ) {
        var payload;
        if ( typeof data === 'string' ) {
            payload = data + '&action=' + encodeURIComponent( action ) + '&nonce=' + encodeURIComponent( nonce );
        } else {
            data.action = action;
            data.nonce  = nonce;
            payload = data;
        }
        $.post( ajax, payload )
            .done( function ( res ) {
                if ( res && res.success ) { done( res.data ); }
                else { fail( ( res && res.data ) || 'Unexpected error.' ); }
            } )
            .fail( function () { fail( 'Network error.' ); } );
    }

    function showToast( msg, type ) {
        $toast.removeClass( 'de-toast-success de-toast-error' )
              .addClass( type === 'error' ? 'de-toast-error' : 'de-toast-success' )
              .text( msg )
              .fadeIn( 200 );
        clearTimeout( showToast._timer );
        showToast._timer = setTimeout( function () { $toast.fadeOut( 400 ); }, 4000 );
    }

    function fieldInputType( col ) {
        var large = [ 'text', 'mediumtext', 'longtext', 'tinytext' ];
        var ints  = [ 'int', 'bigint', 'smallint', 'mediumint', 'tinyint' ];
        if ( large.indexOf( col.data_type ) !== -1 ) { return 'textarea'; }
        if ( ints.indexOf( col.data_type ) !== -1 )  { return 'number'; }
        if ( col.data_type === 'date' )               { return 'date'; }
        return 'text';
    }

    function isLargeType( dataType ) {
        return [ 'text', 'mediumtext', 'longtext', 'tinytext' ].indexOf( dataType ) !== -1;
    }

    function truncate( str, len ) {
        if ( ! str ) { return ''; }
        str = String( str );
        return str.length > len ? str.slice( 0, len ) + '…' : str;
    }

    /* ── Table selector ───────────────────────────────────────── */

    $.each( cfg.tables || {}, function ( key, label ) {
        $tableSelect.append( $( '<option>' ).val( key ).text( label ) );
    } );

    $tableSelect.on( 'change', function () {
        state.table  = $( this ).val();
        state.page   = 1;
        state.search = '';
        $search.val( '' );
        if ( state.table ) {
            $searchWrap.show();
            loadList();
        } else {
            $searchWrap.hide();
            $listSection.hide();
        }
    } );

    /* ── Search (debounced) ───────────────────────────────────── */

    var searchTimer;
    $search.on( 'input', function () {
        clearTimeout( searchTimer );
        searchTimer = setTimeout( function () {
            state.search = $search.val().trim();
            state.page   = 1;
            loadList();
        }, 320 );
    } );

    /* ── Pagination ───────────────────────────────────────────── */

    $prev.on( 'click', function () {
        if ( state.page > 1 ) { state.page--; loadList(); }
    } );

    $next.on( 'click', function () {
        if ( state.page * state.per < state.total ) { state.page++; loadList(); }
    } );

    /* ── Load record list ─────────────────────────────────────── */

    function loadList() {
        $listBody.html( '<tr class="de-loading"><td colspan="20">Loading…</td></tr>' );
        $listSection.show();

        post( 'loc_de_list', {
            table  : state.table,
            page   : state.page,
            search : state.search,
        }, function ( data ) {
            state.total   = data.total;
            state.per     = data.per;
            state.columns = data.columns;
            renderList( data );
        }, function ( err ) {
            $listBody.html( '<tr class="de-loading"><td colspan="20">Error: ' + $( '<span>' ).text( err ).html() + '</td></tr>' );
        } );
    }

    function renderList( data ) {
        var cols  = data.columns;
        var rows  = data.rows;
        var total = data.total;
        var pages = Math.ceil( total / state.per );

        // Header
        $listHead.empty();
        $.each( cols, function ( _, col ) {
            $listHead.append( $( '<th>' ).text( col.column_name ) );
        } );
        $listHead.append( $( '<th>' ).text( '' ) );

        // Rows
        $listBody.empty();
        if ( ! rows || rows.length === 0 ) {
            $listBody.html( '<tr class="de-loading"><td colspan="20">No records found.</td></tr>' );
        } else {
            $.each( rows, function ( _, row ) {
                var $tr = $( '<tr>' );
                $.each( cols, function ( _, col ) {
                    $tr.append( $( '<td>' ).attr( 'title', row[ col.column_name ] || '' )
                                           .text( truncate( row[ col.column_name ], 60 ) ) );
                } );
                var $btn = $( '<button type="button" class="de-edit-btn">Edit</button>' )
                    .on( 'click', function () { loadRecord( row.ct_id ); } );
                $tr.append( $( '<td>' ).append( $btn ) );
                $tr.on( 'click', function ( e ) {
                    if ( ! $( e.target ).hasClass( 'de-edit-btn' ) ) { loadRecord( row.ct_id ); }
                } );
                $listBody.append( $tr );
            } );
        }

        // Pagination UI
        $recordCount.text( total + ' record' + ( total !== 1 ? 's' : '' ) );
        $pageInfo.text( 'Page ' + state.page + ' of ' + ( pages || 1 ) );
        $prev.prop( 'disabled', state.page <= 1 );
        $next.prop( 'disabled', state.page * state.per >= total );
    }

    /* ── Load + render record form ────────────────────────────── */

    function loadRecord( id ) {
        post( 'loc_de_get', { table: state.table, id: id }, function ( data ) {
            renderForm( data );
        }, function ( err ) {
            showToast( 'Error loading record: ' + err, 'error' );
        } );
    }

    function renderForm( data ) {
        var record  = data.record;
        var columns = data.columns;
        var children = data.child_tables || {};

        $recordTable.val( state.table );
        $recordId.val( record.ct_id );
        $formTitle.text( 'Edit #' + record.ct_id + ( record.ct_gifts_name || record.ct_careers_name || record.ct_species_name || record.ct_skills_name || record.ct_equipment_name || record.ct_books_title ? ' — ' + ( record.ct_gifts_name || record.ct_careers_name || record.ct_species_name || record.ct_skills_name || record.ct_equipment_name || record.ct_books_title ) : '' ) );

        // Main fields
        $fields.empty();
        $.each( columns, function ( _, col ) {
            var name  = col.column_name;
            var val   = record[ name ] != null ? record[ name ] : '';
            var itype = fieldInputType( col );
            var wide  = isLargeType( col.data_type );

            var $field = $( '<div class="de-field">' )
                .toggleClass( 'de-field-wide', wide )
                .append( $( '<label>' ).text( name ) );

            if ( name === 'ct_id' ) {
                $field.append( $( '<input type="text" readonly>' ).val( val ) );
            } else if ( itype === 'textarea' ) {
                $field.append( $( '<textarea>' ).attr( 'name', 'fields[' + name + ']' ).val( val ) );
            } else {
                $field.append(
                    $( '<input>' ).attr( { type: itype, name: 'fields[' + name + ']' } ).val( val )
                );
            }

            $fields.append( $field );
        } );

        // Child tables
        $childTables.empty();
        var hasChildren = false;

        $.each( children, function ( childKey, childData ) {
            if ( ! childData.rows || childData.rows.length === 0 ) { return; }
            hasChildren = true;

            var label = childKey === 'gift_sections'
                ? 'Gift Sections'
                : childKey === 'gift_requirements'
                ? 'Gift Requirements'
                : childKey;

            var editable = childData.editable;

            var $section = $( '<div class="de-child-section">' );
            $section.append( $( '<h3>' ).text( label ) );

            var $scroll = $( '<div class="de-child-scroll">' );
            var $tbl    = $( '<table class="de-child-table">' );

            // Header
            var $thead = $( '<thead><tr></tr></thead>' );
            $thead.find( 'tr' ).append( $( '<th>' ).text( 'ct_id' ) );
            $.each( editable, function ( _, col ) {
                $thead.find( 'tr' ).append( $( '<th>' ).text( col ) );
            } );
            $tbl.append( $thead );

            // Rows
            var $tbody = $( '<tbody>' );
            $.each( childData.rows, function ( _, row ) {
                var $tr = $( '<tr>' );
                $tr.append( $( '<td class="de-child-id">' ).text( row.ct_id ) );

                $.each( editable, function ( _, col ) {
                    var val  = row[ col ] != null ? row[ col ] : '';
                    var $inp;
                    if ( col === 'ct_body' || col === 'ct_heading' ) {
                        $inp = $( '<textarea rows="3">' )
                            .attr( 'name', 'child_rows[' + childKey + '][' + row.ct_id + '][' + col + ']' )
                            .val( val );
                    } else if ( col === 'ct_sort' || col === 'ct_req_ref_id' ) {
                        $inp = $( '<input type="number">' )
                            .attr( 'name', 'child_rows[' + childKey + '][' + row.ct_id + '][' + col + ']' )
                            .val( val );
                    } else {
                        $inp = $( '<input type="text">' )
                            .attr( 'name', 'child_rows[' + childKey + '][' + row.ct_id + '][' + col + ']' )
                            .val( val );
                    }
                    $tr.append( $( '<td>' ).append( $inp ) );
                } );

                $tbody.append( $tr );
            } );

            $tbl.append( $tbody );
            $scroll.append( $tbl );
            $section.append( $scroll );
            $childTables.append( $section );
        } );

        $childWrap.toggle( hasChildren );

        $overlay.fadeIn( 180 );
        $overlay.scrollTop( 0 );
    }

    /* ── Close / cancel form ──────────────────────────────────── */

    function closeForm() {
        $overlay.fadeOut( 180 );
    }

    $( '#de-form-close, #de-cancel' ).on( 'click', closeForm );

    $overlay.on( 'click', function ( e ) {
        if ( $( e.target ).is( '#de-form-overlay' ) ) { closeForm(); }
    } );

    $( document ).on( 'keydown', function ( e ) {
        if ( e.key === 'Escape' ) { closeForm(); }
    } );

    /* ── Save ─────────────────────────────────────────────────── */

    $form.on( 'submit', function ( e ) {
        e.preventDefault();

        $saveBtn.prop( 'disabled', true );
        $saving.show();

        // Collect serialised form data + table + id
        var formData = $form.serializeArray();
        formData.push( { name: 'table', value: $recordTable.val() } );
        formData.push( { name: 'id',    value: $recordId.val()    } );

        post( 'loc_de_save', $.param( formData ), function ( data ) {
            $saveBtn.prop( 'disabled', false );
            $saving.hide();
            showToast( data.message || 'Saved.', 'success' );
            closeForm();
            loadList();
        }, function ( err ) {
            $saveBtn.prop( 'disabled', false );
            $saving.hide();
            showToast( 'Save failed: ' + err, 'error' );
        } );
    } );

})(jQuery);
