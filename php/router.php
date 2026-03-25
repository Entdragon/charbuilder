<?php
/**
 * PHP built-in server router for local development.
 *
 * Usage:  php -S 0.0.0.0:5000 php/router.php
 *
 * Rules:
 *   /ajax.php            → php/ajax.php  (API endpoint)
 *   /assets/**, /vendor/** → served as static files by returning false
 *   everything else      → php/index.php (SPA shell)
 */

$uri  = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$root = dirname(__DIR__);

// ── API endpoint ───────────────────────────────────────────────────────────────
if ($uri === '/ajax.php') {
    require __DIR__ . '/ajax.php';
    exit;
}

// ── Static files (assets, vendor, public root files) ─────────────────────────
// Returning false tells php -S to serve the file with its built-in file handler.
$disk = $root . $uri;
if ($uri !== '/' && file_exists($disk) && is_file($disk)) {
    return false;
}

// ── SPA catch-all → main PHP template ─────────────────────────────────────────
require __DIR__ . '/index.php';
