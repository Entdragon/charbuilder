<?php
/**
 * Template Name: Gift Class Detail
 */
get_header();

$slug = sanitize_text_field( get_query_var('slug') );
global $wpdb;

// Load the gift class entry
$gift_class = $wpdb->get_row( $wpdb->prepare("
    SELECT * 
      FROM DcVnchxg4_customtables_table_giftclass
     WHERE ct_slug = %s
", $slug ) );

if ( ! $gift_class ) {
    echo '<p>Sorry, that gift class couldn’t be found.</p>';
    get_footer();
    exit;
}
?>

<article class="book-detail" style="padding: 20px;">
  <h1><?php echo esc_html( $gift_class->ct_class_name ); ?></h1>

  <hr>

  <!-- Gifts with this class -->
  <?php
  $gifts = $wpdb->get_results( $wpdb->prepare("
      SELECT ct_gifts_name, ct_slug
        FROM DcVnchxg4_customtables_table_gifts
       WHERE ct_gift_class = %d
       ORDER BY ct_gifts_name ASC
  ", $gift_class->ct_id ) );

  if ( $gifts ) : ?>
    <h2>Gifts in This Class</h2>
    <div class="skill-grid">
      <?php foreach ( $gifts as $g ) : ?>
        <div class="skill-card">
          <a href="/gift/<?php echo esc_attr( $g->ct_slug ); ?>/">
            <?php echo esc_html( $g->ct_gifts_name ); ?>
          </a>
        </div>
      <?php endforeach; ?>
    </div>
  <?php else : ?>
    <p>No gifts are currently classified under this class.</p>
  <?php endif; ?>

  <hr>
   <div class="skill-grid" style="margin-top: 40px;">
  <div class="skill-card">
    <a href="/index/">
      ← Back to Index
    </a>
  </div>
</div>
</article>

<?php get_footer(); ?>
