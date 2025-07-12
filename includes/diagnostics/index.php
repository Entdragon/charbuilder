<?php
// includes/diagnostics/index.php

require_once __DIR__ . '/ajax-diagnostics.php';

add_action( 'wp_ajax_cg_run_diagnostics', 'cg_run_diagnostics' );
