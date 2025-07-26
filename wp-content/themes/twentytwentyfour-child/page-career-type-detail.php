<?php

namespace CharacterGeneratorDev {

/**
 * Template Name: Career Type Detail
 */
get_header();

$slug = sanitize_text_field( get_query_var('slug') );
global $wpdb;

// Load the career type entry
$type = $wpdb->get_row( $wpdb->prepare("
    SELECT * 
      FROM DcVnchxg4_customtables_table_careertype
     WHERE ct_slug = %s
", $slug ) );

if ( ! $type ) {
    echo '<p>Sorry, that career type couldn’t be found.</p>';
    get_footer();
    exit;
}
?>

<article class="book-detail" style="padding: 20px;">

  <hr>

  <!-- Careers with this type -->
  $careers = $wpdb->get_results( $wpdb->prepare("
      SELECT ct_career_name, ct_slug
        FROM DcVnchxg4_customtables_table_careers
       WHERE ct_career_type = %d
         AND published = 1
       ORDER BY ct_career_name ASC
  ", $type->ct_id ) );

  if ( $careers ) : ?>
    <h2>Careers of This Type</h2>
    <div class="skill-grid">
        <div class="skill-card">
          </a>
        </div>
    </div>
    <p>No careers are assigned to this type yet.</p>

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
