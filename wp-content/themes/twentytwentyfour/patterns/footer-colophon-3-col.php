<?php

namespace CharacterGeneratorDev {

/**
 * Title: Footer with colophon, 3 columns
 * Slug: twentytwentyfour/footer-colophon-3-col
 * Categories: footer
 * Block Types: core/template-part/footer
 * Description: A footer section with a colophon and 3 columns.
 */
?>

<!-- wp:group {"align":"wide","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignwide">
	<!-- wp:group {"align":"wide","style":{"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|50"}}}} -->
	<div class="wp-block-group alignwide" style="padding-top:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--50)">
		<!-- wp:image {"width":"40px","height":"auto","sizeSlug":"full","linkDestination":"none"} -->
		<figure class="wp-block-image size-full is-resized">
		</figure>
		<!-- /wp:image -->

		<!-- wp:separator {"className":"is-style-wide"} -->
		<hr class="wp-block-separator has-alpha-channel-opacity is-style-wide" />
		<!-- /wp:separator -->

		<!-- wp:columns {"style":{"spacing":{"padding":{"top":"var:preset|spacing|10"}}}} -->
		<div class="wp-block-columns" style="padding-top:var(--wp--preset--spacing--10)">
			<!-- wp:column {"width":"57%"} -->
			<div class="wp-block-column" style="flex-basis:57%">
				<!-- wp:heading {"fontSize":"x-large"} -->
				<!-- /wp:heading -->
			</div>
			<!-- /wp:column -->
			<!-- wp:column {"width":"30%"} -->
			<div class="wp-block-column" style="flex-basis:30%">
				<!-- wp:group {"style":{"spacing":{"blockGap":"var:preset|spacing|10"}},"layout":{"type":"flex","orientation":"vertical"}} -->
				<div class="wp-block-group">
					<!-- wp:heading {"level":3,"fontSize":"medium","fontFamily":"body"} -->
					<!-- /wp:heading -->
					<!-- wp:paragraph -->
					<!-- /wp:paragraph -->
				</div>
				<!-- /wp:group -->
			</div>
			<!-- /wp:column -->
			<!-- wp:column {"width":"30%"} -->
			<div class="wp-block-column" style="flex-basis:30%">
				<!-- wp:columns {"isStackedOnMobile":false} -->
				<div class="wp-block-columns is-not-stacked-on-mobile">
					<!-- wp:column -->
					<div class="wp-block-column">
						<!-- wp:group {"style":{"spacing":{"blockGap":"var:preset|spacing|10"}},"layout":{"type":"flex","orientation":"vertical"}} -->
						<div class="wp-block-group">
							<!-- wp:heading {"level":3,"fontSize":"medium","fontFamily":"body"} -->
							<!-- /wp:heading -->
							<!-- wp:paragraph -->
							<!-- /wp:paragraph -->
						</div>
						<!-- /wp:group -->
					</div>
					<!-- /wp:column -->
				</div>
				<!-- /wp:columns -->
			</div>
			<!-- /wp:column -->
		</div>
		<!-- /wp:columns -->

		<!-- wp:spacer {"height":"var:preset|spacing|50"} -->
		<div style="height:var(--wp--preset--spacing--50)" aria-hidden="true" class="wp-block-spacer"></div>
		<!-- /wp:spacer -->

		<!-- wp:group {"layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between","verticalAlignment":"top"}} -->
		<div class="wp-block-group">
			<!-- wp:group {"style":{"spacing":{"blockGap":"6px"}},"layout":{"type":"flex","flexWrap":"wrap"}} -->
			<div class="wp-block-group">
				<!-- wp:paragraph {"fontSize":"small"} -->
				<!-- /wp:paragraph -->
				<!-- wp:site-title {"level":0,"style":{"typography":{"fontStyle":"normal","fontWeight":"400"}},"fontSize":"small"} /-->
			</div>
			<!-- /wp:group -->
			<!-- wp:paragraph {"fontSize":"small"} -->
			<p class="has-small-font-size">
				/* Translators: WordPress link. */
				$wordpress_link = '<a href="' . esc_url( __( 'https://wordpress.org', 'twentytwentyfour' ) ) . '" rel="nofollow">WordPress</a>';
				echo sprintf(
					/* Translators: Designed with WordPress */
					esc_html__( 'Designed with %1$s', 'twentytwentyfour' ),
					$wordpress_link
				);
				?>
			</p>
			<!-- /wp:paragraph -->
		</div>
		<!-- /wp:group -->

	</div>
	<!-- /wp:group -->
</div>
<!-- /wp:group -->

} // namespace CharacterGeneratorDev
