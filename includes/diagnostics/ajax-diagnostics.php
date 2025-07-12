<?php
add_action('wp_ajax_cg_ping', function() {
  wp_send_json_success([
    'memory_usage_bytes' => memory_get_usage(),
    'peak_usage_bytes'   => memory_get_peak_usage(),
    'php_memory_limit'   => ini_get('memory_limit'),
    'time'               => current_time('mysql')
  ]);
});