<?php
/**
 * Plugin Name:     Character Generator
 * Plugin URI:      https://yourdomain.com/character-generator
 * Description:     Modular character creation system with custom tables, AJAX, and dynamic UI.
 * Version:         0.6.0
 * Author:          Your Name
 * Author URI:      https://yourdomain.com
 * Text Domain:     character-generator
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Core includes
require_once __DIR__ . '/includes/index.php';
require_once __DIR__ . '/includes/shortcode-ui.php';

// Canonical (Stage) asset loader + hardening (shortcode-gated).
require_once __DIR__ . '/includes/enqueue-hardening.php';

// NOTE:
// - Legacy cg_enqueue_assets() (which enqueued gifts.bundle.js) has been removed.
// - All front-end assets are now loaded ONLY via includes/enqueue-hardening.php
