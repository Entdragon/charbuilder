<?php
// includes/skills/index.php

require_once __DIR__ . '/ajax-skills.php';

add_action( 'wp_ajax_cg_get_skill_list',   'cg_get_skill_list' );
add_action( 'wp_ajax_cg_get_skill_detail', 'cg_get_skill_detail' );
