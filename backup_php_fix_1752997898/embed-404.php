<?php

namespace CharacterGeneratorDev {

/**
 * Contains the post embed content template part
 *
 * When a post is embedded in an iframe, this file is used to create the content template part
 * output if the active theme does not include an embed-404.php template.
 *
 * @package WordPress
 * @subpackage Theme_Compat
 * @since 4.5.0
 */
?>
<div class="wp-embed">

	<div class="wp-embed-excerpt">
		<p>
			printf(
				/* translators: %s: A link to the embedded site. */
				__( 'It looks like nothing was found at this location. Maybe try visiting %s directly?' ),
				'<strong><a href="' . esc_url( home_url() ) . '">' . esc_html( get_bloginfo( 'name' ) ) . '</a></strong>'
			);
			?>
		</p>
	</div>

	/** This filter is documented in wp-includes/theme-compat/embed-content.php */
	do_action( 'embed_content' );
	?>

	<div class="wp-embed-footer">
	</div>
</div>

} // namespace CharacterGeneratorDev
