<?php
// includes/career/index.php

require_once __DIR__ . '/ajax-career.php';

add_action( 'wp_ajax_cg_get_career_list',     'cg_get_career_list' );
add_action( 'wp_ajax_cg_get_career_profile',  'cg_get_career_profile' );
