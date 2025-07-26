<?php

namespace CharacterGeneratorDev {

/**
 * Template Name: Gift Type Detail
 */
get_header();

$slug = sanitize_text_field( get_query_var('slug') );
global $wpdb;

// Load the gift type entry
$gift_type = $wpdb->get_row( $wpdb->prepare("
    SELECT * 
      FROM DcVnchxg4_customtables_table_gifttype
     WHERE ct_slug = %s
", $slug ) );

if ( ! $gift_type ) {
    echo '<p>Sorry, that gift type couldn’t be found.</p>';
    get_footer();
    exit;
}
?>

<article class="book-detail" style="padding: 20px;">

  <hr>

  <!-- Gifts with this type -->
  $gifts = $wpdb->get_results( $wpdb->prepare("
      SELECT ct_gifts_name, ct_slug
        FROM DcVnchxg4_customtables_table_gifts
       WHERE ct_gift_type = %d
       OR ct_gift_type_two = %d
       OR ct_gift_type_three = %d
       OR ct_gift_type_four = %d
       OR ct_gift_type_five = %d
       OR ct_gift_type_six = %d
       OR ct_gift_type_seven = %d
       OR ct_gift_type_eight = %d
       ORDER BY ct_gifts_name ASC
  ", $gift_type->ct_id, $gift_type->ct_id, $gift_type->ct_id, $gift_type->ct_id, $gift_type->ct_id, $gift_type->ct_id, $gift_type->ct_id, $gift_type->ct_id ) );

  if ( $gifts ) : ?>
    <h2>Gifts of This Type</h2>
    <div class="skill-grid">
        <div class="skill-card">
          </a>
        </div>
    </div>
    <p>No gifts are currently categorized under this type.</p>

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
