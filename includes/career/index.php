<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Load the career AJAX handlers
require_once __DIR__ . '/ajax-career.php';

// Register AJAX endpoints for logged-in users (and guests, if you want)
add_action( 'wp_ajax_cg_get_career_list',            'cg_get_career_list' );
add_action( 'wp_ajax_nopriv_cg_get_career_list',     'cg_get_career_list' );

add_action( 'wp_ajax_cg_get_career_gifts',           'cg_get_career_gifts' );
add_action( 'wp_ajax_nopriv_cg_get_career_gifts',    'cg_get_career_gifts' );

//add_action( 'wp_ajax_cg_get_eligible_extra_careers', 'cg_get_eligible_extra_careers' );
//add_action( 'wp_ajax_nopriv_cg_get_eligible_extra_careers', 'cg_get_eligible_extra_careers' );
