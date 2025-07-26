<?php

namespace CharacterGeneratorDev {

<!DOCTYPE html>
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" href="https://libraryofcalbria.com/wp-content/uploads/2025/06/flav.jpg" type="image/jpeg" sizes="96x96">
<link rel="apple-touch-icon" href="https://libraryofcalbria.com/wp-content/uploads/2025/06/flav.jpg">
<meta name="theme-color" content="#ffffff">

</head>


<div class="site-container" style="
  max-width:1280px;
  margin:2rem auto;
  border:4px solid #5a8f5c;
  border-radius:20px;
  box-shadow:0 4px 20px rgba(0,0,0,0.15);
  background:#fff;
  display:flex;
  flex-direction:column;
  overflow:hidden;
">

  <div style="display:flex; flex:1; min-height:100vh;">

    <!-- Sidebar -->
    <aside id="site-sidebar" style="
      width:250px;
      background:#f1f1f1;
      border-top-left-radius:16px;
      border-bottom-left-radius:16px;
      border-right:1px solid #ccc;
      padding:2em 1em 1em;
      display:flex;
      flex-direction:column;
      flex-shrink:0;
    ">
      <nav aria-label="Primary Menu">
        wp_nav_menu( [
          'theme_location' => 'primary',
          'menu_class'     => 'sidebar-menu',
          'container'      => false,
        ] );
        ?>
      </nav>
    </aside>

    <!-- Main Content -->
    <div id="main-content" style="flex:1; display:flex; flex-direction:column;">

      <header class="site-header" style="
        background:#f7f7f7;
        border-bottom:2px solid #888;
        padding:1.5em 2em;
        border-top-right-radius:20px;
      ">
        <div class="site-title" style="font-size:2rem; margin:0;">
             style="text-decoration:none; color:inherit;">
            The Library of Calabria
          </a>
        </div>
global $wpdb;
$random_book = $wpdb->get_row("
  SELECT ct_ct_slug, ct_book_name, ct_ct_cover_image
  FROM {$wpdb->prefix}customtables_table_books
  WHERE ct_ct_cover_image IS NOT NULL
  ORDER BY RAND()
  LIMIT 1
");
?>

  <div class="random-header-thumb">
        'alt' => esc_attr( $random_book->ct_book_name ),
        'class' => 'random-book-thumb'
      ] ); ?>
    </a>
  </div>

      </header>

      <main class="site-main" style="padding:20px; flex:1;">
        <!-- Your page content starts here… -->

} // namespace CharacterGeneratorDev
