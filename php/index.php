<?php
/**
 * Character Generator — PHP app shell.
 *
 * This replaces public/index.html for PHP hosting.
 * Sets window.CG_AJAX to point at ajax.php in the same directory.
 * All frontend JS/CSS assets are loaded from the /assets/ path.
 *
 * Deploy the entire project root to your server so that:
 *   /assets/js/dist/core.bundle.js   is reachable
 *   /assets/css/dist/core.css        is reachable
 *   /php/ajax.php                    is the API endpoint
 *
 * Or if you place this in public_html/characters/, adjust asset paths below.
 */

// Asset paths — relative to the document root of this subdomain
$assetsBase = '/assets';
$ajaxUrl    = '/ajax.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Library of Calabria — Character Generator</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;700&family=Crimson+Pro:ital,wght@0,300;0,400;0,600;1,300;1,400&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="<?= htmlspecialchars($assetsBase) ?>/css/dist/core.css">
</head>
<body id="site-wrapper" class="cg-dark-theme">

  <!-- Site header -->
  <header id="site-header">
    <div class="site-header-inner">
      <a href="/" class="site-logo">
        <span class="site-logo-title">The Library of Calabria</span>
        <span class="site-logo-sub">Character Generator</span>
      </a>
    </div>
  </header>

  <div id="site-body">
    <div id="site-content">
      <main id="cg-app-screen" class="cg-app-screen">
        <!-- App is mounted here by core.bundle.js -->
      </main>
    </div>
  </div>

  <!-- CG AJAX config — must come before the bundle -->
  <script>
    window.CG_AJAX = {
      ajax_url: <?= json_encode($ajaxUrl) ?>,
      nonce:    '1'
    };
  </script>

  <script src="<?= htmlspecialchars($assetsBase) ?>/js/dist/core.bundle.js"></script>
</body>
</html>
