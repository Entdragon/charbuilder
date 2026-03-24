<?php
/**
 * Template Name: Species Cycle Detail Page
 *
 * Lists all species active during a particular cycle (Day / Night / Twilight).
 *
 * Fixed for Migration 002: the old query used ct_species_cycle column on the
 * species table — that column no longer exists.
 * The normalised schema stores all species attributes in:
 *   DcVnchxg4_customtables_table_species_traits
 *   (columns: species_id, trait_key, ref_id, text_value, sort)
 * The cycle slot uses trait_key = 'cycle' and ref_id = the cycle's ct_id.
 */
get_header();
?>

<main class="site-main" style="padding: 20px;">
<?php
global $wpdb;
$p    = 'DcVnchxg4_';
$slug = sanitize_text_field( get_query_var( 'slug' ) );

$cycle = $wpdb->get_row( $wpdb->prepare(
    "SELECT * FROM {$p}customtables_table_cycle WHERE ct_slug = %s AND published = 1 LIMIT 1",
    $slug
) );

if ( ! $cycle ) {
    echo '<p>Cycle not found.</p>';
    get_footer();
    exit;
}
?>

<h1><?php echo esc_html( $cycle->ct_cycle_name ); ?></h1>

<?php if ( ! empty( $cycle->ct_description ) ) : ?>
  <p><?php echo nl2br( esc_html( $cycle->ct_description ) ); ?></p>
<?php endif; ?>

<?php
/*
 * Correct query: JOIN species → species_traits where trait_key = 'cycle'
 * and ref_id matches this cycle's ct_id.
 * Old (broken) query used ct_species_cycle column that was removed in the
 * schema normalisation.
 */
$species_using = $wpdb->get_results( $wpdb->prepare(
    "SELECT s.ct_species_name, s.ct_slug
       FROM {$p}customtables_table_species AS s
       JOIN {$p}customtables_table_species_traits AS st
         ON st.species_id = s.ct_id
      WHERE st.trait_key = 'cycle'
        AND st.ref_id = %d
        AND s.published = 1
      ORDER BY s.ct_species_name ASC",
    $cycle->ct_id
) );

if ( $species_using ) :
?>
  <h2>Species Active During This Cycle</h2>
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
  <p>No species found for this cycle.</p>
<?php endif; ?>

<div class="skill-grid" style="margin-top: 40px;">
  <div class="skill-card">
    <a href="/cycles/">← Back to Cycles</a>
  </div>
</div>

</main>

<?php get_footer(); ?>
