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

  // 1) Load the Gift + all lookups
  $gift = $wpdb->get_row( $wpdb->prepare("
    SELECT 
      g.*,

      -- Class
      gc.ct_class_name    AS class_name,

      -- Types (up to 8)
      gt1.ct_type_name    AS type1,
      gt2.ct_type_name    AS type2,
      gt3.ct_type_name    AS type3,
      gt4.ct_type_name    AS type4,
      gt5.ct_type_name    AS type5,
      gt6.ct_type_name    AS type6,
      gt7.ct_type_name    AS type7,
      gt8.ct_type_name    AS type8,

      -- Refresh
      rf.ct_refresh_name  AS refresh_name,

      -- Source Book
      b.ct_book_name,
      b.ct_ct_slug        AS book_slug

    FROM DcVnchxg4_customtables_table_gifts AS g

    LEFT JOIN DcVnchxg4_customtables_table_giftclass    AS gc  ON g.ct_gift_class      = gc.ct_id

    LEFT JOIN DcVnchxg4_customtables_table_gifttype     AS gt1 ON g.ct_gift_type       = gt1.ct_id
    LEFT JOIN DcVnchxg4_customtables_table_gifttype     AS gt2 ON g.ct_gift_type_two   = gt2.ct_id
    LEFT JOIN DcVnchxg4_customtables_table_gifttype     AS gt3 ON g.ct_gift_type_three = gt3.ct_id
    LEFT JOIN DcVnchxg4_customtables_table_gifttype     AS gt4 ON g.ct_gift_type_four  = gt4.ct_id
    LEFT JOIN DcVnchxg4_customtables_table_gifttype     AS gt5 ON g.ct_gift_type_five  = gt5.ct_id
    LEFT JOIN DcVnchxg4_customtables_table_gifttype     AS gt6 ON g.ct_gift_type_six   = gt6.ct_id
    LEFT JOIN DcVnchxg4_customtables_table_gifttype     AS gt7 ON g.ct_gift_type_seven = gt7.ct_id
    LEFT JOIN DcVnchxg4_customtables_table_gifttype     AS gt8 ON g.ct_gift_type_eight = gt8.ct_id

    LEFT JOIN DcVnchxg4_customtables_table_refresh      AS rf  ON g.ct_gifts_refresh   = rf.ct_id

    LEFT JOIN DcVnchxg4_customtables_table_books        AS b   ON g.ct_book_id         = b.ct_id

    WHERE g.ct_slug = %s
  ", $slug ) );

  if ( ! $gift ) {
      echo '<p>Sorry, that gift couldn’t be found.</p>';
      get_footer();
      exit;
  }

  // Title & Description
  echo '<h1>' . esc_html( $gift->ct_gifts_name ) . '</h1>';
  if ( ! empty( $gift->ct_description ) ) {
      echo '<p>' . nl2br( esc_html( $gift->ct_description ) ) . '</p>';
  }

  echo '<hr>';

  //
  // 2) Gift Details
  //
  echo '<h2>Gift Details</h2>';

  // allows_multiple, manifold, class
  echo '<div style="display:flex; gap:30px; margin-bottom:20px;">';
    echo '<div>Can be taken multiple times: <strong>' 
         . ( $gift->ct_gifts_allows_multiple ? 'Yes' : 'No' ) 
         . '</strong></div>';

    echo '<div>Manifold Gift: <strong>' 
         . ( $gift->ct_gifts_manifold ? 'Yes' : 'No' ) 
         . '</strong></div>';

    echo '<div>Class: <strong>' 
         . esc_html( $gift->class_name ?: '—' ) 
         . '</strong></div>';
  echo '</div>';

  // Types (limit 5)
  $types = array_filter([
    $gift->type1, $gift->type2, $gift->type3, $gift->type4,
    $gift->type5, $gift->type6, $gift->type7, $gift->type8,
  ]);
  if ( $types ) {
    echo '<h3>Types</h3>';
    echo '<ul style="display:flex; gap:20px; list-style:none; padding:0; margin:0 0 20px;">';
      foreach ( array_slice( $types, 0, 5 ) as $t ) {
        echo '<li>' . esc_html( $t ) . '</li>';
      }
    echo '</ul>';
  }

  //
  // 3) Requirements (numeric + special)
  //
  // Numeric requires fields
  $req_fields = [
    'ct_gifts_requires','ct_gifts_requires_two','ct_gifts_requires_three',
    'ct_gifts_requires_four','ct_gifts_requires_five','ct_gifts_requires_six',
    'ct_gifts_requires_seven','ct_gifts_requires_eight','ct_gifts_requires_nine',
    'ct_gifts_requires_ten','ct_gifts_requires_eleven','ct_gifts_requires_twelve',
    'ct_gifts_requires_thirteen','ct_gifts_requires_fourteen','ct_gifts_requires_fifteen',
    'ct_gifts_requires_sixteen','ct_gifts_requires_seventeen','ct_gifts_requires_eighteen',
    'ct_gifts_requires_nineteen'
  ];
  $requires = [];
  foreach ( $req_fields as $field ) {
    $id = $gift->$field;
    if ( $id ) {
      $name = $wpdb->get_var( $wpdb->prepare(
        "SELECT ct_gifts_name FROM DcVnchxg4_customtables_table_gifts WHERE ct_id=%d",
        $id
      ) );
      if ( $name ) {
        $requires[] = $name;
        if ( count( $requires ) === 5 ) break;
      }
    }
  }

  // Special text requires
  $spec_fields = [
    'ct_gifts_requires_special','ct_gifts_requires_special_two',
    'ct_gifts_requires_special_three','ct_gifts_requires_special_four',
    'ct_gifts_requires_special_five','ct_gifts_requires_special_six',
    'ct_gifts_requires_special_seven','ct_gifts_requires_special_eight'
  ];
  $specials = [];
  foreach ( $spec_fields as $field ) {
    if ( ! empty( $gift->$field ) ) {
      $specials[] = $gift->$field;
    }
  }

  if ( $requires || $specials ) {
    echo '<h3>Gift Requires</h3>';
    echo '<ul style="display:flex; gap:20px; list-style:none; padding:0; margin:0 0 20px;">';
      foreach ( $requires as $r ) {
        echo '<li>Gift requires: ' . esc_html( $r ) . '</li>';
      }
      foreach ( $specials as $s ) {
        echo '<li>Gift requires: ' . esc_html( $s ) . '</li>';
      }
    echo '</ul>';
  }

  //
  // 4) Trigger, Refresh, Effect
  //
  if ( ! empty( $gift->ct_gift_trigger ) ) {
    echo '<h3>Trigger</h3>';
    echo '<p>' . nl2br( esc_html( $gift->ct_gift_trigger ) ) . '</p>';
  }

  if ( $gift->refresh_name ) {
    echo '<h3>Refresh</h3>';
    echo '<p>' . esc_html( $gift->refresh_name ) . '</p>';
  }

  if ( ! empty( $gift->ct_gifts_effect ) ) {
    echo '<h3>Effect</h3>';
    echo '<p>' . nl2br( esc_html( $gift->ct_gifts_effect ) ) . '</p>';
  }
  if ( ! empty( $gift->ct_gifts_effect_description ) ) {
    echo '<h3>Effect Description</h3>';
    echo '<p>' . nl2br( esc_html( $gift->ct_gifts_effect_description ) ) . '</p>';
  }

  //
  // 5) Source Book & Page
  //
  if ( $gift->ct_book_name ) {
    echo '<h3>Source</h3>';
    echo '<p><a href="/book/' . esc_attr( $gift->book_slug ) . '/" target="_blank" rel="noopener noreferrer">'
       . esc_html( $gift->ct_book_name ) . '</a>, Pg. No. ' . intval( $gift->ct_pg_no ) . '</p>';
  }

  //
  // 6) Species that use this Gift
  //
  $species = $wpdb->get_results( $wpdb->prepare("
    SELECT ct_species_name, ct_slug
      FROM DcVnchxg4_customtables_table_species
     WHERE ct_species_gift_one   = %d
        OR ct_species_gift_two   = %d
        OR ct_species_gift_three = %d
  ", $gift->ct_id, $gift->ct_id, $gift->ct_id ) );

  if ( $species ) {
    echo '<h2>Species with This Gift</h2>';
    echo '<ul style="list-style:none; padding-left:0; margin:0 0 20px;">';
      foreach ( $species as $sp ) {
        echo '<li><a href="/species/' . esc_attr( $sp->ct_slug ) . '/">'
           . esc_html( $sp->ct_species_name ) . '</a></li>';
      }
    echo '</ul>';
  }

  //
  // 7) Careers that use this Gift
  //
  $careers = $wpdb->get_results( $wpdb->prepare("
    SELECT ct_career_name, ct_slug
      FROM DcVnchxg4_customtables_table_careers
     WHERE published = 1
       AND (
         ct_career_gift_one   = %d OR
         ct_career_gift_two   = %d OR
         ct_career_gift_three = %d
       )
     ORDER BY ct_career_name ASC
  ", $gift->ct_id, $gift->ct_id, $gift->ct_id ) );

  if ( $careers ) {
    echo '<h2>Careers with This Gift</h2>';
    echo '<ul style="list-style:none; padding-left:0; margin:0 0 20px;">';
      foreach ( $careers as $c ) {
        echo '<li><a href="/career/' . esc_attr( $c->ct_slug ) . '/">'
           . esc_html( $c->ct_career_name ) . '</a></li>';
      }
    echo '</ul>';
  }

  // Back link
  echo '<p><a href="/gifts/">← Back to Gifts Catalog</a></p>';
  ?>
</main>


} // namespace CharacterGeneratorDev
