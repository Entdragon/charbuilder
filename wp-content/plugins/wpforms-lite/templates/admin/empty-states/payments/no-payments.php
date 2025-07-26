<?php

namespace CharacterGeneratorDev {

/**
 * No Payments HTML template.
 *
 * @since 1.8.2
 *
 * @var string $cta_url URL for the "Go To All Forms" CTA button.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="wpforms-admin-empty-state-container wpforms-admin-no-payments">

	</a>

	<p class="wpforms-admin-no-forms-footer">
		printf(
			wp_kses( /* translators: %s - URL to the comprehensive guide. */
				__( 'Need some help? Check out our <a href="%s" rel="noopener noreferrer" target="_blank">comprehensive guide.</a>', 'wpforms-lite' ),
				[
					'a' => [
						'href'   => [],
						'rel'    => [],
						'target' => [],
					],
				]
			),
			esc_url(
				wpforms_utm_link(
					'https://wpforms.com/docs/using-stripe-with-wpforms-lite/',
					'Payments Dashboard',
					'Activated - Manage Payments Documentation'
				)
			)
		);
	?>
	</p>
</div>

} // namespace CharacterGeneratorDev
