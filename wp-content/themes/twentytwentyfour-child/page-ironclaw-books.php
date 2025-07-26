<?php

namespace CharacterGeneratorDev {

/* Template Name: Ironclaw Book Catalog */
get_header();
?>

<main class="site-main" style="padding: 20px;">
  <h1>Ironclaw Book Catalog</h1>

  <div class="skill-grid" style="gap: 20px;">
    global $wpdb;
    $table = $wpdb->prefix . 'customtables_table_books';
    $books = $wpdb->get_results("SELECT * FROM $table");

    if ( $books ) :
      foreach ( $books as $book ) :
        $slug     = esc_attr( $book->ct_ct_slug );
        $name     = esc_html( $book->ct_book_name );
        $abstract = esc_html( $book->ct_book_abstract );
        $buy_url  = esc_url( $book->ct_url_to_buy );
        $cover_id = $book->ct_ct_cover_image;
    ?>
      <div class="skill-card" style="display: flex; flex-direction: column; justify-content: space-between; padding: 20px;">
          <div class="book-thumbnail" style="margin-bottom: 12px;">
              'alt' => $name,
              'class' => 'book-cover-thumb'
            ] ); ?>
          </div>

        <h3 style="margin-top: 0;">
          </a>
        </h3>


          <div style="margin-top: auto; margin-top: 10px;">
              🛒 Buy this book from DriveThru RPG
            </a>
          </div>
      </div>
      endforeach;
    else :
    ?>
      <p>No books found.</p>
  </div>
</main>






} // namespace CharacterGeneratorDev
