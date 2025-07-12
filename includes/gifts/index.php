<?php
// includes/gifts/index.php

require_once __DIR__ . '/ajax-gifts.php';

add_action( 'wp_ajax_cg_get_free_gifts',      'cg_get_free_gifts' );
add_action( 'wp_ajax_cg_get_species_profile', 'cg_get_species_profile' );
add_action( 'wp_ajax_cg_get_career_gifts',    'cg_get_career_gifts' );
add_action( 'wp_ajax_cg_get_eligible_extra_careers', 'cg_get_eligible_extra_careers' );
// …etc for any other gifts endpoints…
