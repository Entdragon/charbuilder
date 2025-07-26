<?php

namespace CharacterGeneratorDev {

/**
 * Template Name: Refresh Details Page
 */
get_header();

$slug = sanitize_text_field( get_query_var( 'slug' ) );
global $wpdb;

// Load the refresh entry
$refresh = $wpdb->get_row( $wpdb->prepare("
  SELECT ct_id, ct_refresh_name
    FROM DcVnchxg4_customtables_table_refresh
   WHERE ct_slug = %s
", $slug ) );

if ( ! $refresh ) {
  echo '<main class="site-main" style="padding:20px;"><p>Refresh category not found.</p></main>';
  get_footer();
  exit;
}
?>

<main class="site-main" style="padding:20px;">

  <hr>

  <h2>Gifts That Use This Refresh</h2>
  $gifts = $wpdb->get_results( $wpdb->prepare("
    SELECT ct_gifts_name, ct_slug
      FROM DcVnchxg4_customtables_table_gifts
     WHERE ct_gifts_refresh = %d
     ORDER BY ct_gifts_name ASC
  ", $refresh->ct_id ) );

  if ( $gifts ) :
  ?>
    <div class="skill-grid" style="margin-bottom: 20px;">
        <div class="skill-card">
          </a>
          
        </div>
    </div>
    <p>No gifts are currently assigned this refresh type.</p>

  <div class="skill-grid" style="margin-top: 40px;">
  <div class="skill-card">
    <a href="/index/">
      ← Back to Index
    </a>
  </div>
</div>
</main>



} // namespace CharacterGeneratorDev
