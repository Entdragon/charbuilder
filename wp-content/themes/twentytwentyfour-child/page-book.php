<?php

namespace CharacterGeneratorDev {

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


  <div class="book-header-flex" style="display: flex; flex-wrap: wrap; gap: 20px; margin-bottom: 30px;">
    <div class="book-description" style="flex: 2; min-width: 250px;">
    </div>

      <div class="book-cover" style="flex: 1; min-width: 200px; max-width: 400px;">
          'alt' => $book->ct_book_name,
          'style' => 'width: 100%; height: auto; border: 2px solid #ccc; border-radius: 8px;'
        ] ); ?>
      </div>
  </div>

    <div class="skill-grid" style="margin-top: 20px;">
      <div class="skill-card">
          🛒 Buy this book from DriveThru RPG
        </a>
      </div>
    </div>

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
        <div class="skill-card">
        </div>
    </div>

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
        <div class="skill-card">
        </div>
    </div>

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
      <div class="skill-card">
      </div>
  </div>




  <div class="skill-grid" style="margin-top: 40px;">
    <div class="skill-card">
      <a href="/ironclaw-books/">← Back to Book List</a>
    </div>
  </div>

</article>


} // namespace CharacterGeneratorDev
