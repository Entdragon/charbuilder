<?php
/**
 * Ironclaw info pages — mini-router.
 * All /ic/* paths are dispatched here.
 */
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';

$uri  = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = preg_replace('#^/ic/?#', '', $uri);
$path = trim($path, '/');

$parts   = ($path === '') ? [] : explode('/', $path);
$section = $parts[0] ?? '';
$slug    = $parts[1] ?? '';

$sections = [
    ''          => 'home',
    'species'   => 'species',
    'careers'   => 'careers',
    'gifts'     => 'gifts',
    'skills'    => 'skills',
    'equipment' => 'equipment',
    'weapons'   => 'weapons',
    'search'    => 'search',
];

if (!array_key_exists($section, $sections)) {
    http_response_code(404);
    require __DIR__ . '/ic/pages/home.php';
    exit;
}

if ($slug !== '') {
    $entity   = $section;
    $pageFile = __DIR__ . '/ic/pages/detail.php';
} else {
    $entity   = $section;
    $pageFile = __DIR__ . '/ic/pages/' . $sections[$section] . '.php';
}

if (file_exists($pageFile)) {
    require $pageFile;
} else {
    http_response_code(404);
    require __DIR__ . '/ic/pages/home.php';
}
