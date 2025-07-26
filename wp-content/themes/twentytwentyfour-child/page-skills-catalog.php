<?php

namespace CharacterGeneratorDev {

/**
 * Template Name: Skills Catalog
 */
get_header();
?>

<main class="site-main" style="padding:20px;">
  <h1>Skills</h1>

  <div class="skill-grid">
    global $wpdb;

    $skills = $wpdb->get_results("
      SELECT ct_skill_name, ct_slug
        FROM DcVnchxg4_customtables_table_skills
      ORDER BY ct_skill_name ASC
    ");

    foreach ( $skills as $sk ) : ?>
      <div class="skill-card">
        </a>
      </div>
  </div>
</main>



} // namespace CharacterGeneratorDev
