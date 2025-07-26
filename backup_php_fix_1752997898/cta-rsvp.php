<?php

namespace CharacterGeneratorDev {

/**
 * Title: RSVP
 * Slug: twentytwentyfour/cta-rsvp
 * Categories: call-to-action, featured
 * Viewport width: 1100
 * Description: A large RSVP heading sideways, a description, and a CTA button.
 */
?>

<div class="wp-block-group alignfull has-accent-5-background-color has-background" style="margin-top:0;margin-bottom:0;padding-top:var(--wp--preset--spacing--50);padding-right:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--50);padding-left:var(--wp--preset--spacing--50)">
	<!-- wp:columns {"align":"wide","style":{"spacing":{"blockGap":{"top":"var:preset|spacing|50","left":"var:preset|spacing|30"}}}} -->
	<div class="wp-block-columns alignwide">
		<!-- wp:column {"verticalAlignment":"stretch","width":"40%"} -->
		<div class="wp-block-column is-vertically-aligned-stretch" style="flex-basis:40%">
			<!-- wp:group {"style":{"dimensions":{"minHeight":"100%"},"spacing":{"blockGap":"var:preset|spacing|50"}},"layout":{"type":"flex","orientation":"vertical","justifyContent":"left","verticalAlignment":"space-between"}} -->
			<div class="wp-block-group" style="min-height:100%">

				<!-- wp:heading {"textAlign":"right","level":2,"style":{"typography":{"fontSize":"12rem","writingMode":"vertical-rl","lineHeight":"1"},"spacing":{"margin":{"right":"0","left":"calc( var(--wp--preset--spacing--20) * -1)"}}}} -->
				<!-- /wp:heading -->

				<!-- wp:group {"layout":{"type":"constrained","contentSize":"300px","justifyContent":"left"}} -->
				<div class="wp-block-group">
					<!-- wp:paragraph {"style":{"layout":{"selfStretch":"fixed","flexSize":"50%"}}} -->
					<!-- /wp:paragraph -->

					<!-- wp:buttons -->
					<div class="wp-block-buttons">
						<!-- wp:button -->
						<div class="wp-block-button">
						</div>
						<!-- /wp:button -->
					</div>
					<!-- /wp:buttons -->
				</div>
				<!-- /wp:group -->
			</div>
			<!-- /wp:group -->
		</div>
		<!-- /wp:column -->

		<!-- wp:column {"verticalAlignment":"top","width":"60%"} -->
		<div class="wp-block-column is-vertically-aligned-top" style="flex-basis:60%">
			<!-- wp:image {"aspectRatio":"3/4","scale":"cover","sizeSlug":"large","linkDestination":"none","style":{"color":{"duotone":"var:preset|duotone|duotone-5"}},"className":"is-style-rounded"} -->
			<figure class="wp-block-image size-large is-style-rounded">
			</figure>
			<!-- /wp:image -->
		</div>
		<!-- /wp:column -->
	</div>
	<!-- /wp:columns -->
</div>
<!-- /wp:group -->

} // namespace CharacterGeneratorDev
