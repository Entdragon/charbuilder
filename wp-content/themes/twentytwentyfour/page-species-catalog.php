<?php

namespace CharacterGeneratorDev {

/**
 * Template Name: Species Catalog
 */
get_header();
?>

<main class="site-main" style="padding:20px;">
  <h1>Species</h1>

  <table style="width:100%; border-collapse:collapse;">
    <tbody>
      global $wpdb;

      // Pull species + their source book name & slug
      $species = $wpdb->get_results("
        SELECT 
          s.*,
          b.ct_book_name,
          b.ct_ct_slug AS book_slug
        FROM DcVnchxg4_customtables_table_species AS s
        LEFT JOIN DcVnchxg4_customtables_table_books AS b
          ON s.ct_species_source_book = b.ct_id
        ORDER BY s.ct_species_name ASC
      ");

      $count = 0;
      foreach ( $species as $sp ) {
        if ( $count % 4 === 0 ) {
          echo '<tr>';
        }

        echo '<td style="border:1px solid #ccc; padding:8px; width:25%; vertical-align:top;">';

          // Species name link
          echo '<strong><a href="/species/' . esc_attr($sp->ct_slug) . '/">'
             . esc_html($sp->ct_species_name) . '</a></strong><br>';

          // Source book link
          if ( ! empty( $sp->book_slug ) ) {
            echo '<small><a href="/book/' . esc_attr($sp->book_slug) . '/" target="_blank" rel="noopener noreferrer">'
               . esc_html($sp->ct_book_name) . '</a></small>';
          } else {
            echo '<small>' . esc_html($sp->ct_book_name) . '</small>';
          }

        echo '</td>';

        $count++;
        if ( $count % 4 === 0 ) {
          echo '</tr>';
        }
      }

      // Close final row if incomplete
      if ( $count % 4 !== 0 ) {
        echo '</tr>';
      }
      ?>
    </tbody>
  </table>
</main>


} // namespace CharacterGeneratorDev
