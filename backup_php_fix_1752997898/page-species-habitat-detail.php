<?php

namespace CharacterGeneratorDev {

/**
 * Template Name: Species Habitat Detail
 */
get_header();

$slug = sanitize_text_field( get_query_var('slug') );
global $wpdb;

// Load the habitat entry
$habitat = $wpdb->get_row( $wpdb->prepare("
    SELECT * 
      FROM DcVnchxg4_customtables_table_habitat
     WHERE ct_slug = %s
", $slug ) );

if ( ! $habitat ) {
    echo '<p>Sorry, that habitat couldn’t be found.</p>';
    get_footer();
    exit;
}
?>

<article class="book-detail" style="padding: 20px;">

  <hr>

  <!-- Species that use this habitat -->
  $species = $wpdb->get_results( $wpdb->prepare("
      SELECT ct_species_name, ct_slug
        FROM DcVnchxg4_customtables_table_species
       WHERE ct_species_habitat = %d
       ORDER BY ct_species_name ASC
  ", $habitat->ct_id ) );

  if ( $species ) : ?>
    <h2>Species with This Habitat</h2>
    <div class="skill-grid">
        <div class="skill-card">
          </a>
        </div>
    </div>
    <p>No species are assigned to this habitat yet.</p>

  <hr>
  
  <div class="skill-grid" style="margin-top: 40px;">
  <div class="skill-card">
    <a href="/index/">
      ← Back to Index
    </a>
  </div>
</div>

 
</article>


} // namespace CharacterGeneratorDev
