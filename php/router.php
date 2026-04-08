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

// ── Admin editor ───────────────────────────────────────────────────────────────
if ($uri === '/admin' || $uri === '/admin/') {
    require __DIR__ . '/admin.php';
    exit;
}

// ── Blog admin ─────────────────────────────────────────────────────────────────
if ($uri === '/blog-admin' || $uri === '/blog-admin/') {
    require __DIR__ . '/blog-admin.php';
    exit;
}

// ── Main landing page ──────────────────────────────────────────────────────────
if ($uri === '/') {
    require __DIR__ . '/home.php';
    exit;
}

// ── Character builder (moved from / to /builder) ───────────────────────────────
if ($uri === '/builder' || $uri === '/builder/') {
    require __DIR__ . '/index.php';
    exit;
}

// ── Static files (assets, vendor, public root files) ─────────────────────────
// Returning false tells php -S to serve the file with its built-in file handler.
$disk = $root . $uri;
if ($uri !== '/' && file_exists($disk) && is_file($disk)) {
    return false;
}

// ── Urban Jungle character generator ──────────────────────────────────────────
if (str_starts_with($uri, '/uj') && ($uri === '/uj' || $uri === '/uj/' || str_starts_with($uri, '/uj/'))) {
    require __DIR__ . '/uj.php';
    exit;
}

// ── Ironclaw reference pages ───────────────────────────────────────────────────
if (str_starts_with($uri, '/ic') && ($uri === '/ic' || $uri === '/ic/' || str_starts_with($uri, '/ic/'))) {
    require __DIR__ . '/ic.php';
    exit;
}

// ── SPA catch-all → main PHP template ─────────────────────────────────────────
require __DIR__ . '/index.php';
