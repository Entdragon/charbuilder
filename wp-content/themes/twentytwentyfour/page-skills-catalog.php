<?php

namespace CharacterGeneratorDev {

/**
 * Template Name: Skills Catalog
 */
get_header();
?>

<main class="site-main" style="padding:20px;">
  <h1>Skills</h1>

  <table style="width:100%; border-collapse:collapse;">
    <tbody>
      global $wpdb;

      $skills = $wpdb->get_results("
        SELECT *
          FROM DcVnchxg4_customtables_table_skills
        ORDER BY ct_skill_name ASC
      ");

      $count = 0;
      foreach ( $skills as $sk ) {
        if ( $count % 4 === 0 ) echo '<tr>';
        echo '<td style="border:1px solid #ccc; padding:8px; width:25%; vertical-align:top;">';
        echo '<a href="/skill/' . esc_attr($sk->ct_slug) . '/">'
           . esc_html($sk->ct_skill_name) . '</a>';
        echo '</td>';
        $count++;
        if ( $count % 4 === 0 ) echo '</tr>';
      }
      if ( $count % 4 !== 0 ) echo '</tr>';
      ?>
    </tbody>
  </table>
</main>


} // namespace CharacterGeneratorDev
