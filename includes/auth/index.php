<?php
// includes/auth/index.php

require_once __DIR__ . '/ajax-auth.php';

add_action( 'wp_ajax_nopriv_cg_login',  'cg_ajax_login' );
add_action( 'wp_ajax_cg_logout',        'cg_ajax_logout' );
