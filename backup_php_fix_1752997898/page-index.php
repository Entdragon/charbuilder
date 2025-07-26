<?php

namespace CharacterGeneratorDev {

/**
 * Template Name: Species, Gifts & Career Index
 */
get_header();
?>

<main class="site-main" style="padding: 20px;">
  <h1>Index</h1>

  global $wpdb;

  $sections = [
    'Species Habitats'    => ['DcVnchxg4_customtables_table_habitat',         'ct_habitat_name',       'species-habitat'],
    'Species Diet'        => ['DcVnchxg4_customtables_table_diet',            'ct_diet_name',          'species-diet'],
    'Species Cycle'       => ['DcVnchxg4_customtables_table_cycle',           'ct_cycle_name',         'species-cycle'],
    'Species Senses'      => ['DcVnchxg4_customtables_table_senses',          'ct_senses_name',        'species-senses'],
    'Species Weapons'     => ['DcVnchxg4_customtables_table_weapons',         'ct_weapons_name',       'species-weapons'],
    'Career Type'         => ['DcVnchxg4_customtables_table_careertype',      'ct_careertype_name',    'career-type'],
    'Career Archetype'    => ['DcVnchxg4_customtables_table_archtype',        'ct_archtype_name',      'career-archtype'],
    'Gift Class'          => ['DcVnchxg4_customtables_table_giftclass',       'ct_class_name',         'gift-class'],
    'Gift Type'           => ['DcVnchxg4_customtables_table_gifttype',        'ct_type_name',          'gift-type'],
    'Gift Refresh'        => ['DcVnchxg4_customtables_table_refresh',         'ct_refresh_name',       'refresh'],
    'Skill Descriptors'   => ['DcVnchxg4_customtables_table_skillsdescriptors','ct_name',               'skill-descriptor']
  ];

  foreach ( $sections as $label => [$table, $name_field, $prefix] ) {
    echo "<h2>{$label}</h2>";
    echo '<div class="skill-grid">';

    $items = $wpdb->get_results(
      $wpdb->prepare("SELECT {$name_field} AS name, ct_slug FROM {$table} ORDER BY name ASC")
    );

    foreach ( $items as $item ) {
      echo '<div class="skill-card">';
      echo '<a href="/' . esc_attr( $prefix . '/' . $item->ct_slug ) . '/">' . esc_html( $item->name ) . '</a>';
      echo '</div>';
    }

    echo '</div><br>';
  }
  ?>

  <h2>Gift Traits</h2>
  <div class="skill-grid" style="margin-bottom: 20px;">
    <div class="skill-card">
      <a href="/gifts-multiple/">Multiple Times</a>
    </div>
    <div class="skill-card">
      <a href="/gifts-manifold/">Manifold Gift</a>
    </div>
  </div>
</main>



} // namespace CharacterGeneratorDev
