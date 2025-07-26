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

  $paged  = max( 1, get_query_var('paged', 1) );
  $limit  = 100;
  $offset = ( $paged - 1 ) * $limit;

  $total = (int) $wpdb->get_var("
    SELECT COUNT(ct_id)
      FROM DcVnchxg4_customtables_table_gifts
  ");
  $pages = ceil( $total / $limit );

  $gifts = $wpdb->get_results( $wpdb->prepare("
    SELECT ct_gifts_name, ct_slug
      FROM DcVnchxg4_customtables_table_gifts
     ORDER BY ct_gifts_name ASC
     LIMIT %d OFFSET %d
  ", $limit, $offset ) );
  ?>

  <div class="skill-grid">
      <div class="skill-card">
        </a>
      </div>
  </div>

  <nav class="pagination" style="margin-top: 20px;">
  </nav>
</main>


} // namespace CharacterGeneratorDev
