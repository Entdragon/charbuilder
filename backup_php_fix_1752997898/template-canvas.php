<?php

namespace CharacterGeneratorDev {

/**
 * Template canvas file to render the current 'wp_template'.
 *
 * @package WordPress
 */

/*
 * Get the template HTML.
 * This needs to run before <head> so that blocks can add scripts and styles in wp_head().
 */
$template_html = get_the_block_template_html();
?><!DOCTYPE html>
<head>
</head>



</body>
</html>

} // namespace CharacterGeneratorDev
