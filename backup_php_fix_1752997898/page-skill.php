<?php

namespace CharacterGeneratorDev {

/**
 * Template Name: Skill Detail Page
 */
get_header();
?>

<main class="site-main" style="padding:20px;">
global $wpdb;
$slug = sanitize_text_field( get_query_var('slug') );

$skill = $wpdb->get_row( $wpdb->prepare("
  SELECT 
    s.*,
    d1.ct_name AS descriptor_one, d1.ct_slug AS descriptor_one_slug,
    d2.ct_name AS descriptor_two, d2.ct_slug AS descriptor_two_slug,
    d3.ct_name AS descriptor_three, d3.ct_slug AS descriptor_three_slug,

    g1.ct_gifts_name AS improve1,  g1.ct_slug AS improve1_slug,
    g2.ct_gifts_name AS improve2,  g2.ct_slug AS improve2_slug,
    g3.ct_gifts_name AS improve3,  g3.ct_slug AS improve3_slug,
    g4.ct_gifts_name AS improve4,  g4.ct_slug AS improve4_slug,
    g5.ct_gifts_name AS improve5,  g5.ct_slug AS improve5_slug,
    g6.ct_gifts_name AS improve6,  g6.ct_slug AS improve6_slug,
    g7.ct_gifts_name AS improve7,  g7.ct_slug AS improve7_slug,
    g8.ct_gifts_name AS improve8,  g8.ct_slug AS improve8_slug
  FROM DcVnchxg4_customtables_table_skills AS s
  LEFT JOIN DcVnchxg4_customtables_table_skillsdescriptors AS d1 ON s.ct_skill_descriptor_one = d1.ct_id
  LEFT JOIN DcVnchxg4_customtables_table_skillsdescriptors AS d2 ON s.ct_skill_descriptor_two = d2.ct_id
  LEFT JOIN DcVnchxg4_customtables_table_skillsdescriptors AS d3 ON s.ct_skill_descriptor_three = d3.ct_id
  LEFT JOIN DcVnchxg4_customtables_table_gifts AS g1 ON s.ct_skill_gifts_improve = g1.ct_id
  LEFT JOIN DcVnchxg4_customtables_table_gifts AS g2 ON s.ct_skill_gifts_improve2 = g2.ct_id
  LEFT JOIN DcVnchxg4_customtables_table_gifts AS g3 ON s.ct_skill_gifts_improve3 = g3.ct_id
  LEFT JOIN DcVnchxg4_customtables_table_gifts AS g4 ON s.ct_skill_gifts_improve4 = g4.ct_id
  LEFT JOIN DcVnchxg4_customtables_table_gifts AS g5 ON s.ct_skill_gifts_improve5 = g5.ct_id
  LEFT JOIN DcVnchxg4_customtables_table_gifts AS g6 ON s.ct_skill_gifts_improve6 = g6.ct_id
  LEFT JOIN DcVnchxg4_customtables_table_gifts AS g7 ON s.ct_skill_gifts_improve7 = g7.ct_id
  LEFT JOIN DcVnchxg4_customtables_table_gifts AS g8 ON s.ct_skill_gifts_improve8 = g8.ct_id
  WHERE s.ct_slug = %s
", $slug ) );

if ( ! $skill ) {
  echo '<p>Sorry, that skill couldn’t be found.</p>';
  get_footer();
  exit;
}

// Title
echo '<h1>' . esc_html( $skill->ct_skill_name ) . '</h1>';

// Description
if ( ! empty( $skill->ct_description ) ) {
  echo '<p>' . nl2br( esc_html( $skill->ct_description ) ) . '</p>';
}
?>

<hr>

$descriptor_cards = array_filter([
  ['name' => $skill->descriptor_one,   'slug' => $skill->descriptor_one_slug],
  ['name' => $skill->descriptor_two,   'slug' => $skill->descriptor_two_slug],
  ['name' => $skill->descriptor_three, 'slug' => $skill->descriptor_three_slug],
], fn($desc) => !empty($desc['name']) && !empty($desc['slug']));

if ( $descriptor_cards ) :
?>
  <h2>Descriptors</h2>
  <div class="skill-grid" style="margin-bottom: 20px;">
      <div class="skill-card">
        </a>
      </div>
  </div>


// Suggested Favourite Use
$favs = [];
for ( $i = 1; $i <= 10; $i++ ) {
  $field = 'ct_skill_suggested_fav' . ( $i === 1 ? '' : $i );
  if ( ! empty( $skill->{$field} ) ) {
    $favs[] = $skill->{$field};
    if ( count( $favs ) === 5 ) break;
  }
}
if ( $favs ) :
?>
  <h2>Suggested Favourite Use</h2>
  <ul style="display:flex; gap:20px; list-style:none; padding:0; margin:0 0 20px;">
  </ul>

  <h2>Description</h2>

// Gifts that Improve This Skill
$improves = [];
for ( $i = 1; $i <= 8; $i++ ) {
  $name = 'improve' . $i;
  $slug = 'improve' . $i . '_slug';
  if ( ! empty( $skill->$name ) && ! empty( $skill->$slug ) ) {
    $improves[] = [
      'name' => $skill->$name,
      'slug' => $skill->$slug
    ];
  }
}
if ( $improves ) :
?>
  <h2>Gifts that Improve This Skill</h2>
  <div class="skill-grid" style="margin-bottom: 20px;">
      <div class="skill-card">
        </a>
      </div>
  </div>

// Species using this Skill
$species = $wpdb->get_results( $wpdb->prepare("
  SELECT ct_species_name, ct_slug FROM DcVnchxg4_customtables_table_species
  WHERE ct_species_skill_one = %d OR ct_species_skill_two = %d OR ct_species_skill_three = %d
  ORDER BY ct_species_name ASC
", $skill->ct_id, $skill->ct_id, $skill->ct_id ) );

if ( $species ) :
?>
  <h2>Species with This Skill</h2>
  <div class="skill-grid">
      <div class="skill-card">
        </a>
      </div>
  </div>

// Careers using this Skill
$careers = $wpdb->get_results( $wpdb->prepare("
  SELECT ct_career_name, ct_slug FROM DcVnchxg4_customtables_table_careers
  WHERE published = 1 AND (
    ct_career_skill_one = %d OR
    ct_career_skill_two = %d OR
    ct_career_skill_three = %d )
  ORDER BY ct_career_name ASC
", $skill->ct_id, $skill->ct_id, $skill->ct_id ) );

if ( $careers ) :
?>
  <h2>Careers with This Skill</h2>
  <div class="skill-grid">
      <div class="skill-card">
        </a>
      </div>
  </div>

  <div class="skill-grid" style="margin-top: 40px;">
  <div class="skill-card">
    <a href="/skills/">
      ← Back to Skills
    </a>
  </div>
</div>


</main>


} // namespace CharacterGeneratorDev
