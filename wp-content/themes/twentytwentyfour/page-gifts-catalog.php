<?php

namespace CharacterGeneratorDev {

/**
 * Template Name: Gifts Catalog
 */
get_header();
?>

<main class="site-main" style="padding:20px;">
  <h1>Gifts</h1>

  global $wpdb;

  // Pagination setup
  $paged  = max( 1, get_query_var('paged', 1) );
  $limit  = 100;
  $offset = ( $paged - 1 ) * $limit;

  // Total count for pagination
  $total = (int) $wpdb->get_var("
    SELECT COUNT(ct_id)
      FROM DcVnchxg4_customtables_table_gifts
  ");
  $pages = ceil( $total / $limit );

  // Fetch gifts + their source-book name & slug
  $gifts = $wpdb->get_results( $wpdb->prepare("
    SELECT 
      g.*,
      b.ct_book_name,
      b.ct_ct_slug   AS book_slug
    FROM DcVnchxg4_customtables_table_gifts AS g
    LEFT JOIN DcVnchxg4_customtables_table_books AS b
      ON g.ct_book_id = b.ct_id
    ORDER BY g.ct_gifts_name ASC
    LIMIT %d OFFSET %d
  ", $limit, $offset ) );
  ?>

  <table style="width:100%; border-collapse:collapse;">
    <tbody>
      $count = 0;
      foreach ( $gifts as $gf ) {
        if ( $count % 4 === 0 ) {
          echo '<tr>';
        }

        echo '<td style="border:1px solid #ccc; padding:8px; width:25%; vertical-align:top;">';

          // Gift name link
          echo '<strong><a href="/gift/' . esc_attr($gf->ct_slug) . '/">'
             . esc_html($gf->ct_gifts_name) . '</a></strong><br>';

          // Book link or plain name
          if ( ! empty( $gf->book_slug ) ) {
            echo '<small><a href="/book/' . esc_attr($gf->book_slug) . '/" target="_blank" rel="noopener noreferrer">'
               . esc_html($gf->ct_book_name) . '</a></small>';
          } else {
            echo '<small>' . esc_html($gf->ct_book_name) . '</small>';
          }

        echo '</td>';

        $count++;
        if ( $count % 4 === 0 ) {
          echo '</tr>';
        }
      }
      // Close last row if incomplete
      if ( $count % 4 !== 0 ) {
        echo '</tr>';
      }
      ?>
    </tbody>
  </table>

  <nav class="pagination" style="margin-top:20px;">
  </nav>
</main>


} // namespace CharacterGeneratorDev
