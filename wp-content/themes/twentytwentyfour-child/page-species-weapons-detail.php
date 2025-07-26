<?php

namespace CharacterGeneratorDev {

/**
 * Template Name: Species Weapons Detail
 */
get_header();

$slug = sanitize_text_field( get_query_var('slug') );
global $wpdb;

// Load the weapon entry
$weapon = $wpdb->get_row( $wpdb->prepare("
    SELECT * 
      FROM DcVnchxg4_customtables_table_weapons
     WHERE ct_slug = %s
", $slug ) );

if ( ! $weapon ) {
    echo '<p>Sorry, that weapon couldn’t be found.</p>';
    get_footer();
    exit;
}
?>

<article class="book-detail" style="padding: 20px;">

  <hr>

  <!-- Species with this weapon -->
  $species = $wpdb->get_results( $wpdb->prepare("
      SELECT ct_species_name, ct_slug
        FROM DcVnchxg4_customtables_table_species
       WHERE ct_species_weapon_one = %d
       OR ct_species_weapon_two = %d
       OR ct_species_weapon_three = %d
       ORDER BY ct_species_name ASC
  ", $weapon->ct_id, $weapon->ct_id, $weapon->ct_id ) );

  if ( $species ) : ?>
    <h2>Species with This Weapon</h2>
    <div class="skill-grid">
        <div class="skill-card">
          </a>
        </div>
    </div>
    <p>No species are assigned to this weapon type yet.</p>

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
