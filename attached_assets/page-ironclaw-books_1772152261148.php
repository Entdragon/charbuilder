<?php
/* Template Name: Ironclaw Book Catalog */
get_header();
?>

<main class="site-main" style="padding: 20px;">
  <h1>Ironclaw Book Catalog</h1>

  <div class="skill-grid" style="gap: 20px;">
    <?php
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
        <?php if ( $cover_id ) : ?>
          <div class="book-thumbnail" style="margin-bottom: 12px;">
            <?php echo wp_get_attachment_image( $cover_id, 'thumbnail', false, [
              'alt' => $name,
              'class' => 'book-cover-thumb'
            ] ); ?>
          </div>
        <?php endif; ?>

        <h3 style="margin-top: 0;">
          <a class="skill-tab internal-link" href="/book/<?php echo $slug; ?>/">
            📖 <?php echo $name; ?>
          </a>
        </h3>

        <p><?php echo mb_strimwidth( $abstract, 0, 150, '…' ); ?></p>

        <?php if ( $buy_url ) : ?>
          <div style="margin-top: auto; margin-top: 10px;">
            <a class="skill-tab external-link" href="<?php echo $buy_url; ?>" target="_blank" rel="noopener noreferrer">
              🛒 Buy this book from DriveThru RPG
            </a>
          </div>
        <?php endif; ?>
      </div>
    <?php
      endforeach;
    else :
    ?>
      <p>No books found.</p>
    <?php endif; ?>
  </div>
</main>

<?php get_footer(); ?>




