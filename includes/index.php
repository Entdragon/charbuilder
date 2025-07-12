<?php
// includes/index.php

require_once __DIR__ . '/activation.php';
require_once __DIR__ . '/shortcode-ui.php';

foreach ( [ 'character','gifts','career','species','skills','diagnostics','auth' ] as $feat ) {
    require_once __DIR__ . "/{$feat}/index.php";
}
