<?php

namespace CharacterGeneratorDev {

/**
 * Template Name: Skill Descriptor Detail
 */
get_header();

$slug = sanitize_text_field( get_query_var('slug') );
global $wpdb;

// Load the descriptor entry
$descriptor = $wpdb->get_row( $wpdb->prepare("
    SELECT * 
      FROM DcVnchxg4_customtables_table_skillsdescriptors
     WHERE ct_slug = %s
", $slug ) );

if ( ! $descriptor ) {
    echo '<p>Sorry, that skill descriptor couldn’t be found.</p>';
    get_footer();
    exit;
}
?>

<article class="book-detail" style="padding: 20px;">

  <hr>

  <!-- Skills with this descriptor -->
  $skills = $wpdb->get_results( $wpdb->prepare("
      SELECT ct_skill_name, ct_slug
        FROM DcVnchxg4_customtables_table_skills
       WHERE ct_skill_descriptor_one = %d
       OR ct_skill_descriptor_two = %d
       OR ct_skill_descriptor_three = %d
       ORDER BY ct_skill_name ASC
  ", $descriptor->ct_id, $descriptor->ct_id, $descriptor->ct_id ) );

  if ( $skills ) : ?>
    <h2>Skills with This Descriptor</h2>
    <div class="skill-grid">
        <div class="skill-card">
          </a>
        </div>
    </div>
    <p>No skills are assigned to this descriptor yet.</p>

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
