<?php
/**
 * Template Name: Character Generator
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();
?>

<style id="loc-character-generator-page-style">
  .loc-cg-page {
    max-width: 1120px;
    margin: 0 auto;
  }

  .loc-cg-hero {
    margin: 0 0 1.25rem;
  }

  .loc-cg-hero h1 {
    margin: 0 0 0.45rem;
  }
</style>

<div class="loc-cg-page">
  <section class="loc-cg-hero">
    <h1><?php echo esc_html( get_the_title() ?: 'Character Generator' ); ?></h1>
  </section>

  <?php echo loc_character_generator_shortcode(); ?>
</div>

<?php
get_footer();
