<?php
/**
 * Template Name: Species Weapons Detail Page
 *
 * Lists all species that have a particular natural weapon.
 *
 * Fixed for Migration 002: the old query used ct_species_weapon_one/two/three
 * columns on the species table — those columns no longer exist.
 * The normalised schema stores all species attributes (weapons, gifts, cycle,
 * habitat, etc.) in:
 *   DcVnchxg4_customtables_table_species_traits
 *   (columns: species_id, trait_key, ref_id, text_value, sort)
 * Weapon slots use trait_key = 'weapon_1', 'weapon_2', 'weapon_3'
 * and ref_id = the weapon's ct_id.
 */
get_header();
?>

<main class="site-main" style="padding: 20px;">
<?php
global $wpdb;
$p    = 'DcVnchxg4_';
$slug = sanitize_text_field( get_query_var( 'slug' ) );

$weapon = $wpdb->get_row( $wpdb->prepare(
    "SELECT * FROM {$p}customtables_table_weapons WHERE ct_slug = %s AND published = 1 LIMIT 1",
    $slug
) );

if ( ! $weapon ) {
    echo '<p>Weapon not found.</p>';
    get_footer();
    exit;
}
?>

<h1><?php echo esc_html( $weapon->ct_weapons_name ); ?></h1>

<?php if ( ! empty( $weapon->ct_description ) ) : ?>
  <p><?php echo nl2br( esc_html( $weapon->ct_description ) ); ?></p>
<?php endif; ?>

<?php
/*
 * Correct query: JOIN species → species_traits where trait_key is one of the
 * three weapon slots and ref_id matches this weapon's ct_id.
 * Old (broken) query used ct_species_weapon_one/two/three columns that were
 * removed in the schema normalisation.
 */
$species_using = $wpdb->get_results( $wpdb->prepare(
    "SELECT DISTINCT s.ct_species_name, s.ct_slug
       FROM {$p}customtables_table_species AS s
       JOIN {$p}customtables_table_species_traits AS st
         ON st.species_id = s.ct_id
      WHERE st.trait_key IN ('weapon_1', 'weapon_2', 'weapon_3')
        AND st.ref_id = %d
        AND s.published = 1
      ORDER BY s.ct_species_name ASC",
    $weapon->ct_id
) );

if ( $species_using ) :
?>
  <h2>Species With This Weapon</h2>
  <div class="skill-grid" style="margin-bottom: 20px;">
    <?php foreach ( $species_using as $sp ) : ?>
      <div class="skill-card">
        <a href="/species/<?php echo esc_attr( $sp->ct_slug ); ?>/">
          <?php echo esc_html( $sp->ct_species_name ); ?>
        </a>
      </div>
    <?php endforeach; ?>
  </div>
<?php else : ?>
  <p>No species found with this weapon.</p>
<?php endif; ?>

<div class="skill-grid" style="margin-top: 40px;">
  <div class="skill-card">
    <a href="/weapons/">← Back to Weapons</a>
  </div>
</div>

</main>

<?php get_footer(); ?>
