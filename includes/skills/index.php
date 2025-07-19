<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Load the Skills AJAX callbacks
require_once __DIR__ . '/ajax-skills.php';

// List all skills
add_action(   'wp_ajax_cg_get_skills_list',       'cg_get_skills_list' );
add_action(   'wp_ajax_nopriv_cg_get_skills_list','cg_get_skills_list' );

// Fetch details for one skill
add_action(   'wp_ajax_cg_get_skill_detail',       'cg_get_skill_detail' );
add_action(   'wp_ajax_nopriv_cg_get_skill_detail','cg_get_skill_detail' );

