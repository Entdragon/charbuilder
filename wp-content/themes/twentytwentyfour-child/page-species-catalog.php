<?php

namespace CharacterGeneratorDev {

/**
 * Template Name: Species Catalog
 */
get_header();
?>

<main class="site-main" style="padding: 20px;">
  <h1>Species</h1>

  <div class="skill-grid">
    global $wpdb;

    $species = $wpdb->get_results("
      SELECT ct_species_name, ct_slug
        FROM DcVnchxg4_customtables_table_species
       WHERE published = 1
       ORDER BY ct_species_name ASC
    ");

    foreach ( $species as $sp ) : ?>
      <div class="skill-card">
        </a>
      </div>
  </div>
</main>


} // namespace CharacterGeneratorDev
