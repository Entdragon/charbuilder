<?php
// includes/character/index.php

require_once __DIR__ . '/ajax-character.php';

// character AJAX endpoint
add_action( 'wp_ajax_cg_get_character', 'cg_get_character' );
// if you allow guests:
// add_action( 'wp_ajax_nopriv_cg_get_character', 'cg_get_character' );
