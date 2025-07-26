<?php

namespace CharacterGeneratorDev {

/**
 * Template Name: Book Detail
 */
get_header();

// Grab the slug from the URL
$slug = sanitize_text_field( get_query_var('slug') );

global $wpdb;

// 1) Load the Book
$book = $wpdb->get_row( $wpdb->prepare("
    SELECT * 
      FROM DcVnchxg4_customtables_table_books
     WHERE ct_ct_slug = %s
", $slug ) );

if ( ! $book ) {
    echo '<p>Sorry, that book couldn’t be found.</p>';
    get_footer();
    exit;
}
?>

<article class="book-detail" style="padding:20px;">
            target="_blank" rel="noopener noreferrer">
          Buy this book
      </a></p>

    <hr>

    <!-- 2) Careers in this Book -->
    $careers = $wpdb->get_results( $wpdb->prepare("
        SELECT ct_career_name, ct_slug
          FROM DcVnchxg4_customtables_table_careers
         WHERE ct_career_source_book = %d
           AND published = 1
         ORDER BY ct_career_name ASC
    ", $book->ct_id ) );

    if ( $careers ) : ?>
      <h2>Careers in This Book</h2>
      <ul style="list-style: none; padding-left: 0;">
          <li>
            </a>
          </li>
      </ul>

    <hr>

    <!-- 3) Species in this Book -->
    // Assumes your species table has a ct_species_source_book FK
    $species = $wpdb->get_results( $wpdb->prepare("
        SELECT ct_species_name, ct_slug
          FROM DcVnchxg4_customtables_table_species
         WHERE ct_species_source_book = %d
         ORDER BY ct_species_name ASC
    ", $book->ct_id ) );

    if ( $species ) : ?>
      <h2>Species in This Book</h2>
      <ul style="list-style: none; padding-left: 0;">
          <li>
            </a>
          </li>
      </ul>

    <hr>

    <!-- 4) Gifts in this Book -->
    // Gather all gifts used by careers in this book
    $gifts = $wpdb->get_results( $wpdb->prepare("
        SELECT DISTINCT gift.ct_gifts_name, gift.ct_slug
          FROM DcVnchxg4_customtables_table_careers AS c
    INNER JOIN DcVnchxg4_customtables_table_gifts    AS gift
            ON gift.ct_id IN (
                c.ct_career_gift_one,
                c.ct_career_gift_two,
                c.ct_career_gift_three
            )
         WHERE c.ct_career_source_book = %d
         ORDER BY gift.ct_gifts_name ASC
    ", $book->ct_id ) );

    if ( $gifts ) : ?>
      <h2>Gifts in This Book</h2>
      <ul style="display: flex; gap: 20px; list-style: none; padding-left: 0;">
          <li>
            </a>
          </li>
      </ul>

    <hr>
    <p><a href="/ironclaw-books/">← Back to catalog</a></p>
</article>


} // namespace CharacterGeneratorDev
