<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once __DIR__ . '/ajax-character.php';

// Load saved characters list (AUTH REQUIRED)
add_action( 'wp_ajax_cg_load_characters', 'cg_load_characters' );

// Fetch one character’s data (AUTH REQUIRED)
add_action( 'wp_ajax_cg_get_character', 'cg_get_character' );

// Save (insert or update) a character (AUTH REQUIRED)
add_action( 'wp_ajax_cg_save_character', 'cg_save_character' );
