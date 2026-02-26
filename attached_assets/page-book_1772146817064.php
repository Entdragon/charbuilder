<?php
/**
 * Template Name: Book Detail
 */
get_header();

$slug = sanitize_text_field( get_query_var('slug') );
global $wpdb;

$book = $wpdb->get_row( $wpdb->prepare("
  SELECT * FROM DcVnchxg4_customtables_table_books
  WHERE ct_ct_slug = %s
", $slug ) );

if ( ! $book ) {
  echo '<p>Sorry, that book couldn’t be found.</p>';
  get_footer();
  exit;
}
?>

<article class="book-detail" style="padding: 20px;">

  <h1 style="margin-bottom: 1em;"><?php echo esc_html( $book->ct_book_name ); ?></h1>

  <div class="book-header-flex" style="display: flex; flex-wrap: wrap; gap: 20px; margin-bottom: 30px;">
    <div class="book-description" style="flex: 2; min-width: 250px;">
      <p><?php echo nl2br( esc_html( $book->ct_book_abstract ) ); ?></p>
    </div>

    <?php if ( $book->ct_ct_cover_image ) : ?>
      <div class="book-cover" style="flex: 1; min-width: 200px; max-width: 400px;">
        <?php echo wp_get_attachment_image( $book->ct_ct_cover_image, 'medium', false, [
          'alt' => $book->ct_book_name,
          'style' => 'width: 100%; height: auto; border: 2px solid #ccc; border-radius: 8px;'
        ] ); ?>
      </div>
    <?php endif; ?>
  </div>

  <?php if ( $book->ct_url_to_buy ) : ?>
    <div class="skill-grid" style="margin-top: 20px;">
      <div class="skill-card">
        <a class="skill-tab external-link" href="<?php echo esc_url( $book->ct_url_to_buy ); ?>" target="_blank" rel="noopener noreferrer">
          🛒 Buy this book from DriveThru RPG
        </a>
      </div>
    </div>
  <?php endif; ?>

  <?php
     $careers = $wpdb->get_results( $wpdb->prepare("
    SELECT ct_career_name, ct_slug
    FROM DcVnchxg4_customtables_table_careers
    WHERE ct_career_source_book = %d AND published = 1
    ORDER BY ct_career_name ASC
  ", $book->ct_id ) );

  if ( $careers ) : ?>
    <hr>
    <h2>Careers in This Book</h2>
    <div class="skill-grid">
      <?php foreach ( $careers as $c ) : ?>
        <div class="skill-card">
          <a href="/career/<?php echo esc_attr( $c->ct_slug ); ?>/"><?php echo esc_html( $c->ct_career_name ); ?></a>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <?php
  $species = $wpdb->get_results( $wpdb->prepare("
    SELECT ct_species_name, ct_slug
    FROM DcVnchxg4_customtables_table_species
    WHERE ct_species_source_book = %d
    ORDER BY ct_species_name ASC
  ", $book->ct_id ) );

  if ( $species ) : ?>
      <hr>
    <h2>Species in This Book</h2>
    <div class="skill-grid">
      <?php foreach ( $species as $s ) : ?>
        <div class="skill-card">
          <a href="/species/<?php echo esc_attr( $s->ct_slug ); ?>/"><?php echo esc_html( $s->ct_species_name ); ?></a>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

<?php
$gifts = $wpdb->get_results( $wpdb->prepare("
  SELECT ct_gifts_name, ct_slug
  FROM DcVnchxg4_customtables_table_gifts
  WHERE ct_book_id = %d
  ORDER BY ct_gifts_name ASC
", $book->ct_id ) );

if ( $gifts ) : ?>
    <hr>
  <h2>Gifts in This Book</h2>
  <div class="skill-grid">
    <?php foreach ( $gifts as $g ) : ?>
      <div class="skill-card">
        <a href="/gift/<?php echo esc_attr( $g->ct_slug ); ?>/"><?php echo esc_html( $g->ct_gifts_name ); ?></a>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>




  <div class="skill-grid" style="margin-top: 40px;">
    <div class="skill-card">
      <a href="/ironclaw-books/">← Back to Book List</a>
    </div>
  </div>

</article>

<?php get_footer(); ?>
