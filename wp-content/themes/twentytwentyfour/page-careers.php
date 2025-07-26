<?php

namespace CharacterGeneratorDev {

/**
 * Template Name: Career Catalog
 */
get_header();
?>

<main class="site-main" style="padding: 20px;">
  <h1>Careers</h1>

  <table style="width: 100%; border-collapse: collapse;">
    <tbody>
      global $wpdb;

      $careers = $wpdb->get_results("
        SELECT 
          careers.*,
          books.ct_book_name,
          books.ct_ct_slug AS book_slug
        FROM DcVnchxg4_customtables_table_careers AS careers
        LEFT JOIN DcVnchxg4_customtables_table_books AS books 
          ON careers.ct_career_source_book = books.ct_id
        WHERE careers.published = 1
        ORDER BY careers.ct_career_name ASC
      ");

      $count = 0;
      foreach ( $careers as $career ) {
          if ( $count % 4 === 0 ) echo '<tr>';

          echo '<td style="border: 1px solid #ccc; padding: 8px; width: 25%; vertical-align: top;">';
          echo '<strong><a href="/career/' . esc_attr($career->ct_slug) . '/">' . esc_html($career->ct_career_name) . '</a></strong><br>';

          if ( !empty($career->book_slug) ) {
              echo '<small><a href="/book/' . esc_attr($career->book_slug) . '/" target="_blank" rel="noopener noreferrer">'
                . esc_html($career->ct_book_name) . '</a></small>';
          } else {
              echo '<small>' . esc_html($career->ct_book_name) . '</small>';
          }

          echo '</td>';

          $count++;
          if ( $count % 4 === 0 ) echo '</tr>';
      }

      if ( $count % 4 !== 0 ) echo '</tr>';
      ?>
    </tbody>
  </table>
</main>


} // namespace CharacterGeneratorDev
