<?php
/**
 * Template Name: Species Detail Page
 *
 * Updated for Migration 002: all FK columns on the species table were replaced
 * by the species_traits junction table (trait_key: habitat, diet, cycle,
 * sense_1/2/3, weapon_1/2/3, skill_1/2/3, gift_1/2/3).
 * Skills are now stored as plain text_value — they display without a link.
 */
get_header();
?>

<main class="site-main" style="padding:20px;">
<?php
global $wpdb;
$p    = 'DcVnchxg4_';
$slug = sanitize_text_field( get_query_var( 'slug' ) );

$species = $wpdb->get_row( $wpdb->prepare( "
  SELECT
    s.*,

    hab.ct_habitat_name,  hab.ct_slug  AS habitat_slug,
    d.ct_diet_name,       d.ct_slug    AS diet_slug,
    cy.ct_cycle_name,     cy.ct_slug   AS cycle_slug,

    sen1.ct_senses_name AS sense_one,   sen1.ct_slug AS sense_one_slug,
    sen2.ct_senses_name AS sense_two,   sen2.ct_slug AS sense_two_slug,
    sen3.ct_senses_name AS sense_three, sen3.ct_slug AS sense_three_slug,

    w1.ct_weapons_name AS weapon_one,   w1.ct_slug AS weapon_one_slug,
    w2.ct_weapons_name AS weapon_two,   w2.ct_slug AS weapon_two_slug,
    w3.ct_weapons_name AS weapon_three, w3.ct_slug AS weapon_three_slug,

    ts1.text_value AS skill_one,
    ts2.text_value AS skill_two,
    ts3.text_value AS skill_three,

    g1.ct_gifts_name AS gift_one,   g1.ct_slug AS gift_one_slug,
    g2.ct_gifts_name AS gift_two,   g2.ct_slug AS gift_two_slug,
    g3.ct_gifts_name AS gift_three, g3.ct_slug AS gift_three_slug,

    b.ct_book_name, b.ct_ct_slug AS book_slug

  FROM {$p}customtables_table_species AS s

  LEFT JOIN {$p}customtables_table_species_traits AS th
         ON th.species_id = s.ct_id AND th.trait_key = 'habitat'
  LEFT JOIN {$p}customtables_table_habitat AS hab ON hab.ct_id = th.ref_id

  LEFT JOIN {$p}customtables_table_species_traits AS tdt
         ON tdt.species_id = s.ct_id AND tdt.trait_key = 'diet'
  LEFT JOIN {$p}customtables_table_diet AS d ON d.ct_id = tdt.ref_id

  LEFT JOIN {$p}customtables_table_species_traits AS tcy
         ON tcy.species_id = s.ct_id AND tcy.trait_key = 'cycle'
  LEFT JOIN {$p}customtables_table_cycle AS cy ON cy.ct_id = tcy.ref_id

  LEFT JOIN {$p}customtables_table_species_traits AS tsn1
         ON tsn1.species_id = s.ct_id AND tsn1.trait_key = 'sense_1'
  LEFT JOIN {$p}customtables_table_senses AS sen1 ON sen1.ct_id = tsn1.ref_id

  LEFT JOIN {$p}customtables_table_species_traits AS tsn2
         ON tsn2.species_id = s.ct_id AND tsn2.trait_key = 'sense_2'
  LEFT JOIN {$p}customtables_table_senses AS sen2 ON sen2.ct_id = tsn2.ref_id

  LEFT JOIN {$p}customtables_table_species_traits AS tsn3
         ON tsn3.species_id = s.ct_id AND tsn3.trait_key = 'sense_3'
  LEFT JOIN {$p}customtables_table_senses AS sen3 ON sen3.ct_id = tsn3.ref_id

  LEFT JOIN {$p}customtables_table_species_traits AS tw1
         ON tw1.species_id = s.ct_id AND tw1.trait_key = 'weapon_1'
  LEFT JOIN {$p}customtables_table_weapons AS w1 ON w1.ct_id = tw1.ref_id

  LEFT JOIN {$p}customtables_table_species_traits AS tw2
         ON tw2.species_id = s.ct_id AND tw2.trait_key = 'weapon_2'
  LEFT JOIN {$p}customtables_table_weapons AS w2 ON w2.ct_id = tw2.ref_id

  LEFT JOIN {$p}customtables_table_species_traits AS tw3
         ON tw3.species_id = s.ct_id AND tw3.trait_key = 'weapon_3'
  LEFT JOIN {$p}customtables_table_weapons AS w3 ON w3.ct_id = tw3.ref_id

  LEFT JOIN {$p}customtables_table_species_traits AS ts1
         ON ts1.species_id = s.ct_id AND ts1.trait_key = 'skill_1'
  LEFT JOIN {$p}customtables_table_species_traits AS ts2
         ON ts2.species_id = s.ct_id AND ts2.trait_key = 'skill_2'
  LEFT JOIN {$p}customtables_table_species_traits AS ts3
         ON ts3.species_id = s.ct_id AND ts3.trait_key = 'skill_3'

  LEFT JOIN {$p}customtables_table_species_traits AS tg1
         ON tg1.species_id = s.ct_id AND tg1.trait_key = 'gift_1'
  LEFT JOIN {$p}customtables_table_gifts AS g1 ON g1.ct_id = tg1.ref_id

  LEFT JOIN {$p}customtables_table_species_traits AS tg2
         ON tg2.species_id = s.ct_id AND tg2.trait_key = 'gift_2'
  LEFT JOIN {$p}customtables_table_gifts AS g2 ON g2.ct_id = tg2.ref_id

  LEFT JOIN {$p}customtables_table_species_traits AS tg3
         ON tg3.species_id = s.ct_id AND tg3.trait_key = 'gift_3'
  LEFT JOIN {$p}customtables_table_gifts AS g3 ON g3.ct_id = tg3.ref_id

  LEFT JOIN {$p}customtables_table_books AS b
         ON s.ct_species_source_book = b.ct_id

  WHERE s.ct_slug = %s
", $slug ) );

if ( ! $species ) {
    echo '<p>Sorry, that species couldn&rsquo;t be found.</p>';
    get_footer();
    exit;
}
?>

<h1><?php echo esc_html( $species->ct_species_name ); ?></h1>

<!-- Core Traits -->
<h2>Core Traits</h2>
<div class="skill-grid" style="margin-bottom: 20px;">
  <?php if ( $species->ct_habitat_name ) : ?>
    <div class="skill-card">
      <a href="/species-habitat/<?php echo esc_attr( $species->habitat_slug ); ?>/">
        <?php echo esc_html( $species->ct_habitat_name ); ?>
      </a>
    </div>
  <?php endif; ?>
  <?php if ( $species->ct_diet_name ) : ?>
    <div class="skill-card">
      <a href="/species-diet/<?php echo esc_attr( $species->diet_slug ); ?>/">
        <?php echo esc_html( $species->ct_diet_name ); ?>
      </a>
    </div>
  <?php endif; ?>
  <?php if ( $species->ct_cycle_name ) : ?>
    <div class="skill-card">
      <a href="/species-cycle/<?php echo esc_attr( $species->cycle_slug ); ?>/">
        <?php echo esc_html( $species->ct_cycle_name ); ?>
      </a>
    </div>
  <?php endif; ?>
</div>

<!-- Senses -->
<h2>Senses</h2>
<div class="skill-grid" style="margin-bottom: 20px;">
  <?php foreach ( [ 'sense_one', 'sense_two', 'sense_three' ] as $sf ) :
    if ( ! empty( $species->$sf ) ) :
      $slug_field = $sf . '_slug'; ?>
      <div class="skill-card">
        <a href="/species-senses/<?php echo esc_attr( $species->$slug_field ); ?>/">
          <?php echo esc_html( $species->$sf ); ?>
        </a>
      </div>
  <?php endif; endforeach; ?>
</div>

<!-- Weapons -->
<h2>Weapons</h2>
<div class="skill-grid" style="margin-bottom: 20px;">
  <?php foreach ( [ 'weapon_one', 'weapon_two', 'weapon_three' ] as $wf ) :
    if ( ! empty( $species->$wf ) ) :
      $slug_field = $wf . '_slug'; ?>
      <div class="skill-card">
        <a href="/species-weapons/<?php echo esc_attr( $species->$slug_field ); ?>/">
          <?php echo esc_html( $species->$wf ); ?>
        </a>
      </div>
  <?php endif; endforeach; ?>
</div>

<!-- Skills (plain text — no slug available after migration) -->
<h2>Skills</h2>
<div class="skill-grid" style="margin-bottom: 20px;">
  <?php foreach ( [ 'skill_one', 'skill_two', 'skill_three' ] as $sf ) :
    if ( ! empty( $species->$sf ) ) : ?>
      <div class="skill-card"><?php echo esc_html( $species->$sf ); ?></div>
  <?php endif; endforeach; ?>
</div>

<!-- Gifts -->
<h2>Gifts</h2>
<ul class="skill-grid" style="gap:20px; list-style:none; padding:0; margin:0 0 20px;">
  <?php foreach ( [ 'gift_one', 'gift_two', 'gift_three' ] as $gf ) :
    if ( ! empty( $species->$gf ) ) :
      $slug_field   = $gf . '_slug';
      $choice_field = 'ct_species_' . $gf . '_choice'; ?>
      <li class="skill-card">
        <a href="/gift/<?php echo esc_attr( $species->$slug_field ); ?>/">
          <?php echo esc_html( $species->$gf ); ?>
        </a>
        <?php if ( ! empty( $species->$choice_field ) ) : ?>
          <br><small><?php echo esc_html( $species->$choice_field ); ?></small>
        <?php endif; ?>
      </li>
  <?php endif; endforeach; ?>
</ul>

<!-- Description -->
<h2>Description</h2>
<p><?php echo nl2br( esc_html( $species->ct_species_description ) ); ?></p>

<!-- Source -->
<h2>Source</h2>
<?php if ( ! empty( $species->ct_book_name ) ) : ?>
  <div class="skill-grid" style="margin-bottom: 20px;">
    <div class="skill-card">
      <a href="/book/<?php echo esc_attr( $species->book_slug ); ?>/" target="_blank" rel="noopener noreferrer">
        <?php echo esc_html( $species->ct_book_name ); ?>
      </a>
      <?php if ( ! empty( $species->ct_pg_no ) ) : ?>
        <br><small>Pg. No. <?php echo intval( $species->ct_pg_no ); ?></small>
      <?php endif; ?>
    </div>
  </div>
<?php endif; ?>

<!-- Back -->
<div class="skill-grid" style="margin-top: 40px;">
  <div class="skill-card">
    <a href="/species-catalog/">← Back to Species</a>
  </div>
</div>

</main>

<?php get_footer(); ?>
