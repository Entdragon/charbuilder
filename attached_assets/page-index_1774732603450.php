<?php
/**
 * Template Name: Species, Gifts & Career Index
 */
get_header();
?>

<main class="site-main" style="padding: 20px;">
  <h1>Index</h1>

  <?php
  global $wpdb;

  // Per-section optional WHERE clause (only used where needed)
  $sections = [
    'Species Habitats'    => ['DcVnchxg4_customtables_table_habitat',           'ct_habitat_name',        'species-habitat',  ''],
    'Species Diet'        => ['DcVnchxg4_customtables_table_diet',              'ct_diet_name',           'species-diet',     ''],
    'Species Cycle'       => ['DcVnchxg4_customtables_table_cycle',             'ct_cycle_name',          'species-cycle',    ''],
    'Species Senses'      => ['DcVnchxg4_customtables_table_senses',            'ct_senses_name',         'species-senses',   ''],

    // ✅ Only species-level weapons
    'Species Weapons'     => ['DcVnchxg4_customtables_table_weapons',           'ct_weapons_name',        'species-weapons',  'WHERE ct_is_species_weapon = 1'],

    'Career Type'         => ['DcVnchxg4_customtables_table_careertype',        'ct_careertype_name',     'career-type',      ''],
    'Career Archetype'    => ['DcVnchxg4_customtables_table_archtype',          'ct_archtype_name',       'career-archtype',  ''],
    'Gift Class'          => ['DcVnchxg4_customtables_table_giftclass',         'ct_class_name',          'gift-class',       ''],
    'Gift Type'           => ['DcVnchxg4_customtables_table_gifttype',          'ct_type_name',           'gift-type',        ''],
    'Gift Refresh'        => ['DcVnchxg4_customtables_table_refresh',           'ct_refresh_name',        'refresh',          ''],
    'Skill Descriptors'   => ['DcVnchxg4_customtables_table_skillsdescriptors', 'ct_name',                'skill-descriptor', ''],
  ];

  foreach ( $sections as $label => $def ) {
    [$table, $name_field, $prefix, $where] = array_pad($def, 4, '');
    $where = is_string($where) ? trim($where) : '';

    echo "<h2>" . esc_html($label) . "</h2>";
    echo '<div class="skill-grid">';

    $sql = "SELECT `{$name_field}` AS name, ct_slug
            FROM `{$table}`
            {$where}
            ORDER BY name ASC";

    $items = $wpdb->get_results($sql);

    foreach ( $items as $item ) {
      $slug = isset($item->ct_slug) ? trim((string) $item->ct_slug) : '';
      if ($slug === '') continue;

      echo '<div class="skill-card">';
      echo '<a href="' . esc_url( home_url('/' . $prefix . '/' . $slug . '/') ) . '">' . esc_html( $item->name ) . '</a>';
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

<?php get_footer(); ?>