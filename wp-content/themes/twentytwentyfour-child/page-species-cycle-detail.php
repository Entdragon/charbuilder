<?php

namespace CharacterGeneratorDev {

/**
 * Template Name: Species Cycle Detail
 */
get_header();

$slug = sanitize_text_field( get_query_var('slug') );
global $wpdb;

// Load the cycle entry
$cycle = $wpdb->get_row( $wpdb->prepare("
    SELECT * 
      FROM DcVnchxg4_customtables_table_cycle
     WHERE ct_slug = %s
", $slug ) );

if ( ! $cycle ) {
    echo '<p>Sorry, that life cycle couldn’t be found.</p>';
    get_footer();
    exit;
}
?>

<article class="book-detail" style="padding: 20px;">

  <hr>

  <!-- Species with this cycle -->
  $species = $wpdb->get_results( $wpdb->prepare("
      SELECT ct_species_name, ct_slug
        FROM DcVnchxg4_customtables_table_species
       WHERE ct_species_cycle = %d
       ORDER BY ct_species_name ASC
  ", $cycle->ct_id ) );

  if ( $species ) : ?>
    <h2>Species with This Life Cycle</h2>
    <div class="skill-grid">
        <div class="skill-card">
          </a>
        </div>
    </div>
    <p>No species are assigned to this life cycle yet.</p>

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
