<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once __DIR__ . '/ajax-gifts.php';

// Local Knowledge gift (fixed ID)
add_action(   'wp_ajax_cg_get_local_knowledge',      'cg_get_local_knowledge' );
add_action(   'wp_ajax_nopriv_cg_get_local_knowledge','cg_get_local_knowledge' );

// Language gift (fixed ID)
add_action(   'wp_ajax_cg_get_language_gift',        'cg_get_language_gift' );
add_action(   'wp_ajax_nopriv_cg_get_language_gift','cg_get_language_gift' );

// Free‐choice gifts
add_action(   'wp_ajax_cg_get_free_gifts',           'cg_get_free_gifts' );
add_action(   'wp_ajax_nopriv_cg_get_free_gifts',   'cg_get_free_gifts' );
