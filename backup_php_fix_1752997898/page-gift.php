<?php

namespace CharacterGeneratorDev {

/**
 * Template Name: Gift Detail Page
 */
get_header();
?>

<main class="site-main" style="padding:20px;">
global $wpdb;
$slug = sanitize_text_field( get_query_var('slug') );

$gift = $wpdb->get_row( $wpdb->prepare("
  SELECT 
    g.*,
    gc.ct_class_name AS class_name,
    gc.ct_slug AS class_slug,
    gt1.ct_type_name AS type1, gt1.ct_slug AS type1_slug,
    gt2.ct_type_name AS type2, gt2.ct_slug AS type2_slug,
    gt3.ct_type_name AS type3, gt3.ct_slug AS type3_slug,
    gt4.ct_type_name AS type4, gt4.ct_slug AS type4_slug,
    gt5.ct_type_name AS type5, gt5.ct_slug AS type5_slug,
    gt6.ct_type_name AS type6, gt6.ct_slug AS type6_slug,
    gt7.ct_type_name AS type7, gt7.ct_slug AS type7_slug,
    gt8.ct_type_name AS type8, gt8.ct_slug AS type8_slug,
    rf.ct_refresh_name AS refresh_name, rf.ct_slug AS refresh_slug,
    b.ct_book_name,
    b.ct_ct_slug AS book_slug
  FROM DcVnchxg4_customtables_table_gifts AS g
  LEFT JOIN DcVnchxg4_customtables_table_giftclass AS gc ON g.ct_gift_class = gc.ct_id
  LEFT JOIN DcVnchxg4_customtables_table_gifttype AS gt1 ON g.ct_gift_type = gt1.ct_id
  LEFT JOIN DcVnchxg4_customtables_table_gifttype AS gt2 ON g.ct_gift_type_two = gt2.ct_id
  LEFT JOIN DcVnchxg4_customtables_table_gifttype AS gt3 ON g.ct_gift_type_three = gt3.ct_id
  LEFT JOIN DcVnchxg4_customtables_table_gifttype AS gt4 ON g.ct_gift_type_four = gt4.ct_id
  LEFT JOIN DcVnchxg4_customtables_table_gifttype AS gt5 ON g.ct_gift_type_five = gt5.ct_id
  LEFT JOIN DcVnchxg4_customtables_table_gifttype AS gt6 ON g.ct_gift_type_six = gt6.ct_id
  LEFT JOIN DcVnchxg4_customtables_table_gifttype AS gt7 ON g.ct_gift_type_seven = gt7.ct_id
  LEFT JOIN DcVnchxg4_customtables_table_gifttype AS gt8 ON g.ct_gift_type_eight = gt8.ct_id
  LEFT JOIN DcVnchxg4_customtables_table_refresh AS rf ON g.ct_gifts_refresh = rf.ct_id
  LEFT JOIN DcVnchxg4_customtables_table_books AS b ON g.ct_book_id = b.ct_id
  WHERE g.ct_slug = %s
", $slug ) );

if ( ! $gift ) {
  echo '<p>Sorry, that gift couldn’t be found.</p>';
  get_footer();
  exit;
}
?>



<hr>

<h2>Gift Details</h2>
<div class="skill-grid" style="margin-bottom: 20px;">
    <div class="skill-card" style="background-color: #e6f9e6;">
      <a href="/gifts-multiple/">Multiple Times</a>
    </div>

    <div class="skill-card" style="background-color: #e6f9e6;">
      <a href="/gifts-manifold/">Manifold Gift</a>
    </div>

    <div class="skill-card">
      </a>
    </div>
</div>

$types = [];
for ( $i = 1; $i <= 8; $i++ ) {
  $name = "type{$i}";
  $slug = "type{$i}_slug";
  if ( !empty($gift->$name) ) {
    $types[] = [
      'name' => $gift->$name,
      'slug' => $gift->$slug
    ];
  }
}
if ( $types ) : ?>
  <h3>Types</h3>
  <div class="skill-grid" style="margin-bottom: 20px;">
      <div class="skill-card">
      </div>
  </div>

$req_fields = [];
for ( $i = 0; $i <= 19; $i++ ) {
  $f = $i === 0 ? 'ct_gifts_requires' : "ct_gifts_requires_{$i}";
  if ( property_exists( $gift, $f ) && !empty($gift->$f) ) {
    $req_fields[] = $gift->$f;
  }
}
$required = [];
if ( $req_fields ) {
  $placeholders = implode( ',', array_fill( 0, count($req_fields), '%d' ) );
  $required = $wpdb->get_results( $wpdb->prepare("
    SELECT ct_slug, ct_gifts_name FROM DcVnchxg4_customtables_table_gifts
    WHERE ct_id IN ($placeholders)
  ", ...$req_fields ) );
}
$special_fields = array_filter([
  $gift->ct_gifts_requires_special ?? null,
  $gift->ct_gifts_requires_special_two ?? null,
  $gift->ct_gifts_requires_special_three ?? null,
  $gift->ct_gifts_requires_special_four ?? null,
  $gift->ct_gifts_requires_special_five ?? null,
  $gift->ct_gifts_requires_special_six ?? null,
  $gift->ct_gifts_requires_special_seven ?? null,
  $gift->ct_gifts_requires_special_eight ?? null
]);

if ( $required || $special_fields ) : ?>
  <h3>Gift Requires</h3>
  <div class="skill-grid" style="margin-bottom: 20px;">
      <div class="skill-card">
      </div>
  </div>

  <h3>Trigger</h3>

  <h3>Refresh</h3>
  <div class="skill-grid" style="margin-bottom: 20px;">
    <div class="skill-card">
      </a>
    </div>
  </div>

  <h3>Effect</h3>

  <h3>Effect Description</h3>

<h2>Source</h2>
  <div class="skill-grid" style="margin-bottom: 20px;">
    <div class="skill-card">
</a>


    </div>
  </div>

$species = $wpdb->get_results( $wpdb->prepare("
  SELECT ct_species_name, ct_slug FROM DcVnchxg4_customtables_table_species
  WHERE ct_species_gift_one = %d OR ct_species_gift_two = %d OR ct_species_gift_three = %d
", $gift->ct_id, $gift->ct_id, $gift->ct_id ) );

if ( $species ) : ?>
  <h3>Species That Use This Gift</h3>
  <div class="skill-grid" style="margin-bottom: 20px;">
      <div class="skill-card">
        </a>
      </div>
  </div>

$careers = $wpdb->get_results( $wpdb->prepare("
  SELECT ct_career_name, ct_slug FROM DcVnchxg4_customtables_table_careers
  WHERE published = 1 AND (
    ct_career_gift_one = %d OR
    ct_career_gift_two = %d OR
    ct_career_gift_three = %d
  )
  ORDER BY ct_career_name ASC
", $gift->ct_id, $gift->ct_id, $gift->ct_id ) );

if ( $careers ) : ?>
  <h3>Careers That Use This Gift</h3>
  <div class="skill-grid" style="margin-bottom: 20px;">
      <div class="skill-card">
        </a>
      </div>
  </div>

<div class="skill-grid" style="margin-top: 40px;">
  <div class="skill-card">
    <a href="/gifts/">
      ← Back to Gifts
    </a>
  </div>
</div>

</main>


} // namespace CharacterGeneratorDev
