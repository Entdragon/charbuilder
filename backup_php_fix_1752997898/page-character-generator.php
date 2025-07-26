<?php

namespace CharacterGeneratorDev {

// 🛑 Block access unless logged in — show message instead of redirecting
if ( ! is_user_logged_in() ) {
  get_header();
  echo '<main style="padding:20px;">';
  echo '<p>You must be logged in to use the character generator. ';
  echo '<a href="' . esc_url( wp_login_url( get_permalink() ) ) . '">Log in here</a>.</p>';
  echo '</main>';
  get_footer();
  exit;
}
?>



/**
 * Template Name: Character Generator
 */
get_header();
?>

<main class="site-main" style="padding: 20px;">
  <h1>Character Generator</h1>

  <div class="skill-grid">
    <div class="skill-card" style="padding: 2em;">
    </div>
  </div>

</main>


} // namespace CharacterGeneratorDev
