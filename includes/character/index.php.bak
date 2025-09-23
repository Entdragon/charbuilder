<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once __DIR__ . '/ajax-character.php';

// Load saved characters list
add_action(   'wp_ajax_cg_load_characters',        'cg_load_characters' );
add_action(   'wp_ajax_nopriv_cg_load_characters', 'cg_load_characters' );

// Fetch one character’s data
add_action(   'wp_ajax_cg_get_character',          'cg_get_character' );
add_action(   'wp_ajax_nopriv_cg_get_character',   'cg_get_character' );

// Save (insert or update) a character
add_action(   'wp_ajax_cg_save_character',         'cg_save_character' );
add_action(   'wp_ajax_nopriv_cg_save_character',  'cg_save_character' ); // remove nopriv if guests not allowed

