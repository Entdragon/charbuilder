<?php

namespace CharacterGeneratorDev {

/**
 * Title: Centered testimonial
 * Slug: twentytwentyfour/testimonial-centered
 * Keywords: quote, review, about
 * Categories: testimonials, text
 * Viewport width: 1300
 * Description: A centered testimonial section with an avatar, name, and job title.
 */
?>

<div class="wp-block-group alignfull has-base-color has-contrast-background-color has-text-color has-background" style="margin-top:0;margin-bottom:0;padding-top:var(--wp--preset--spacing--60);padding-right:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--60);padding-left:var(--wp--preset--spacing--60)">
	<!-- wp:group {"layout":{"type":"constrained"}} -->
	<div class="wp-block-group">
		<!-- wp:paragraph {"align":"center","style":{"typography":{"lineHeight":"1.2"}},"textColor":"base","fontSize":"x-large","fontFamily":"heading"} -->
		<p class="has-text-align-center has-base-color has-text-color has-heading-font-family has-x-large-font-size" style="line-height:1.2">
		</p>
		<!-- /wp:paragraph -->

		<!-- wp:spacer {"height":"var:preset|spacing|10"} -->
		<div style="height:var(--wp--preset--spacing--10)" aria-hidden="true" class="wp-block-spacer"></div>
		<!-- /wp:spacer -->

		<div class="wp-block-group">
			<!-- wp:image {"align":"center","width":"60px","aspectRatio":"1","scale":"cover","sizeSlug":"thumbnail","linkDestination":"none","style":{"border":{"radius":"100px"}}} -->
			<figure class="wp-block-image aligncenter size-thumbnail is-resized has-custom-border">
				<img alt="" style="border-radius:100px;aspect-ratio:1;object-fit:cover;width:60px" />
			</figure>
			<!-- /wp:image -->

			<!-- wp:paragraph {"align":"center","style":{"spacing":{"margin":{"top":"var:preset|spacing|10","bottom":"0"}}}} -->
			<!-- /wp:paragraph -->

			<!-- wp:paragraph {"align":"center","style":{"typography":{"fontStyle":"normal","fontWeight":"300"}},"textColor":"contrast-3","fontSize":"small"} -->
			<!-- /wp:paragraph -->
		</div>
		<!-- /wp:group -->

	</div>
	<!-- /wp:group -->
</div>
<!-- /wp:group -->

} // namespace CharacterGeneratorDev
