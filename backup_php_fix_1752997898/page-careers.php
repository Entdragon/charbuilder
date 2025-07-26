<?php

namespace CharacterGeneratorDev {

/**
 * Template Name: Career Catalog
 */
get_header();
?>

<main class="site-main" style="padding: 20px;">
  <h1>Careers</h1>

  <div class="skill-grid">
    global $wpdb;

    $careers = $wpdb->get_results("
      SELECT ct_career_name, ct_slug
        FROM DcVnchxg4_customtables_table_careers
       WHERE published = 1
       ORDER BY ct_career_name ASC
    ");

    foreach ( $careers as $career ) : ?>
      <div class="skill-card">
        </a>
      </div>
  </div>
</main>



} // namespace CharacterGeneratorDev
