<?php

namespace CharacterGeneratorDev {

/**
 * Title: Project layout
 * Slug: twentytwentyfour/gallery-project-layout
 * Categories: gallery, featured, portfolio
 * Viewport width: 1600
 * Description: A gallery section with a project layout with 2 images.
 */
?>

<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|50","right":"var:preset|spacing|50","bottom":"var:preset|spacing|50","left":"var:preset|spacing|50"},"margin":{"top":"0","bottom":"0"}},"elements":{"link":{"color":{"text":"var:preset|color|base-2"}}}},"backgroundColor":"contrast","textColor":"base-2","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull has-base-2-color has-contrast-background-color has-text-color has-background has-link-color" style="margin-top:0;margin-bottom:0;padding-top:var(--wp--preset--spacing--50);padding-right:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--50);padding-left:var(--wp--preset--spacing--50)">
	<!-- wp:columns {"align":"wide","style":{"spacing":{"blockGap":{"top":"var:preset|spacing|50","left":"var:preset|spacing|60"}}}} -->
	<div class="wp-block-columns alignwide">
		<!-- wp:column {"verticalAlignment":"stretch","width":"60%","style":{"spacing":{"padding":{"right":"0"}}}} -->
		<div class="wp-block-column is-vertically-aligned-stretch" style="padding-right:0;flex-basis:60%">
			<!-- wp:group {"style":{"dimensions":{"minHeight":"100%"}},"layout":{"type":"flex","orientation":"vertical","verticalAlignment":"space-between","justifyContent":"stretch"}} -->
			<div class="wp-block-group" style="min-height:100%">
				<!-- wp:image {"aspectRatio":"9/16","scale":"cover","sizeSlug":"large","linkDestination":"none","className":"is-style-rounded"} -->
				<figure class="wp-block-image size-large is-style-rounded">
				</figure>
				<!-- /wp:image -->

				<!-- wp:paragraph {"fontSize":"medium"} -->
				<!-- /wp:paragraph -->
			</div>
			<!-- /wp:group -->
		</div>
		<!-- /wp:column -->

		<!-- wp:column {"width":"40%"} -->
		<div class="wp-block-column" style="flex-basis:40%">
			<!-- wp:group {"layout":{"type":"constrained"}} -->
			<div class="wp-block-group">
				<!-- wp:paragraph {"style":{"typography":{"lineHeight":"1.2","fontStyle":"normal","fontWeight":"500"}},"fontSize":"large"} -->
				<!-- /wp:paragraph -->

				<!-- wp:spacer {"height":"var:preset|spacing|40","style":{"layout":{}}} -->
				<div style="height:var(--wp--preset--spacing--40)" aria-hidden="true" class="wp-block-spacer">
				</div>
				<!-- /wp:spacer -->

				<!-- wp:group {"layout":{"type":"default"}} -->
				<div class="wp-block-group">
					<!-- wp:paragraph {"fontSize":"medium"} -->
					<!-- /wp:paragraph -->

					<!-- wp:image {"aspectRatio":"9/16","scale":"cover","sizeSlug":"large","linkDestination":"none","className":"is-style-rounded"} -->
					<figure class="wp-block-image size-large is-style-rounded">
					</figure>
					<!-- /wp:image -->
				</div>
				<!-- /wp:group -->
			</div>
			<!-- /wp:group -->
		</div>
		<!-- /wp:column -->
	</div>
	<!-- /wp:columns -->
</div>
<!-- /wp:group -->

} // namespace CharacterGeneratorDev
