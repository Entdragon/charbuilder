<?php
/**
 * IC Character Builder — production entry point.
 * The .htaccess routes /builder → builder.php; this file simply delegates
 * to index.php which contains the full application.
 */
require_once __DIR__ . '/index.php';
