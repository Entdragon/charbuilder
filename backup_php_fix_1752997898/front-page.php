<?php

namespace CharacterGeneratorDev {

get_header();
?>

<main class="site-main">
  if ( have_posts() ) {
    while ( have_posts() ) {
      the_post();
      the_content();
    }
  }
  ?>
</main>

get_footer();
?>

} // namespace CharacterGeneratorDev
