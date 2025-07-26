<?php

namespace CharacterGeneratorDev {

/**
 * Template Name: Species Detail Page
 */
get_header();
?>

<main class="site-main" style="padding:20px;">
global $wpdb;
$slug = sanitize_text_field( get_query_var('slug') );

// Load species and related lookups
$species = $wpdb->get_row( $wpdb->prepare("
  SELECT 
    s.*,
    hab.ct_habitat_name, hab.ct_slug AS habitat_slug,
    d.ct_diet_name, d.ct_slug AS diet_slug,
    cy.ct_cycle_name, cy.ct_slug AS cycle_slug,
    sen1.ct_senses_name AS sense_one, sen1.ct_slug AS sense_one_slug,
    sen2.ct_senses_name AS sense_two, sen2.ct_slug AS sense_two_slug,
    sen3.ct_senses_name AS sense_three, sen3.ct_slug AS sense_three_slug,
    w1.ct_weapons_name AS weapon_one, w1.ct_slug AS weapon_one_slug,
    w2.ct_weapons_name AS weapon_two, w2.ct_slug AS weapon_two_slug,
    w3.ct_weapons_name AS weapon_three, w3.ct_slug AS weapon_three_slug,
    sk1.ct_skill_name AS skill_one, sk1.ct_slug AS skill_one_slug,
    sk2.ct_skill_name AS skill_two, sk2.ct_slug AS skill_two_slug,
    sk3.ct_skill_name AS skill_three, sk3.ct_slug AS skill_three_slug,
    g1.ct_gifts_name AS gift_one, g1.ct_slug AS gift_one_slug,
    g2.ct_gifts_name AS gift_two, g2.ct_slug AS gift_two_slug,
    g3.ct_gifts_name AS gift_three, g3.ct_slug AS gift_three_slug,
    b.ct_book_name, b.ct_ct_slug AS book_slug
  FROM DcVnchxg4_customtables_table_species AS s
  LEFT JOIN DcVnchxg4_customtables_table_habitat AS hab ON s.ct_species_habitat = hab.ct_id
  LEFT JOIN DcVnchxg4_customtables_table_diet AS d ON s.ct_species_diet = d.ct_id
  LEFT JOIN DcVnchxg4_customtables_table_cycle AS cy ON s.ct_species_cycle = cy.ct_id
  LEFT JOIN DcVnchxg4_customtables_table_senses AS sen1 ON s.ct_species_senses_one = sen1.ct_id
  LEFT JOIN DcVnchxg4_customtables_table_senses AS sen2 ON s.ct_species_senses_two = sen2.ct_id
  LEFT JOIN DcVnchxg4_customtables_table_senses AS sen3 ON s.ct_species_senses_three = sen3.ct_id
  LEFT JOIN DcVnchxg4_customtables_table_weapons AS w1 ON s.ct_species_weapon_one = w1.ct_id
  LEFT JOIN DcVnchxg4_customtables_table_weapons AS w2 ON s.ct_species_weapon_two = w2.ct_id
  LEFT JOIN DcVnchxg4_customtables_table_weapons AS w3 ON s.ct_species_weapon_three = w3.ct_id
  LEFT JOIN DcVnchxg4_customtables_table_skills AS sk1 ON s.ct_species_skill_one = sk1.ct_id
  LEFT JOIN DcVnchxg4_customtables_table_skills AS sk2 ON s.ct_species_skill_two = sk2.ct_id
  LEFT JOIN DcVnchxg4_customtables_table_skills AS sk3 ON s.ct_species_skill_three = sk3.ct_id
  LEFT JOIN DcVnchxg4_customtables_table_gifts AS g1 ON s.ct_species_gift_one = g1.ct_id
  LEFT JOIN DcVnchxg4_customtables_table_gifts AS g2 ON s.ct_species_gift_two = g2.ct_id
  LEFT JOIN DcVnchxg4_customtables_table_gifts AS g3 ON s.ct_species_gift_three = g3.ct_id
  LEFT JOIN DcVnchxg4_customtables_table_books AS b ON s.ct_species_source_book = b.ct_id
  WHERE s.ct_slug = %s
", $slug ) );

if ( ! $species ) {
    echo '<p>Sorry, that species couldn’t be found.</p>';
    get_footer();
    exit;
}
?>


<!-- Core Traits -->
<h2>Core Traits</h2>
<div class="skill-grid" style="margin-bottom: 20px;">
  <div class="skill-card">
    </a>
  </div>
  <div class="skill-card">
    </a>
  </div>
  <div class="skill-card">
    </a>
  </div>
</div>

<!-- Senses -->
<h2>Senses</h2>
<div class="skill-grid" style="margin-bottom: 20px;">
    if ( $species->$s ) :
      $slug = $s . '_slug'; ?>
      <div class="skill-card">
        </a>
      </div>
</div>

<!-- Weapons -->
<h2>Weapons</h2>
<div class="skill-grid" style="margin-bottom: 20px;">
    if ( $species->$w ) :
      $slug = $w . '_slug'; ?>
      <div class="skill-card">
        </a>
      </div>
</div>

<!-- Skills -->
<h2>Skills</h2>
<div class="skill-grid" style="margin-bottom: 20px;">
    if ( $species->$s ) :
      $slug = $s . '_slug'; ?>
      <div class="skill-card">
        </a>
      </div>
</div>

<!-- Gifts -->
<h2>Gifts</h2>
<ul class="skill-grid" style="gap:20px; list-style:none; padding:0; margin:0 0 20px;">
    <li class="skill-card">
      </a>
    </li>

    <li class="skill-card">
      </a>
    </li>

    <li class="skill-card">
      </a>
    </li>
</ul>

<!-- Description -->
<h2>Description</h2>

<h2>Source</h2>
  <div class="skill-grid" style="margin-bottom: 20px;">
    <div class="skill-card">
      </a>
    </div>
  </div>


<!-- Back -->
<div class="skill-grid" style="margin-top: 40px;">
  <div class="skill-card">
    <a href="/species-catalog/">
      ← Back to Species
    </a>
  </div>
</div>

</main>

} // namespace CharacterGeneratorDev
