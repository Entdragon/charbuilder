<?php
/**
 * Urban Jungle — mini-router.
 * All /uj/* paths are dispatched here.
 */
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';

$uri  = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = preg_replace('#^/uj/?#', '', $uri);
$path = trim($path, '/');

$parts   = ($path === '') ? [] : explode('/', $path);
$section = $parts[0] ?? '';
$slug    = $parts[1] ?? '';

// Map section → page file
$sections = [
    ''        => 'home',
    'species' => 'species',
    'types'   => 'types',
    'careers' => 'careers',
    'skills'  => 'skills',
    'gifts'   => 'gifts',
    'soaks'   => 'soaks',
    'attacks' => 'attacks',
    'items'   => 'items',
    'books'   => 'books',
    'search'  => 'search',
    'builder' => 'builder',
];

if (!array_key_exists($section, $sections)) {
    http_response_code(404);
    require __DIR__ . '/uj/pages/home.php';
    exit;
}

if ($slug !== '') {
    $entity   = $section;
    $pageFile = __DIR__ . '/uj/pages/detail.php';
} else {
    $entity   = $section;
    $pageFile = __DIR__ . '/uj/pages/' . $sections[$section] . '.php';
}

if (file_exists($pageFile)) {
    require $pageFile;
} else {
    http_response_code(404);
    require __DIR__ . '/uj/pages/home.php';
}
