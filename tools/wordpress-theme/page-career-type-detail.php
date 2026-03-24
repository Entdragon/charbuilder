<?php
/**
 * Template Name: Career Type Detail Page
 *
 * Lists all careers belonging to a given career type.
 * Fixed: typo ct_caeertype_name → ct_careertype_name (was breaking on line 25).
 */
get_header();
?>

<main class="site-main" style="padding: 20px;">
<?php
global $wpdb;
$p    = 'DcVnchxg4_';
$slug = sanitize_text_field( get_query_var( 'slug' ) );

$type = $wpdb->get_row( $wpdb->prepare(
    "SELECT * FROM {$p}customtables_table_careertype WHERE ct_slug = %s LIMIT 1",
    $slug
) );

if ( ! $type ) {
    echo '<p>Career type not found.</p>';
    get_footer();
    exit;
}

/* ---- line 25 equivalent — correct column name ---- */
$type_name = esc_html( $type->ct_careertype_name );
?>

<h1><?php echo $type_name; ?></h1>

<?php if ( ! empty( $type->ct_description ) ) : ?>
  <p><?php echo nl2br( esc_html( $type->ct_description ) ); ?></p>
<?php endif; ?>

<?php
$careers = $wpdb->get_results( $wpdb->prepare(
    "SELECT ct_career_name, ct_slug
       FROM {$p}customtables_table_careers
      WHERE ct_career_type = %d
        AND published = 1
      ORDER BY ct_career_name ASC",
    $type->ct_id
) );

if ( $careers ) :
?>
  <h2>Careers</h2>
  <div class="skill-grid" style="margin-bottom: 20px;">
    <?php foreach ( $careers as $c ) : ?>
      <div class="skill-card">
        <a href="/career/<?php echo esc_attr( $c->ct_slug ); ?>/">
          <?php echo esc_html( $c->ct_career_name ); ?>
        </a>
      </div>
    <?php endforeach; ?>
  </div>
<?php else : ?>
  <p>No careers found for this type.</p>
<?php endif; ?>

<div class="skill-grid" style="margin-top: 40px;">
  <div class="skill-card">
    <a href="/career-types/">← Back to Career Types</a>
  </div>
</div>

</main>

<?php get_footer(); ?>
