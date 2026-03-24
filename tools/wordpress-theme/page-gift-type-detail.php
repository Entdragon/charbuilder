<?php
/**
 * Template Name: Gift Type Detail Page
 *
 * Lists all gifts belonging to a given gift type.
 *
 * Fixed for Migration 002: the old query used ct_gift_type / ct_gift_type_two …
 * ct_gift_type_eight columns on the gifts table — those columns no longer exist.
 * The normalised schema stores gift↔type relationships in:
 *   DcVnchxg4_customtables_table_gift_type_map  (columns: gift_id, type_id)
 */
get_header();
?>

<main class="site-main" style="padding: 20px;">
<?php
global $wpdb;
$p    = 'DcVnchxg4_';
$slug = sanitize_text_field( get_query_var( 'slug' ) );

$type = $wpdb->get_row( $wpdb->prepare(
    "SELECT * FROM {$p}customtables_table_gifttype WHERE ct_slug = %s LIMIT 1",
    $slug
) );

if ( ! $type ) {
    echo '<p>Gift type not found.</p>';
    get_footer();
    exit;
}
?>

<h1><?php echo esc_html( $type->ct_type_name ); ?></h1>

<?php if ( ! empty( $type->ct_description ) ) : ?>
  <p><?php echo nl2br( esc_html( $type->ct_description ) ); ?></p>
<?php endif; ?>

<?php
/*
 * Correct query: JOIN gifts → gift_type_map on type_id.
 * Old (broken) query used ct_gift_type / ct_gift_type_two … ct_gift_type_eight
 * FK columns that were removed in the schema normalisation.
 */
$gifts = $wpdb->get_results( $wpdb->prepare(
    "SELECT g.ct_gifts_name, g.ct_slug
       FROM {$p}customtables_table_gifts AS g
       JOIN {$p}customtables_table_gift_type_map AS gtm
         ON gtm.gift_id = g.ct_id
      WHERE gtm.type_id = %d
        AND g.published = 1
      ORDER BY g.ct_gifts_name ASC",
    $type->ct_id
) );

if ( $gifts ) :
?>
  <h2>Gifts</h2>
  <div class="skill-grid" style="margin-bottom: 20px;">
    <?php foreach ( $gifts as $g ) : ?>
      <div class="skill-card">
        <a href="/gift/<?php echo esc_attr( $g->ct_slug ); ?>/">
          <?php echo esc_html( $g->ct_gifts_name ); ?>
        </a>
      </div>
    <?php endforeach; ?>
  </div>
<?php else : ?>
  <p>No gifts found for this type.</p>
<?php endif; ?>

<div class="skill-grid" style="margin-top: 40px;">
  <div class="skill-card">
    <a href="/gift-types/">← Back to Gift Types</a>
  </div>
</div>

</main>

<?php get_footer(); ?>
