<?php

namespace CharacterGeneratorDev {

/**
 * Template Name: Gifts Manifold
 */
get_header();
?>

<main class="site-main" style="padding:20px;">
  <h1>Gifts: Manifold</h1>

  global $wpdb;

  $gifts = $wpdb->get_results("
    SELECT ct_id, ct_gifts_name, ct_slug
      FROM DcVnchxg4_customtables_table_gifts
     WHERE ct_gifts_manifold = 1
     ORDER BY ct_gifts_name ASC
  ");
  ?>

  <div class="skill-grid">
      <div class="skill-card">
        </a>
    </div>
  </div>


  <div class="skill-grid" style="margin-top: 40px;">
  <div class="skill-card">
    <a href="/index/">
      ← Back to Index
    </a>
  </div>
</div>
</main>


} // namespace CharacterGeneratorDev
