<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// includes/species/index.php
require_once __DIR__ . '/ajax-species.php';

// Species list
add_action( 'wp_ajax_cg_get_species_list',        'cg_get_species_list' );
add_action( 'wp_ajax_nopriv_cg_get_species_list', 'cg_get_species_list' );

// Species profile
add_action( 'wp_ajax_cg_get_species_profile',        'cg_get_species_profile' );
add_action( 'wp_ajax_nopriv_cg_get_species_profile', 'cg_get_species_profile' );
